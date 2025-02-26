<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function store()
    {
        return $this->belongsTo(Store::class, 'id_store', 'id_store');
    }

}
