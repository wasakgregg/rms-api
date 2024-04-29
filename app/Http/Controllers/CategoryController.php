<?php

namespace App\Http\Controllers;

use App\Models\dr_category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function categorylist(){
        return dr_category::all();
    }
}
