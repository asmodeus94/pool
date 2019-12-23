<?php

use Pool\Job\JobException;

class Job extends \Pool\Job\AbstractJob
{
    /**
     * @var string
     */
    private $param1;

    /**
     * @var string
     */
    private $param2;

    /**
     * Job constructor.
     *
     * @param string $param1
     * @param string $param2
     */
    public function __construct(string $param1, string $param2)
    {
        $this->param1 = $param1;
        $this->param2 = $param2;
        echo "{$this->getPID()} param1: $param1; param2: $param2" . PHP_EOL;
    }

    /**
     * @return void
     * @throws JobException
     */
    public function run(): void
    {
        $nOfEle = (int)$this->getData();
        $this->setName($this->getName() . ' ' . $this->param1 . ' ' . $this->param2);
        $text = "job {$this->getJobId()} {$this->getName()} with PID {$this->getPID()} and parent PID {$this->getParentPID()} ";
        echo $text . 'started' . PHP_EOL;

        if (4 === $nOfEle) {
            throw new JobException('An error occurred test...');
        }

        $nOfEle = 10 ** $nOfEle;
        for ($i = 0; $i < $nOfEle; $i++) {
            md5(uniqid('', true));
        }

        sleep(5);
        echo $text . 'ended' . PHP_EOL;
        echo memory_get_peak_usage() / 1024 / 1024 . PHP_EOL;
    }
}
