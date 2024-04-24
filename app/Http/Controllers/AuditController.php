<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Dr_audit;

class AuditController extends Controller
{
    function audit(Request $request)
    {
        $storeName = $request->input('store_name');
        $date = $request->input('date');

        // If JSON payload is sent
        if ($request->isJson()) {
            $jsonPayload = $request->json()->all();
            $storeName = $jsonPayload['store_name'] ?? $storeName;
            $date = $jsonPayload['date'] ?? $date;
        }

        // Query audits based on store_name and date
        $query = Dr_audit::query();

        if ($storeName) {
            $query->where('store_name', $storeName);
        }

        if ($date) {
            $query->whereDate('date', $date);
        }

        return $query->get();
    }
}
