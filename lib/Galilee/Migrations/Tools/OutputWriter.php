<?php

namespace Galilee\Migrations\Tools;

/**
 * Class OutputWriter
 * @package Galilee\Migrations\Tools
 */
class OutputWriter
{
    /** @var  \Closure */
    private $closure;

    public function __construct(\Closure $closure = null)
    {
        if (null === $closure) {
            $closure = function ($message) {};
        }
        $this->setClosure($closure);
    }

    public function write($message)
    {
        $closure = $this->getClosure();
        $closure($message);
    }

    /**
     * @return callable
     */
    public function getClosure()
    {
        return $this->closure;
    }

    /**
     * @param callable $closure
     */
    public function setClosure($closure)
    {
        $this->closure = $closure;
    }
}
