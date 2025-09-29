<?php

namespace App\Http\Middleware;

use Illuminate\Support\Facades\DB;
use Closure;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Config;
use App\Models\Security\UserSession;
use Pages;

class Appauth
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    protected $ignored_auth=false;

    public function handle($request, Closure $next)
    {
        $allowed = false;
        $jwt     = $request->header('x_jwt');
        $db      = $request->header('database');
        $ip      = request()->ip();

        $database=$request->input('database');

        // Set tenant connection dynamically
        Config::set('database.connections.tenant.database', $db);

        // Reconnect with new DB
        DB::purge('tenant');
        DB::reconnect('tenant');

        // Optional: make 'tenant' the default connection
        DB::setDefaultConnection('tenant');

        $session=UserSession::selectRaw("expired_date,refresh_date,ip_number,now() as curr_time")
        ->where('token',$jwt)
        ->first();
        if ($session) {
            if ($session->curr_time>$session->expired_date){
                UserSession::where('token',$jwt)->delete();
                $message="token was expired, access denied (APP-AUTH)";
            } else {
                $allowed= true;
            }
        } else {
            $message  = "token invalid, access denied (APP-AUTH)";
        }
        if (($allowed==true) || ($this->ignored_auth==true)){
            return $next($request);
        } else {
            return response()->error('',401,$message);
        }
    }
}
