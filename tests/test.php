<?php
require __DIR__ . '/../../../autoload.php';
require __DIR__ . '/Job.php';

try {
    $pool = new \Pool\Pool(2);
    foreach ([4, 5, 6, 3] as $value) {
        $pool->setJob(new \Pool\Job\JobConfig(Job::class, $value, [md5($value), md5($value ** 10)]));
    }
    $pool->setName('master');
    var_dump($pool->run());
} catch (\Pool\Job\JobException|Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}
