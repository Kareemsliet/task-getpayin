<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentWebhook extends Model
{
    protected $table = "payment_webhooks";

    protected $fillable = [
        "idempotency_key",
        "order_id",
        "payload"
    ];

    protected function casts()
    {
        return [
            "payload" => "array",
        ];
    }

    public function scopeByIdempotencyKey(Builder $query, $key)
    {
        return $query->where("idempotency_key", $key);
    }
}
