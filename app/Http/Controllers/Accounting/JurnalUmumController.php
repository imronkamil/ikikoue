<?php

namespace App\Http\Controllers\Accounting;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Accounting\AccountDtl;
use App\Models\Accounting\Jurnal1;
use App\Models\Accounting\Jurnal2;
use App\Models\Master\Lokasi;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bank;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class JurnalUmumController extends Controller
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
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
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
        $data['t_bank_keluar2']= Jurnal2::from('t_bank_keluar2 as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->leftJoin('t_ap_invoice1 as c', function ($join) {
            $join->on('a.base_ref','=','c.doc_key')
            ->where('a.base_type',21); //21=AP Invoice
        })
        ->leftJoin('t_ap_credit1 as d', function ($join) {
            $join->on('a.base_ref','=','d.doc_key')
            ->where('a.base_type',22); //22=AP Credit
        })
        ->selectRaw("a.*, b.base_name AS base_type_name,
            COALESCE(c.no_doc,d.no_doc) AS ref_no_doc, COALESCE(c.tgl_doc,d.tgl_doc) AS ref_tgl_doc,
            COALESCE(c.rp_total,d.rp_total) AS ref_rp_total,
            COALESCE(c.rp_sisa,d.rp_sisa)+a.rp_bayar-a.rp_diskon AS ref_rp_jatah,
            COALESCE(c.rp_sisa,d.rp_sisa)+a.rp_bayar-a.rp_diskon AS ref_rp_sisa")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //BankKeluar3
        $data['t_bank_keluar3']= BankKeluar3::from('t_bank_keluar3 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
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

        //Master Supplier Filter
        $data['m_supplier_filter']= Supplier::from('m_supplier as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_supplier")
        ->get();

        //Master Customer
        /*$data['m_customer_filter']= Customer::from('m_customer')
        ->selectRaw("*")
        ->where("fl_aktif","true")
        ->orderBy("kd_customer","desc")
        ->get();*/

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
        ->leftJoin('t_bank_keluar2 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("b.*, a.tgl_doc, a.no_doc, a.kd_lokasi, a.nm_partner")
        ->where("a.doc_key",$doc_key)
        ->whereRaw("COALESCE(b.rp_bayar,0) <> 0")
        ->where("a.enum_tipe","<>","A") //Selain Advance
        ->get();
        //Jurnal Debet (Purchase Invoice)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_bayar != 0) {
                $jurnalAP= new AccountDtl();
                $jurnalAP->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalAP->no_account = $recJurnal->no_account;
                $jurnalAP->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_bayar > 0) {
                    $jurnalAP->enum_debet_kredit = 'D';
                    $jurnalAP->rp_debet = abs($recJurnal->rp_bayar);
                    $jurnalAP->rp_kredit = 0;
                } else {
                    $jurnalAP->enum_debet_kredit = 'K';
                    $jurnalAP->rp_debet = 0;
                    $jurnalAP->rp_kredit = abs($recJurnal->rp_bayar);
                }
                $jurnalAP->tgl_doc = $recJurnal->tgl_doc;
                $jurnalAP->catatan = $recJurnal->nm_partner;
                $jurnalAP->no_ref1 = $recJurnal->no_doc;
                $jurnalAP->no_ref2 = '';
                $jurnalAP->user_id = $user_id;
                $jurnalAP->base_type = $docTrans; //Bank Keluar
                $jurnalAP->base_doc_key = $recJurnal->doc_key;
                $jurnalAP->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalAP->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalAP->save();
            }
        }

        //Jurnal Biaya
        $jurnal= Jurnal1::from('t_jurnal1 as a')
        ->leftJoin('t_bank_keluar2 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("b.*, a.tgl_doc, a.no_doc, a.kd_lokasi")
        ->where("a.doc_key",$doc_key)
        ->whereRaw("COALESCE(b.rp_bayar,0) <> 0")
        ->where("a.enum_tipe","=","A") //Advance
        ->get();
        //Jurnal Debet (Biaya)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_bayar != 0) {
                $jurnalBiaya= new AccountDtl();
                $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBiaya->no_account = $recJurnal->no_account;
                $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_bayar > 0) {
                    $jurnalBiaya->enum_debet_kredit = 'D';
                    $jurnalBiaya->rp_debet = abs($recJurnal->rp_bayar);
                    $jurnalBiaya->rp_kredit = 0;
                } else {
                    $jurnalBiaya->enum_debet_kredit = 'K';
                    $jurnalBiaya->rp_debet = 0;
                    $jurnalBiaya->rp_kredit = abs($recJurnal->rp_bayar);
                }
                $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBiaya->catatan = $recJurnal->catatan;
                $jurnalBiaya->no_ref1 = $recJurnal->no_doc;
                $jurnalBiaya->no_ref2 = '';
                $jurnalBiaya->user_id = $user_id;
                $jurnalBiaya->base_type = $docTrans; //Bank Keluar
                $jurnalBiaya->base_doc_key = $recJurnal->doc_key;
                $jurnalBiaya->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalBiaya->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBiaya->save();
            }
        }

        //Jurnal Biaya, Rounding, Bank
        $subQ1= DB::table('t_bank_keluar2 as a')
        ->selectRaw('a.doc_key, string_agg(a.base_no_doc::varchar, \', \') AS catatan_dtl2')
        ->groupBy('a.doc_key');
        $subQ2= DB::table('t_bank_keluar3 as a')
        ->selectRaw('a.doc_key, string_agg(a.catatan::varchar, \', \') AS catatan_dtl3')
        ->groupBy('a.doc_key');
        $jurnal= Jurnal1::from('t_jurnal1 as a')
        ->leftJoin('m_bank as b','a.bank_id','=','b.bank_id')
        ->leftJoinSub($subQ1, 'c', function($join) {
            $join->on('a.doc_key', '=', 'c.doc_key');
        })
        ->leftJoinSub($subQ2, 'd', function($join) {
            $join->on('a.doc_key', '=', 'd.doc_key');
        })
        ->selectRaw("a.*, b.no_account AS no_acc_bank,
            trim(
                COALESCE(c.catatan_dtl2,'') ||
                CASE WHEN c.catatan_dtl2 IS NOT NULL AND d.catatan_dtl3 IS NOT NULL THEN ', ' ELSE '' END ||
                COALESCE(d.catatan_dtl3,'')
            ) AS catatan_dtl")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Kredit (Biaya, Rounding, Bank)
        foreach($jurnal as $recJurnal) {
            //Biaya
            if ($recJurnal->rp_biaya != 0) {
                $jurnalPPN= new AccountDtl();
                $jurnalPPN->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPPN->no_account = UtilityController::getAccountConfig('no_acc_bk_charge');
                $jurnalPPN->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_biaya > 0) {
                    $jurnalPPN->enum_debet_kredit = 'D';
                    $jurnalPPN->rp_debet = abs($recJurnal->rp_biaya);
                    $jurnalPPN->rp_kredit = 0;
                } else {
                    $jurnalPPN->enum_debet_kredit = 'K';
                    $jurnalPPN->rp_debet = 0;
                    $jurnalPPN->rp_kredit = abs($recJurnal->rp_biaya);
                }
                $jurnalPPN->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPPN->catatan = $recJurnal->catatan;
                $jurnalPPN->no_ref1 = $recJurnal->no_doc;
                $jurnalPPN->no_ref2 = '';
                $jurnalPPN->user_id = $user_id;
                $jurnalPPN->base_type = $docTrans; //Bank Keluar
                $jurnalPPN->base_doc_key = $recJurnal->doc_key;
                //$jurnalPPN->base_dtl_key = $recJurnal->doc_key;
                $jurnalPPN->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPPN->save();
            }
            //Rounding
            if ($recJurnal->rp_rounding != 0) {
                $jurnalRounding= new AccountDtl();
                $jurnalRounding->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalRounding->no_account = UtilityController::getAccountConfig('no_acc_bk_rounding');
                $jurnalRounding->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_rounding > 0) {
                    $jurnalRounding->enum_debet_kredit = 'D';
                    $jurnalRounding->rp_debet = abs($recJurnal->rp_rounding);
                    $jurnalRounding->rp_kredit = 0;
                } else {
                    $jurnalRounding->enum_debet_kredit = 'K';
                    $jurnalRounding->rp_debet = 0;
                    $jurnalRounding->rp_kredit = abs($recJurnal->rp_rounding);
                }
                $jurnalRounding->tgl_doc = $recJurnal->tgl_doc;
                $jurnalRounding->catatan = $recJurnal->catatan;
                $jurnalRounding->no_ref1 = $recJurnal->no_doc;
                $jurnalRounding->no_ref2 = '';
                $jurnalRounding->user_id = $user_id;
                $jurnalRounding->base_type = $docTrans; //Bank Keluar
                $jurnalRounding->base_doc_key = $recJurnal->doc_key;
                //$jurnalRounding->base_dtl_key = $recJurnal->doc_key;
                $jurnalRounding->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalRounding->save();
            }
            //Bank
            if ($recJurnal->rp_bayar != 0) {
                $jurnalBank= new AccountDtl();
                $jurnalBank->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBank->no_account = $recJurnal->no_acc_bank;
                $jurnalBank->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_bayar > 0) {
                    $jurnalBank->enum_debet_kredit = 'K';
                    $jurnalBank->rp_debet = 0;
                    $jurnalBank->rp_kredit = abs($recJurnal->rp_bayar);
                } else {
                    $jurnalBank->enum_debet_kredit = 'D';
                    $jurnalBank->rp_debet = abs($recJurnal->rp_bayar);
                    $jurnalBank->rp_kredit = 0;
                }
                $jurnalBank->tgl_doc = $recJurnal->tgl_doc;
                //$jurnalBank->catatan = $recJurnal->catatan_dtl; //string too long 100
                $jurnalBank->catatan = $recJurnal->catatan;
                $jurnalBank->no_ref1 = $recJurnal->no_doc;
                $jurnalBank->no_ref2 = '';
                $jurnalBank->user_id = $user_id;
                $jurnalBank->base_type = $docTrans; //Bank Keluar
                $jurnalBank->base_doc_key = $recJurnal->doc_key;
                //$jurnalBank->base_dtl_key = $recJurnal->doc_key;
                $jurnalBank->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBank->save();
            }
        }

        //Update Doc Key Jurnal di Jurnal1
        Jurnal1::where('doc_key',$recJurnal->doc_key)->update(['doc_key_jurnal'=>$doc_key]);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_jurnal1'];
        $dataTrans2= $data['t_bank_keluar2'];
        $dataTrans3= $data['t_bank_keluar3'];

        //Hanya jika rp_bayar != 0
        $dataTrans2 = array_values(array_filter($dataTrans2, function ($item) {
            return isset($item['rp_bayar']) && $item['rp_bayar'] != 0;
        }));

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'kd_lokasi'=>'bail|required',
            ],[
                'kd_lokasi.required'=>'Kode Lokasi harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $bankKeluar1= Jurnal1::where('doc_key',$where['doc_key'])->first();
            if ($bankKeluar1) {
                BankKeluarController::updateLinkData($doc_key, FALSE);
            } else {
                $bankKeluar1= new Jurnal1();
                $bankKeluar1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $bankKeluar1->no_doc         = $dataTrans1['no_doc'];
            $bankKeluar1->tgl_doc        = $dataTrans1['tgl_doc'];
            $bankKeluar1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $bankKeluar1->no_referensi   = $dataTrans1['no_referensi'];
            $bankKeluar1->enum_tipe      = $dataTrans1['enum_tipe'];
            $bankKeluar1->kd_partner     = $dataTrans1['kd_partner'];
            $bankKeluar1->kd_kontak      = $dataTrans1['kd_kontak'];
            $bankKeluar1->nm_penerima    = $dataTrans1['nm_penerima'];
            $bankKeluar1->catatan        = $dataTrans1['catatan'];
            $bankKeluar1->kd_bayar       = $dataTrans1['kd_bayar'];
            $bankKeluar1->no_cek         = $dataTrans1['no_cek'];
            $bankKeluar1->tgl_cair       = $dataTrans1['tgl_cair'];
            $bankKeluar1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $bankKeluar1->rp_biaya       = $dataTrans1['rp_biaya'];
            $bankKeluar1->rp_rounding    = $dataTrans1['rp_rounding'];
            $bankKeluar1->rp_total       = $dataTrans1['rp_total'];
            $bankKeluar1->rp_bayar       = $dataTrans1['rp_bayar'];
            $bankKeluar1->cara_bayar     = $dataTrans1['cara_bayar'];
            $bankKeluar1->base_type      = $dataTrans1['base_type'];
            $bankKeluar1->base_ref       = $dataTrans1['base_ref'];
            $bankKeluar1->fl_tutup       = $dataTrans1['fl_tutup'];
            $bankKeluar1->fl_batal       = $dataTrans1['fl_batal'];
            $bankKeluar1->cetak          = $dataTrans1['cetak'];
            $bankKeluar1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $bankKeluar1->create_tgl     = $dataTrans1['create_tgl'];
            $bankKeluar1->create_userid  = $dataTrans1['create_userid'];
            $bankKeluar1->create_lokasi  = $dataTrans1['create_lokasi'];
            $bankKeluar1->update_tgl     = $dataTrans1['update_tgl'];
            $bankKeluar1->update_userid  = $dataTrans1['update_userid'];
            $bankKeluar1->update_lokasi  = $dataTrans1['update_lokasi'];
            $bankKeluar1->no_account     = $dataTrans1['no_account'];
            $bankKeluar1->nm_partner     = $dataTrans1['nm_partner'];
            $bankKeluar1->alamat_inv     = $dataTrans1['alamat_inv'];
            $bankKeluar1->telp_inv       = $dataTrans1['telp_inv'];
            $bankKeluar1->kd_project     = $dataTrans1['kd_project'];
            $bankKeluar1->tgl_proses     = $dataTrans1['tgl_proses'];
            $bankKeluar1->bank_id        = $dataTrans1['bank_id'];
            $bankKeluar1->save();

            //Data Jurnal2
            $existingIds = Jurnal2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Jurnal2::whereIn('dtl2_key', $toDelete)->delete();

            //Data Jurnal2
            //Jurnal2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans2 as $recTrans2) {
                /*$validator=Validator::make($recTrans2,[
                    'rp_bayar'=>'bail|required',
                ],[
                    'rp_bayar.required'=>'Rp Bayar harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }*/

                $bankKeluar2 = Jurnal2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($bankKeluar2)) {
                    $bankKeluar2 = new Jurnal2();
                    $bankKeluar2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $bankKeluar2->doc_key           = $bankKeluar1->doc_key;
                $bankKeluar2->no_urut           = $recTrans2['no_urut'];
                $bankKeluar2->rp_bayar          = $recTrans2['rp_bayar'];
                $bankKeluar2->rp_diskon         = $recTrans2['rp_diskon'];
                $bankKeluar2->base_type         = $recTrans2['base_type'];
                $bankKeluar2->base_ref          = $recTrans2['base_ref'];
                $bankKeluar2->catatan           = $recTrans2['catatan'];
                $bankKeluar2->no_account        = $recTrans2['no_account'];
                $bankKeluar2->base_no_doc       = $recTrans2['base_no_doc'];
                $bankKeluar2->save();
            }

            //Data BankKeluar3
            $existingIds = BankKeluar3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans3)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            BankKeluar3::whereIn('dtl3_key', $toDelete)->delete();

            //Data BankKeluar3
            //BankKeluar3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $bankKeluar3 = BankKeluar3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($bankKeluar3)) {
                    $bankKeluar3 = new BankKeluar3();
                    $bankKeluar3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $bankKeluar3->doc_key        = $bankKeluar1->doc_key;
                $bankKeluar3->no_urut        = $recTrans3['no_urut'];
                $bankKeluar3->no_account     = $recTrans3['no_account'];
                $bankKeluar3->nm_account     = $recTrans3['nm_account'];
                $bankKeluar3->catatan        = $recTrans3['catatan'];
                $bankKeluar3->rp_bayar       = $recTrans3['rp_bayar'];
                $bankKeluar3->save();
            }

            BankKeluarController::updateLinkData($bankKeluar1->doc_key, TRUE);
            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                BankKeluarController::generateJurnal($bankKeluar1->doc_key, $user_id);
            }

            DB::commit();
            $bk2 = Jurnal2::where('doc_key',$doc_key)->first();

            $response['doc_key'] = $doc_key;
            $response['trans2'] = $dataTrans2;
            $response['bk2'] = $bk2;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
