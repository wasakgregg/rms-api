<?php

namespace App\Http\Controllers;

use App\Models\dr_payment_details;
use Illuminate\Http\Request;


class PaymentDetailsController extends Controller
{
    public function PaymentDetails(Request $request){
        
        $month = $request->input('month');
        $branch = $request->input('branch');
        $concept = $request->input('concept');
    
        $query = dr_payment_details::query();

       if($concept != "ALL"){
        $baseQuery = $query->selectRaw('description, SUM(amount) as total_amount')
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
        ->where('concept_id', $concept)
        ->whereNotIn('description', ['Discount', 'MEM CREDIT'])
        ->groupBy('description');
    

        if($branch !== 'ALL' && !is_null($branch)){
            $baseQuery->where('branch', $branch);
        }

       }else{
        $baseQuery = $query->selectRaw('description, SUM(amount) as total_amount')
        ->whereRaw('DATE_FORMAT(date, "%Y-%m") = ?', [$month])
        ->whereNotIn('description', ['Discount', 'MEM CREDIT'])
        ->groupBy('description');
    

        if($branch !== 'ALL' && !is_null($branch)){
            $baseQuery->where('branch', $branch);
        }
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

    public function paymentData(Request $request){
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $branch = $request->input('branch');
    $concept = $request->input('concept');

    $paymentData = dr_payment_details::select(
        'date',
        'branch',
        'pay_type',
        'description',
        'amount'
    )
    ->whereBetween('date',[$startDate, $endDate]);

    if($concept != 'ALL' ){
        $paymentData->where('concept_id', $concept);
    }
    
    if($branch != 'ALL'){
        $paymentData->where('branch', $branch);
    }

    $results = $paymentData->get();

    $data=[];

    foreach($results as $result){
        $data[] = [
            'data_formatted' =>$result->date,
            'branch' => $result->branch,
            'description' =>$result->description,
            'amount' => floatval($result->amount),
        ];
    }

    return response()->json($data);
}

}
