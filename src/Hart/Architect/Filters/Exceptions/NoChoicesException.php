<?php
namespace Hart\Architect\Filters\Exceptions;

class NoChoicesException extends \Exception
{
    public function __construct($message, $code = 0, Exception $previous = null)
    {
        $message .= " You must pass an option among choices,query,model";
        parent::__construct($message, $code, $previous);

    }

}
