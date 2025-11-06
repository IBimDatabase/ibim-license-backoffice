<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\DashboardService;
use App\Models\Product;
use App\Models\ProductLicenseKeys;
use App\Models\LicenseType;
use App\Models\Customer;

class DashboardController extends Controller
{
    public function dashboard() 
    {
        return view('dashboard');
    }

    public function getSummary()
    {
        $response = DashboardService::getSummaryData();
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

    
    public function getTodayPurchases()
    {
        $response = DashboardService::getTodayPurchasesData();
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

    public function getProductBasedLicenseSummary()
    {
        $response = DashboardService::getProductBasedLicenseSummaryData();
        $response = json_decode($response);

        return response()->json(["status" => $response->status, "code" => $response->status_code, "message" => $response->message, "data" => $response->data], $response->status_code);
    }

}
