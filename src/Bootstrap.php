<?php

namespace Siarko\CliBootstrap;

use Psr\Log\LoggerInterface;
use Siarko\CliBootstrap\Events\CleanupEvent;
use Siarko\CliBootstrap\Events\EventInitialized;
use Siarko\CliBootstrap\Events\ExitEvent;
use Siarko\CliBootstrap\Events\SignalEvent;
use Siarko\CliBootstrap\Exception\FabricatedException;
use Throwable;

class Bootstrap
{
    private bool $run = true;
    private bool $cleanedUp = false;

    private int $targetTPS = 100;
    private float $currentTPS = 0.0;
    private float $loopTime = 0;

    public function __construct(
        private readonly EventManager $eventManager,
        private readonly LoggerInterface $logger,
        private array $tasks = [],
        private readonly array $signals = [
            SIGTSTP, //CTRL + Z
            SIGINT, //CTRL + C
            SIGUSR1,
            SIGUSR2,
            SIGWINCH // TERMINAL RESIZED
        ]
    )
    {
        $this->configureSignals();
        $this->eventManager->queueEvent(new EventInitialized(), postpone: true);
    }

    protected function configureSignals(): void
    {
        pcntl_async_signals(true);

        register_shutdown_function(function () {
            $this->cleanup();
        });

        foreach ($this->signals as $signal) {
            pcntl_signal($signal, fn() => $this->eventManager->queueEvent(new SignalEvent($signal)));
        }
    }

    public function getTargetTPS(): int
    {
        return $this->targetTPS;
    }

    public function setTargetTPS(int $targetTPS): void
    {
        $this->targetTPS = $targetTPS;
    }

    public function getLoopTime(): float
    {
        return $this->loopTime;
    }

    public function getCurrentTPS(): float
    {
        return $this->currentTPS;
    }

    public function addTask(callable|TaskInterface $task, string $name = null): void
    {
        $this->tasks[$name ?? uniqid()] = $task;
    }

    public function run(): void
    {
        if (empty($this->tasks)) {
            throw new \RuntimeException("No tasks registered");
        }
        $this->eventManager->consumeEvents();
        try {
            $previousTime = microtime(true);
            while ($this->run) {
                $currentTime = microtime(true);
                $loopTime = $currentTime - $previousTime;
                $this->currentTPS = 1.0 / ($loopTime != 0 ? $loopTime : 1);
                $targetInterval = 1.0 / $this->targetTPS;

                if ($loopTime >= $targetInterval) {
                    $previousTime = $currentTime;
                    $runStart = microtime(true);
                    foreach ($this->tasks as $task) {
                        $this->runTask($task);
                    }
                    $this->loopTime = microtime(true) - $runStart;

                } else {
                    $sleepTime = ($targetInterval - $loopTime) * 1_000_000;
                    usleep((int) $sleepTime);
                }
            }
        } catch (\Throwable $e) {
            $this->cleanup($e);
        }
    }

    private function runTask(TaskInterface|callable $task): void
    {
        if ($task instanceof TaskInterface) {
            $task->execute();
        } else {
            $task();
        }
    }

    public function stop(): void
    {
        $this->run = false;
    }

    public function cleanup(?Throwable $exception = null): void
    {
        if ($this->cleanedUp) {
            return;
        }
        $errors = [];
        try{
            $this->eventManager->queueEvent(new CleanupEvent());
        }catch (\Throwable $e) {
            $errors[] = $e;
        }
        $this->cleanedUp = true;
        if ($this->run || $exception) {
            if ($exception) {
                $errors[] = $exception;
            } else {
                $data = error_get_last();
                if (is_array($data) && count($data)) {
                    $exception = new FabricatedException($data['message']);
                    $exception->setFile($data['file']);
                    $exception->setLine($data['line']);
                    $errors[] = $exception;
                } else {
                    $errors[] = new FabricatedException(serialize($data));
                }
            }
            foreach ($errors as $error) {
                $this->logger->critical("[UNCAUGHT] ".$error->getMessage(), [
                    'exception' => $error
                ]);
            }
        }
        $this->eventManager->queueEvent(new ExitEvent($errors));
    }

    private function createErrorData(Throwable $exception): array
    {
        return [
            'exception' => $exception,
        ];
    }
}