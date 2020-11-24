<?php

namespace Pool\Job;

use RuntimeException;

class JobConfig
{
    /**
     * Job class name
     *
     * @var string
     */
    private $className;

    /**
     * Data to be provided to the job at the time of its call
     *
     * @var mixed
     */
    private $data;

    /**
     * An array of parameters that will be passed to the job constructor before its called
     *
     * @var array
     */
    private $constructorArguments;


    /**
     * JobConfig constructor.
     *
     * @param string $className
     * @param mixed  $dataSet
     * @param mixed  $constructorArguments
     */
    public function __construct(string $className, $dataSet = null, array $constructorArguments = [])
    {
        $this->setClassName($className);
        $this->setData($dataSet);
        $this->setConstructorArguments($constructorArguments);
    }

    /**
     * Returns the class name
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Sets the name of the class whose object will be brought to life in the job
     *
     * @param string $className
     *
     * @return $this
     */
    public function setClassName(string $className): JobConfig
    {
        if (!is_subclass_of($className, AbstractJob::class)) {
            throw new RuntimeException(sprintf('Job class %s must inherit from %s', $className, AbstractJob::class));
        }

        $this->className = $className;

        return $this;
    }

    /**
     * Returns the data passed to the job
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data we want to pass to the job
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data): JobConfig
    {
        $this->data = is_array($data) && empty($data) ? null : $data;

        return $this;
    }

    /**
     * Returns an array of arguments given to the job's constructor
     *
     * @return array
     */
    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    /**
     * Sets up an array of arguments for the job constructor
     *
     * @param array $constructorArguments
     *
     * @return $this
     */
    public function setConstructorArguments(array $constructorArguments): JobConfig
    {
        $this->constructorArguments = $constructorArguments;

        return $this;
    }
}
