<?php
namespace Bitrock;

use Monolog\Logger as MonologLogger;
use Monolog\Handler\StreamHandler;

/** USAGE
 * $logger = new Logger();
 * (optional) $logger->setLogPath($logPath) - to change default log path
 * $logger->info(`User with login ${$login} successfully logged in`);
 */

class Logger
{
    public CONST DEFAULT_LOG_NAME  = 'default';
    public CONST DEFAULT_LOG_LEVEL  = 'debug';
    public CONST DEFAULT_LOG_FILE  = 'DEFAULT_LOG_FILE';
    public CONST DEFAULT_LOG_DIR = 'DEFAULT_LOG_DIR';

    /** file to place logs */
    protected $logPath;

    /**
     * List of methods and levels
     *
     * @var array
     */
    protected static $confLevels = [
        'debug' => MonologLogger::DEBUG,
        'info' => MonologLogger::INFO,
        'notice' => MonologLogger::NOTICE,
        'warning' => MonologLogger::WARNING,
        'error' => MonologLogger::ERROR,
        'critical' => MonologLogger::CRITICAL,
        'alert' => MonologLogger::ALERT,
        'emergency' => MonologLogger::EMERGENCY,
    ];

    public function __construct()
    {

    }

    /**
     * Call method of current MonologLogger instance
     *
     * @param string $name
     * @param array $arguments
     */
    public function __call($name, $arguments)
    {
        if (!self::$confLevels[$name]) return false;

        $logPath = $this->getActualLogPath();

        if (!empty($logPath)) {
            $logger = new MonologLogger($this->getLogName());
            $logger->pushHandler(new StreamHandler($logPath, $this->getLogLevel()));
            $logger->$name(...$arguments);
            return true;
        }

        return false;
    }

    public function getActualLogPath()
    {
        if (!empty($this->getLogPath())) {
            $logPath = $this->getLogPath();
        } else {
            $env = LetsEnv::getInstance();
            $logPath = $env->getEnv(static::DEFAULT_LOG_DIR)
                . $env->getEnv(static::DEFAULT_LOG_FILE);
        }

        if (!empty($logPath) && file_exists($logPath)) {
            return $logPath;
        }

        return false;
    }

    public function getLogPath()
    {
        return $this->logPath;
    }

    public function setLogPath($logPath)
    {
        if (empty($logPath)) return false;

        if (file_exists($logPath)) {
            $this->logPath = $logPath;
            return true;
        }

        return false;
    }

    private function getLogLevel()
    {
        $envLogLevel = LetsEnv::getInstance()->getEnv('LOG_LEVEL');
        return !empty($envLogLevel)
            ? $envLogLevel
            : static::DEFAULT_LOG_LEVEL;
    }

    private function getLogName()
    {
        $envLogName = LetsEnv::getInstance()->getEnv('LOG_NAME');
        return !empty($envLogName)
            ? $envLogName
            : static::DEFAULT_LOG_NAME;
    }
}