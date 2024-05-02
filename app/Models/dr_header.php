<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class dr_header extends Model
{
    protected $table = 'dr_header';

    public function itemSales()
    {
        return $this->hasMany(dr_item_sales::class, 'date', 'date');
    }
}