<?php

use Daycry\CronJob\Job;
use Daycry\CronJob\JobRunner;
use CodeIgniter\Test\CIUnitTestCase as TestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @internal
 */
final class JobRunnerTest extends TestCase
{
    use DatabaseTestTrait;

    protected $refresh   = true;
    protected $namespace = 'Sparks\Settings';
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = new \Daycry\CronJob\Config\CronJob();
        $this->config->logPerformance = true;
        $this->config->logSavingMethod = 'database';
    }

    public function testRunWithNoTasks()
    {
        $this->assertNull($this->getRunner()->run());
    }

    public function testRunWithSuccess()
    {
        $config = config('CronJob');

        $task1 = (new Job('closure', static function () {
            echo 'Task 1';
        }))->daily('12:05 am', true)->named('task1');
        $task2 = (new Job('closure', static function () {
            echo 'Task 2';
        }))->daily('12:00 am')->named('task2');

        ob_start();

        $runner = $this->getRunner([$task1, $task2]);

        $time = ( new \DateTime('now') )->setTime(00, 00)->format('Y-m-d H:i:s');
        $runner->withTestTime($time)->run();

        // Only task 2 should have ran
        $this->assertSame('Task 2', $this->getActualOutput());

        ob_end_clean();

        $this->assertTrue(is_dir($config->FilePath));
        $this->assertTrue(is_file($config->FilePath . 'jobs_' . date('Y-m-d') . '.json'));
    }

    protected function getRunner(array $tasks = [])
    {
        $scheduler = service('scheduler');
        $this->setPrivateProperty($scheduler, 'tasks', $tasks);
        \Config\Services::injectMock('scheduler', $scheduler);

        return new JobRunner();
    }
}