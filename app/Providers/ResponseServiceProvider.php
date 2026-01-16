<?php
// Place this file on the Providers folder of your project
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\ResponseFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use App\Models\Security\UserSession;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Security\UserActivity;

class ResponseServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    private $salt='$2y$06$Pi1ND0N3S1A#&m3Rd3K4#@%';
    public function boot(ResponseFactory $factory)
    {
        $request = $this->app->request;
        $salt    = $this->salt;   // 👈 capture here

        $factory->macro('success', function ($message = '', $data = null, $rowcount = 0)
            use ($factory, $request, $salt) {

            $jwt = $request->header('x_jwt');
            $uri = strtolower($request->getPathInfo());

            $respon_code = $request->isMethod('post') ? 201 : 200;

            $header = [
                'status'   => 'OK',
                'error_no' => 0,
                'message'  => $message
            ];

            $format = [
                'header'   => $header,
                'contents' => $data
            ];

            if ($jwt !== "") {
                $session = UserSession::selectRaw("
                        token,user_id,created_date,refresh_date,expired_date,now() as curr_time
                    ")
                    ->where('token', $jwt)
                    ->first();

                if ($session && $session->curr_time > $session->expired_date) {

                    // ✅ now safe: using $salt from closure
                    $token = Hash::make($session->user_id . $salt . date('YmdHis'));
                    $refresh_date = date('Y-m-d H:i:s', strtotime('+120 minutes'));

                    $session->hash_code    = $token;
                    $session->refresh_date = $refresh_date;
                    $session->save();

                    $format['new_jwt'] = $token;
                }

                /*
                $body= $request->json()->all();
                $log = new UserActivity();
                $log->log_timestamp=Date('Y-m-d H:i:s');
                $log->user_id     = isset($session->user_id) ? $session->user_id :'N/A';
                $log->url_link    = $request->fullUrl();
                $log->method      = isset($method) ? $method :'N/A';
                $log->respon_code = isset($respon_code) ? $respon_code :'N/A';
                $log->body_data   = json_encode($body,JSON_PRETTY_PRINT);
                $log->respon_data = json_encode($format,JSON_PRETTY_PRINT);
                $log->ip_number   = $request->ip();
                $log->path_url    = $request->url();
                $log->query_data  = json_encode($request->query(),JSON_PRETTY_PRINT);
                $log->save();
                */
            }

            return $factory->make($format, $respon_code);
        });

        $factory->macro('error', function (string $message = '', $error_code = 0, $errors = []) use ($factory,$request) {
            $jwt = $request->header('x_jwt');
            $session=UserSession::selectRaw("token,user_id,created_date,refresh_date,expired_date,now() as curr_time")
            ->where('token',$jwt)->first();

            $method = $request->method();
            $respon_code=200;
            if ($request->isMethod('post')) {
                $respon_code=201;
            }

            $header = [
                'status'=>'NOT_OK',
                'error_no'=>$error_code,
                'message'=>$message
            ];
            $info['message'] =$errors;
            $format = [
                'header'=>$header,
                'contents'=>$info
            ];

            /*
            $body= $request->json()->all();
            $log = new UserActivity();
            $log->log_timestamp=Date('Y-m-d H:i:s');
            $log->user_id     = isset($session->user_id) ? $session->user_id :'N/A';
            $log->url_link    = $request->fullUrl();
            $log->method      = isset($method) ? $method :'N/A';
            $log->respon_code = isset($respon_code) ? $respon_code :'N/A';
            $log->body_data   = json_encode($body,JSON_PRETTY_PRINT);
            $log->respon_data = json_encode($format,JSON_PRETTY_PRINT);
            $log->ip_number   = $request->ip();
            $log->path_url    = $request->url();
            $log->query_data  = json_encode($request->query(),JSON_PRETTY_PRINT);
            $log->save();
            */
            return $factory->make($format);
        });
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $request = $this->app->request;
    }
}
