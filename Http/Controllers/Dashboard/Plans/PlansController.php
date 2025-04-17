<?php

namespace App\Http\Controllers\Dashboard\Plans;



use App\Http\Requests\PlanRequest;
use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionUser;

class PlansController extends Controller
{

    //    public function __construct()
    //    {
    //        $this->middleware('permission:user-list' . session('appKey'), ['only' => ['index']]);
    //        $this->middleware('permission:user-create' . session('appKey'), ['only' => ['create', 'store']]);
    //        $this->middleware('permission:user-edit' . session('appKey'), ['only' => ['edit', 'update']]);
    //        $this->middleware('permission:user-delete' . session('appKey'), ['only' => ['destroy']]);
    //    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $plans = Subscription::whereNotNull('title')->orderBy('id', 'DESC')->get();
        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */

    public function store(PlanRequest $request)
    {
	
        $data = $request->validated();
        $data += [
            'title' => $request->title,
            'details' => $request->details,
            'android' => $request->android,
			 'ios' => $request->ios,
            'validity' => $request->validity,
            'status' => $request->planStatus,
        ];
        $Subscription = Subscription::create($data);

        return redirect()->route('plans.index')
            ->with('success', __('words.plan_created'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $Subscription = Subscription::where('id', $id)->first();

        return view('admin.plans.show', compact('Subscription'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $plan = Subscription::where("id", $id)->first();

        return view('admin.plans.update', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PlanRequest $request, $id)
    {
        $data = $request->validated();

        $plan = Subscription::where("id", $id)->first();

        $data += [
            'title' => $request->title,
            'details' => $request->details,
            'android' => $request->android,
			 'ios' => $request->ios,
            'validity' => $request->validity,
            'status' => $request->planStatus,
        ];

        $plan->update($data);

        return redirect()->route('plans.index')
            ->with('success', __('words.subscribtion_updated'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // Subscription::where("id", $id)->delete();

        // return redirect()->route('plans.index')
            // ->with('success', __('words.client_deleted'));
    }

    public function subscribedusers(){
        $subscriptionUser = SubscriptionUser::get();

        return view('admin.plans.subscribedusers', compact('subscriptionUser'));
    }


    // Function to get the first 5 words from a given string
    static function getFirstFiveWords($inputString)
    {
        $words = str_word_count($inputString, 1);
        $selectedWords = array_slice($words, 0, 5); 
        $result = implode(' ', $selectedWords); 
        return $result.'...'; 
    }
	   public function changeStatus(Request $request)
    {
        \Log::info('Change status request received', $request->all());

        $plan = Plan::find($request->plan_id);
        if ($plan) {
            $plan->status = $request->status;
            $plan->save();

            \Log::info('Status updated successfully', ['plan_id' => $plan->id, 'status' => $plan->status]);

            return response()->json(['success' => true]);
        }

        \Log::error('Failed to update status', ['plan_id' => $request->plan_id]);

        return response()->json(['success' => false], 400);
    }

}
