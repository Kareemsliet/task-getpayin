<?php

namespace App\Models;

use App\Enums\PaymentStatusEnum;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = "orders";

    protected $with = ["hold"];

    protected $fillable = [
        "hold_id",
        "payment_status",
    ];

    protected function casts()
    {
        return [
            "payment_status" => PaymentStatusEnum::class,
        ];
    }

    public function hold()
    {
        return $this->belongsTo(Hold::class, "hold_id");
    }
}
