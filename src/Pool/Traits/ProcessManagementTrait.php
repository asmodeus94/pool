<?php

namespace Pool\Traits;


trait ProcessManagementTrait
{
    /**
     * Id bieżącego procesu
     *
     * @var int
     */
    private $PID;

    /**
     * Id rodzica bieżącego procesu
     *
     * @var int
     */
    private $parentPID;

    /**
     * Pobiera PID bieżącego procesu
     *
     * @return int
     */
    public function getPID(): int
    {
        if (!isset($this->PID)) {
            $this->PID = posix_getpid();
        }

        return $this->PID;
    }

    /**
     * Zwraca PID rodzica bieżącego procesu
     *
     * @return int
     */
    public function getParentPID(): int
    {
        if (!isset($this->parentPID)) {
            $this->parentPID = posix_getppid();
        }

        return $this->parentPID;
    }

    /**
     * Ustawia nazwę bieżącego procesu
     *
     * @param string $title
     *
     * @return bool
     */
    public function setName(string $title): bool
    {
        return cli_set_process_title($title);
    }

    /**
     * Zwraca nazwę bieżącego procesu
     *
     * @return string
     */
    public function getName(): string
    {
        return cli_get_process_title();
    }
}
