<?php

namespace App\Http\Controllers;

use App\Models\dr_concept;
use Illuminate\Http\Request;

class ConceptController extends Controller
{
    public function ConceptList(){

        return dr_concept::all();
    }
}
