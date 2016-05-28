<?php
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

class AppNexus_Monolog
{
    /**
     * The monologger instance
     *
     * @var string
     */
    protected static $_instance;

    /**
     * Enable/disable logging
     * @var string
     */
    protected static $_enableLogging;

    /**
     * Get instance of monologger
     *
     * @return Logger
     */
    public static function getInstance()
    {
        if (!isset(self::$_instance)) {
            self::$_instance = new Logger('AppNexus');
            self::$_instance->pushHandler( new StreamHandler(__DIR__ . '/../../data/logs/appnexus.log', Logger::INFO) );
        }
        return self::$_instance;
    }

    /**
     * Log an info message
     *
     * @param string $message
     */
    public static function addInfo($message)
    {
        if (self::$_enableLogging) {
            self::getInstance()->addInfo($message);
        }
    }

    /**
     * Enable/disable error logging
     *
     * @param bool $enable
     */
    public static function setEnableLogging($enable = false)
    {
        self::$_enableLogging = $enable;
    }

    /**
     * Get whether logging is enabled or not
     *
     * @return bool
     */
    public static function getEnableLogging()
    {
        if (!self::$_enableLogging) {
            self::$_enableLogging = false;
        }

        return self::$_enableLogging;
    }

}
