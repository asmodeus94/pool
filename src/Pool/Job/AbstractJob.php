<?php

namespace Pool\Job;


use Pool\Traits\ProcessManagementTrait;

abstract class AbstractJob
{
    use ProcessManagementTrait;

    /**
     * Dane odróżniające/sterujące bieżącym zadaniem
     *
     * @var mixed
     */
    private $data;

    /**
     * Id bieżącego zadania
     *
     * @var int
     */
    private $jobId;

    /**
     * UStawia dane nadające cechy odróżniające bieżące zadanie od innych
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
     * Zwraca dane bieżącego zadania
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Ustawia id bieżącego zadania
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
     * Zwraca id bieżącego zadania
     *
     * @return int
     */
    public function getJobId(): int
    {
        return $this->jobId;
    }

    /**
     * Uruchamia zlecone zadanie
     *
     * @return void
     * @throws JobException
     */
    abstract public function run(): void;
}
