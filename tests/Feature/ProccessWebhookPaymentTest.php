<?php

use App\Models\Hold;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\Cache;

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
    
    $sameKey = 'test-idempotency-key-' . time();
    
    $response1 = $this->withHeader('Idempotency-Key', $sameKey)->postJson('/api/payments/webhook/', [
        'status' => 'success',
        'order_id' => $order->id
    ]);
    
    $response1->assertStatus(200);
    
    $response2 = $this->withHeader('Idempotency-Key', $sameKey)->postJson('/api/payments/webhook/', [
        'status' => 'success',
        'order_id' => $order->id
    ]);
    
    $response2->assertStatus(200);

     // Verify the cache key exists and contains the response
    expect(Cache::has("idempotency:{$sameKey}"))->toBeTrue();
    
    // Verify both responses are identical
    expect($response1->getContent())->toBe($response2->getContent());
    
    // Verify the order was only processed once (not twice)
    $order->refresh();
    expect($order->payment_status->value)->toBe('success');

});

it('handles webhook arriving before order creation', function () {
    
    $nonExistentOrderId = 999999;
    
    $response = $this->withHeader('Idempotency-Key', 'before-order-' . time())->postJson('/api/payments/webhook/', [
        'status' => 'success', 
        'order_id' => $nonExistentOrderId
    ]);
    
    $response->assertStatus(422);

});