<?php
namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Company extends Model
{
    use LocalFactory;
    protected $table      = "companies";
    protected $primaryKey = "id_company";

    protected $guarded = ["id_company"];

    protected $fillable = [
        "id_company",
        'email',
        'name',
        'last_name',
        'address',
        'company_type',
        'status',
    ];

    // relations stores

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'companies
        _users', 'id_company', 'id_user')
            ->select('users.id_user', 'email', 'name', 'last_name', 'address', 'company_type', 'users.status')
            ->withPivot('status')
            ->wherePivot('status', 1);
    }

    public function stores(): HasMany
    {
        return $this->hasMany(Store::class, 'id_company', 'id_company')->select(['id_store', 'id_company', 'name', 'address', 'status']);
    }


    // reation with Cart
    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class, 'id_company', 'id_company')->select(['id_cart', 'id_company', 'status', 'total_price', 'bought_at']);
    }

}
