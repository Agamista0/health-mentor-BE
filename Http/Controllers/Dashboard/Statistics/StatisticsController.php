<?php

namespace App\Http\Controllers\Dashboard\Statistics;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AgeStatistic;
use App\Models\User;
use Illuminate\Http\Request;

class StatisticsController extends Controller
{
     public function index()
     {

         $clientsCount = User::count();
         $adminCount = Admin::count();
         $min_age = 15;
         $max_age = 60;
         $minAge = AgeStatistic::where('min_age', '>=', $min_age)->min('min_age');
         $maxAge = AgeStatistic::where('max_age', '<=', $max_age)->max('max_age');
         $AgeStatisticCount = AgeStatistic::where('min_age', '>=', $min_age)
             ->where('max_age', '<=', $max_age)
             ->count();
                $doctor_Count = Admin::where('type','doctor')->count();
         return view('admin.home', compact(['clientsCount', 'adminCount', 'AgeStatisticCount','minAge','maxAge','doctor_Count']));
     }
}
