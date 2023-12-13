<?php

namespace App\Http\Controllers;

use App\Models\MAdmin;
use App\Models\MUser;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Log;

class AuthController extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;


    public function health()
    {
        return [
            'code' => '10000',
            'data' => null,
            'message' => 'success'
        ];
    }

    public function getMe()
    {
        $user_oid = auth('api')->user()->_id;
        $user = MUser::find($user_oid);
        return [
            'code' => '10000',
            'data' => $user,
            'message' => 'success'
        ];
    }

    public function getAdmin()
    {
        $user_oid = auth('api')->user()->_id;
        $user = MAdmin::find($user_oid);
        return [
            'code' => '10000',
            'data' => $user,
            'message' => 'success'
        ];
    }

}
