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
     * @return \Monolog\Logger Logger instance
     */
    public static function init(
        Config $config
    ): MLogger {
        // check all config items exist
        if (self::_checkConfig($config) === false) {
            throw new Exception\ConfigurationException(
                "Logger configuration is not complete. Make sure all "
                . "required settings are properly set."
            );
        }

        // load propper handler and instantiate the Monolog\Logger
        $handler = null;
        switch ($config["logger.loggerType"]) {
            case Helper::L_TYPE_FILE:
                $method = "_init{$config["logger.loggerType"]}";
                $handler = self::{$method}($config);
                break;
            default:
                throw new Exception\UnknownHandlerException(
                    "The handler you are tring to use is not known or not "
                    . "supported."
                );
        }
        $logger = new MLogger($config["logger.name"]);
        $logger->pushHandler($handler);
        return $logger;
    }

    /**
     * Initialize StreamHandler
     *
     * Initialize the Monolog StreamHandler handler and return it.
     *
     * @param \SlaxWeb\Config\Container $config The Config componen instance
     * @return StreamHandler
     */
    protected function _initStreamHandler(Config $config): LogHandlerInterface
    {
        return new \Monolog\Handler\StreamHandler(
            ...$config["logger.handlerArgs.{$config["logger.loggerType"]}"]
        );
    }

    /**
     * Check Logger configuration
     *
     * Check that all keys that are required to instantiate the Logger component
     * exist.
     *
     * @param \SlaxWeb\Config\Container $config Configuration module
     * @return bool
     */
    protected static function _checkConfig(Config $config): bool
    {
        return isset($config["logger.name"])
            && isset($config["logger.loggerType"])
            && isset($config["logger.streamSettings.{$config["logger.loggerType"]}"]);
    }
}
