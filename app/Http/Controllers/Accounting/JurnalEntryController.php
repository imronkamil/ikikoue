<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Accounting\AccountDtl;
use App\Models\Accounting\Jurnal1;
use App\Models\Accounting\Jurnal2;
use App\Models\Master\Project;
use App\Models\Master\Lokasi;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bank;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class JurnalEntryController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data['t_jurnal1']= Jurnal1::from('t_jurnal1 as a')
        ->selectRaw("a.*")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
        //return response()->success('Success',$query1);
    }

    public function show2() {
        $data['t_jurnal1']= Jurnal1::from('t_jurnal1 as a')
        ->selectRaw("a.*")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_jurnal1']= Jurnal1::from('t_jurnal1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_jurnal1']= Jurnal1::from('t_jurnal1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_jurnal1']) ? $data['t_jurnal1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //Jurnal1
        $data['t_jurnal1']= Jurnal1::from('t_jurnal1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();

        //Jurnal2
        $data['t_jurnal2']= Jurnal2::from('t_jurnal2 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_baris","asc")
        ->get();

        //Master DocNo
        $data['i_docno']= DocNo::from('i_docno as a')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("base_type",$base_type)
        ->orderBy("base_type","asc")
        ->orderBy("fl_default","desc")
        ->orderBy("nm_docno","asc")
        ->get();

        //Master Bank
        $data['m_bank']= Bank::from('m_bank as a')
        ->selectRaw("a.*")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_bank","asc")
        ->get();

        //Master Project
        $data['m_project']= Project::from('m_project as a')
        ->selectRaw("a.*")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_project","asc")
        ->get();

        //Master Lokasi
        $data['m_lokasi']= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_pusat, a.fl_lokasi, a.fl_aktif, a.fl_account, a.fl_stok, a.fl_hold,
            a.kd_server, a.kd_lokasi_acc, a.kd_lokasi || ' - ' || a.nm_lokasi AS ket_lokasi,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_lokasi","asc")
        ->get();

        //Master Pajak
        $subPajak= DB::table(DB::raw("(SELECT CAST(NULL AS varchar(20)) AS kd_pajak,
            '(null)'::varchar AS nm_pajak, 0 AS persen_pajak, 'true'::boolean AS fl_aktif) AS b"));
        $data['m_pajak']= Pajak::from('m_pajak')
        ->selectRaw("kd_pajak, nm_pajak, persen_pajak, fl_aktif")
        ->unionAll($subPajak)
        ->orderByRaw("kd_pajak NULLS FIRST")
        ->get();

        //Master Account Filter
        $data['m_account_filter']= Account::from('m_account as a')
        ->selectRaw("a.no_account, a.nm_account, a.no_account_parent, a.level_account, a.enum_debet_kredit,
            a.fl_aktif_account, a.fl_cash_bank, a.fl_control_account, a.grup_acc, a.kd_type_account, a.kd_grup_account, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("fl_aktif","true")
        ->where("fl_aktif_account","true")
        ->orderBy("a.no_account")
        ->get();

        //Master Lokasi Filter
        $data['m_lokasi_filter']= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_pusat, a.fl_lokasi, a.fl_aktif, a.fl_account, a.fl_stok, a.fl_hold,
            a.kd_server, a.kd_lokasi_acc, a.kd_lokasi || ' - ' || a.nm_lokasi AS ket_lokasi,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        //->where("a.fl_aktif","true")
        ->orderBy("a.kd_lokasi","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        Jurnal2::where('doc_key',$doc_key)->delete();
        Jurnal1::where('doc_key',$doc_key)->delete();
        AccountDtl::where('base_doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $jurnal1= Jurnal1::where('doc_key',$doc_key)->first();
        if ($jurnal1) {
            if ($jurnal1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            AccountDtl::where('base_doc_key',$doc_key)->delete();
            //Update PO1
            $jurnal1->catatan = $catatan . "\n" . $jurnal1->catatan;
            $jurnal1->fl_batal = 'true';
            /*$jurnal1->batal_tgl = date('Y-m-d H:i:s');
            $jurnal1->batal_userid = $request->userid;
            $jurnal1->batal_lokasi = $request->lokasi;*/
            $jurnal1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';
        $docTrans=1; //Jurnal Umum

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal Umum
        $jurnal= Jurnal1::from('t_jurnal1 as a')
        ->leftJoin('t_jurnal2 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("b.*, a.tgl_doc, a.no_doc")
        ->where("a.doc_key",$doc_key)
        ->orderBy("b.no_baris","asc")
        ->get();
        //Jurnal Debet/Kredit (Jurnal Umum)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_debet != 0) {
                $jurnalUmum= new AccountDtl();
                $jurnalUmum->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalUmum->no_account = $recJurnal->no_account;
                $jurnalUmum->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_debet > 0) {
                    $jurnalUmum->enum_debet_kredit = 'D';
                    $jurnalUmum->rp_debet = abs($recJurnal->rp_debet);
                    $jurnalUmum->rp_kredit = 0;
                } else {
                    $jurnalUmum->enum_debet_kredit = 'K';
                    $jurnalUmum->rp_debet = 0;
                    $jurnalUmum->rp_kredit = abs($recJurnal->rp_debet);
                }
                $jurnalUmum->tgl_doc = $recJurnal->tgl_doc;
                $jurnalUmum->catatan = $recJurnal->catatan;
                $jurnalUmum->no_ref1 = $recJurnal->no_doc;
                $jurnalUmum->no_ref2 = '';
                $jurnalUmum->user_id = $user_id;
                $jurnalUmum->base_type = $docTrans; //Jurnal Umum
                $jurnalUmum->base_doc_key = $recJurnal->doc_key;
                $jurnalUmum->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalUmum->kd_project = $recJurnal->kd_project;
                $jurnalUmum->save();
            } else if ($recJurnal->rp_kredit != 0) {
                $jurnalUmumK= new AccountDtl();
                $jurnalUmumK->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalUmumK->no_account = $recJurnal->no_account;
                $jurnalUmumK->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_kredit > 0) {
                    $jurnalUmumK->enum_debet_kredit = 'K';
                    $jurnalUmumK->rp_debet = 0;
                    $jurnalUmumK->rp_kredit = abs($recJurnal->rp_kredit);
                } else {
                    $jurnalUmumK->enum_debet_kredit = 'D';
                    $jurnalUmumK->rp_debet = abs($recJurnal->rp_kredit);
                    $jurnalUmumK->rp_kredit = 0;
                }
                $jurnalUmumK->tgl_doc = $recJurnal->tgl_doc;
                $jurnalUmumK->catatan = $recJurnal->catatan;
                $jurnalUmumK->no_ref1 = $recJurnal->no_doc;
                $jurnalUmumK->no_ref2 = '';
                $jurnalUmumK->user_id = $user_id;
                $jurnalUmumK->base_type = $docTrans; //Jurnal Umum
                $jurnalUmumK->base_doc_key = $recJurnal->doc_key;
                $jurnalUmumK->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalUmumK->kd_project = $recJurnal->kd_project;
                $jurnalUmumK->save();
            }
        }

        //Update Doc Key Jurnal di Jurnal1
        Jurnal1::where('doc_key',$recJurnal->doc_key)->update(['doc_key_gl'=>$doc_key]);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_jurnal1'];
        $dataTrans2= $data['t_jurnal2'];

        //Hanya jika rp_bayar != 0
        $dataTrans2 = array_filter($dataTrans2, function($item) {
            return ($item['rp_debet'] != 0 || $item['rp_kredit'] != 0);
        });

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'tgl_doc'=>'bail|required',
            ],[
                'tgl_doc.required'=>'Tanggal Dokumen harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $jurnal1= Jurnal1::where('doc_key',$where['doc_key'])->first();
            if (!$jurnal1) {
                $jurnal1= new Jurnal1();
                $jurnal1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $jurnal1->no_doc         = $dataTrans1['no_doc'];
            $jurnal1->tgl_doc        = $dataTrans1['tgl_doc'];
            $jurnal1->tp_jurnal      = $dataTrans1['tp_jurnal'];
            $jurnal1->kd_nominasi    = $dataTrans1['kd_nominasi'];
            $jurnal1->tgl_jurnal     = $dataTrans1['tgl_jurnal'];
            $jurnal1->no_referensi   = $dataTrans1['no_referensi'];
            $jurnal1->catatan        = $dataTrans1['catatan'];
            $jurnal1->rp_total_cr    = $dataTrans1['rp_total_cr'];
            $jurnal1->rp_total_dr    = $dataTrans1['rp_total_dr'];
            $jurnal1->fl_post        = $dataTrans1['fl_post'];
            $jurnal1->tgl_post       = $dataTrans1['tgl_post'];
            $jurnal1->create_tgl     = $dataTrans1['create_tgl'];
            $jurnal1->create_userid  = $dataTrans1['create_userid'];
            $jurnal1->create_lokasi  = $dataTrans1['create_lokasi'];
            $jurnal1->update_tgl     = $dataTrans1['update_tgl'];
            $jurnal1->update_userid  = $dataTrans1['update_userid'];
            $jurnal1->update_lokasi  = $dataTrans1['update_lokasi'];
            $jurnal1->lock_userid    = $dataTrans1['lock_userid'];
            $jurnal1->lock_lokasi    = $dataTrans1['lock_lokasi'];
            $jurnal1->doc_key_gl     = $dataTrans1['doc_key_gl'];
            $jurnal1->tgl_proses     = $dataTrans1['tgl_proses'];
            $jurnal1->fl_batal       = $dataTrans1['fl_batal'];
            $jurnal1->save();

            //Data Jurnal2
            $existingIds = Jurnal2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Jurnal2::whereIn('dtl2_key', $toDelete)->delete();

            //Data Jurnal2
            //Jurnal2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans2 as $recTrans2) {
                $validator=Validator::make($recTrans2,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $jurnal2 = Jurnal2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($jurnal2)) {
                    $jurnal2 = new Jurnal2();
                    $jurnal2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $jurnal2->doc_key           = $jurnal1->doc_key;
                $jurnal2->no_baris          = $recTrans2['no_baris'];
                $jurnal2->kd_project        = $recTrans2['kd_project'];
                $jurnal2->grup1             = $recTrans2['grup1'];
                $jurnal2->grup2             = $recTrans2['grup2'];
                $jurnal2->no_account        = $recTrans2['no_account'];
                $jurnal2->nm_account        = $recTrans2['nm_account'];
                $jurnal2->ket_account       = $recTrans2['ket_account'];
                $jurnal2->rp_debet          = $recTrans2['rp_debet'];
                $jurnal2->rp_kredit         = $recTrans2['rp_kredit'];
                $jurnal2->save();
            }

            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                JurnalUmumController::generateJurnal($jurnal1->doc_key, $user_id);
            }

            DB::commit();

            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
