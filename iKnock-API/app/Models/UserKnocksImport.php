<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GeneaLabs\LaravelModelCaching\Traits\Cachable;

class UserKnocksImport extends Model
{
    use HasFactory,Cachable;

    protected $table = "user_knocks_imports";

    protected $guarded = array();
}
