<?php

namespace App\Http\Controllers\Auth;

use Socialite;
use Illuminate\Routing\Controller;

class SocialController extends Controller
{
    /**
     * 將用戶重定向到Facebook身份驗證頁面。
     *
     * @return \Illuminate\Http\Response
     */
    public function fbAuth()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * 從Facebook獲取用戶信息。
     *
     * @return \Illuminate\Http\Response
     */
    public function fbAuthCallback()
    {
        $user = Socialite::driver('facebook')->user();
        dd($user);
        // $user->token;
    }

    /**
     * 將用戶重定向到Google身份驗證頁面。
     *
     * @return \Illuminate\Http\Response
     */
    public function googleAuth()
    {
        return Socialite::driver('google')->redirect();
    }

    /**
     * 從Google獲取用戶信息。
     *
     * @return \Illuminate\Http\Response
     */
    public function googleAuthCallback()
    {
        $user = Socialite::driver('google')->user();
        dd($user);
        // $user->token;
    }
}