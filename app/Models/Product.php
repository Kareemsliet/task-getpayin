<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = "products";

    protected $fillable = [
        "name",
        "description",
        "price",
        "stock",
    ];

    public function holds()
    {
        return $this->hasMany(Hold::class, "product_id");
    }

    public function orders()
    {
        return $this->hasManyThrough(Order::class, Hold::class,"product_id","hold_id");
    }

}
