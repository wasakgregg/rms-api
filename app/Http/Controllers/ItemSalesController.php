<?php

namespace App\Http\Controllers;

use App\Models\dr_header;
use App\Models\dr_item_sales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemSalesController extends Controller
{
    public function calculateAverageSalesPerDay(Request $request)
    {
        $month = $request->query('month');
        $concept = $request->query('concept');
        $branch = $request->query('branch');
    
        $query = dr_item_sales::query()->whereRaw('date_format(date, "%Y-%m") = ?', [$month]);
    
        // Apply concept filter if concept is not 'ALL'
        if ($concept !== 'ALL') {
            $query->where('concept_name', $concept);
        }
    
        // Apply branch filter if branch is not 'ALL'
        if ($branch !== 'ALL') {
            $query->where('branch', $branch);
        }
    
        // Calculate average sales per day
        $averageSales = $query->selectRaw('(SUM(net_sales) / COUNT(DISTINCT date)) AS average_sales')->first();
    
        // If the result is null or average_sales is null, return 0
        if (!$averageSales || is_null($averageSales->average_sales)) {
            return response()->json([
                'average_sales' => 0
            ]);
        }
    
        // Return the average sales per day
        return response()->json([
            'average_sales' => $averageSales->average_sales
        ]);
    }
    

    public function calculateTotalSales(Request $request)
    {
        $month = $request->input('month');
        $branch = $request->input('branch');
        $concept  = $request->input('concept');

        $query = dr_item_sales::query();

        if($concept != "ALL"){
          
            if ($branch == 'ALL' || empty($branch)) {
                $totalSales = $query->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                    ->where('concept_name', $concept)
                                    ->sum('net_sales');
            } else {
                $totalSales = $query->where('branch', $branch)
                                    ->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                    ->sum('net_sales');
            }
            
        }else{
            
              if ($branch == 'ALL' || empty($branch)) {
            $totalSales = $query->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                ->sum('net_sales');
        } else {
            $totalSales = $query->where('branch', $branch)
                                ->whereRaw('date_format(date, "%Y-%m") = ?', [$month])
                                ->sum('net_sales');
        }
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
        $concept = $request->input('concept');
    
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
        ->whereBetween('date', [$startDate, $endDate]);
    
    if ($concept != 'ALL') {
        $productMix->where('dr_item_sales.concept_name', $concept);
    }
    
    if ($category != 'ALL') {
        $productMix->where('dr_item_sales.category_code', $category);
    }
    
    if ($branch != 'ALL') {
        $productMix->where('dr_item_sales.branch', $branch);
    }
    
    $productMix->groupBy('dr_item_sales.category_code', 'dr_item_sales.product_code');
    

     
            
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

    public function DailySalesReport(Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $branch = $request->input('branch');
        $concept = $request->input('concept');
    
        // Initialize SQL query and parameters
        $sql = "SELECT 
                    dr_header.date, 
                    dr_header.branch, 
                    dr_header.or_from, 
                    dr_header.or_to, 
                    dr_header.beg_balance, 
                    dr_header.end_balance, 
                    dr_header.no_transaction, 
                    dr_header.reg_guest, 
                    dr_header.ftime_guest, 
                    dr_header.no_void, 
                    dr_header.senior_disc, 
                    dr_header.pwd_disc, 
                    dr_header.other_disc, 
                    dr_header.open_disc, 
                    dr_header.employee_disc, 
                    dr_header.vip_disc, 
                    dr_header.promo_disc, 
                    dr_header.free_disc, 
                    dr_header.z_count, 
                    SUM(dr_item_sales.total_gross) AS total_gross, 
                    SUM(dr_item_sales.service_charge) AS service_charge, 
                    SUM(dr_item_sales.net_sales) AS net_sales
                FROM 
                    dr_header
                LEFT JOIN 
                    dr_item_sales ON dr_header.date = dr_item_sales.date
                WHERE 
                    dr_header.date BETWEEN ? AND ?";
    
        $params = [$startDate, $endDate];
    
        // Add additional conditions based on the concept and branch values
        if ($concept != "ALL") {
            $sql .= " AND dr_header.concept_name = ?";
            $params[] = $concept;
        }
    
        if ($branch != "ALL" && !empty($branch)) {
            $sql .= " AND dr_header.branch = ?";
            $params[] = $branch;
        }
    
        // Add GROUP BY clause to the SQL query
        $sql .= " GROUP BY 
                    dr_header.date, 
                    dr_header.branch, 
                    dr_header.or_from, 
                    dr_header.or_to, 
                    dr_header.beg_balance, 
                    dr_header.end_balance, 
                    dr_header.no_transaction, 
                    dr_header.reg_guest,
                    dr_header.ftime_guest, 
                    dr_header.no_void, 
                    dr_header.senior_disc, 
                    dr_header.pwd_disc, 
                    dr_header.other_disc, 
                    dr_header.open_disc, 
                    dr_header.employee_disc, 
                    dr_header.vip_disc, 
                    dr_header.promo_disc, 
                    dr_header.free_disc, 
                    dr_header.z_count";
    
        // Execute the query
        $result = DB::select($sql, $params);
    
        // Format numerical values to two decimal places
        foreach ($result as $row) {
            $row->total_gross = number_format($row->total_gross, 2);
            $row->service_charge = number_format($row->service_charge, 2);
            $row->net_sales = number_format($row->net_sales, 2);
        }
    
        // Return response
        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    }
    


public function discountData(Request $request){

    $branch = $request->input('branch');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $concept = $request->input('concept');

    $discountData = dr_item_sales::select(
        'date',
        DB::raw('SUM(net_sales) AS net_sales'),
        DB::raw('SUM(senior_disc) AS senior_discount'),
        DB::raw('SUM(pwd_disc) AS pwd_discount'),
        DB::raw('SUM(other_disc) AS other_discount'),
        DB::raw('SUM(open_disc) AS open_discount'),
        DB::raw('SUM(employee_disc) AS employee_discount'),
        DB::raw('SUM(vip_disc) AS vip_discount'),
        DB::raw('SUM(PROMO) AS promo'),
        DB::raw('SUM(FREE) AS free')
    )
    ->whereBetween('date', [$startDate, $endDate]);

    if($concept != 'ALL'){
        $discountData->where('concept_name', $concept);
    }

    if($branch != 'ALL'){
        $discountData->where('branch', $branch);
    }

    $discountData->groupBy('date');

    $results = $discountData->get();

    $data = [];

    foreach($results as $result){
        $data[] = [
            'date_formatted' => $result->date,
            'senior_discount' => $result->senior_discount,
            'pwd_discount' => $result->pwd_discount,
            'other_discount' => $result->other_discount,
            'open_discount' => $result->open_discount,
            'employee_discount' => $result->employee_discount,
            'vip_discount' => $result->vip_discount,
            'promo'=> $result->promo,
            'free' => $result->free,
        ];
    }
    
    return response()->json($data);
}

}