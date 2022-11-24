<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use App\Models\User;
use App\Models\UserPoint;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function customLogin(Request $request)
    {
        $request->validate([
            'mobile' => 'required',
            'password' => 'required',
        ]);

        $credentials = $request->only('mobile', 'password');
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            Auth::login($user, true);
            return [
                'success' => 'You have logged in!'
            ];
        }

        return [
            'fail' => 'Login details are not valid. Please try again.'
        ];
    }

    public function registration()
    {
        return view('auth.registration');
    }

    public function customRegistration(Request $request)
    {
        $request->validate([
            'mobile' => 'required|unique:user',
            'password' => 'required|min:6',
        ]);

        $data = $request->all();
        $newUser = $this->create($data);

        $userId = $newUser->id;
        UserPoint::create([
            'user_id' => $userId,
            'point_earned' => 0,
            'point_used' => 0,
        ]);

        return $newUser;
    }

    public function create(array $data)
    {
        return User::create([
            'mobile' => $data['mobile'],
            'password' => Hash::make($data['password'])
        ]);
    }

    public function dashboard()
    {
        if (Auth::check()) {
            return view('dashboard');
        }

        return redirect("login")->withSuccess('You are not allowed to access');
    }

    public function signOut()
    {
        Session::flush();
        Auth::logout();

        return Redirect('login');
    }
}
