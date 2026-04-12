# What is this
This is a simple library that implements methods usefull in creating console applications.

## Features

- Maintaining constant TPS (Ticks Per Second)
- Running code in loop, multiple tasks can be defined
- Basic event manager
- Events emmited on: 
  - Initialization complete
  - OS Signals received
  - Cleanup started (Exiting, but before error logs are collected)
  - Exit (Last moment to execute any code)

## Examples

### Event manager

Q: Why is it in this repo and not as a separate project/package?\
A: No good reason really, it should be there but I don't have time to maintain another repo

IMPORTANT - Consumer methods / callables require a type. This type is used to figure out on
what event should consumer be executed. 
Missing type will throw Exception.
If you want to execute for any Event - use `EventInterface`.
EventManager uses `instanceof` to check if event can be used for a consumer

```php
$logger = new BasicLogger(__DIR__.'/logs.log');
// Initilize new instance
// Logger is required to log uncaught exceptions from each consumer. 
// One consumer's exception doesn't have to stop others
$eventManager = new EventManager($logger);

// Optional, set by default
// This will cause execution of consumers at the moment of queueEvent()
$eventManager->setConsumeImmediately(true);

// If you want...
$eventManager->setThrowOnFirstConsumerError(true);
// It will use logger first and then throw

// Anonymous classes can be registered - as well as just objects implementing the ConsumerInterface
$eventManager->addListener(new class implements ConsumerInterface {
    public function consumeEvent(EventInterface $event)
    {
        echo "[ANY EVENT] ";
    }
});

// Callables can be registered
$eventManager->addListener(function(EventInitialized $event) use($bootstrap) {
    echo "[INITIALIZED, running at {$bootstrap->getTargetTPS()} TPS]\n";
});

// And you post / execute consumers
$eventManager->queueEvent(new \Siarko\CliBootstrap\Events\EventInitialized());

// Also, if setConsumeImmediately(false), consumers won't be executed.
// Events will be queued and then consumers will execute at once with
$eventManager->consumeEvents();

// If you want to postpone execution of a selected event, use
$eventManager->queueEvent(new \Siarko\CliBootstrap\Events\EventInitialized(), postpone: true);
//It will execute the consumers for this event either with next normal event execution of with
$eventManager->consumeEvents();
```

### Bootstrap

```php
$eventManager = new EventManager();
// Logger is also a part of this lib; but very basic
$logger = new BasicLogger(__DIR__.'/logs.log');

$bootstrap = new Bootstrap($eventManager, $logger);

// Set how much times to execute tasks per second
$bootstrap->setTargetTPS(2);


/** @var $event SignalEvent */
$eventManager->addListener(function(SignalEvent $event) use($bootstrap){
    // CTRL + C -> We should stop running
    if($event->getData() === SIGINT) {
        echo "[INTERRUPT]\n";
        // This is how we stop
        $bootstrap->stop();
    }
});

$eventManager->addListener(function(CleanupEvent $event) {
    // This will be called almost always
    echo "[CLEANUP EVENT]\n";
});

$eventManager->addListener(function(ExitEvent $event) {
    // This will be called almost always - last event before exiting
    echo "[EXIT EVENT]\n";
});

$bootstrap->addTask(function () use ($bootstrap){
    // Just to slow down the logs
    usleep(200000);
    echo "ITERATION\n";
    echo $bootstrap->getLoopTime().", TPS: ". $bootstrap->getCurrentTPS()."\n";
});

// Start loop execution. Will block until loop is stopped
// Either with $bootstrap->stop(), with error or however else.
$bootstrap->run();
```

### Extending / Development

This repo ships with docker compose configuration to ease development.
Build container: `.docker/build.sh`
Start container: `.docker/run.sh`

Dockerfile already has a `XDEBUG_TRIGGER=1` flag, so all executions should work with xdebug OOTB.
As a basic example, there's a `cli.php` script that I used for testing in `dev` directory.

### AI

No AI was used for creation of this code.