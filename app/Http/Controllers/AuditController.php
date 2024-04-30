<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dr_audit;
use App\Models\dr_header;


class AuditController extends Controller
{
   
    public function averageSalesPerCustomer(Request $request)
{   
    $branch = $request->query('branch');
    $month = $request->query('month');
    $concept = $request->query('concept');

    // Query to calculate the total sales
    $totalSalesQuery = Dr_audit::query();

   if($concept != "ALL"){
    $totalSalesQuery->selectRaw('SUM(net_sales + delivery_charge + service_charge) as total_sales')
        ->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
        ->where('concept_id', $concept);

    if($branch !== 'ALL' && !is_null($branch)){
    $totalSalesQuery->where('store_name', $branch);
    }
   }else{
    $totalSalesQuery->selectRaw('SUM(net_sales + delivery_charge + service_charge) as total_sales')
    ->whereRaw('date_format(date, "%Y-%m") = ?', [$month]);
        if($branch !== 'ALL' && !is_null($branch)){
        $totalSalesQuery->where('store_name', $branch);
        }

   }
    $totalSalesResult = $totalSalesQuery->first();

    // Query to calculate the total number of guests
    $totalGuestsQuery = dr_header::query();
    $totalGuestsQuery->selectRaw('SUM(reg_guest) as total_guests')
                     ->whereRaw('date_format(date, "%Y-%m") = ?', [$month]);
    if($branch !== 'ALL' && !is_null($branch)){
        $totalGuestsQuery->where('branch', $branch);
    }
    $totalGuestsResult = $totalGuestsQuery->first();

    // Calculate the average sales per customer
    if($totalGuestsResult->total_guests != 0){
        $averageSalesPerCustomer = $totalSalesResult->total_sales / $totalGuestsResult->total_guests;
    } else {
        $averageSalesPerCustomer = 0;
    }

    return response()->json([
        'average_sales_per_customer' => $averageSalesPerCustomer
    ]);
}

}