<?php

namespace Pool\Job;


use Pool\Traits\ProcessManagementTrait;

abstract class AbstractJob
{
    use ProcessManagementTrait;

    /**
     * Data that distinguish/controls the current job
     *
     * @var mixed
     */
    private $data;

    /**
     * Id of current job
     *
     * @var int
     */
    private $jobId;

    /**
     * Sets data that gives characteristics that distinguish the current job from others
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data): AbstractJob
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Returns the data passed to the current job
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the id of the current job
     *
     * @param int $jobId
     *
     * @return $this
     */
    public function setJobId(int $jobId): AbstractJob
    {
        $this->jobId = $jobId;

        return $this;
    }

    /**
     * Returns the id of the current job
     *
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * Starts job
     *
     * @return void
     * @throws JobException
     */
    abstract public function run(): void;
}
