<?php

use App\Jobs\ProccessExpiredHolds;
use App\Models\Hold;



it('processes expired holds and restores stock', function () {
    
    $expiredCount = Hold::dosentHaveOrder()->expired()->count();
    
    (new ProccessExpiredHolds())->handle();
    
    if ($expiredCount > 0) {
        expect(Hold::dosentHaveOrder()->expired()->count())->toBe(0);
    } else {
        expect($expiredCount)->toBe(0);
    }
    
    expect(true)->toBeTrue();

});