<?php
namespace SlaxWeb\Logger;

/**
 * Logger library
 * 
 * Helps write logs throughout the application.
 *
 * @author Tomaz Lovrec <tomaz.lovrec@gmail.com>
 */
class Logger
{
    /**
     * Log directory
     *
     * @var string
     */
    protected $_logDir = '';
    /**
     * Log file
     *
     * @var string
     */
    protected $_logFile = '';
    /**
     * Log file handle
     *
     * @var resource
     */
    protected $_logHandle = null;
    /**
     * Logger config
     *
     * @var array
     */
    protected $_config = array();

    /**
     * Log levels
     */
    const EMERGENCY = 0;
    const ALERT     = 1;
    const CRITICAL  = 2;
    const ERROR     = 3;
    const WARNING   = 4;
    const NOTICE    = 5;
    const INFO      = 6;
    const DEBUG     = 7;

    /**
     * Default constructor
     *
     * Creates the log file for current day if it does not exist.
     *
     * @param $fileName string Name of the log file
     * @param $logDir string Name of the log directory, if set to null, defaults to SlaxWeb Logs dir
     */
    public function __construct($fileName = null, $logDir = null)
    {
        // load the config
        require_once 'Config/LoggerConfig.php';
        $this->_config = $config;

        // set log dir
        if ($logDir === null) {
            $this->_logDir = $this->_config['log_directory'];
        } else {
            $this->_logDir = $logDir;
        }

        // get current date
        $date = date('Ymd');
        // check fileName
        if($fileName === null) {
            $fileName = 'log';
        }
        // append date to file name
        $this->_logFile = $this->_logDir . $fileName . '-' . $date . '.log';
        // check if Logs dir exists 
        if (is_dir($this->_logDir) === false) {
            $created = @mkdir($this->_logDir);
            // check if the dir was created
            if ($created === false) {
                echo "{$this->_logDir} dir does not exist. Tried to create it, but permission was denied. Please create it
                      or fix permissions.\n<br />";
            }
        }
        
        // open the file for writting
        $this->_logHandle = @fopen($this->_logFile, 'a');
        // check if file was opened
        if ($this->_logHandle === false) {
            echo "Log file could not be created in {$this->_logDir}. Check permissions for the directory.\n<br />";
        }
    }

    /**
     * Class destructor
     *
     * Closes the file handle
     */
    public function __destruct()
    {
        fclose($this->_logHandle);
    }

    /**
     * Write to log
     *
     * Writes the message to the log file. Checks for correct level, and interpolates the string
     *
     * @param $level string Log level
     * @param $message string Log message
     * @param $context array Context values that replace placeholders in message
     */
    public function logMessage($level, $message, array $context = array ()) {
        // check if level is set correctly
        $levelCheck = $this->_checkLevel($level);
        if ($levelCheck !== false) {
            // check if we should log
            if ($this->_checkLogConfig($level) === true) {
                // interpolate the message first
                $message = $this->_interpolate($message, $context);
                $line = date('Y-m-d H:i:s');
                $line .= ' - ' . strtoupper($level) . ' - ' . $message . " Data: \n";
                $line .= print_r($this->getBacktrace($level, debug_backtrace()), true);
                fwrite($this->_logHandle, $line);
            }
        } else {
            // level not set correctly
            $context['level'] = $level;
            $this->logMessage(
                LVL_NOTICE,
                'Log level ({level}) was set incorrectly in \Library\Logger\Logger::logMessage()',
                $context
            );
            return false;
        }
    }

    /**
     * Interpolate context values into the message placeholders
     *
     * @param $message string Log message
     * @param $context array Context values that replace placeholders in message
     * @return string Interpolated string
     */
    protected function _interpolate($message, array $context = array ())
    {
        // build a replacement array with braces around the context keys
        $replace = array ();
        foreach ($context as $key => $val) {
            $replace['{' . $key . '}'] = $val;
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * Checks that the level was correctly set
     *
     * @param $level string Log level
     * @return bool Status
     */
    protected function _checkLevel($level)
    {
        $status = false;
        switch ($level) {
            case LVL_EMERGENCY:
            case LVL_ALERT:
            case LVL_CRITICAL:
            case LVL_ERROR:
            case LVL_WARNING:
            case LVL_NOTICE:
            case LVL_INFO:
            case LVL_DEBUG:
                $status = true;
                break;
        }
        return $status;
    }

    /**
     * Check log config
     *
     * Checks if logging is turned on, and to which level and returns true if everything passed,
     * or false if the message should not be logged
     */
    protected function _checkLogConfig($level)
    {
        //check if loggin is enabled
        if ($this->_config['logging_enabled'] === 1) {
            // check if the level is equal or above the set logging level
            $level = constant('self::' . $level);
            if ($this->_config['logging_log_level'] >= $level) {
                return true;
            } else {
                // logging level is lower than set
                return false;
            }
        } else {
            // logging is disabled
            return false;
        }
    }

    /**
     * Get the debug backtrace
     *
     * Returns the debug backtrace, amount depending on the level
     *
     * @param $level string Log level
     * @param $backtrace array Raw debug backtrace
     * @return string Debug backtrace
     */
    protected function getBacktrace($level, $backtrace)
    {
        // get the correct backtrace for the level
        $return = '';
        switch ($level) {
            case LVL_EMERGENCY:
            case LVL_ALERT:
            case LVL_CRITICAL:
            case LVL_ERROR:
            case LVL_WARNING:
            case LVL_DEBUG:
                $return['class'] = $backtrace[1]['class'];
                $return['method'] = $backtrace[1]['function'];
                break;
        }
        return $return;
    }
}
