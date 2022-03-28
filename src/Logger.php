<?php
declare(strict_types=1);
namespace dsTools;

use Colors\Color;
use Psr\Log\LogLevel;

/**
 * Simple logger class.
 */
class Logger
{
    const SUCCESS = 'success';

    /** @var bool Should we echo output? */
    protected $verbose = true;

    /** @var bool Should all records start with current date/time? */
    public $show_timer = true;

    /** @var Color */
    protected $color;

    /** @var array Color theme */
    protected $theme = [
        LogLevel::EMERGENCY => ['white', 'bg_red', 'bold'],
        LogLevel::ALERT => ['white', 'bg_red'],
        LogLevel::CRITICAL => ['red', 'bold'],
        LogLevel::ERROR => ['red'],
        LogLevel::WARNING => ['yellow', 'bold'],
        LogLevel::NOTICE => ['cyan'],
        LogLevel::INFO => ['dark_gray'],
        LogLevel::DEBUG => ['light_gray'],
        self::SUCCESS => ['green'],
        'timer' => ['dark_gray'],
        'mark' => ['magenta'], // mark special places in string, like variables
    ];

    public function __construct(array $props = [])
    {
        foreach ($props as $k => $v) {
            $this->{$k} = $v;
        }

        if (!$this->color) {
            $this->color = new Color();
            $this->color->setTheme($this->theme);
        }
    }
    public function emergency(string $s): void
    {
        $this->log($s, LogLevel::EMERGENCY);
        die();
    }
    public function alert(string $s): void
    {
        $this->log($s, LogLevel::ALERT);
    }
    public function critical(string $s): void
    {
        $this->log($s, LogLevel::CRITICAL);
    }
    public function error(string $s): void
    {
        $this->log($s, LogLevel::ERROR);
    }
    public function warning(string $s): void
    {
        $this->log($s, LogLevel::WARNING);
    }
    public function notice(string $s): void
    {
        $this->log($s, LogLevel::NOTICE);
    }
    public function info(string $s): void
    {
        $this->log($s, LogLevel::INFO);
    }
    public function debug(string $s): void
    {
        $this->log($s, LogLevel::DEBUG);
    }
    public function success(string $s): void
    {
        $this->log($s, self::SUCCESS);
    }

    protected function log(string $s, string $type = LogLevel::DEBUG): void
    {
        if ($this->verbose || $type == LogLevel::EMERGENCY) {
            echo
                ($this->show_timer ? $this->getTime() . ' ' : '') .
                $this->color->apply($type, $this->color->colorize($s)) .
                PHP_EOL;
        }
    }

    protected function getTime(): string
    {
        return (string) $this->color->apply('timer', '[' . date('Y-m-d H:i:s') . ']');
    }
}
