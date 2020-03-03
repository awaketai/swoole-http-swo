<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/19
 * Time: 11:45 上午
 */

namespace swo\exception;


use Throwable;

class ServerNotFoundException extends \Exception
{
    private $status;
    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $this->status = $code;
        parent::__construct($message, $this->status, $previous);
    }
}