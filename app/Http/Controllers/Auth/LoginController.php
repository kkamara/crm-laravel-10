<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';

    /** @var array to store seeder logins for site users */
    protected $loginEmails = array(
        array(
            'email' => 'admin@mail.com',
            'role'  => 'admin',
        ),
        array(
            'email' => 'clientadmin@mail.com',
            'role'  => 'client_admin',
        ),
        array(
            'email' => 'clientuser@mail.com',
            'role'  => 'client_user',
        ),
    );

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('create', 'logout');
    }

    public function create()
    {
        if(Auth::check()) {
            return redirect()->route('Dashboard');
        }

        return view('auth/login')
            ->withTitle('Login')
            ->withLoginEmails($this->loginEmails);
    }

    public function store(Request $request)
    {
        $email = strtolower( trim( request('email') ) );

        if(($email && request('password')) == FALSE)
        {
            return back()->with('errors', ['Missing email and password.']);
        }

        if(!$user = DB::select('select distinct * from users where email = ?', [$email]))
        {
            return back()->with('errors', ['Invalid login credentials provided.']);
        }

        $user = $user[0];

        if(!Auth::attempt(['email' => $user->email, 
            'password' => request('password')], 
            (int) request('remember_me')))
        {
            return back()->with('errors', ['Invalid login credentials provided.']);
        }

        DB::table('users')->update([
            'last_login' => date('Y-m-d H:i:s')
        ]);

        return redirect()
                ->route('Dashboard')
                ->with('flashSuccess', 'You have logged in, '.$user->first_name.' '.$user->last_name.'!');        
    }

    public function logout()
    {
        Auth::logout();

        return redirect('/');
    }
}
