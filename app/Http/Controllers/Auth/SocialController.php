<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Providers\RouteServiceProvider;
use Socialite;
use Illuminate\Routing\Controller;
use App\Models\User;

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
        
        // 確認使用者是否已經使用此方法註冊過
        if (User::where('email', $user->getEmail())->exists()) {  // 有相同 email 的使用者
            Auth::guard('web')->login(
                User::where('email', $user->getEmail())->first()
            );
        } else {
            // 沒找到，自動註冊
            $user = User::create(
                [
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'password' => Hash::make(uniqid('FB_')),
                'source' => 'facebook',
                ]
            );
            // $user->attachRole('member');
            Auth::guard('web')->login(
                User::where('email', $user->getEmail())->first()
            );
        }
        return redirect()->intended(RouteServiceProvider::HOME);

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