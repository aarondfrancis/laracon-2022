<?php
/**
 * @author Aaron Francis <aarondfrancis@gmail.com|https://twitter.com/aarondfrancis>
 */

namespace App;

use Aws\Result;
use GuzzleHttp\Promise\Each;
use Hammerstone\Sidecar\PHP\LaravelLambda;
use Hammerstone\Sidecar\Sidecar;
use Illuminate\Contracts\Console\Kernel;
use ParaTest\Runners\PHPUnit\BaseRunner;
use ParaTest\Runners\PHPUnit\RunnerInterface;
use ParaTest\Runners\PHPUnit\Suite;
use PHPUnit\TextUI\Command;

class SidecarRunner extends BaseRunner implements RunnerInterface
{
    protected $tests = [];

    protected function beforeLoadChecks(): void
    {
        //
    }

    protected function max()
    {
        return $this->options->processes();
    }

    protected function doRun(): void
    {
        $this->createApp();
        $this->runPendingTasks();
    }

    private function createApp()
    {
        $app = require getcwd() . '/bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function yieldPromises()
    {
        $phpunit = $this->options->phpunit();
        $phpunitOptions = $this->options->filtered();

        foreach ($this->pending as $test) {
            $test = new Suite(
                $test->getPath(),
                $test->getFunctions(),
                $this->options->hasCoverage(),
                $this->options->hasLogTeamcity(),
                '/tmp'
            );

            $passthru = $this->options->passthru();
            $phpunitOptions['do-not-cache-result'] = null;
            $base = base_path();
            $this->tests[] = $test;

            yield LaravelLambda::executeAsync(function () use ($test, $phpunit, $phpunitOptions, $passthru, $base) {
                $arguments = $test->commandArguments($phpunit, $phpunitOptions, $passthru);

                $arguments = array_map(function ($arg) use ($base) {
                    return str_replace($base, getcwd(), $arg);
                }, $arguments);

                $command = new Command;
                $command->run($arguments, false);

                $contents = file_get_contents($test->getTempFile());

                return str_replace(getcwd(), $base, $contents);
            })->rawPromise();
        }
    }

    private function runPendingTasks(): void
    {
        // For our demo, the environment we deployed is `local`.
        // When tests are running, the environment is `testing`
        // and so the function cannot be found.
        Sidecar::overrideEnvironment('local');

        // `ofLimit` will run all of our promises, but limits the
        // max concurrency to the second param. The third and
        // fourth functions are the progress functions.
        Each::ofLimit($this->yieldPromises(), $this->max(), [$this, 'flush'], [$this, 'flush'])->wait();
    }

    public function flush(Result $result, $index)
    {
        $test = $this->tests[$index];

        $result = json_decode((string) $result->get('Payload'));
        file_put_contents($test->getTempFile(), $result);
        $this->printer->printFeedback($test);
    }

}
