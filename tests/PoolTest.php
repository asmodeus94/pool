<?php

namespace Pool\Tests;

use Error;
use Exception;
use PHPUnit\Framework\TestCase;
use Pool\Job\JobConfig;
use Pool\Job\JobException;
use Pool\Pool;
use Pool\Tests\Fixtures\Job;

class PoolTest extends TestCase
{
    public function testCanSetJobs(): Pool
    {
        $pool = new Pool();
        foreach (range(1, 4) as $value) {
            $this->assertInstanceOf(JobConfig::class, $job = $this->createJobConfig($value + 1));
            $this->assertInstanceOf(Pool::class, $pool->setJob($job));
        }

        return $pool;
    }

    private function createJobConfig($data): JobConfig
    {
        return new JobConfig(Job::class, $data, [md5($data), md5($data ** 10)]);
    }

    /**
     * @depends testCanSetJobs
     *
     * @param Pool $pool
     */
    public function testCanRunPool(Pool $pool): void
    {
        $this->assertInstanceOf(Pool::class, $pool);
        $result = null;

        try {
            $result = $pool->run();
        } catch (JobException $e) {
            $this->assertStringContainsString('An error occurred test...', $e->getMessage());
            // This is a child process, so we need to call exit instead of return to avoid displaying a test summary
            exit;
        } catch (Exception|Error $e) {
            $this->fail('An inappropriate ' . ($e instanceof Exception ? 'exception' : 'error') . ' has been thrown - ' . JobException::class . ' is required');
        }

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
        foreach ($result as $jobId => $PID) {
            $this->assertIsInt($jobId);
            $this->assertIsInt($PID);
            $this->assertGreaterThan(0, $PID);
        }
    }

    public function testGetProcessName(): void
    {
        $pool = new Pool();

        $this->assertSame(basename($pool->getName()), 'phpunit');
    }

    public function testCanChangeProcessName(): void
    {
        $pool = new Pool();
        $this->assertTrue($pool->setName(__CLASS__));
        $newProcessName = $pool->getName() . '#2';
        $this->assertTrue($pool->setName($newProcessName));
        $this->assertSame($newProcessName, $pool->getName());
    }
}
