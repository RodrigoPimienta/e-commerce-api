<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;

class ProductCart extends Model
{
    use LocalFactory;
    protected $table = "products_carts";
    protected $primaryKey = "id_product_cart";

    protected $guarded = ["id_product_cart"];

    protected $fillable = [
        'id_product_cart',
        'id_cart',
        'id_product',
        'quantity',
        'final_price',
    ];

    public function product()
    {
        return $this->belongsTo(ProductStore::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }
}
