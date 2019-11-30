<?php

use Pool\Job\JobException;

class Job extends \Pool\Job\AbstractJob
{
    /**
     * @return void
     * @throws JobException
     */
    public function make(): void
    {
        $nOfEle = (int)$this->getData();

        if (4 === $nOfEle) {
            throw new JobException('An error occurred...');
        }

        $nOfEle = 10 ** $nOfEle;
        $text = "job {$this->getJobId()} with PID {$this->getPID()} and parent PID {$this->getParentPID()} ";
        echo $text . 'started' . PHP_EOL;
        for ($i = 0; $i < $nOfEle; $i++) {
            md5(uniqid('', true));
        }
        echo $text . 'ended' . PHP_EOL;
    }
}
