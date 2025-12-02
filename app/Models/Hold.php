<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Hold extends Model
{
    protected $table = "holds";

    protected $with = ["product"];
    
    protected $appends = ["is_expired"];

    protected $fillable = [
        "product_id",
        "quantity",
        "expires_at",
    ];

    protected function casts()
    {
        return [
            "expires_at" => "datetime",
        ];
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at->lessThanOrEqualTo(now());
    }

    public  function product()
    {
        return $this->belongsTo(Product::class, "product_id");
    }

    public function order()
    {
        return $this->hasOne(Order::class,"hold_id");
    }

    public function scopeExpired(Builder $builder)
    {
        return $builder->where("expires_at","<=", now());
    }

    public function scopeDosentHaveOrder(Builder $builder)
    {
        return $builder->doesntHave("order");
    }

}
