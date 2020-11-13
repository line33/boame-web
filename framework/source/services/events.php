<?php

use Lightroom\Events\{
    Dispatcher, Listener, AttachEvent
};
use Lightroom\Packager\Moorexa\Helpers\Partials;

/**
 * @package Event registry file
 * @author Amadi Ifeanyi <amadiify.com>
 * 
 * Here you can attach an event class, register it's method, listen for an event, dispatch an event
 */
AttachEvent::attach(Lightroom\Events\ExampleBasic::class, 'ev');

// listen for alert event
event()->on('ev', 'alert', function(string $message, string $type = 'error') use (&$partial){
    
    Partials::exportVars('alert-modal', [
        'alertType'     => ($type == 'success') ? 'auto-show alert-success' : 'auto-show',
        'alertMessage'  => $message
    ]);
}); 