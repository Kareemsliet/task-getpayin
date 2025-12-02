<?php

use App\Models\Product;


it('creates a hold successfully and deducts stock', function () {
    
    $product = Product::first();
    $initialStock = $product->stock;

    $response = $this->postJson('/api/holds', [
        'product_id' => $product->id,
        'quantity' => 10,
    ]);

    $response->assertStatus(200);

    expect($product->fresh()->stock)->toBe($initialStock - 10);

});

it('prevents overselling under concurrent requests', function () {

    $product = Product::first();

    $responses = collect(range(1, 2))->map(fn () =>
        $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 10,
        ])
    );

    $success = $responses->filter(fn($res) => $res->isOk());
    $deducted = $success->map(fn($res) => $res->json('data.quantity'))->sum();

    $expectedStock = $product->stock - $deducted;

    expect($product->fresh()->stock)->toBe($expectedStock);

});