<?php
/**
 * Logger Factory
 *
 * Instantiate the Monolog\Logger, and return an instance to the caller.
 *
 * @package   SlaxWeb\Logger
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.2
 */
namespace SlaxWeb\Logger;

use Monolog\Logger as MLogger;
use SlaxWeb\Config\Container as Config;
use Monolog\Registry as LoggerContainer;
use Monolog\Handler\HandlerInterface as LogHandlerInterface;

class Factory
{
    /**
     * Logger Init
     *
     * Instantiate the Monolog Logger with the defined handler and configuration
     * values retrieved from the Config component.
     *
     * @param \SlaxWeb\Config\Container $config SlaxWeb Config component
     * @param string $loggerName Logger name
     * @return \Monolog\Logger Logger instance
     */
    public static function init(Config $config, string $loggerName): MLogger
    {
        // check all config items exist
        if (self::_checkConfig($config) === false) {
            throw new Exception\ConfigurationException(
                "Logger configuration is not complete. Make sure all required settings are properly set."
            );
        }

        if ($loggerName === "") {
            $loggerName = $config["logger.defaultLogger"];
        }

        // if already in the container, return it
        if (LoggerContainer::hasLogger($loggerName)) {
            return LoggerContainer::getInstance($loggerName);
        }

        $logger = new MLogger($loggerName);
        foreach ($config["logger.loggerSettings"][$loggerName] as $type => $settings) {
            // load propper handler and instantiate the Monolog\Logger
            $handler = null;
            switch ($type) {
                case Helper::L_TYPE_FILE:
                    $method = "_init{$type}";
                    $handler = self::{$method}($settings);
                    break;
                default:
                    throw new Exception\UnknownHandlerException(
                        "The handler you are tring to use is not known or not supported."
                    );
            }
            $logger->pushHandler($handler);
        }
        return $logger;
    }

    /**
     * Initialize StreamHandler
     *
     * Initialize the Monolog StreamHandler handler and return it.
     *
     * @param array $settings Handler settings in an array
     * @return StreamHandler
     */
    protected function _initStreamHandler(array $settings): LogHandlerInterface
    {
        return new \Monolog\Handler\StreamHandler(...$settings);
    }
}
