<?php

namespace Siarko\CliBootstrap;


use Siarko\CliBootstrap\Events\CleanupEvent;
use Siarko\CliBootstrap\Events\ConsumerInterface;
use Siarko\CliBootstrap\Events\EventInitialized;
use Siarko\CliBootstrap\Events\EventInterface;
use Siarko\CliBootstrap\Events\ExitEvent;
use Siarko\CliBootstrap\Events\SignalEvent;
use Siarko\CliBootstrap\Logger\BasicLogger;

const PROJECT_ROOT = __DIR__.'/../..';
require_once PROJECT_ROOT . "/vendor/autoload.php";


$logger = new BasicLogger(__DIR__.'/logs.log');
$eventManager = new EventManager($logger);

$bootstrap = new Bootstrap($eventManager, $logger);
$bootstrap->setTargetTPS(2);

$eventManager->addListener(new class implements ConsumerInterface {
    public function consumeEvent(EventInterface $event)
    {
        echo "[ANY EVENT] ";
    }
});

$eventManager->addListener(function(EventInitialized $event) use($bootstrap) {
    echo "[INITIALIZED, running at {$bootstrap->getTargetTPS()} TPS]\n";
});

/** @var $event SignalEvent */
$eventManager->addListener(function(SignalEvent $event) use($bootstrap){
    if($event->getData() === SIGINT) {
        echo "[INTERRUPT]\n";
        $bootstrap->stop();
    }else{
        echo "[SIGNAL: {$event->getData()}]\n";
    }
});

$eventManager->addListener(function(ExitEvent $event) {
    echo "[EXIT EVENT]\n";
});

$eventManager->addListener(function(CleanupEvent $event) {
    echo "[CLEANUP EVENT]\n";
});

$bootstrap->addTask(function () use ($bootstrap){
    usleep(200000);
    echo "ITERATION\n";
    echo $bootstrap->getLoopTime().", TPS: ". $bootstrap->getCurrentTPS()."\n";
});

$bootstrap->run();