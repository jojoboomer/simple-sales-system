<?php

namespace App\Exceptions;

use DomainException;

class PriceMismatchException extends DomainException
{
    public static function forProduct(string $productName, float $expected, float $provided): self
    {
        return new self(
            "Price mismatch for product '{$productName}'. Expected: {$expected}, Provided: {$provided}"
        );
    }
}
