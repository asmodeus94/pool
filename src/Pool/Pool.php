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
     * @param int|null $maxChildren Liczba określająca maksymalna liczbę uruchomionych procesów-dzieci
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
     * Ustawia zadanie
     *
     * @param JobConfig $job Konfiguracja zadania
     *
     * @return $this
     */
    public function setJob(JobConfig $job): Pool
    {
        $this->jobs[$this->jobsCounter++] = $job;

        return $this;
    }

    /**
     * Ustawia zadania
     *
     * @param JobConfig[] $jobs Tablica konfiguracji zadań
     *
     * @return void
     */
    public function setJobs(array $jobs = []): void
    {
        foreach ($jobs as $job) {
            $this->setJob($job);
        }
    }

    /**
     * Czeka na zakończenie podanego procesu
     *
     * @param int $PID Id procesu lub -1 oznaczające dowolne dziecko bieżącego procesu
     *
     * @return void
     *
     * @see pcntl_waitpid
     */
    protected function wait(int $PID): void
    {
        $PID = pcntl_waitpid($PID, $status);
        unset($this->PIDs[$PID]);
    }

    /**
     * Oczekuje na zakończenie wszystkich procesów
     *
     * @return void
     */
    protected function synchronize(): void
    {
        foreach ($this->PIDs as $PID) {
            $this->wait($PID);
        }
    }

    /**
     * Uruchamia zlecone zadania w osobnych procesach-dzieciach.
     * Zwraca tablicę z numerami zadań (o numerach 0..n-1) wraz z przypisanymi do nich PIDami procesów-dzieci
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
                $this->wait(-1);
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
