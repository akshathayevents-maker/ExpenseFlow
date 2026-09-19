#!/usr/bin/env python3
"""Drive an INTERACTIVE bash inside a real pty (so job control is real) and press Ctrl+Z / Ctrl+C
while a command runs. Reports process states so a test can prove nothing is left stopped (T).

usage: pty_job_control.py <ctrl-z|ctrl-c> <trigger-regex> <timeout> -- <shell command line>
Prints:  STATES_AFTER_KEY=<comma list of `ps` stat letters of the command's process tree>
         JOBS=<output of `jobs`>   EXIT_MARK=<exit status echoed after completion>
Exit 0 always (the caller inspects the output)."""
import os, pty, re, select, subprocess, sys, time, signal

mode, trigger, tmo = sys.argv[1], sys.argv[2], float(sys.argv[3])
cmd = " ".join(sys.argv[sys.argv.index("--") + 1:])
key = b"\x1a" if mode == "ctrl-z" else b"\x03"

pid, fd = pty.fork()
if pid == 0:
    for _s in (signal.SIGINT, signal.SIGHUP, signal.SIGQUIT, signal.SIGTERM, signal.SIGPIPE):
        signal.signal(_s, signal.SIG_DFL)      # a background-launched suite inherits "ignored"; a real terminal does not
    os.execvp("bash", ["bash", "--norc", "--noprofile", "-i"])
buf = b""
def pump(t):
    global buf
    end = time.time() + t
    while time.time() < end:
        r, _, _ = select.select([fd], [], [], 0.1)
        if r:
            try: d = os.read(fd, 65536)
            except OSError: return False
            if not d: return False
            buf += d
    return True

def tree_states(root):
    out = subprocess.run(["ps", "-eo", "pid,ppid,pgid,sid,stat,comm"], capture_output=True, text=True).stdout.splitlines()[1:]
    rows = [l.split(None, 5) for l in out]
    kids = {}
    for r in rows: kids.setdefault(int(r[1]), []).append(r)
    res, stack = [], [root]
    while stack:
        p = stack.pop()
        for r in kids.get(p, []):
            res.append(r); stack.append(int(r[0]))
    return res

pump(0.6)
os.write(fd, b"PS1='' ; PROMPT_COMMAND=''\n"); pump(0.3)
os.write(fd, (cmd + " ; echo EXIT_MARK=$?\n").encode())
deadline = time.time() + tmo
while time.time() < deadline and not re.search(trigger.encode(), buf):
    if not pump(0.2): break
if not re.search(trigger.encode(), buf):
    print("TRIGGER_NOT_SEEN"); print(buf.decode(errors="replace")[-800:]); sys.exit(0)
time.sleep(0.4)
os.write(fd, key)
pump(1.5)
states = [f"{r[5]}:{r[4]}" for r in tree_states(pid)]
print("STATES_AFTER_KEY=" + ",".join(states))
os.write(fd, b"jobs\n"); pump(0.5)
jobs_txt = re.findall(rb"\[\d+\][+-]?\s+\S+.*", buf[-600:])
print("JOBS=" + (jobs_txt[-1].decode(errors="replace").strip() if jobs_txt else "(none)"))
# let it finish (or resume a stopped job so the test can clean up)
if mode == "ctrl-z":
    os.write(fd, b"fg\n"); pump(0.3)
end = time.time() + tmo
while time.time() < end and not re.search(rb"EXIT_MARK=\d", buf):   # digits: the echoed command line also contains "EXIT_MARK="
    if not pump(0.3): break
m = re.search(rb"EXIT_MARK=(\d+)", buf)
print("EXIT_MARK=" + (m.group(1).decode() if m else "none"))
os.write(fd, b"exit\n"); pump(0.3)
try: os.kill(pid, signal.SIGKILL)
except ProcessLookupError: pass
if os.environ.get("PTY_LAST_OUTPUT"): open(os.environ["PTY_LAST_OUTPUT"], "wb").write(buf)   # debugging aid, opt-in
