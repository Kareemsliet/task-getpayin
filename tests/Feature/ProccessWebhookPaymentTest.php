<?php

use App\Models\Hold;
use App\Models\Order;
use App\Models\PaymentWebhook;
use App\Models\Product;

it('creates order from valid hold', function () {
    
    $product = Product::first();
    
    $holdResponse = $this->postJson('/api/holds', [
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
    
    $hold = Hold::find($holdResponse->json('data.id'));
    
    $response = $this->postJson('/api/orders', [
        'hold_id' => $hold->id
    ]);
    
    $response->assertStatus(200);

});

it('prevents duplicate webhook with same idempotency key', function () {
    
    $product = Product::first();
    
    $holdResponse = $this->postJson('/api/holds', [
        'product_id' => $product->id,
        'quantity' => 5,
    ]);
    
    $hold = Hold::find($holdResponse->json('data.id'));
    
    $orderResponse = $this->postJson('/api/orders', [
        'hold_id' => $hold->id
    ]);
    
    $order = Order::find($orderResponse->json('data.id'));
    
    $sameKey = 'duplicate-key-' . time();
    
    $response1 = $this->postJson('/api/payments/webhook/', [
        'idempotency_key' => $sameKey,
        'status' => 'success',
        'order_id' => $order->id
    ]);
    
    $response1->assertStatus(200);
    
    $response2 = $this->postJson('/api/payments/webhook/', [
        'idempotency_key' => $sameKey,
        'status' => 'success',
        'order_id' => $order->id
    ]);
    
    $response2->assertStatus(422);
    
    expect(PaymentWebhook::where('idempotency_key', $sameKey)->count())->toBe(1);

});

it('handles webhook arriving before order creation', function () {
    
    $nonExistentOrderId = 999999;
    
    $response = $this->postJson('/api/payments/webhook/', [
        'idempotency_key' => 'before-order-' . time(),
        'status' => 'success', 
        'order_id' => $nonExistentOrderId
    ]);
    
    $response->assertStatus(422);
    
    expect(PaymentWebhook::where('order_id', $nonExistentOrderId)->exists())->toBeTrue();

});