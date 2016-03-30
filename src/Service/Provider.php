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
            function (string $loggerName = "") use ($container)
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
                            $container["temp.logger.settings"] = $settings;
                            $handler = $container["logger.{$type}.service"];
                            unset($continer["temp.logger.settings"]);
                            break;
                        default:
                            throw new \SlaxWeb\Logger\Exception\UnknownHandlerException(
                                "The handler you are tring to use is not known or not supported."
                            );
                    }
                    $logger->pushHandler($handler);
                }

                return $logger;
            }
        );

        $container["logger.StreamHandler.service"] = $container->factory(
            function (Container $cont) {
                return new \Monolog\Handler\StreamHandler(...$cont["temp.logger.settings"]);
            }
        );
    }
}
