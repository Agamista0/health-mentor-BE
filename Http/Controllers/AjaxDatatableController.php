<?php

namespace App\Http\Controllers;

use App\Models\Coupons;
use App\Models\SubscriptionUser;
use Carbon\Carbon;
use Illuminate\Http\Request;


class AjaxDatatableController extends Controller
{
        /**
     * Display a listing of the resource.
     */
    public function GetCouponsbyselect(Request $request)
    {
        //
        $status = $request->input('status');
        $currentDate = Carbon::now();
        $endDate = $currentDate->copy()->addDays(30)->endOfDay(); // Date after a month

        if ($status === 'select_all') {
            $data = Coupons::get();

        } elseif ($status === 'percent') {
            $data = Coupons::where('type', 'percent')->get();

        } elseif ($status === 'fixed') {
            $data = Coupons::where('type', 'fixed')->get();

        } elseif ($status === 'valid') {
            $data = Coupons::where('expiry_date', '>', $currentDate)->WhereRaw('CAST(limit_user AS UNSIGNED) > CAST(used AS UNSIGNED)')->get();

        } elseif ($status === 'expired') {
            $data = Coupons::where('expiry_date', '<', $currentDate)->orWhereRaw('CAST(limit_user AS UNSIGNED) <= CAST(used AS UNSIGNED)')->get();

        }  elseif ($status === 'expire_next_month') {
            $nextMonth = Carbon::now()->addMonth()->endOfMonth();
            $data = Coupons::where('expiry_date', '>', $currentDate)
                                        ->where('expiry_date', '<=', $nextMonth)
                                        ->get();
       
        }elseif ($status === 'expire_in_current_month') {
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();
            $data = Coupons::whereBetween('expiry_date', [$startOfMonth, $endOfMonth])->get(); // Fetch data expiring in the current month
        } else {
            $data = [];
        }

        return response()->json($data);
    }


    public function GetUserExpiredPlans(Request $request)
    {
        //
        $status = $request->input('status');
        $currentDate = Carbon::now();
        $endDate = $currentDate->copy()->addDays(30)->endOfDay(); // Date after a month

        if ($status === 'select_all') {
            $data = SubscriptionUser::get();

        } elseif ($status === 'expired') {
            $data = SubscriptionUser::where('end_date', '<', $currentDate)->get();

        } elseif ($status === 'valid') {
            $data = SubscriptionUser::where('end_date', '>', $currentDate)->get();

        } elseif ($status === 'expire_next_month') {
            $nextMonth = Carbon::now()->addMonth()->endOfMonth();
            $data = SubscriptionUser::where('end_date', '>', $currentDate)
                                        ->where('end_date', '<=', $nextMonth)
                                        ->get();

        }elseif ($status === 'expire_in_current_month') {
            $startOfMonth = $currentDate->copy()->startOfMonth();
            $endOfMonth = $currentDate->copy()->endOfMonth();
            $data = SubscriptionUser::whereBetween('end_date', [$startOfMonth, $endOfMonth])->get();

            
            // Fetch data expiring in the current month
        } else {
            $data = [];
        }

        return response()->json($data);
    }
    
    
}
