<?php

namespace App\Http\Controllers\Dashboard\Auth;


use App\Http\Controllers\Controller;

use App\Models\Admin;
use App\Models\AgeStatistic;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

use function App\helpers\update;

class AuthController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login_form()
    {
        //
        return view('admin.Auth.login');
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);
        $credentials = $request->only('email', 'password');
        if (Auth::attempt($credentials)) {
            return redirect()->intended('/')
                ->withSuccess('Signed in');
        }
        return redirect("login")->withSuccess('Login details are not valid');
    }

    public function regisetration()
    {
        return view('auth.registration');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $check = $this->create($data);

        return redirect("dashboard")->withSuccess('You have signed-in');
    }

    public function create(array $data)
    {
        return Admin::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public function dashboard()
    {
        if (Auth::check()) {
            $min_age = 10;
            $max_age = 60;
            $clientsCount = User::count();
            $adminCount = Admin::count();
            $minAge = AgeStatistic::where('min_age', '>=', $min_age)->min('min_age');
            $maxAge = AgeStatistic::where('max_age', '<=', $max_age)->max('max_age');

            $AgeStatisticCount = AgeStatistic::where('min_age', '>=', $min_age)
                ->where('max_age', '<=', $max_age)
                ->count();
                  $doctor_Count = Admin::where('type','doctor')->count();
            return view('admin.home', compact(['clientsCount', 'adminCount', 'AgeStatisticCount','minAge','maxAge','doctor_Count']));
        }

    }

    public function logout()
    {
        Session::flush();
        Auth::logout();

        return redirect()->route('login_form');
    }

    public function profile()
    {
        $admin = auth()->user();
        return view(session('dashboard') . '.admin.Auth.profile', compact('admin'));
    }

    public function edit_profile(Request $request)
    {
        $admin = Auth::user();
        $admin->name = $request->name;
        $admin->email = $request->email;
        $admin->save();
        if ($admin->save()) {
            return redirect()->route('home')->with(['success' => __('words.admin_deleted'), 'status' => 'success']);
        }
        return redirect()->route('home')->with('error', 'Error Occur Edit Profile');
    }
}
