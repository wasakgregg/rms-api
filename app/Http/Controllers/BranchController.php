<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dr_branch;

class BranchController extends Controller
{
    function branchList(){
        
        return dr_branch::all();
    }
}
