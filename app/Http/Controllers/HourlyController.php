<?php

namespace App\Http\Controllers;

use App\Models\dr_hourly;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Corrected namespace

class HourlyController extends Controller
{
    //
    public function HourlySales(Request $request){
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branch = $request->input('branch');
        $concept = $request->input('concept');
    
        $hourlySales = dr_hourly::selectRaw("
            CONCAT(
                LPAD(hour % 12, 2, '0'),
                ':00',
                IF(hour<12, 'AM', 'PM'),
                ' - ', 
                LPAD((hour + 1) % 12, 2, '0'),
                ':00 ',
                IF((hour + 1 )% 24 < 12, 'AM' , 'PM')
            ) as hour_range,
            SUM(no_tx) AS total_no_tx,
            SUM(no_void_tx) AS total_no_void_tx,
            SUM(sales_value) AS total_sales_value,
            SUM(discount_amount) AS total_discount_amount
        ")
        ->whereBetween('date', [$startDate, $endDate]);
    
        // Apply branch filter only if branch is not "ALL"
        if($branch !== "ALL"){
            $hourlySales->where('branch', $branch);
        }
    
        // Apply concept filter only if concept is not "ALL"
        if($concept !== "ALL"){
            $hourlySales->where('concept_name', $concept);
        }
    
        $hourlySales->groupBy(DB::raw('FLOOR(hour / 1)'));
    
        $results = $hourlySales->get();
    
        $data = [];
    
        foreach($results as $result){
            $data[] = [
                'hour_range' => $result->hour_range,
                'no_trans' => $result->total_no_tx,
                'no_void' => $result->total_no_void_tx,
                'sales_value' => number_format($result->total_sales_value, 2),
                'discount_amount' => number_format($result->total_discount_amount , 2)
            ];
        }
        return response()->json($data);
    }
}
