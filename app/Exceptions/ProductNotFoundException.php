<?php

namespace App\Exceptions;

use DomainException;

class ProductNotFoundException extends DomainException
{
    public static function withId(string $productId): self
    {
        return new self("Product with ID '{$productId}' not found.");
    }
}
