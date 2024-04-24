<?php

namespace App\Http\Controllers;

use App\Models\dr_item_sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSalesController extends Controller
{
    public function CalculateAverageSalesPerDay(Request $request)
    {
        $month = $request->query('month');
        $branch = $request->query('branch');

        $query = dr_item_sales::query();

        // Build the base SQL query without the branch filter
        $baseQuery = $query->selectRaw('(SUM(net_sales) / (SELECT COUNT(date) FROM (SELECT DISTINCT(date) FROM dr_item_sales WHERE date_format(date, "%Y-%m") = ? GROUP BY date) AS sub_dr_item_sales)) AS average_sales', [$month])
            ->whereRaw('date_format(date, "%Y-%m") = ?', [$month]);

        // If branch is not 'ALL', add the branch filter to the base query
        if ($branch !== 'ALL' && !is_null($branch)) {
            $baseQuery->where('branch', $branch);
        }

        // Execute the query and retrieve the result
        $result = $baseQuery->first();

        // If the result is null or average_sales is null, return 0
        if (!$result || is_null($result->average_sales)) {
            return response()->json([
                'average_sales' => 0
            ]);
        }

        // Return the average sales
        return response()->json([
            'average_sales' => $result->average_sales
        ]);
    }

    public function calculateTotalSales(Request $request)
    {
        $month = $request->input('month');
        $branch = $request->input('branch');

        $query = dr_item_sales::query();

        if ($branch == 'ALL' || empty($branch)) {
            $totalSales = $query->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                ->sum('net_sales');
        } else {
            $totalSales = $query->where('branch', $branch)
                                ->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                ->sum('net_sales');
        }

        return response()->json([
            'total_sales' => $totalSales ?? 0, // Provide a default value of 0 if $totalSales is null
        ]);
    }


    
}
