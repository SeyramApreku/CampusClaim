<?php

$tests = glob(__DIR__ . '/*Test.php');
foreach ($tests as $test) {
    require $test;
}

fwrite(STDOUT, count($tests) . " test file(s) passed.\n");
