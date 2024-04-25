<?php

namespace App\Http\Controllers;

use App\Models\dr_payment_details;
use Illuminate\Http\Request;


class PaymentDetailsController extends Controller
{
    public function PaymentDetails(Request $request){
        
        $month = $request->input('month');
        $branch = $request->input('branch');
    
        $query = dr_payment_details::query();

        $baseQuery = $query->selectRaw('description, SUM(amount) as total_amount')
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
        ->whereNotIn('description', ['Discount', 'MEM CREDIT'])
        ->groupBy('description');
    

        if($branch !== 'ALL' && !is_null($branch)){
            $baseQuery->where('branch', $branch);
        }

        $results = $baseQuery->get();

        $data = [];
     
        
        foreach($results as $result){
          
            $data[] = [
                'description' => $result->description,
                'amount' => (float)$result->total_amount
            ];
        }
        return response()->json($data);
    }
}
