<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dr_category extends Model
{
    protected $table = 'dr_category';
    use HasFactory;

    public function itemSales(){
        return $this->hasMany(dr_item_sales::class, 'category_code', 'category_code');

    }
}


