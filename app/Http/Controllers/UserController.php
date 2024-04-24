<?php

namespace App\Http\Controllers;

use App\Models\Dr_user;
use Illuminate\Http\Request;

class UserController extends Controller
{
    function list(Request $request)
    {
        $user_id = $request->query('user_id');

        // Check if user_id is provided
        if ($user_id) {
            // If user_id is provided, return the user with that ID
            return Dr_user::where('user_id', $user_id)->first();
        } else {
            // If user_id is not provided, return all users
            return Dr_user::all();
        }
    }
}
