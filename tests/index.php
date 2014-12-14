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
