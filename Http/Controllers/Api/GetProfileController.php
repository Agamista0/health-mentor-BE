<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AccountResource;
use App\Models\User;
use App\Response\ApiResponse;

class GetProfileController extends Controller
{
    public function index(){
        $account = User::where('id', auth()->user()->id)->first();
        $account->is_current = 1;

        if(isset($account)){
            return(new ApiResponse(200,__('AccountRetrievedSuccessfully'),[
                'profile'=>new AccountResource($account)
             ]))->send();
        }else{
            return(new ApiResponse(404,__('AccountNotFound'),[]))->send();
        }
    }
}
