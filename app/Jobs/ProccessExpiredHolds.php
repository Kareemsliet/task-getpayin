<?php

namespace App\Jobs;

use App\Models\Hold;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProccessExpiredHolds implements ShouldQueue
{
    use Queueable;

    public function __construct()
    {
      
    }

    public function handle(): void
    {
        // Fetch holds that are expired and do not have associated orders
        $expiredHolds = Hold::dosentHaveOrder()->expired()->get();

        // If no expired holds, log and exit
        if ($expiredHolds->isEmpty()) {
            Log::info('No expired holds found');
            return;
        }

        // Process each expired hold
        $expiredHolds->each(function($hold){
           DB::transaction(function() use ($hold) {
               
               // get the product associated with the hold
               $product = $hold->product;

               // Release the held stock back to product stock
               $product->increment('stock', $hold->quantity);

               // Delete the expired hold
               $hold->delete();

               // Log the stock release
               Log::info("Released {$hold->quantity} units back to product ID: {$hold->product_id} from expired holds");
           
            });
        });

        // Log completion
        Log::info('Expired holds processed and deleted successfully');
    }
}