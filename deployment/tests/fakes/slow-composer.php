<?php
// Test double: behaves like composer but sleeps first (SLOW_COMPOSER seconds) — holds a deploy in a known step.
$slow = in_array('install', $argv, true) ? (int) (getenv('SLOW_COMPOSER') ?: 6) : 0;   // only `install` is slowed; version probes are fast
if ($slow > 0) { fwrite(STDERR, "slow-composer: sleeping {$slow}s\n"); sleep($slow); }
$args = implode(' ', array_map('escapeshellarg', array_slice($argv, 1)));
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(getenv('REAL_COMPOSER') ?: '/usr/local/bin/composer') . ' ' . $args, $rc);
exit($rc);
