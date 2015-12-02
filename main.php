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
ini_set('memory_limit', '256M');
Process::init();
Process::fork('test1', function(){
    Process::loop();
});



// Process::sendMsg('test1', (array('msgtype' => 'command', 'msgname' => 'test', 'msgparam' => 'this is a test')));

Process::loop();