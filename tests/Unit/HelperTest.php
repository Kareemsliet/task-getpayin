<?php


it('returns success response with message and data', function () {

    $message = 'Operation successful';

    $data = ['id' => 1, 'name' => 'Test Product'];

    $response = successJsonResponse($message, $data);

    expect($response->status())->toBe(200)
    ->and($response->getData(true)['status'])->toBe(true)
    ->and($response->getData(true)['message'])->toBe($message)
    ->and($response->getData(true)['data'])->toBe($data);

});

it('returns error response with default status code', function () {
    
    $message = 'Validation failed';

    $response = errorJsonResponse($message,null,422);

    expect($response->status())->toBe(422)
    ->and($response->getData(true)['status'])->toBe(false)
    ->and($response->getData(true)['message'])->toBe($message)
    ->and($response->getData(true)['data'])->toBe(null);

});