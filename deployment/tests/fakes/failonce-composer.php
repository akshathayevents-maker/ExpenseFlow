<?php
// Test double: `composer install` FAILS on its first call(s) (FAIL_COUNT, default 1; counter in $EF_TEST_STATE), then works.
$s = getenv('EF_TEST_STATE') . '/composer_calls';
$isInstall = in_array('install', $argv, true);
$n = 0;
if ($isInstall) { $n = (int) @file_get_contents($s); $n++; file_put_contents($s, (string) $n); }   // count only `install` calls
$failFirst = (int) (getenv('FAIL_COUNT') ?: 1);
if ($isInstall && $n <= $failFirst) { fwrite(STDERR, "failonce-composer: simulated composer failure (call $n)\n"); exit(2); }
$args = implode(' ', array_map('escapeshellarg', array_slice($argv, 1)));
passthru(escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(getenv('REAL_COMPOSER') ?: '/usr/local/bin/composer') . ' ' . $args, $rc);
exit($rc);
