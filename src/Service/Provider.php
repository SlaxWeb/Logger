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
 * @version   0.2
 */
namespace SlaxWeb\Logger\Service;

use Pimple\Container;
use SlaxWeb\Logger\Helper;
use Monolog\Logger as MLogger;
use SlaxWeb\Config\Container as Config;

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
        $container["logger.service"] = function (Container $cont) {
            /*
             * Check the config service has been defined and provides correct
             * object
             */
            if (
                isset($cont["config.service"]) === false
                || get_class($cont["config.service"]) !== "SlaxWeb\\Config\\Container"
            ) {
                throw new \SlaxWeb\Logger\Exception\ConfigurationException(
                    "Config component provider must be registered before you "
                    . "can use the Logger component."
                );
            }

            // check all config items exist
            if ($this->_checkConfig($cont["config.service"]) === false) {
                throw new \SlaxWeb\Logger\Exception\ConfigurationException(
                    "Logger configuration is not complete. Make sure all "
                    . "required settings are properly set."
                );
            }

            // load propper handler and instantiate the Monolog\Logger
            $handler = null;
            switch ($cont["config.service"]["logger.loggerType"]) {
                case Helper::L_TYPE_FILE:
                    $handler = $cont["logger.{$cont["config.service"]["logger.loggerType"]}.service"];
                    break;
                default:
                    throw new \SlaxWeb\Logger\Exception\UnknownHandlerException(
                        "The handler you are tring to use is not known or not "
                        . "supported."
                    );
            }
            $logger = new MLogger($cont["config.service"]["logger.name"]);
            $logger->pushHandler($handler);
            return $logger;
        };

        $container["logger.StreamHandler.service"] = $container->factory(
            function (Container $cont) {
                return new \Monolog\Handler\StreamHandler(
                    ...$cont["config.service"]["logger.handlerArgs.{$cont["config.service"]["logger.loggerType"]}"]
                );
            }
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
    protected function _checkConfig(Config $config): bool
    {
        return isset($config["logger.name"])
            && isset($config["logger.loggerType"])
            && isset($config["logger.handlerArgs.{$config["logger.loggerType"]}"]);
    }
}
