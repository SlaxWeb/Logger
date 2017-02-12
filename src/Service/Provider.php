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
                $cacheName = "logger.service-{$loggerName}";
                if (isset($container[$cacheName])) {
                    return $container[$cacheName];
                }
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

                $logger = new MLogger($loggerName);
                foreach ($config["logger.loggerSettings"][$loggerName] as $type => $settings) {
                    // load propper handler and instantiate the Monolog\Logger
                    $handler = null;
                    switch ($type) {
                        case Helper::L_TYPE_FILE:
                            $container["temp.logger.settings"] = $settings;
                            $handler = $container["logger.{$type}.service"];
                            unset($container["temp.logger.settings"]);
                            break;
                        default:
                            throw new \SlaxWeb\Logger\Exception\UnknownHandlerException(
                                "The handler you are tring to use is not known or not supported."
                            );
                    }
                    $logger->pushHandler($handler);
                }

                return $container[$cacheName] = $logger;
            }
        );

        $container["logger.StreamHandler.service"] = $container->factory(
            function (Container $cont) {
                if ($cont["temp.logger.settings"][0][0] !== DIRECTORY_SEPARATOR) {
                    $cont["temp.logger.settings"][0] = $cont["config.service"]["logger.logFilePath"] ?? ""
                        . $cont["temp.logger.settings"][0];
                }
                return new \Monolog\Handler\StreamHandler(...$cont["temp.logger.settings"]);
            }
        );
    }
}
