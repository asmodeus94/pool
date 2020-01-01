<?php

namespace Pool\Tests\Fixtures;

use Pool\Job\AbstractJob;
use Pool\Job\JobException;

class Job extends AbstractJob
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
    }

    /**
     * @return void
     * @throws JobException
     */
    public function run(): void
    {
        $nOfEle = (int)$this->getData();
        $this->setName($this->getName() . ' ' . $this->param1 . ' ' . $this->param2 . ' ' . $nOfEle);
        $text = "job {$this->getJobId()} {$this->getName()} with PID {$this->getPID()} and parent PID {$this->getParentPID()} ";
        echo $text . PHP_EOL;

        if (4 === $nOfEle) {
            throw new JobException('An error occurred test...');
        }

        $nOfEle = 10 ** $nOfEle;
        for ($i = 0; $i < $nOfEle; $i++) {
            md5(uniqid('', true));
        }
    }
}
