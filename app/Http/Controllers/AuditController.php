<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dr_audit;
use App\Models\dr_header;
use Illuminate\Support\Facades\DB;



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
                ->where('concept_name', $concept);

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

        public function AuditReport(Request $request){

            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $concept = $request->input('concept');
            $branch = $request->input('branch');
        
            $auditReport = 'SELECT * FROM dr_audit WHERE date BETWEEN ? and ? ';
        
            $params = [$startDate, $endDate];
        
            if($concept != 'ALL'){
                $auditReport .= "AND concept_name  = ?";
                $params[] = $concept;
            } elseif($branch != 'ALL' && !empty($branch)){
                $auditReport .= "AND store_name = ?";
                $params[]  = $branch;
            }
        
            $auditReport .= " ORDER BY or_no, store_name, date";
        
            $result = DB::select($auditReport, $params);
            $data = [];
            foreach($result as $row){
                $data[] = [
                    'store_name' => $row->store_name,
                    'product_code' => $row->product_code,
                    'description' => $row->description,
                    'category' => $row->category,
                    'sub_category' => $row->sub_category,
                    'date' => $row->date,
                    'time' => $row->time,
                    'term_no' => $row->term_no,
                    'or_no' => $row->or_no,
                    'price' => $row->price,
                    'qty_sold' => $row->qty_sold,
                    'discount_amount' => $row->discount_amount,
                    'net_sales' => $row->net_sales,
                    'gross_sales' => $row->gross_sales,
                    'discount_type' => $row->discount_type,
                    'vatable' => $row->vatable,
                    'vat_exempt' => $row->vat_exempt,
                    'vat_amount' => $row->vat_amount,
                    'vat_zero_rated' => $row->vat_zero_rated,
                    'transaction_type' => $row->transaction_type,
                    'pay_type_1' => $row->pay_type_1,
                    'pay_amount_1' => $row->pay_amount_1,
                    'pay_type_2' => $row->pay_type_2,
                    'pay_amount_2' => $row->pay_amount_2,
                    'pay_type_3' => $row->pay_type_3,
                    'pay_amount_3' => $row->pay_amount_3,
                    'service_charge' => $row->service_charge,
                    'delivery_charge' => $row->delivery_charge,
                    'cashier' => $row->cashier,
                ];
            }
            return response()->json($data);
        }
        
}