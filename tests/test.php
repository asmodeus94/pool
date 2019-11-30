<?php
require __DIR__ . '/../../../autoload.php';
require 'Job.php';

$pool = new \Pool\Pool(2);
$pool->setJobs(array_fill(0, 4, Job::class), [4, 5, 6, 3]);

try {
    $pool->run();
} catch (\Pool\Job\JobException $e) {
    echo $e->getMessage() . PHP_EOL;
}
