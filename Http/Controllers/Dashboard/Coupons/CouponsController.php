<?php

namespace App\Http\Controllers\Dashboard\Coupons;

use App\Http\Controllers\Controller;
use App\Models\Coupons as Coupons;
use Illuminate\Http\Request;

class CouponsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $coupons = Coupons::whereNotNull('code')->orderBy('id', 'DESC')->get();
        return view('admin.Coupons.index', compact('coupons'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
        return view('admin.Coupons.create');

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // $request->validate([
        //     'code' => 'required|unique:coupons',
        //     'type' => 'required',
        //     'percent_value' => 'required|numeric',
        //     'discount_value' => 'required|numeric'            
        // ]);
        // $data = $request->validated();
        $discount_value = $request->couponType == 'fixed'? $request->discount_value: 0;
        $percent_value = $request->couponType == 'percent'? $request->percent_value: 0;
        $data = [
            'code' => $request->couponcode,
            'type' => $request->couponType,
            'percent_value' => $percent_value,
            'discount_value' => $discount_value,
            'limit_user' => $request->limit_user,
            'expiry_date' => $request->expiry_date,
        ];
        $coupon = Coupons::create($data);

        session()->flash('message','Coupon has been created successfully!');
        return redirect()->route('coupons.index')
        ->with('success', __('words.coupon_created'));
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $coupon = Coupons::where('id', $id)->first();

        return view('admin.Coupons.show', compact('coupon'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
        $Coupon = Coupons::where("id", $id)->first();

        return view('admin.Coupons.update', compact('Coupon'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $discount_value = $request->couponType == 'fixed'? $request->discount_value: 0;
        $percent_value = $request->couponType == 'percent'? $request->percent_value: 0;   
        $Coupon = Coupons::where("id", $id)->first();

        $data = [
            'code' => $request->couponcode,
            'type' => $request->couponType,
            'percent_value' => $percent_value,
            'discount_value' => $discount_value,
            'limit_user' => $request->limit_user,
            'expiry_date' => $request->expiry_date,
        ];
        $Coupon->update($data);

        session()->flash('message','Coupon has been Updated successfully!');
        return redirect()->route('coupons.index')
        ->with('success', __('words.coupon_updated'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        Coupons::where("id", $id)->delete();

        return redirect()->route('coupons.index')
            ->with('success', __('words.client_deleted'));
    }
}
