#!/usr/bin/env python3
"""exec <command...> with INT/HUP/QUIT/TERM/PIPE reset to their defaults.
A suite launched as a background job (or under nohup) inherits 'signal ignored', which bash cannot undo;
the scripts under test must see the same dispositions an operator's foreground terminal gives them."""
import os, signal, sys
for s in (signal.SIGINT, signal.SIGHUP, signal.SIGQUIT, signal.SIGTERM, signal.SIGPIPE):
    signal.signal(s, signal.SIG_DFL)
os.execvp(sys.argv[1], sys.argv[1:])
