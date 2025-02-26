<?php

namespace App\Models;

use Carbon\Traits\LocalFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyUser extends Model
{
    use LocalFactory;

    protected $table = "companies_users";
    protected $primaryKey = "id";

    protected $guarded = ["id"];
    protected $fillable = [
        'id_company',
        'id_user',
        'status',
    ];
}
