<?php

namespace App\Http\Controllers\Api\V1;

use App\CentralLogics\Helpers;
use App\Http\Controllers\Controller;
use App\Models\CashBack;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CashBackController extends Controller
{
    public function getCashback(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required',
        ]);
        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::error_processor($validator)], 403);
        }
        $customer_id=Auth::user()?->id ?? $request->customer_id ?? 'all';
        return  Helpers::getCalculatedCashBackAmount(amount:$request->amount, customer_id:$customer_id);
    }
    public function list(){
        $customer_id=Auth::user()?->id ?? 'all';
        $data =CashBack::active()
        ->Running()
        ->where(function($query)use($customer_id){
            $query->whereJsonContains('customer_id', [$customer_id])->orWhereJsonContains('customer_id', ['all']);
        })
        ->orderBy('cashback_amount','desc')->get();
        return response()->json($data, 200);
    }

}
