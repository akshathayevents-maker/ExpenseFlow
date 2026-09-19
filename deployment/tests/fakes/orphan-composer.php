<?php
// Test double: like composer, but leaves a detached process that inherits STDOUT (as a stray grandchild could),
// which keeps a `tee` logger alive after the deploy finishes. The deploy must still exit promptly.
if (in_array('install', $argv, true)) {
    proc_open(['sleep', '25'], [0 => ['file', '/dev/null', 'r'], 1 => STDOUT, 2 => STDERR], $pipes);   // not waited for
}
$args = implode(' ', array_map('escapeshellarg', array_slice($argv, 1)));
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(getenv('REAL_COMPOSER') ?: '/usr/local/bin/composer') . ' ' . $args, $rc);
exit($rc);
