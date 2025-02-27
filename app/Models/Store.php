<?php
namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Store extends Model
{
    use LocalFactory;
    protected $table      = "stores";
    protected $primaryKey = "id_store";

    protected $guarded = ["id_store"];

    protected $fillable = [
        "id_store",
        "id_company",
        "name",
        "address",
        "status",
    ];

    // relation with Company
    public function company()
    {
        return $this->belongsTo(Company::class, 'id_company', 'id_company');
    }

    // relation with ProductStore
    public function products(): HasMany
    {
        return $this->hasMany(ProductStore::class, 'id_store', 'id_store')->select(['id_product', 'id_store', 'name', 'description', 'price', 'stock', 'status']);
    }

    public function productsCarts(int $id = 0)
    {
        return DB::table('products_carts')
            ->join('products_stores', 'products_carts.id_product', '=', 'products_stores.id_product')
            ->join('carts', 'products_carts.id_cart', '=', 'carts.id_cart')
            ->select(
                'products_carts.id_cart',
                'carts.id_company',
                'carts.bought_at',
                'carts.status as cart_status',
                'products_carts.id_product',
                'products_stores.name as product_name',
                'products_stores.description as product_description',
                'products_carts.quantity',
                'products_carts.final_price'
            )
            ->where('products_stores.id_store', $id)
            ->get();
    }

    public function formatSells()
    {
        if ($this->productsCarts->isEmpty()) {
            return [];
        }
        $groupedByCart = $this->productsCarts->groupBy('id_cart')->map(function ($cartGroup) {
            $totalItems = $cartGroup->sum('quantity');

            $totalPrice = $cartGroup->sum(function ($cartItem) {
                return $cartItem->final_price * $cartItem->quantity;
            });

            return [
                'id_cart'     => $cartGroup->first()->id_cart,
                'total_items' => $totalItems,
                'total_price' => $totalPrice,
                'bought_at'   => $cartGroup->first()->bought_at,
                'cart_status' => $cartGroup->first()->cart_status,
                'products'    => $cartGroup->map(function ($cartItem) {
                    return [
                        'id_product'          => $cartItem->id_product,
                        'product_name'        => $cartItem->product_name,
                        'product_description' => $cartItem->product_description,
                        'quantity'            => $cartItem->quantity,
                        'price'               => $cartItem->final_price,
                        'total'               => $cartItem->quantity * $cartItem->final_price, // Total por producto
                    ];
                }),
            ];
        });

        return $groupedByCart->values()->all();
    }

}
