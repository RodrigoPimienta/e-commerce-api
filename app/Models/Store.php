<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Store extends Model
{
    use LocalFactory;
    protected $table = "stores";
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



}
