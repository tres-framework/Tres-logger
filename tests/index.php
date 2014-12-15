<?php

use Tres\logger\Logger;

spl_autoload_register(function($class){
    $dirs = [
        dirname(__DIR__).'/src/'
    ];
    
    foreach($dirs as $dir){
        $file = str_replace('\\', '/', rtrim($dir, '/').'/'.$class.'.php');
        
        if(is_readable($file)){
            require_once($file);
            break;
        }
    }
});

$config = [
    'dir' => [
        'path'        => __DIR__.'/logs',
        //'permissions' => 0777,
    ],
    
    'file' => [
        //'extension'         => '.log',
        //'size'              => 2097152, // 2 MB
        //'permissions'       => 0755,
        //'max_nesting_level' => 999,
    ],
];

$logger = new Logger($config);

echo 'View your log files in: '.$config['dir']['path'].'<br />';

$logger->log('message');
$logger->log('message 2'.PHP_EOL);


$debug = false;

if(!$debug){
    set_error_handler(function($errno, $errstr, $errfile, $errline) use ($logger){
        switch($errno){ 
            case E_ERROR:
                $type = 'E_ERROR';
            break;
            
            case E_WARNING:
                $type = 'E_WARNING';
            break;
            
            case E_PARSE:
                $type = 'E_PARSE';
            break;
            
            case E_NOTICE:
                $type = 'E_NOTICE';
            break;
            
            case E_CORE_ERROR:
                $type = 'E_CORE_ERROR';
            break;
            
            case E_CORE_WARNING:
                $type = 'E_CORE_WARNING';
            break;
            
            case E_COMPILE_ERROR:
                $type = 'E_COMPILE_ERROR';
            break;
            
            case E_COMPILE_WARNING:
                $type = 'E_COMPILE_WARNING';
            break;
            
            case E_USER_ERROR:
                $type = 'E_USER_ERROR';
            break;
            
            case E_USER_WARNING:
                $type = 'E_USER_WARNING';
            break;
            
            case E_USER_NOTICE:
                $type = 'E_USER_NOTICE';
            break;
            
            case E_STRICT:
                $type = 'E_STRICT';
            break;
            
            case E_RECOVERABLE_ERROR:
                $type = 'E_RECOVERABLE_ERROR';
            break;
            
            case E_DEPRECATED:
                $type = 'E_DEPRECATED';
            break;
            
            case E_USER_DEPRECATED:
                $type = 'E_USER_DEPRECATED';
            break;
            
            default:
                $type = 'E_UNKOWN';
            break;
        }
        
        $msg  = '[ERROR]'.PHP_EOL;
        $msg .= '|-> Type: '.$type.PHP_EOL;
        $msg .= '|-> Called in: '.$errfile.' on line '.$errline.PHP_EOL;
        $msg .= '|-> Message: '.$errstr.PHP_EOL;
        
        $logger->log($msg);
    }, E_ALL);
    
    set_exception_handler(function($e) use ($logger){
        $msg  = '[EXCEPTION]'.PHP_EOL;
        $msg .= '|-> Uncaught exception: '.get_class($e).PHP_EOL;
        $msg .= '|-> Stack trace: '.$e->getTraceAsString().PHP_EOL;
        $msg .= '|-> Thrown in: '.$e->getFile().' on line '.$e->getLine().PHP_EOL;
        $msg .= '|-> Message: '.$e->getMessage().PHP_EOL;
        
        $logger->log($msg); 
    });
}

4 / 0;

//throw new Exception('Example exception.');

class TestException extends Exception {}

throw new TestException('Example exception 2.');

echo '<hr />Reached end of file.';
