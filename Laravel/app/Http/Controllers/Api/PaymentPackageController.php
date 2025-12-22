<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\PaymentPackage;
use Illuminate\Http\Request;

class PaymentPackageController extends Controller
{
    public function index()
    {
        $packages = PaymentPackage::where('status', 1)
        ->orderBy('display_order', 'asc')
        ->get();
        return response()->json($packages);
    }
}