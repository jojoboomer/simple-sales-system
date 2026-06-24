<?php

namespace App\Exceptions;

use DomainException;

class InsufficientStockException extends DomainException
{
    public static function forProduct(string $productName, int $available, int $requested): self
    {
        return new self(
            "Insufficient stock for product '{$productName}'. Available: {$available}, Requested: {$requested}"
        );
    }
}
