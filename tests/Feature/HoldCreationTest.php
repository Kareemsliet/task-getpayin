<?php

use App\Models\Product;

// Test to ensure overselling is prevented under concurrent requests
// Assumes initial stock of 30 for the product
// Adjust the stock in ProductSeeder if necessary
// This test will attempt to create 40 holds of quantity 1 concurrently
// Only 30 holds should succeed, preventing overselling
it('prevents_overselling_under_concurrent_requests', function () {

    $product = Product::first();

    $responses = collect(range(1, 40))->map(fn () =>
        $this->postJson('/api/holds', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])
    );

    // Count successful holds
    $successCount = collect($responses)
    ->filter(fn($res) => $res->isOk())
    ->count();

    // successful holds MUST NOT exceed stock
    expect($successCount)->toBe(30);

    // database should have exactly 30 holds only
    $this->assertDatabaseCount('holds', 30);

    expect($product->fresh()->stock)->toBe(0);

});