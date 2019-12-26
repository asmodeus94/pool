<?php

namespace Pool\Traits;


trait ProcessManagementTrait
{
    /**
     * Pobiera PID bieżącego procesu
     *
     * @return int
     */
    public function getPID(): int
    {
        return posix_getpid();
    }

    /**
     * Zwraca PID rodzica bieżącego procesu
     *
     * @return int
     */
    public function getParentPID(): int
    {
        return posix_getppid();
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
