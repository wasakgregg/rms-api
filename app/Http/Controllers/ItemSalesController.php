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

    public function ProductMix(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branch = $request->input('branch');
        $category = $request->input('category');
    
        // Perform the query using Eloquent
        $productMix = dr_item_sales::select(
                'dr_item_sales.category_code',
                'dr_category.category_desc',
                'dr_item_sales.product_code',
                'dr_item_sales.description',
                DB::raw('SUM(dr_item_sales.quantity) as total_quantity'),
                DB::raw('SUM(dr_item_sales.net_sales) as total_net_sales')
            )
            ->join('dr_category', 'dr_category.category_code', '=', 'dr_item_sales.category_code')
            ->whereBetween('date', [$startDate, $endDate])
            ->groupBy('dr_item_sales.category_code', 'dr_item_sales.product_code');// Corrected groupBy

            if($branch !== 'ALL' && !is_null($branch)){
                $productMix->where('branch', $branch);
            }

            if($category !== 'ALL' && !is_null($category)){
                $productMix->where('dr_category.category_code', $category);
            }
            
            $results = $productMix->get();

            $data = [];

            foreach($results as $result){
                $data[] = [
                    'category_code' => $result->category_code,
                    'category_desc' => $result->category_desc,
                    'product_code' => $result->product_code,
                    'description' => $result->description,
                    'total_quantity' => $result->total_quantity,
                    'total_net_sales' => $result->total_net_sales,
                ];
            }
            return response()->json($data);
    }
    
}
