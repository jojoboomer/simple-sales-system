<?php


use App\Casts\MoneyCast;
use App\Models\Product;

test('money cast converts cents to dollars on get', function () {
    $cast = new MoneyCast;

    $value = $cast->get(new Product, 'price', 1000, []);

    expect($value)->toEqual(10.0);
});

test('money cast converts dollars to cents on set', function () {
    $cast = new MoneyCast;

    $value = $cast->set(new Product, 'price', 10.0, []);

    expect($value)->toBe(1000);
});
