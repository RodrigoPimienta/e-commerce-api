<?php
namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\DB;

class Cart extends Model
{
    use LocalFactory;

    protected $table      = "carts";
    protected $primaryKey = "id_cart";

    protected $guarded = ["id_cart"];

    protected $fillable = [
        'id_cart',
        'id_company',
        'status',
    ];

    // relation with Company

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }

    // relation with ProductCart multiple

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(ProductStore::class, 'products_carts', 'id_cart', 'id_product')
        ->withPivot(['quantity', 'final_price'])
        ->select([
            'products_stores.id_product',
            'products_stores.id_store',
            'products_stores.name as product_name',
            'products_stores.description as product_description',
            'products_stores.status as product_status',
            'products_carts.quantity',
            DB::raw('(CASE WHEN products_carts.final_price IS NULL THEN products_stores.price ELSE products_carts.final_price END) as price')
        ]);
    }

}
