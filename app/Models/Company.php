<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use LocalFactory;
    protected $table = "companies";
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

}
