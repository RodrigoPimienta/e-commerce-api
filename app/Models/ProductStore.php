<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductStore extends Model
{
    //
    use LocalFactory;

    protected $table = "products_stores";
    protected $primaryKey = "id_product";

    protected $guarded = ["id_product"];


    protected $fillable = [
        'id_product',
        'id_store',
        'name',
        'description',
        'price',
        'stock',
        'status',
    ];

    public function store(): BelongsTo
    {
        return $this->belongsTo(Store::class, 'id_store', 'id_store');
    }

    public function carts(): BelongsToMany
    {
        return $this->belongsToMany(Cart::class, 'products_carts', 'id_product', 'id_cart')
                    ->withPivot(['quantity', 'final_price']);
    }

}
