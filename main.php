<?php
require_once("vendor/autoload.php");

// use core\events\Libevent;
// use core\events\EventInterface;

// $event = new Libevent();

// $event->add(SIGUSR1, EventInterface::EV_SIGNAL, function ($fd, $event, $args = null){
//     print_r($fd);
//     echo "\n";
//     print_r($event);
//     echo "\n";
// });

// $event->add(SIGUSR2, EventInterface::EV_SIGNAL, function ($fd, $event, $args = null){
//     print_r($fd);
//     echo "\n";
//     print_r($event);
//     echo "\n";
// });

// $event->loop();
// 


use core\proc\Process;

Process::init();
Process::fork('test1', function(){
    file_get_contents("http://www.baidu.com");
    echo 1;
});


print_r(Process::$child);
print_r(Process::$child);

// Process::loop();