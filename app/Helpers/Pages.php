<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use App\Models\Config\UserSession;
use Illuminate\Support\Facades\Hash;

class Pages
{

   public static function UserSessions(){
        $request = request();
        $jwt     = base64_decode($request->header('x_jwt'));
        $session = UserSession::from('o_session as a')
        ->join("pas_users as b","a.user_id","=","b.user_id")
        ->selectRaw("a.token,a.user_id,a.created_date,a.expired_date,b.user_name,b.x_type,
        b.kd_grup,b.kd_staf")
        ->where('a.token',$jwt)->first();
        return $session;
   }

   public static function GenerateToken($usename,$password){
    $token        = Hash::make($usename.$password.Date('YmdHis'));
    $refresh_date =  date('Y-m-d H:i:s', strtotime('+60 minutes'));
    $expired_date =  date('Y-m-d H:i:s', strtotime('+1 day'));
    $ip = request()->ip();

    DB::table('o_session')->insert([
       'token'=>$token,
       'user_id'=>$usename,
       'created_date'=>date('Y-m-d H:i:s'),
       'refresh_date'=>$refresh_date,
       'expired_date'=>$expired_date,
       'ip_number'=>$ip
    ]);
    return $token;
   }

   public static function get_data($code,$type='C'){
      $value='';
      $data=DB::table('o_system')
            ->where('key_word',$code)
            ->first();
      if (!($data)){
         DB::table('o_system')->insert([
            'key_word'=>$code,
            'key_type'=>'C',
            'key_length'=>1000
         ]);
         if ($type=='C'){
            $value='';
         } else if ($type=='I'){
            $value=-1;
         } else if ($type=='N'){
            $value=0;
         } else if ($type=='D'){
            $value=date();
         } else if ($type=='B'){
            $value=false;
         }
      } else {
         if ($type=='C'){
            $value=$data->key_value_nvarchar;
         } else if ($type=='I'){
            $value=(int)$data->key_value_integer;
         } else if ($type=='N'){
            $value=(float)$data->key_value_decimal;
         } else if ($type=='D'){
            $value=date_create($data->key_value_date);
         } else if ($type=='B'){
            $value=(bool)$data->key_value_boolean;
         }
      }
      return $value;
   }

   public static function write_data($code,$type='C',$value){
      $data=DB::table('o_system')
            ->where('key_word',$code)
            ->first();
      if (!($data)){
         DB::table('o_system')->insert([
            'key_word'=>$code,
            'key_type'=>'C',
            'key_length'=>1000
         ]);
      }
      $rec= array();
      if ($type=='C'){
         $rec=array('key_value_nvarchar'=>$value);
      } else if ($type=='I'){
         $rec=array('key_value_integer'=>$value);
      } else if ($type=='N'){
         $rec=array('key_value_decimal'=>$value);
      } else if ($type=='D'){
         $rec=array('key_value_date'=>$value);
      } else if ($type=='B'){
         $rec=array('key_value_boolean'=>$value);
      }
      DB::table('o_system')
         ->where('key_word',$code)
         ->update($rec);
   }

   public static function my_server_url()
    {
      $profile=DB::table('m_profile')->select('folder_api')->first();
      $folder="";
      if ($profile){
         $folder=$profile->folder_api;
         if (!($folder=="")){
            $folder="/".$folder;
         }
      }
      $server_name = $_SERVER['SERVER_NAME'];
      if (!in_array($_SERVER['SERVER_PORT'], [80, 443])) {
            $port = ":$_SERVER[SERVER_PORT]";
      } else {
            $port = '';
      }
      if (!empty($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1')) {
            $scheme = 'https';
      } else {
            $scheme = 'http';
      }

      return $scheme.'://'.$server_name.$port.$folder;
   }

   public static function month($index)
   {
      $months=['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
      return $months[$index-1];
   }

   public static function Terbilang( $num ,$dec=4){
    $stext = array(
        "Nol",
        "Satu",
        "Dua",
        "Tiga",
        "Empat",
        "Lima",
        "Enam",
        "Tujuh",
        "Delapan",
        "Sembilan",
        "Sepuluh",
        "Sebelas"
    );
    $say  = array(
        "Ribu",
        "Juta",
        "Milyar",
        "Triliun",
        "Biliun", // remember limitation of float
        "--apaan---" ///setelah biliun namanya apa?
    );
    $w = "";

    if ($num <0 ) {
        $w  = "Minus ";
        //make positive
        $num *= -1;
    }

    $snum = number_format($num,$dec,",",".");
    $strnum =  explode(".",substr($snum,0,strrpos($snum,",")));
    //parse decimalnya
    $koma = substr($snum,strrpos($snum,",")+1);

    $isone = substr($num,0,1)  ==1;
    if (count($strnum)==1) {
        $num = $strnum[0];
        switch (strlen($num)) {
            case 1:
            case 2:
                if (!isset($stext[$strnum[0]])){
                    if($num<19){
                        $w .=$stext[substr($num,1)]." Belas";
                    }else{
                        $w .= $stext[substr($num,0,1)]." Puluh ".
                            (intval(substr($num,1))==0 ? "" : $stext[substr($num,1)]);
                    }
                }else{
                    $w .= $stext[$strnum[0]];
                }
                break;
            case 3:
                $w .=  ($isone ? "Seratus" : Pages::terbilang(substr($num,0,1)) .
                    " Ratus").
                    " ".(intval(substr($num,1))==0 ? "" : Pages::terbilang(substr($num,1)));
                break;
            case 4:
                $w .=  ($isone ? "Seribu" : Pages::terbilang(substr($num,0,1)) .
                    " Ribu").
                    " ".(intval(substr($num,1))==0 ? "" : Pages::terbilang(substr($num,1)));
                break;
            default:
                break;
        }
    }else{
        $text = $say[count($strnum)-2];
        $w = ($isone && strlen($strnum[0])==1 && count($strnum) <=3? "Se".strtolower($text) : Pages::terbilang($strnum[0]).' '.$text);
        array_shift($strnum);
        $i =count($strnum)-2;
        foreach ($strnum as $k=>$v) {
            if (intval($v)) {
                $w.= ' '.Pages::Terbilang($v).' '.($i >=0 ? $say[$i] : "");
            }
            $i--;
        }
    }
    $w = trim($w);
    if ($dec = intval($koma)) {
        $w .= " Koma ". Pages::Terbilang($koma);
    }
    return trim($w);
   }

   public static function Response($response,$filename='download.xlsx')
   {
      $attachment='attachment; filename="'.$filename.'"';
      $response->setStatusCode(200);
      $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
      $response->headers->set('Content-Disposition', $attachment);
      $response->headers->set('Access-Control-Allow-Credentials', true);
      $response->headers->set('Access-Control-Allow-Origin', 'http://localhost:8080');
      $response->headers->set('Access-Control-Expose-Headers', '*');
      return $response;
   }



   public static function curl_data($url,$form,$post=true) {
      $info['status']=true;
      $info['message']='';
      $info['data']=null;

      $ip="192.168.43.2";
      $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)';
      $header[0]  = "Accept: text/xml,application/xml,application/xhtml+xml,";
      $header[0] .= "text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5";
      $header[] = "Cache-Control: max-age=0";
      $header[] = "Connection: keep-alive";
      $header[] = "Keep-Alive: 300";
      $header[] = "Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7";
      $header[] = "Accept-Language: en-us,en;q=0.5";
      $header[] = "Pragma: "; // browsers = blank
      $header[] = "X_FORWARDED_FOR: " . $ip;
      $header[] = "REMOTE_ADDR: " . $ip;
      $ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_USERAGENT, $agent);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
      if ($post){
         curl_setopt($ch, CURLOPT_POST, true);
      }
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,0);
      if (!($form==null)){
         curl_setopt($ch, CURLOPT_POSTFIELDS, $form);
      }
		$output = curl_exec($ch);
		if ($output==false)	{
         $info['status']=false;
			$info['message']=curl_error($ch);
		} else {
         $output = preg_replace('/&(?!#?[a-z0-9]+;)/', '&amp;', $output);
         $info['json']=json_decode($output,true);
         $info['message']=json_last_error_msg();
		}
      curl_close($ch);
      return $info;
   }
}
