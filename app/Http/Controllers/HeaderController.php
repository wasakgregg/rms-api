<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\dr_header;


class HeaderController extends Controller
{
    
    public function CalculateAverageTxPerDay(Request $request){

        $month = $request->input('month');
        $branch = $request->input('branch');
    
        $query = dr_header::query();
    
        $baseQuery = $query->selectRaw('SUM(no_transaction) as averageTx')
            ->whereRaw('date_format(date, "%Y-%m") = ?', [$month]);
    
        if($branch !== 'ALL' && !is_null($branch)){
            $baseQuery->where('branch', $branch);
        }
    
        $daysInMonth = (int)date('t', strtotime($month));
    
        $result = $baseQuery->first();
        $averageTxPerDay = $result->averageTx / $daysInMonth;
        
        return response()->json([
            'average_transaction_per_day' => $averageTxPerDay
        ]);
    }
    
}
