<?php

namespace App\Http\Middleware;

use App\Providers\RouteServiceProvider;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {


            return redirect('/login');

        }
        
        
        if(auth()->user()->guard!='admin')
        {   
                Auth::logout();

            return redirect('/login')->withErrors(['msg' => 'Please verify that your information is correct']);
        }
        

    return $next($request);
       
    }
}