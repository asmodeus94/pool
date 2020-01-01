<?php

namespace Pool;


use Pool\Job\AbstractJob;
use Pool\Job\JobConfig;
use Pool\Job\JobException;
use Pool\Traits\ProcessManagementTrait;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

class Pool
{
    use ProcessManagementTrait;

    /**
     * @var int
     */
    protected $jobsCounter = 0;

    /**
     * @var JobConfig[]
     */
    protected $jobs = [];

    /**
     * @var int|null
     */
    protected $maxChildren;

    /**
     * @var array
     */
    protected $PIDs = [];

    /**
     * Pool constructor.
     *
     * @param int|null $maxChildren Number specifying the maximum number of children's processes running
     *
     * @throws RuntimeException
     */
    public function __construct(?int $maxChildren = null)
    {
        if ('cli' !== php_sapi_name()) {
            throw new RuntimeException(__CLASS__ . ' class is available only in CLI mode');
        }

        $this->maxChildren = $maxChildren <= 0 ? null : $maxChildren;
    }

    /**
     * Sets job
     *
     * @param JobConfig $job Job configuration
     *
     * @return $this
     */
    public function setJob(JobConfig $job): Pool
    {
        $this->jobs[$this->jobsCounter++] = $job;

        return $this;
    }

    /**
     * Sets jobs
     *
     * @param JobConfig[] $jobs Array of jobs configuration
     *
     * @return Pool
     */
    public function setJobs(array $jobs = []): Pool
    {
        foreach ($jobs as $job) {
            $this->setJob($job);
        }

        return $this;
    }

    /**
     * Waiting for the specified process to complete
     *
     * @param int $PID Process Id or 0 to identify any child of the current process
     *
     * @return int
     *
     * @see pcntl_waitpid
     */
    protected function wait(int $PID): int
    {
        $PID = pcntl_waitpid($PID, $status);
        unset($this->PIDs[$PID]);

        return $PID;
    }

    /**
     * Awaiting completion of all processes
     *
     * @return void
     */
    protected function synchronize(): void
    {
        while (-1 !== $this->wait(0)) ;
    }

    /**
     * Runs the assigned tasks in separate child processes.
     * Returns an array with jobs numbers (0..n-1) along with the PIDs of child processes
     *
     * @return array
     * @throws JobException|ReflectionException
     */
    public function run(): array
    {
        $jobsHistory = [];
        $numberOfJobs = count($this->jobs);
        $this->maxChildren = $this->maxChildren ?? $numberOfJobs;

        for ($jobId = 0; $jobId < $numberOfJobs; $jobId++) {
            if (count($this->PIDs) >= $this->maxChildren) {
                $this->wait(0);
            }

            $PID = pcntl_fork();

            if ($PID) {
                if ($PID < 0) {
                    continue;
                } else {
                    $jobsHistory[$jobId] = $this->PIDs[$PID] = $PID;
                }
            } else {
                $job = $this->jobs[$jobId];
                $className = $job->getClassName();
                $data = $job->getData();
                $constructorArguments = $job->getConstructorArguments();

                /** @var AbstractJob $jobObject */
                $jobObject = empty($constructorArguments) ? new $className()
                    : call_user_func_array([new ReflectionClass($className), 'newInstance'], $constructorArguments);

                if (null !== $data) {
                    $jobObject->setData($data);
                }

                $jobObject->setJobId($jobId);

                try {
                    $jobObject->run();
                } catch (JobException $e) {
                    $e->modifyMessage($jobId, $jobObject->getPID());
                    throw $e;
                }

                exit;
            }
        }

        $this->synchronize();

        return $jobsHistory;
    }
}
