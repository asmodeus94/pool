<?php

namespace Pool;


use Pool\Job\AbstractJob;
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
    protected static $jobsCounter;

    /**
     * @var array
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
     */
    public function __construct(?int $maxChildren = null)
    {
        $this->maxChildren = $maxChildren <= 0 ? null : $maxChildren;
        self::$jobsCounter = 0;
    }

    /**
     * Ustawia zadanie
     *
     * @param string $jobClassName Nazwa klasy zadania
     * @param mixed  $data         Dane, które zostaną przekazane do zadania w chwili jego powołania
     *
     * @return $this
     * @throws ReflectionException|RuntimeException
     */
    public function setJob(string $jobClassName, $data = null): Pool
    {
        $reflection = new ReflectionClass($jobClassName);
        if (!$reflection->isSubclassOf(AbstractJob::class)) {
            throw new RuntimeException(sprintf('Job class (%s) must be an instance of %s', $jobClassName, AbstractJob::class));
        }

        $this->jobs[self::$jobsCounter++] = [$jobClassName, is_array($data) && empty($data) ? null : $data];

        return $this;
    }

    /**
     * Ustawia zadania
     *
     * @param array $jobClassesNames Nazwy klas, pełniące rolę zadań
     * @param array $dataSet         Parametry odpowiadające kolejnym klasom, które przekazane zostaną do zadania
     *
     * @return void
     * @throws ReflectionException|RuntimeException
     */
    public function setJobs(array $jobClassesNames = [], array $dataSet = []): void
    {
        if (count($jobClassesNames) !== count($dataSet)) {
            throw new RuntimeException('Number of arguments in both arrays must be identical');
        }

        foreach ($jobClassesNames as $index => $jobClassName) {
            $this->setJob($jobClassName, $dataSet[$index]);
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
    private function wait(int $PID): void
    {
        $PID = pcntl_waitpid($PID, $status);
        unset($this->PIDs[$PID]);
    }

    /**
     * Oczekuje na zakończenie wszystkich procesów
     *
     * @return void
     */
    private function synchronize(): void
    {
        foreach ($this->PIDs as $PID) {
            $this->wait($PID);
        }
    }

    /**
     * Uruchamia zlecone zadania w osobnych procesach-dzieciach.
     * Zwraca tablicę z numerami zadań (0..n-1) wraz z przypisanymi do nich PIDami procesów-dzieci
     *
     * @return array
     * @throws JobException
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
                [$className, $data] = $this->jobs[$jobId];

                /** @var AbstractJob $jobObject */
                $jobObject = new $className();

                if (null !== $data) {
                    $jobObject->setData($data);
                }

                $jobObject->setJobId($jobId);

                try {
                    $jobObject->make();
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
