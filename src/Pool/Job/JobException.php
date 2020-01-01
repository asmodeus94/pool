<?php

namespace Pool\Job;


use Exception;

class JobException extends Exception
{
    /**
     * Modifies the exception message
     *
     * @param int $jobId Job id
     * @param int $PID   Process id
     *
     * @return void
     */
    public function modifyMessage(int $jobId, int $PID): void
    {
        $this->message = "For job $jobId with PID $PID a " . __CLASS__ . " has been thrown:" . PHP_EOL . $this->getMessage();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return preg_replace('/^' . str_replace('\\', '\\\\', __CLASS__) . ': /', '', parent::__toString(), 1);
    }
}
