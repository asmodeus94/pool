<?php

namespace Pool\Traits;


trait ProcessManagementTrait
{
    /**
     * Returns the PID of the current process
     *
     * @return int
     */
    public function getPID(): int
    {
        return posix_getpid();
    }

    /**
     * Returns the PID of the parent of the current process
     *
     * @return int
     */
    public function getParentPID(): int
    {
        return posix_getppid();
    }

    /**
     * Sets the name of the current process
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
     * Returns the name of the current process
     *
     * @return string
     */
    public function getName(): string
    {
        return cli_get_process_title() ?: $_SERVER['argv'][0] ?? '';
    }
}
