# What is this
This is a simple library that implements methods usefull in creating console applications.

## Features

- Maintaining constant TPS (Ticks Per Second)
- Running code in loop, multiple tasks can be defined
- Events emmited on: 
  - Initialization complete
  - OS Signals received
  - Cleanup started (Exiting, but before error logs are collected)
  - Exit (Last moment to execute any code)

## Example

```php
// This is a dependency Event manager, or any that implements interface
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