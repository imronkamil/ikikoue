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
use App\Helpers\Pages;

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

    public static function getStringSys(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_string")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_string : '';
        $response['value'] = $value;
        //return response()->success('Success',$response);
        return $value;
    }

    public static function getIntegerSys(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_int")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_int : '';
        $response['value'] = $value;
        //return response()->success('Success',$response);
        return $value;
    }

    public static function getDecimalSys(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_decimal")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_decimal : '';
        $response['value'] = $value;
        //return response()->success('Success',$response);
        return $value;
    }

    public static function getDateSys(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_date")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_date : '';
        $response['value'] = $value;
        //return response()->success('Success',$response);
        return $value;
    }

    public static function getBooleanSys(string $system_id) {
        $data['i_system']= System::from('i_system as a')
        ->selectRaw("a.system_id, a.description, a.value_boolean")
        ->where("a.system_id","=",$system_id)
        ->first();
        $value = ($data['i_system']) ? $data['i_system']->value_boolean : '';
        $response['value'] = $value;
        //return response()->success('Success',$response);
        return $value;
    }

    public static function getAutoStok() {
        $stok= UtilityController::getBooleanSys("FLAG_AUTO_STOK");
        $response['value'] = $stok;
        return $stok;
    }

    public static function getAutoJurnal() {
        $jurnal= UtilityController::getBooleanSys("FLAG_AUTO_JURNAL");
        $response['value'] = $jurnal;
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
        $pesan= DB::table('m_pesan as a')
            ->selectRaw('a.*')
            ->where(function ($q) use ($user_id) {
                $q->where('a.user_id', $user_id)
                  ->orWhere('COALESCE(a.user_id,"")', '');
            })
            ->where('a.kd_aplikasi', $kd_aplikasi)
            ->where('a.fl_aktif', true)
            ->get();

        $response= $pesan;
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
