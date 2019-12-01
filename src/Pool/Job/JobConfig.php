<?php

namespace Pool\Job;


use ReflectionClass;
use ReflectionException;
use RuntimeException;

class JobConfig
{
    /**
     * Nazwa klasy zadania
     *
     * @var string
     */
    private $className;

    /**
     * Dane, które zostaną przekazane do zadania w chwili jego powołania
     *
     * @var mixed
     */
    private $data;

    /**
     * Tablica parametrów, jaka zostanie przekazana do konstruktora zadania, zanim te zostanie wywołane
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
     *
     * @throws ReflectionException
     */
    public function __construct(string $className, $dataSet = null, array $constructorArguments = [])
    {
        $this->setClassName($className);
        $this->setData($dataSet);
        $this->setConstructorArguments($constructorArguments);
    }

    /**
     * Zwraca nazwę klasy
     *
     * @return string
     */
    public function getClassName(): string
    {
        return $this->className;
    }

    /**
     * Ustawia nazwę klasy, której obiekt zostanie powołona do życia w ramach zadania
     *
     * @param string $className
     *
     * @return $this
     * @throws ReflectionException
     */
    public function setClassName(string $className): JobConfig
    {
        $reflection = new ReflectionClass($className);
        if (!$reflection->isSubclassOf(AbstractJob::class)) {
            throw new RuntimeException(sprintf('Job class (%s) must be an instance of %s', $className, AbstractJob::class));
        }

        $this->className = $className;

        return $this;
    }

    /**
     * Zwraca dane przekazane do zadania
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Ustawia dane jakie chcemy przekazać do zadania
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = is_array($data) && empty($data) ? null : $data;

        return $this;
    }

    /**
     * Zwraca tablicę argumentów przekazanych do konstruktora zadania
     *
     * @return array
     */
    public function getConstructorArguments(): array
    {
        return $this->constructorArguments;
    }

    /**
     * Ustawia tablicę argumentów dla konstruktora zadania
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
