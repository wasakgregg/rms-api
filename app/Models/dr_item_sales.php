<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class dr_item_sales extends Model
{
    protected $table = 'dr_item_sales';
    use HasFactory;

    public function category(){
        return $this->belongsTo(dr_category::class, 'category_code','category_code' );
    }
}
