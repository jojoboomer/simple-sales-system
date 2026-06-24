<?php


use App\Enums\OrderStatus;

test('order status returns correct labels', function () {
    expect(OrderStatus::PENDING->label())->toBe('Pending order');
    expect(OrderStatus::COMPLETED->label())->toBe('Completed order');
    expect(OrderStatus::REFUNDED->label())->toBe('Payment refunded');
});

test('order status returns correct colors', function () {
    expect(OrderStatus::PENDING->color())->toBe('warning');
    expect(OrderStatus::COMPLETED->color())->toBe('success');
    expect(OrderStatus::REFUNDED->color())->toBe('gray');
});
