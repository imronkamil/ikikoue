<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Accounting\FiscalYear;
use App\Models\Accounting\FiscalPeriod;
use App\Models\Accounting\AccountConfig;
use App\Models\Inventory\Opname1;
use App\Models\Master\Project;
use App\Models\Tools\System;
use App\Models\Tools\PasUsers;
use App\Models\Tools\Profile;
use App\Helpers\Pages;
use App\Services\SystemService;

class UtilityController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data['i_docno']= DocNo::from('i_docno as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.enum_counter, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.base_name, CASE WHEN a.enum_counter=0 THEN 'Lanjut' WHEN a.enum_counter=1 THEN 'Per Tahun'
            ELSE 'Per Bulan' END as ket_counter")
        ->orderBy($sortBy,$sorting)
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['i_docno']= DocNo::from('i_docno as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.enum_counter, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.base_name, CASE WHEN a.enum_counter=0 THEN 'Lanjut' WHEN a.enum_counter=1 THEN 'Per Tahun'
            ELSE 'Per Bulan' END as ket_counter")
        ->orderBy("a.base_type")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_name=isset($request->doc_name) ? $request->doc_name : '';
        $data['t_notran']= NoTran::from('t_notran as a')
        ->selectRaw("a.doc_name, a.doc_key")
        ->where("a.doc_name",$doc_name)
        ->first();
        return response()->success('Success',$data);
    }

    public static function getStatusByTglOpname(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '';
        $tgl_doc=isset($request->tgl_doc) ? $request->tgl_doc : '1970-01-01';
        $t_opname1= Opname1::from('t_opname1 as a')
        ->selectRaw("a.doc_key, a.tgl_doc")
        ->where("a.kd_lokasi",$kd_lokasi)
        ->orderBy('a.tgl_doc','desc')
        ->first();
        $fl_opname= DB::table('i_system as a')
        ->where("a.system_id","=","FLAG_CEK_TGLOPNAME")
        ->value('a.value_boolean');
        $tglopname = ($t_opname1) ? $t_opname1->tgl_doc : '1970-01-01';
        if ($fl_opname == 1) {
            if ($tgl_doc <= $tglopname) {
                $response['value'] = 0; //Tutup
                return response()->success('Success',$response);
            } else {
                $response['value'] = 1; //Buka
                return response()->success('Success',$response);
            }
        } else {
            $response['value'] = 1; //Buka
            return response()->success('Success',$response);
        }
        //$response['value'] = $tglopname;
        //return response()->success('Success',$response);
    }

    public static function getTglOpnameAkhir(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '';
        $t_opname1= Opname1::from('t_opname1 as a')
        ->selectRaw("a.doc_key, a.tgl_doc")
        ->where("a.kd_lokasi",$kd_lokasi)
        ->orderBy('a.tgl_doc','desc')
        ->first();
        if ($t_opname1) {
            $response['value'] = date('d/m/Y',strtotime($t_opname1->tgl_doc));
        } else {
            $response['value'] = date('d/m/Y',strtotime('1/1/1970'));
        }
        return response()->success('Success',$response);
    }

    public static function getPostingDateStatus(Request $request) {
        $tgl_doc=isset($request->tgl_doc) ? $request->tgl_doc : '';
        $data['m_fiscal_period']= FiscalPeriod::from('m_fiscal_period as a')
        ->selectRaw("a.status, a.tgl_awal, a.tgl_akhir")
        ->where("a.tgl_awal","<=",$tgl_doc)
        ->where("a.tgl_akhir",">=",$tgl_doc)
        ->first();
        $status = ($data['m_fiscal_period']) ? $data['m_fiscal_period']->status : 0;
        $response['value'] = $status;
        return response()->success('Success',$response);
    }

    public static function getKodeProjectByLokasi(string $kd_lokasi) {
        $data['m_project']= Project::from('m_project as a')
        ->leftJoin('m_lokasi as b','a.project_id','=','b.project_id')
        ->selectRaw("a.kd_project, a.nm_project")
        ->where("b.kd_lokasi","=",$kd_lokasi)
        ->first();
        $kd_project = ($data['m_project']) ? $data['m_project']->kd_project : '';
        $response['value'] = $kd_project;
        //return response()->success('Success',$response);
        return $kd_project;
    }

    public static function getAccountConfig(string $kd_config) {
        $data['m_account_config']= AccountConfig::from('m_account_config as a')
        ->selectRaw("a.kd_account_config, a.nm_account_config, a.no_account")
        ->where("a.kd_account_config","=",$kd_config)
        ->first();
        $no_account = ($data['m_account_config']) ? $data['m_account_config']->no_account : '';
        $response['value'] = $no_account;
        //return response()->success('Success',$response);
        return $no_account;
    }

    public static function getStringLocal(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_nvarchar")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_nvarchar : '';
        return $value;
    }

    public static function getStringSys(Request $request) {
        $system_id=isset($request->system_id) ? $request->system_id : '';
        $value= self::getStringLocal($system_id);
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function getIntegerLocal(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_int")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_int : '';
        return $value;
    }

    public static function getIntegerSys(Request $request) {
        $system_id=isset($request->system_id) ? $request->system_id : '';
        $value= self::getIntegerLocal($system_id);
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function getDecimalLocal(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_decimal")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_decimal : '';
        return $value;
    }

    public static function getDecimalSys(Request $request) {
        $system_id=isset($request->system_id) ? $request->system_id : '';
        $value= self::getDecimalLocal($system_id);
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function getDateLocal(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_date")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_date : '';
        return $value;
    }

    public static function getDateSys(Request $request) {
        $system_id=isset($request->system_id) ? $request->system_id : '';
        $value= self::getDateLocal($system_id);
        $response['value'] = date('d/m/Y', strtotime($value));
        return response()->success('Success',$response);
    }

    public static function getBooleanLocal(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_boolean")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_boolean : '';
        return $value;
    }

    public static function getBooleanSys(Request $request) {
        $system_id=isset($request->system_id) ? $request->system_id : '';
        $value= self::getBooleanLocal($system_id);
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function getAutoStok() {
        $stok= UtilityController::getBooleanLocal("FLAG_AUTO_STOK");
        return $stok;
    }

    public static function getAutoJurnal() {
        $jurnal= UtilityController::getBooleanLocal("FLAG_AUTO_JURNAL");
        return $jurnal;
    }

    public static function getHakMenu(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        if ($user_id == 'sa') {
            $data['pas_access']= DB::table('o_objects_item')
            ->selectRaw('kd_menu')
            ->where('is_active','=','True')
            ->get();
        } else {
            $subQ1= DB::table('pas_users as a')
            ->join('m_hak_akses as b','a.kd_grup','=','b.user_id')
            ->where('a.user_id','=',$user_id)
            ->selectRaw('kd_menu');
            $data['pas_access']= DB::table('pas_access as a')
            ->join('m_hak_akses as b','a.user_id','=','b.user_id')
            ->where('a.user_id','=',$user_id)
            ->selectRaw('b.kd_menu')
            ->union($subQ1)
            ->distinct()->get();
        }
        return response()->success('Success',$data);
    }

    public static function getHakMenuAction(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $nm_menu=isset($request->nm_menu) ? $request->nm_menu : '';
        if ($user_id == 'sa') {
            $data['m_hak_access']= DB::table('m_hak_akses')
            ->selectRaw('*')
            ->where('kd_menu','=',$nm_menu)
            ->get();
        } else {
            $data['m_hak_access']= DB::table('m_hak_akses')
            ->selectRaw('*')
            ->where('kd_menu','=',$nm_menu)
            ->where('user_id','=',$user_id)
            ->get();
        }
        if ($data['m_hak_access']->count()>0) {
            $value = 'True';
        } else {
            $value = 'False';
        }
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function getNotifTrans(Request $request) {
        //$user_id=isset($request->user_id) ? $request->user_id : '';
        $t_pr1= DB::table('t_pr1')
            ->selectRaw('*')
            ->where(DB::raw('COALESCE(fl_tutup)'),FALSE)
            ->get();
        $t_po1= DB::table('t_po1')
            ->selectRaw('*')
            ->where(DB::raw('COALESCE(fl_tutup)'),FALSE)
            ->get();
        $t_gr1= DB::table('t_pr1')
            ->selectRaw('*')
            ->where(DB::raw('COALESCE(fl_tutup)'),FALSE)
            ->get();

        $notif['t_pr1']=FALSE;
        $notif['t_po1']=FALSE;
        $notif['t_gr1']=FALSE;
        if ($t_pr1->count()>0) {
            $notif['t_pr1']=TRUE;
        }
        if ($t_po1->count()>0) {
            $notif['t_po1']=TRUE;
        }
        if ($t_gr1->count()>0) {
            $notif['t_gr1']=TRUE;
        }
        return response()->success('Success',$notif);
    }

    public static function getNotifTransList(Request $request) {
        $tempo=UtilityController::getIntegerSys('REMINDER_BEFORE');
        $data['t_pr1']= DB::table('t_pr1 as a')
            ->leftJoin('m_lokasi as b', 'a.kd_lokasi', '=', 'b.kd_lokasi')
            ->selectRaw('a.*, b.nm_lokasi')
            ->whereRaw('COALESCE(a.fl_tutup, false) = false')
            ->whereRaw('EXTRACT(doy FROM CURRENT_DATE) - EXTRACT(doy FROM a.tgl_doc) < ?', [$tempo])
            ->orderBy('a.tgl_doc', 'DESC')
            ->orderBy('a.no_doc')
            ->get();
        $data['t_po1']= DB::table('t_po1 as a')
            ->leftJoin('m_lokasi as b', 'a.kd_lokasi', '=', 'b.kd_lokasi')
            ->selectRaw('a.*, b.nm_lokasi')
            ->whereRaw('COALESCE(a.fl_tutup, false) = false')
            ->whereRaw('EXTRACT(doy FROM CURRENT_DATE) - EXTRACT(doy FROM a.tgl_doc) < ?', [$tempo])
            ->orderBy('a.tgl_doc', 'DESC')
            ->orderBy('a.no_doc')
            ->get();
        $data['t_gr1']= DB::table('t_gr1 as a')
            ->leftJoin('m_lokasi as b', 'a.kd_lokasi', '=', 'b.kd_lokasi')
            ->selectRaw('a.*, b.nm_lokasi')
            ->whereRaw('COALESCE(a.fl_tutup, false) = false')
            ->whereRaw('EXTRACT(doy FROM CURRENT_DATE) - EXTRACT(doy FROM a.tgl_doc) < ?', [$tempo])
            ->orderBy('a.tgl_doc', 'DESC')
            ->orderBy('a.no_doc')
            ->get();

        return response()->success('Success',$data);
    }

    public static function getPesan(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $kd_aplikasi=isset($request->kd_aplikasi) ? $request->kd_aplikasi : '';
        $data['m_pesan']= DB::table('m_pesan as a')
            ->selectRaw('a.*')
            ->where(function ($q) use ($user_id) {
                $q->where('a.user_id', $user_id)
                  ->orWhereRaw("COALESCE(a.user_id,'') = ''");
            })
            ->where('a.kd_aplikasi', $kd_aplikasi)
            ->where('a.fl_aktif', true)
            ->orderBy('a.tgl_pesan', 'asc')
            ->orderBy('a.jam_pesan', 'asc')
            ->get();

        $response= $data;
        return response()->success('Success',$response);
    }

    public static function getNewVersion(Request $request) {
        $kd_aplikasi=isset($request->kd_aplikasi) ? $request->kd_aplikasi : '';
        $newVersion= DB::table('objmodul as a')
            ->selectRaw('a.version_number')
            ->where('module_id',$kd_aplikasi)
            ->first();

        $response= $newVersion->version_number;
        return response()->success('Success',$response);
    }

    public static function getOptions(Request $request) {
        $data['options']= DB::selectOne('SELECT pg_backend_pid() as pid, inet_client_addr() as ip');
        return response()->success('Success',$data);
    }

    public static function getProfiles(Request $request) {
        $data['i_profile']= Profile::from('i_profile as a')
            ->selectRaw('a.*')
            ->first();
        return response()->success('Success',$data);
    }

    public static function getLoginAccess(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $data['pas_users']= PasUsers::from('pas_users as a')
            ->selectRaw('a.user_id, a.user_name, a.x_type, a.password, a.kd_grup, a.fl_grup, a.kd_staf,
                a.fl_aktif, CAST(a.foto as text) as foto, a.icon_id, password2, password0')
            ->where('a.user_id',$user_id)
            ->first();

        return response()->success('Success',$data);
    }

    public static function getObjectItem(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $user_type=isset($request->user_type) ? $request->user_type : '';
        if ($user_type == '1') {
            $subQ2 = DB::table('o_objects_item')
                ->select('menu_id')
                ->whereRaw('COALESCE(is_active, false) = false');
            $subQ1 = DB::table('o_objects_item')
                ->select('menu_id')
                ->where('is_access', false)
                ->whereNotIn('header_id', $subQ2)
                ->where('is_active', true);
            $data['o_objects_item'] = DB::table('o_objects_item as a')
                ->leftJoin('m_hak_akses as b', 'a.kd_menu', '=', 'b.kd_menu')
                ->select('a.*')
                ->where(function ($q) use ($subQ1) {
                    $q->whereIn('a.header_id', $subQ1)
                    ->orWhere('a.header_id', 0);
                })
                ->where('a.is_active', true)
                ->whereRaw('COALESCE(a.is_access, false) = false')
                ->orderBy('a.menu_id')
                ->get();
        } else {
            // --- Subquery #2: header_id NOT IN (...) ---
            $subQ2 = DB::table('o_objects_item')
                ->select('menu_id')
                ->whereRaw('COALESCE(is_active, false) = false');
            // --- Subquery #1: header_id IN (...) ---
            $subQ1 = DB::table('o_objects_item')
                ->select('menu_id')
                ->where('is_access', false)
                ->whereNotIn('header_id', $subQ2)
                ->where('is_active', true);
            // --- Main Query ---
            $data['o_objects_item'] = DB::table('o_objects_item as a')
                ->leftJoin('m_hak_akses as b', 'a.kd_menu', '=', 'b.kd_menu')
                ->leftJoin('pas_users as c', function ($join) {
                    $join->on('b.user_id', '=', 'c.user_id')
                        ->orOn('b.user_id', '=', 'c.kd_grup');
                })
                ->select('a.*')
                ->where(function ($q) use ($subQ1) {
                    $q->whereIn('a.header_id', $subQ1)
                    ->orWhere('a.header_id', 0);
                })
                ->where('a.is_active', true)
                ->whereRaw('COALESCE(a.is_access, false) = false')
                ->where(function ($q) use ($user_id) {
                    $q->where('a.is_group', true)
                    ->orWhere(function ($x) use ($user_id) {
                        $x->where('c.user_id', $user_id)
                            ->where('a.is_group', false);
                    });
                })
                ->orderBy('a.menu_id')
                ->get();
        }

        return response()->success('Success',$data);
    }

    public static function getPersenPajak(Request $request) {
        $kd_pajak=isset($request->kd_pajak) ? $request->kd_pajak : '';
        $data['m_pajak']= DB::table('m_pajak as a')
            ->selectRaw('a.*')
            ->where('a.kd_pajak',$kd_pajak)
            ->first();
        $value = ($data['m_pajak']) ? $data['m_pajak']->persen_pajak : '';
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function addUserList(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $tgl_doc=isset($request->tgl_doc) ? $request->tgl_doc : '';
        $user= DB::table('t_user_list')
            ->where('user_id',$user_id)
            ->where('tgl_transaksi',$tgl_doc)
            ->first();
        if (!($user)) {
            DB::table('t_user_list')
                ->insert([
                    'tgl_transaksi'=>date('Y-m-d H:i:s'),
                    'user_id'=>$user_id,
                    'jam_awal'=>date('H:i:s')
                ]);
        } else {
            DB::table('t_user_list')
                ->where('user_id',$user_id)
                ->where('tgl_transaksi',$tgl_doc)
                ->update([
                    'jam_akhir'=>date('H:i:s')
                ]);
        }
        $response['message'] = 'Update data berhasil';
        return response()->success('Success',$response);
    }

    public static function addUserOnline(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $pid=isset($request->pid) ? $request->pid : 0;
        $mac_add=isset($request->mac_add) ? $request->mac_add : 0;
        $user= DB::table('t_user_online')
            ->where('user_id',$user_id)
            ->where('pid',$pid)
            ->first();
        if (!($user)) {
            DB::table('t_user_online')
                ->insert([
                    'pid'=>$pid,
                    'user_id'=>$user_id,
                    'tgl_tran'=>date('Y-m-d H:i:s'),
                    'jam'=>date('H:i:s'),
                    'mac_address'=>$mac_add,
                    'catatan'=>'IKI KOUE',
                    'fl_locked'=>false
                ]);
        }
        $response['message'] = 'Tambah data berhasil';
        return response()->success('Success',$response);
    }

    public static function delUserOnline(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $pid=isset($request->pid) ? $request->pid : 0;
        DB::table('t_user_online')
            ->where('user_id',$user_id)
            ->where('pid',$pid)
            ->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public static function getProsesUpload(Request $request) {
        $upload= DB::table('i_upload as a')
            ->selectRaw('a.*')
            ->get();
        if ($upload->count()>0) {
            $uploadTs = strtotime($upload->tgl_tran.' '.$upload->jam);
            $nowTs    = time() - 7200; //jika > 2 jam maka dianggap selesai
            if ($uploadTs < $nowTs) {
                $value = 'false';
                DB::table('i_upload')->delete();
            } else {
                $value = 'true';
            }
        } else {
            $value = 'false';
        }
        $response['value'] = $value;
        return response()->success('Success',$response);
    }

    public static function addProsesUpload(Request $request) {
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $pid=isset($request->pid) ? $request->pid : 0;
        $mac_add=isset($request->mac_add) ? $request->mac_add : 0;
        DB::table('i_upload')
            ->insert([
                'pid'=>$pid,
                'user_id'=>$user_id,
                'tgl_tran'=>date('Y-m-d H:i:s'),
                'jam'=>date('H:i:s'),
                'mac_address'=>$mac_add,
                'catatan'=>'IKI KOUE'
            ]);
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public static function delProsesUpload(Request $request) {
        DB::table('i_upload')->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function destroy(Request $request) {
        $doc_name=isset($request->doc_name) ? $request->doc_name : '';
        NoTran::where('doc_name',$doc_name)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['i_docno'];
        $rec_thn = $data['i_docno_thn'];

        $validator=Validator::make($rec,[
            'nm_docno'=>'bail|required',
        ],[
            'nm_docno.required'=>'Nama dokumen harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $i_docno = DocNo::where('docno_id',$where['docno_id'])->first();
        if (!($i_docno)) {
            $i_docno = new DocNo();
        }
        $i_docno->nm_docno       = $rec['nm_docno'];
        $i_docno->base_type      = $rec['base_type'];
        $i_docno->doc_type       = $rec['doc_type'];
        $i_docno->nomor          = $rec['nomor'];
        $i_docno->format         = $rec['format'];
        $i_docno->contoh         = $rec['contoh'];
        $i_docno->enum_counter   = $rec['enum_counter'];
        $i_docno->fl_default     = $rec['fl_default'];
        $i_docno->create_tgl     = $rec['create_tgl'];
        $i_docno->create_userid  = $rec['create_userid'];
        $i_docno->create_lokasi  = $rec['create_lokasi'];
        $i_docno->update_tgl     = $rec['update_tgl'];
        $i_docno->update_userid  = $rec['update_userid'];
        $i_docno->update_lokasi  = $rec['update_lokasi'];
        $i_docno->save();
        $lastInsertId= DB::getPdo()->lastInsertId();

        //Data DocNoThn
        foreach($rec_thn as $recthn) {
            $i_docno_thn = DocNoThn::where('docno_thn_id',$recthn['docno_thn_id'])->first();
            if (!($i_docno_thn)) {
                $i_docno_thn = new DocNoThn();
                $i_docno_thn->docno_id = $lastInsertId;
            }
            $i_docno_thn->tahun       = $recthn['tahun'];
            $i_docno_thn->bulan1      = $recthn['bulan1'];
            $i_docno_thn->bulan2      = $recthn['bulan2'];
            $i_docno_thn->bulan3      = $recthn['bulan3'];
            $i_docno_thn->bulan4      = $recthn['bulan4'];
            $i_docno_thn->bulan5      = $recthn['bulan5'];
            $i_docno_thn->bulan6      = $recthn['bulan6'];
            $i_docno_thn->bulan7      = $recthn['bulan7'];
            $i_docno_thn->bulan8      = $recthn['bulan8'];
            $i_docno_thn->bulan9      = $recthn['bulan9'];
            $i_docno_thn->bulan10     = $recthn['bulan10'];
            $i_docno_thn->bulan11     = $recthn['bulan11'];
            $i_docno_thn->bulan12     = $recthn['bulan12'];
            $i_docno_thn->no_urut     = $recthn['no_urut'];
            $i_docno_thn->save();
        }

        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
