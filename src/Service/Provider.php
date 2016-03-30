<?php
/**
 * Logger Service Provider
 *
 * Register the logger and its handler as services.
 *
 * @package   SlaxWeb\Logger
 * @author    Tomaz Lovrec <tomaz.lovrec@gmail.com>
 * @copyright 2016 (c) Tomaz Lovrec
 * @license   MIT <https://opensource.org/licenses/MIT>
 * @link      https://github.com/slaxweb/
 * @version   0.3
 */
namespace SlaxWeb\Logger\Service;

use Pimple\Container;
use SlaxWeb\Logger\Helper;
use Monolog\Logger as MLogger;
use SlaxWeb\Config\Container as Config;
use Monolog\Registry as LoggerContainer;

class Provider implements \Pimple\ServiceProviderInterface
{
    /**
     * Register Logger Service Provider
     *
     * Method used by the DIC to register a new service provider. This Service
     * Provider defines only the Logger service.
     *
     * @param \Pimple\Container $container Pimple Dependency Injection Container
     * @return void
     */
    public function register(Container $container)
    {
        $container["logger.service"] = $container->protect(
            function (string $logger = "") use ($container)
            {
                /*
                 * Check the config service has been defined and provides correct
                 * object
                 */
                if (isset($container["config.service"]) === false
                    || get_class($container["config.service"]) !== "SlaxWeb\\Config\\Container") {
                    throw new \SlaxWeb\Logger\Exception\ConfigurationException(
                        "Config component provider must be registered before you can use the Logger component."
                    );
                }

                $config = $container["config.service"];

                // check all config items exist
                if ($this->_checkConfig($config, $loggerName) === false) {
                    throw new \SlaxWeb\Logger\Exception\ConfigurationException(
                        "Logger configuration is not complete. Make sure all required settings are properly set."
                    );
                }

                // if already in the container, return it
                if (LoggerContainer::hasLogger($logger)) {
                    return LoggerContainer::getInstance($logger);
                }

                // load propper handler and instantiate the Monolog\Logger
                $handler = null;
                $loggerType = $config["logger.loggerType"][$logger];
                switch ($loggerType) {
                    case Helper::L_TYPE_FILE:
                        $container["temp.logger.property"] = [
                            "name"  =>  $logger,
                            "type"  =>  $type
                        ];
                        $handler = $container["logger.{$loggerType}.service"];
                        break;
                    default:
                        throw new \SlaxWeb\Logger\Exception\UnknownHandlerException(
                            "The handler you are tring to use is not known or not supported."
                        );
                }
                $logger = new MLogger($logger);
                $logger->pushHandler($handler);
                return $logger;
            }
        );

        $container["logger.StreamHandler.service"] = $container->factory(
            function (Container $cont) {
                $type = $cont["temp.logger.property"]["type"];
                $logger = $cont["temp.logger.property"]["name"];
                unset(
                    $cont["temp.logger.property"]["type"],
                    $cont["temp.logger.property"]["name"]
                );

                return new \Monolog\Handler\StreamHandler(
                    ...$cont["config.service"]["logger.handlerArgs.{$type}"][$logger]
                );
            }
        );
    }

    /**
     * Check Logger configuration
     *
     * Check that all keys that are required to instantiate the Logger component exist.
     *
     * @param \SlaxWeb\Config\Container $config Configuration module
     * @param string $loggerName Name of the logger that is going to be initialized
     * @return bool
     */
    protected function _checkConfig(Config $config, string $loggerName): bool
    {
        if ($loggerName === "" && ($loggerName = $config["logger.name"] ?? "") === "") {
            return false;
        }

        return ($type = $config["logger.loggerType"][$loggerName] ?? false)
            && $config["logger.handlerArgs.{$type}"][$loggerName] ?? false;
    }
}
