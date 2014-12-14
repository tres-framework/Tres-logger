<?php

namespace Tres\logger {
    
    use DateTime;
    use Exception;
    
    class LogException extends Exception {}
    
    /*
    |-------------------------------------------------------------------------
    | Logger
    |-------------------------------------------------------------------------
    | 
    | This class calculates execution time and makes reports. They will be
    | stored as logs.
    | 
    */
    class Logger {
        
        /**
         * The log directory.
         *
         * @var string
         */
        protected $_directory;
        
        /**
         * The permissions for the log directory.
         * 
         * @var int
         */
        protected $_directoryPermissions = 0777;
        
        /**
         * The extension for the log file.
         * 
         * @var string
         */
        protected $_fileExtension = '.log';
        
        /**
         * The permissions for log files.
         * 
         * @var int
         */
        protected $_filePermissions = 0755;
        
        /**
         * The threshold for the file size.
         * 
         * @var int
         */
        protected $_fileMaxSize = 2097152; // 2 MB
        
        /**
         * The maximum nesting level for the log files.
         * 
         * @var int
         */
        protected $_maxNestingLevel = 999;
        
        /**
         * The new line indicator.
         */
        const CRLF = PHP_EOL;
        
        /**
         * Sets the log directory.
         */
        public function __construct($config = array()) {
            if(isset($config['dir']['path']) && !empty($config['dir']['path'])) {
                $dir = $config['dir']['path'];
            } else {
                $dir = __DIR__.'/logs';
            }
            
            $this->_directory = rtrim($dir, '/').'/';
            
            $this->_generateDir();
            
            if(isset($config['dir']['permissions']) &&
               !empty($config['dir']['permissions']) &&
               is_int($config['dir']['permissions'])
            ){
                $this->_directoryPermissionsPermissions = $config['dir']['permissions'];
            }
            
            if(isset($config['file']['extension']) && !empty($config['file']['extension'])) {
                $this->_fileExtension = $config['file']['extension'];
            }
            
            if(isset($config['file']['permissions']) &&
               !empty($config['file']['permissions']) &&
               is_int($config['file']['permissions'])
            ){
                $this->_filePermissions = $config['file']['permissions'];
            }
            
            if(isset($config['file']['size']) &&
               !empty($config['file']['size']) &&
               is_int($config['file']['size'])
            ){
                $this->_fileMaxSize = $config['file']['size'];
            }
            
            if(isset($config['file']['max_nesting_level']) &&
               !empty($config['file']['max_nesting_level']) &&
               is_int($config['file']['max_nesting_level'])
            ){
                $this->_maxNestingLevel = $config['file']['max_nesting_level'];
            }
            
            ini_set('xdebug.max_nesting_level', $this->_maxNestingLevel);
        }
        
        /**
         * Creates the log.
         *
         * @param string $log The log message.
         */
        public function log($log) {
            $date = new DateTime('now');
            
            if(isset($log) && !empty($log)) {
                if(!is_dir($this->_directory) && !$this->_generateDir()){
                    throw new LogException('Could not find, nor create a log directory.');
                }
                
                $this->_generateSecurity();
                
                $ip = $_SERVER['REMOTE_ADDR'];
                
                $this->_logMessage(
                    $this->_generateFilename($date->format('Y-m-d')),
                    "[{$date->format('Y-m-d h:m:s')}] [{$ip}] ".$log
                );
            }
        }
        
        /**
         * Generates the directory to store the logs.
         * 
         * @return bool
         */
        protected function _generateDir() {
            if(!is_dir($this->_directory)) {
                $status = mkdir($this->_directory, $this->_directoryPermissions, true);
                $this->_generateSecurity();
            } else {
                $status = true;
            }
            
            return $status;
        }
        
        /**
         * Generates a security file (.htaccess) to disable directory listing.
         * Nginx users should do this in their .conf file by adding autoindex off;
         * 
         * @return bool
         */
        protected function _generateSecurity() {
            $file = $this->_directory.'.htaccess';
            
            if(is_writable($this->_directory)) {
                if(!file_exists($file) && $fileHandle = fopen($file, 'w')){
                    chmod($file, $this->_filePermissions);
                    fwrite($fileHandle, 'Options -Indexes');
                    fclose($fileHandle);
                }
            } else {
                throw new LogException('Cannot create/write to file. Permission denied.');
            }
        }
        
        /**
         * Generates a log file and makes sub files if the original one gets
         * too big in size.
         *
         * @param  string $date   The current date.
         * @param  int    $number The file sub number.
         * @return string         The filename.
         */
        protected function _generateFilename($date, $number = 0) {
            // Number safety: in case you are creating more than X files on the same date.
            if($number > $this->_maxNestingLevel) {
                throw new LogException('Maximum log files ('.$this->_maxNestingLevel.') reached.');
                //$float = 3 + strlen($number);
            } else {
                $float = strlen($this->_maxNestingLevel);
            }
            
            // Ex. format: 2014-11-23.000.log
            $file  = $this->_directory;
            $file .= $date.'.'.str_pad($number, $float, '0', STR_PAD_LEFT);
            $file .= '.'.ltrim($this->_fileExtension, '.');
            
            if(is_file($file)) {
                if(is_readable($file) && is_writable($file)) {
                    return (filesize($file) < $this->_fileMaxSize) ? $file : $this->_generateFilename($date, ++$number);
                } else {
                    throw new LogException('Unable to read or write to log file.');
                }
            } else {
                if(is_writable($this->_directory) && $fileHandle = fopen($file, 'w')){
                    chmod($file, $this->_filePermissions);
                    fclose($fileHandle);
                } else {
                    throw new LogException('Cannot create/write to file. Permission denied.');
                }
                
                return $this->_generateFilename($date, $number);
            }
        }
        
        /**
         * Appends the log to the log file.
         *
         * @param  string $path The file to log to.
         * @param  string $log  The message to log.
         */
        protected function _logMessage($path, $log) {
            $file = fopen($path, 'a');
            fwrite($file, $log.self::CRLF);
            fclose($file);
        }
        
    }
    
}
