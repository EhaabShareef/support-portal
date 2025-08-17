<?php

namespace App\Exceptions;

use DomainException;

class CannotDeleteProtectedStatus extends DomainException
{
    public function __construct(string $statusName = 'ticket status', ?\Throwable $previous = null)
    {
        $message = "Cannot delete protected {$statusName}.";
        parent::__construct($message, 0, $previous);
    }
}