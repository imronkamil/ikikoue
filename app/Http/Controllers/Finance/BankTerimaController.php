<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Accounting\AccountDtl;
use App\Models\Finance\BankTerima1;
use App\Models\Finance\BankTerima2;
use App\Models\Finance\BankTerima3;
use App\Models\Finance\APDP1;
use App\Models\Sales\SO5;
use App\Models\Sales\JualBayar;
use App\Models\Master\Lokasi;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bank;
use App\Models\Master\Supplier;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class BankTerimaController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data['t_bank_terima1']= BankTerima1::from('t_bank_terima1 as a')
        ->leftJoin('m_customer as b','a.kd_partner','=','b.kd_customer')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_customer, b.telp, c.nm_bank, d.nm_lokasi")
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
        $data['t_bank_terima1']= BankTerima1::from('t_bank_terima1 as a')
        ->leftJoin('m_customer as b','a.kd_partner','=','b.kd_customer')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_customer, b.telp, c.nm_bank, d.nm_lokasi")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_bank_terima1']= BankTerima1::from('t_bank_terima1 as a')
        ->leftJoin('m_customer as b','a.kd_partner','=','b.kd_customer')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_customer, b.telp, c.nm_bank, d.nm_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getSOBayar(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : 0;
        $tgl1=isset($request->tgl1) ? $request->tgl1 : '1900-01-01';
        $tgl2=isset($request->tgl2) ? $request->tgl2 : '2100-12-31';
        $enum_format=isset($request->enum_format) ? $request->enum_format : 0;
        $data['t_bank_terima2']= BankTerima2::from('t_so1 as a')
        ->leftJoin('t_so5 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('m_bayar as c','b.kd_bayar','=','c.kd_bayar')
        ->leftJoin('i_doctype as d', function ($join) {
            $join->where('d.base_type', 31); //31=Sales Order
        })
        ->selectRaw("a.no_doc, a.kd_partner, a.nm_partner, b.*, c.enum_format, c.persen_format, d.base_name as base_type_name,
            CASE WHEN c.enum_bayar='D' THEN 'Uang Muka' ELSE 'Pelunasan' END AS ket_enum_bayar")
        ->where("a.kd_lokasi",$kd_lokasi)
        ->whereRaw("COALESCE(a.fl_batal,false) = false")
        ->whereBetween("b.tgl_bayar",[$tgl1,$tgl2])
        ->whereColumn(DB::raw("COALESCE(b.rp_cair,0)"),"<","b.rp_tagihan")
        ->where("c.enum_format",$enum_format)
        ->orderBy("b.no_urut")
        ->get();
        return response()->success('Success',$data);
    }

    public function getPOSBayar(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : 0;
        $tgl1=isset($request->tgl1) ? $request->tgl1 : '1900-01-01';
        $tgl2=isset($request->tgl2) ? $request->tgl2 : '2100-12-31';
        $enum_format=isset($request->enum_format) ? $request->enum_format : 0;
        $data['t_bank_terima2']= BankTerima2::from('t_jual as a')
        ->leftJoin('t_jual_bayar as b','a.doc_key','=','b.doc_key')
        ->leftJoin('m_bayar as c','b.kd_bayar','=','c.kd_bayar')
        ->leftJoin('i_doctype as d', function ($join) {
            $join->where('d.base_type', 45); //45=Jual POS
        })
        ->selectRaw("a.no_doc, b.*, c.enum_format, c.persen_format, d.base_name as base_type_name,
            CASE WHEN c.enum_bayar='D' THEN 'Uang Muka' ELSE 'Pelunasan' END AS ket_enum_bayar")
        ->where("a.kd_lokasi",$kd_lokasi)
        ->whereBetween("b.tgl_bayar",[$tgl1,$tgl2])
        ->whereColumn(DB::raw("COALESCE(b.rp_cair,0)"),"<","b.rp_tagihan")
        ->where("c.enum_format",$enum_format)
        ->orderBy("b.no_urut")
        ->get();
        return response()->success('Success',$data);
    }

    public function getAPDeposit(Request $request) {
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : 0;
        $data['t_bank_terima2']= BankTerima2::from('t_apdp1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->leftJoin('m_supplier_grup as c','b.kd_supplier_grup','=','c.kd_supplier_grup')
        ->leftJoin('i_doctype as d', function ($join) {
            $join->where('d.base_type', 20); //20=AP Deposit
        })
        ->selectRaw("a.*, c.no_account AS no_account2, d.base_name as base_type_name")
        ->where("a.kd_partner",$kd_partner)
        ->whereRaw("COALESCE(a.rp_sisa,0) > 0")
        ->whereRaw("COALESCE(a.fl_batal,false) = false")
        ->whereRaw("COALESCE(a.fl_tutup,false) = false")
        ->orderBy("a.tgl_doc","desc")
        ->orderBy("a.doc_key","desc")
        ->get();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_bank_terima1']= BankTerima1::from('t_bank_terima1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_bank_terima1']) ? $data['t_bank_terima1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //BankTerima1
        $data['t_bank_terima1']= BankTerima1::from('t_bank_terima1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();

        //BankTerima2
        $subQ1= DB::table(DB::raw("(SELECT a.dtl5_key, a.doc_key, a.tgl_bayar, a.enum_bayar,
            a.rp_tagihan, a.rp_bayar, a.rp_cair, a.catatan, b.kd_partner, b.nm_partner
            FROM t_so5 AS a
            LEFT JOIN t_so1 AS b ON a.doc_key = b.doc_key) AS c"));
        $subQ2= DB::table(DB::raw("(SELECT a.dtl3_key, a.doc_key, a.tgl_bayar,
            a.rp_tagihan, a.rp_bayar, a.rp_cair, a.catatan
            FROM t_jual_bayar AS a
            LEFT JOIN t_jual AS b ON a.doc_key = b.doc_key) AS d"));
        $subQ3= DB::table(DB::raw("(SELECT a.doc_key, a.tgl_doc, a.rp_total, a.rp_sisa, a.catatan
            FROM t_apdp1 AS a) AS d"));
        $data['t_bank_terima2']= BankTerima2::from('t_bank_terima2 as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->leftJoinSub($subQ1,'c',function($join){
            $join->on('a.base_ref','=','c.dtl5_key')
                 ->where('a.base_type','=',31); //31=Sales Order
        })
        ->leftJoinSub($subQ2,'d',function($join){
            $join->on('a.base_ref','=','d.dtl3_key')
                 ->where('a.base_type','=',45); //45=Jual POS
        })
        ->leftJoinSub($subQ3,'e',function($join){
            $join->on('a.base_ref','=','e.doc_key')
                 ->where('a.base_type','=',20); //20=AP Deposit
        })
        ->selectRaw("a.*, b.base_name as base_type_name,
            COALESCE(c.tgl_bayar,d.tgl_bayar,e.tgl_doc) AS ref_tgl_doc,
            COALESCE(c.catatan,d.catatan,e.catatan) as ref_catatan,
            COALESCE(c.rp_tagihan,d.rp_tagihan,e.rp_total) AS ref_rp_total,
            COALESCE((c.rp_tagihan-c.rp_cair)+a.rp_bayar+a.rp_diskon,
                (d.rp_tagihan-d.rp_cair)+a.rp_bayar+a.rp_diskon,
                e.rp_sisa+a.rp_bayar+a.rp_diskon) AS ref_rp_jatah,
            COALESCE((c.rp_tagihan-c.rp_cair)+a.rp_bayar+a.rp_diskon,
                (d.rp_tagihan-d.rp_cair)+a.rp_bayar+a.rp_diskon,
                e.rp_sisa+a.rp_bayar+a.rp_diskon) AS ref_rp_sisa,
            c.kd_partner as ref_kd_customer, c.nm_partner as ref_nm_customer,
            CASE WHEN a.enum_bayar='D' THEN 'Uang Muka' ELSE 'Pelunasan' END AS ket_enum_bayar")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();
            //c.rp_bayar-c.rp_charge-c.rp_total_komisi as ref_rp_sisa,

        //BankTerima3
        $data['t_bank_terima3']= BankTerima3::from('t_bank_terima3 as a')
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
        BankTerimaController::updateLinkData($doc_key,FALSE);
        BankTerima3::where('doc_key',$doc_key)->delete();
        BankTerima2::where('doc_key',$doc_key)->delete();
        BankTerima1::where('doc_key',$doc_key)->delete();
        AccountDtl::where('base_doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $bankTerima1= BankTerima1::where('doc_key',$doc_key)->first();
        if ($bankTerima1) {
            if ($bankTerima1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            BankTerimaController::updateLinkData($doc_key,FALSE);
            AccountDtl::where('base_doc_key',$doc_key)->delete();
            //Update BankTerima1
            $bankTerima1->catatan = $catatan . "\n" . $bankTerima1->catatan;
            $bankTerima1->fl_batal = 'true';
            $bankTerima1->batal_tgl = date('Y-m-d H:i:s');
            $bankTerima1->batal_userid = $request->userid;
            $bankTerima1->batal_lokasi = $request->lokasi;
            $bankTerima1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //Sales Order
            $dataSO= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_so5 as b","a.base_ref","=","b.dtl5_key")
            ->selectRaw("b.dtl5_key, b.rp_cair, a.rp_bayar, a.rp_diskon, a.rp_charge, a.rp_total_komisi")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",31) //31=Sales Order
            ->get();
            foreach($dataSO as $recSO) {
                //Update SO5
                $so5 = SO5::where('dtl5_key',$recSO->dtl5_key)->first();
                if ($so5) {
                    $so5->fl_tutup = FALSE;
                    $so5->rp_cair = $so5->rp_cair - ($recSO->rp_bayar + $recSO->rp_diskon + $recSO->rp_charge + $recSO->rp_total_komisi);
                    $so5->save();
                }
            }
            //POS Sales
            $dataPOS= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_jual_bayar as b","a.base_ref","=","b.dtl3_key")
            ->selectRaw("b.dtl3_key, b.rp_cair, a.rp_bayar, a.rp_diskon, a.rp_charge, a.rp_total_komisi")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",45) //45=POS Sales
            ->get();
            foreach($dataPOS as $recPOS) {
                //Update Jual Bayar
                $jual_bayar = JualBayar::where('dtl3_key',$recPOS->dtl3_key)->first();
                if ($jual_bayar) {
                    $jual_bayar->rp_cair = $jual_bayar->rp_cair - ($recPOS->rp_bayar + $recPOS->rp_diskon + $recPOS->rp_charge + $recPOS->rp_total_komisi);
                    $jual_bayar->save();
                }
            }
            //AP Deposit
            $dataAPDP= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_apdp1 as b","a.base_ref","=","b.doc_key")
            ->selectRaw("b.doc_key, b.rp_total, b.rp_sisa, a.rp_bayar, a.rp_diskon")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",20) //20=AP Deposit
            ->get();
            foreach($dataAPDP as $recAPDP) {
                //Update APDP1
                $apdp1 = APDP1::where('doc_key',$recAPDP->doc_key)->first();
                if ($apdp1) {
                    if ($apdp1->rp_sisa - ($recAPDP->rp_bayar + $recAPDP->rp_diskon) > 0) {
                        $apdp1->fl_tutup = FALSE;
                    }
                    $apdp1->rp_sisa = $apdp1->rp_sisa + ($recAPDP->rp_bayar + $recAPDP->rp_diskon);
                    $apdp1->save();
                }
            }
        } elseif ($insert == TRUE) {
            //Sales Order
            $dataSO= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_so5 as b","a.base_ref","=","b.dtl5_key")
            ->selectRaw("b.dtl5_key, b.rp_cair, a.rp_bayar, a.rp_diskon, a.rp_charge, a.rp_total_komisi")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",31) //31=Sales Order
            ->get();
            foreach($dataSO as $recSO) {
                //Update SO5
                $so5 = SO5::where('dtl5_key',$recSO->dtl5_key)->first();
                if ($so5) {
                    $so5->fl_tutup = TRUE;
                    $so5->rp_cair = $so5->rp_cair + ($recSO->rp_bayar + $recSO->rp_diskon + $recSO->rp_charge + $recSO->rp_total_komisi);
                    $so5->save();
                }
            }
            //POS Sales
            $dataPOS= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_jual_bayar as b","a.base_ref","=","b.dtl3_key")
            ->selectRaw("b.dtl3_key, b.rp_cair, a.rp_bayar, a.rp_diskon, a.rp_charge, a.rp_total_komisi")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",45) //45=POS Sales
            ->get();
            foreach($dataPOS as $recPOS) {
                //Update Jual Bayar
                $jual_bayar = JualBayar::where('dtl3_key',$recPOS->dtl3_key)->first();
                if ($jual_bayar) {
                    $jual_bayar->rp_cair = $jual_bayar->rp_cair + ($recPOS->rp_bayar + $recPOS->rp_diskon + $recPOS->rp_charge + $recPOS->rp_total_komisi);
                    $jual_bayar->save();
                }
            }
            //AP Deposit
            $dataAPDP= BankTerima2::from("t_bank_terima2 as a")
            ->leftJoin("t_apdp1 as b","a.base_ref","=","b.doc_key")
            ->selectRaw("b.doc_key, b.rp_total, b.rp_sisa, a.rp_bayar, a.rp_diskon")
            ->where("a.doc_key",$doc_key)
            ->where("a.base_type",20) //20=AP Deposit
            ->get();
            foreach($dataAPDP as $recAPDP) {
                //Update APDP1
                $apdp1 = APDP1::where('doc_key',$recAPDP->doc_key)->first();
                if ($apdp1) {
                    if ($apdp1->rp_sisa - ($recAPDP->rp_bayar + $recAPDP->rp_diskon) <= 0) {
                        $apdp1->fl_tutup = TRUE;
                    }
                    $apdp1->rp_sisa = $apdp1->rp_sisa - ($recAPDP->rp_bayar + $recAPDP->rp_diskon);
                    $apdp1->save();
                }
            }
        }
        //var_dump($recPR1->doc_key,$rp_sisa);
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';
        $docTrans=60; //Bank Terima

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal Biaya, Rounding, Bank
        $jurnal= BankTerima1::from('t_bank_terima1 as a')
        ->leftJoin('m_bank as b','a.bank_id','=','b.bank_id')
        ->selectRaw("a.*, b.no_account AS no_acc_bank, b.no_account_charge AS no_acc_bank_charge")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Debet (Bank, Biaya, Rounding)
        foreach($jurnal as $recJurnal) {
            //Bank
            if ($recJurnal->rp_bayar != 0) {
                $jurnalBank= new AccountDtl();
                $jurnalBank->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBank->no_account = $recJurnal->no_acc_bank;
                $jurnalBank->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_bayar > 0) {
                    $jurnalBank->enum_debet_kredit = 'D';
                    $jurnalBank->rp_debet = abs($recJurnal->rp_bayar);
                    $jurnalBank->rp_kredit = 0;
                } else {
                    $jurnalBank->enum_debet_kredit = 'K';
                    $jurnalBank->rp_debet = 0;
                    $jurnalBank->rp_kredit = abs($recJurnal->rp_bayar);
                }
                $jurnalBank->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBank->catatan = substr($recJurnal->catatan, 0, 100);
                $jurnalBank->no_ref1 = $recJurnal->no_doc;
                $jurnalBank->no_ref2 = '';
                $jurnalBank->user_id = $user_id;
                $jurnalBank->base_type = $docTrans; //Bank Terima
                $jurnalBank->base_doc_key = $recJurnal->doc_key;
                //$jurnalBank->base_dtl_key = $recJurnal->doc_key;
                $jurnalBank->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBank->save();
            }
            //Biaya
            if ($recJurnal->rp_biaya != 0) {
                $jurnalBiaya= new AccountDtl();
                $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBiaya->no_account = $recJurnal->no_acc_bank_charge;
                $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_biaya > 0) {
                    $jurnalBiaya->enum_debet_kredit = 'D';
                    $jurnalBiaya->rp_debet = abs($recJurnal->rp_biaya);
                    $jurnalBiaya->rp_kredit = 0;
                } else {
                    $jurnalBiaya->enum_debet_kredit = 'K';
                    $jurnalBiaya->rp_debet = 0;
                    $jurnalBiaya->rp_kredit = abs($recJurnal->rp_biaya);
                }
                $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBiaya->catatan = substr('Bank Charge '.$recJurnal->catatan, 0, 100);
                $jurnalBiaya->no_ref1 = $recJurnal->no_doc;
                $jurnalBiaya->no_ref2 = '';
                $jurnalBiaya->user_id = $user_id;
                $jurnalBiaya->base_type = $docTrans; //Bank Terima
                $jurnalBiaya->base_doc_key = $recJurnal->doc_key;
                //$jurnalBiaya->base_dtl_key = $recJurnal->doc_key;
                $jurnalBiaya->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBiaya->save();
            }
            //Rounding
            if ($recJurnal->rp_rounding != 0) {
                $jurnalRounding= new AccountDtl();
                $jurnalRounding->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalRounding->no_account = UtilityController::getAccountConfig('no_acc_bm_rounding');
                $jurnalRounding->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_rounding > 0) {
                    $jurnalRounding->enum_debet_kredit = 'K';
                    $jurnalRounding->rp_debet = 0;
                    $jurnalRounding->rp_kredit = abs($recJurnal->rp_rounding);
                } else {
                    $jurnalRounding->enum_debet_kredit = 'D';
                    $jurnalRounding->rp_debet = abs($recJurnal->rp_rounding);
                    $jurnalRounding->rp_kredit = 0;
                }
                $jurnalRounding->tgl_doc = $recJurnal->tgl_doc;
                $jurnalRounding->catatan = substr($recJurnal->catatan, 0, 100);
                $jurnalRounding->no_ref1 = $recJurnal->no_doc;
                $jurnalRounding->no_ref2 = '';
                $jurnalRounding->user_id = $user_id;
                $jurnalRounding->base_type = $docTrans; //Bank Keluar
                $jurnalRounding->base_doc_key = $recJurnal->doc_key;
                //$jurnalRounding->base_dtl_key = $recJurnal->doc_key;
                $jurnalRounding->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalRounding->save();
            }

            //Update Doc Key Jurnal di BankTerima1
            BankTerima1::where('doc_key',$recJurnal->doc_key)->update(['doc_key_jurnal'=>$doc_key]);
        }

        //Jurnal Sales
        $jurnal= BankTerima1::from('t_bank_terima1 as a')
        ->leftJoin('t_bank_terima2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->selectRaw("b.*, a.tgl_doc, a.no_doc, a.kd_lokasi, c.no_account_charge AS no_acc_bank_charge")
        ->where("a.doc_key",$doc_key)
        ->whereRaw("COALESCE(b.rp_bayar,0) <> 0")
        ->where("a.enum_tipe","=","S") //Sales
        ->get();
        //Jurnal Debet (Sales)
        foreach($jurnal as $recJurnal) {
            //Komisi
            if ($recJurnal->rp_komisi != 0) {
                $jurnalKomisi = new AccountDtl();
                $jurnalKomisi->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalKomisi->no_account = UtilityController::getAccountConfig('no_acc_bm_komisi');
                $jurnalKomisi->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_komisi > 0) {
                    $jurnalKomisi->enum_debet_kredit = 'D';
                    $jurnalKomisi->rp_debet = abs($recJurnal->rp_komisi);
                    $jurnalKomisi->rp_kredit = 0;
                } else {
                    $jurnalKomisi->enum_debet_kredit = 'K';
                    $jurnalKomisi->rp_debet = 0;
                    $jurnalKomisi->rp_kredit = abs($recJurnal->rp_komisi);
                }
                $jurnalKomisi->tgl_doc = $recJurnal->tgl_doc;
                $jurnalKomisi->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
                $jurnalKomisi->no_ref1 = $recJurnal->no_doc;
                $jurnalKomisi->no_ref2 = '';
                $jurnalKomisi->user_id = $user_id;
                $jurnalKomisi->base_type = $docTrans; //Bank Keluar
                $jurnalKomisi->base_doc_key = $recJurnal->doc_key;
                $jurnalKomisi->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalKomisi->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalKomisi->save();
            }
            //PPN Komisi
            if ($recJurnal->rp_ppn_komisi != 0) {
                $jurnalPPN = new AccountDtl();
                $jurnalPPN->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPPN->no_account = UtilityController::getAccountConfig('no_acc_bm_ppn');
                $jurnalPPN->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_ppn_komisi > 0) {
                    $jurnalPPN->enum_debet_kredit = 'D';
                    $jurnalPPN->rp_debet = abs($recJurnal->rp_ppn_komisi);
                    $jurnalPPN->rp_kredit = 0;
                } else {
                    $jurnalPPN->enum_debet_kredit = 'K';
                    $jurnalPPN->rp_debet = 0;
                    $jurnalPPN->rp_kredit = abs($recJurnal->rp_ppn_komisi);
                }
                $jurnalPPN->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPPN->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
                $jurnalPPN->no_ref1 = $recJurnal->no_doc;
                $jurnalPPN->no_ref2 = '';
                $jurnalPPN->user_id = $user_id;
                $jurnalPPN->base_type = $docTrans; //Bank Keluar
                $jurnalPPN->base_doc_key = $recJurnal->doc_key;
                $jurnalPPN->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalPPN->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPPN->save();
            }
            //Charge
            if ($recJurnal->rp_charge != 0) {
                $jurnalCharge = new AccountDtl();
                $jurnalCharge->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalCharge->no_account = $recJurnal->no_acc_bank_charge;
                $jurnalCharge->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_charge > 0) {
                    $jurnalCharge->enum_debet_kredit = 'D';
                    $jurnalCharge->rp_debet = abs($recJurnal->rp_charge);
                    $jurnalCharge->rp_kredit = 0;
                } else {
                    $jurnalCharge->enum_debet_kredit = 'K';
                    $jurnalCharge->rp_debet = 0;
                    $jurnalCharge->rp_kredit = abs($recJurnal->rp_charge);
                }
                $jurnalCharge->tgl_doc = $recJurnal->tgl_doc;
                $jurnalCharge->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
                $jurnalCharge->no_ref1 = $recJurnal->no_doc;
                $jurnalCharge->no_ref2 = '';
                $jurnalCharge->user_id = $user_id;
                $jurnalCharge->base_type = $docTrans; //Bank Keluar
                $jurnalCharge->base_doc_key = $recJurnal->doc_key;
                $jurnalCharge->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalCharge->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalCharge->save();
            }
            //PPh23
            if ($recJurnal->rp_pph_komisi != 0) {
                $jurnalPPh23 = new AccountDtl();
                $jurnalPPh23->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPPh23->no_account = UtilityController::getAccountConfig('no_acc_bm_pph23');
                $jurnalPPh23->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_pph_komisi > 0) {
                    $jurnalPPh23->enum_debet_kredit = 'K';
                    $jurnalPPh23->rp_debet = 0;
                    $jurnalPPh23->rp_kredit = abs($recJurnal->rp_pph_komisi);
                } else {
                    $jurnalPPh23->enum_debet_kredit = 'D';
                    $jurnalPPh23->rp_debet = abs($recJurnal->rp_pph_komisi);
                    $jurnalPPh23->rp_kredit = 0;
                }
                $jurnalPPh23->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPPh23->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
                $jurnalPPh23->no_ref1 = $recJurnal->no_doc;
                $jurnalPPh23->no_ref2 = '';
                $jurnalPPh23->user_id = $user_id;
                $jurnalPPh23->base_type = $docTrans; //Bank Keluar
                $jurnalPPh23->base_doc_key = $recJurnal->doc_key;
                $jurnalPPh23->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalPPh23->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPPh23->save();
            }
            //Piutang (Kredit)
            $piutang= $recJurnal->rp_bayar + $recJurnal->rp_komisi + $recJurnal->rp_ppn_komisi +
                $recJurnal->rp_charge - $recJurnal->rp_pph_komisi;
            if ($piutang != 0) {
                $jurnalPiutang = new AccountDtl();
                $jurnalPiutang->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPiutang->no_account = UtilityController::getAccountConfig('no_acc_bm_piutang');
                $jurnalPiutang->kd_lokasi = $recJurnal->kd_lokasi;
                if ($piutang > 0) {
                    $jurnalPiutang->enum_debet_kredit = 'K';
                    $jurnalPiutang->rp_debet = 0;
                    $jurnalPiutang->rp_kredit = abs($piutang);
                } else {
                    $jurnalPiutang->enum_debet_kredit = 'D';
                    $jurnalPiutang->rp_debet = abs($piutang);
                    $jurnalPiutang->rp_kredit = 0;
                }
                $jurnalPiutang->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPiutang->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
                $jurnalPiutang->no_ref1 = $recJurnal->no_doc;
                $jurnalPiutang->no_ref2 = '';
                $jurnalPiutang->user_id = $user_id;
                $jurnalPiutang->base_type = $docTrans; //Bank Keluar
                $jurnalPiutang->base_doc_key = $recJurnal->doc_key;
                $jurnalPiutang->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalPiutang->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPiutang->save();
                continue;
            }
        }

        //Jurnal Biaya
        $jurnal= BankTerima1::from('t_bank_keluar1 as a')
        ->leftJoin('t_bank_keluar3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("b.*, a.tgl_doc, a.no_doc, a.kd_lokasi")
        ->where("a.doc_key",$doc_key)
        ->whereRaw("COALESCE(b.rp_bayar,0) <> 0")
        ->where("a.enum_tipe","=","A") //Advance
        ->get();
        //Jurnal Kredit (Biaya)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_bayar != 0) {
                $jurnalBiaya= new AccountDtl();
                $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBiaya->no_account = $recJurnal->no_account;
                $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_bayar > 0) {
                    $jurnalBiaya->enum_debet_kredit = 'K';
                    $jurnalBiaya->rp_debet = 0;
                    $jurnalBiaya->rp_kredit = abs($recJurnal->rp_bayar);
                } else {
                    $jurnalBiaya->enum_debet_kredit = 'D';
                    $jurnalBiaya->rp_debet = abs($recJurnal->rp_bayar);
                    $jurnalBiaya->rp_kredit = 0;
                }
                $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBiaya->catatan = $recJurnal->base_no_doc.'-'.$recJurnal->catatan;
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

        //Update Doc Key Jurnal di BankTerima1
        BankTerima1::where('doc_key',$recJurnal->doc_key)->update(['doc_key_jurnal'=>$doc_key]);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_bank_terima1'];
        $dataTrans2= $data['t_bank_terima2'];
        $dataTrans3= $data['t_bank_terima3'];

        //Hanya jika rp_bayar != 0
        $dataTrans2 = array_filter($dataTrans2, function ($item) {
            return isset($item['rp_bayar']) && $item['rp_bayar'] != 0;
        });

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

            $bankTerima1= BankTerima1::where('doc_key',$where['doc_key'])->first();
            if ($bankTerima1) {
                BankTerimaController::updateLinkData($doc_key, FALSE);
            } else {
                $bankTerima1= new BankTerima1();
                $bankTerima1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $bankTerima1->no_doc         = $dataTrans1['no_doc'];
            $bankTerima1->tgl_doc        = $dataTrans1['tgl_doc'];
            $bankTerima1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $bankTerima1->no_referensi   = $dataTrans1['no_referensi'];
            $bankTerima1->enum_tipe      = $dataTrans1['enum_tipe'];
            $bankTerima1->kd_partner     = $dataTrans1['kd_partner'];
            $bankTerima1->kd_kontak      = $dataTrans1['kd_kontak'];
            $bankTerima1->nm_penerima    = $dataTrans1['nm_penerima'];
            $bankTerima1->catatan        = $dataTrans1['catatan'];
            $bankTerima1->kd_bayar       = $dataTrans1['kd_bayar'];
            $bankTerima1->no_cek         = $dataTrans1['no_cek'];
            $bankTerima1->tgl_cair       = $dataTrans1['tgl_cair'];
            $bankTerima1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $bankTerima1->rp_biaya       = $dataTrans1['rp_biaya'];
            $bankTerima1->rp_rounding    = $dataTrans1['rp_rounding'];
            $bankTerima1->rp_total       = $dataTrans1['rp_total'];
            $bankTerima1->rp_bayar       = $dataTrans1['rp_bayar'];
            $bankTerima1->cara_bayar     = $dataTrans1['cara_bayar'];
            $bankTerima1->base_type      = $dataTrans1['base_type'];
            $bankTerima1->base_ref       = $dataTrans1['base_ref'];
            $bankTerima1->fl_tutup       = $dataTrans1['fl_tutup'];
            $bankTerima1->fl_batal       = $dataTrans1['fl_batal'];
            $bankTerima1->cetak          = $dataTrans1['cetak'];
            $bankTerima1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $bankTerima1->create_tgl     = $dataTrans1['create_tgl'];
            $bankTerima1->create_userid  = $dataTrans1['create_userid'];
            $bankTerima1->create_lokasi  = $dataTrans1['create_lokasi'];
            $bankTerima1->update_tgl     = $dataTrans1['update_tgl'];
            $bankTerima1->update_userid  = $dataTrans1['update_userid'];
            $bankTerima1->update_lokasi  = $dataTrans1['update_lokasi'];
            $bankTerima1->no_account     = $dataTrans1['no_account'];
            $bankTerima1->nm_partner     = $dataTrans1['nm_partner'];
            $bankTerima1->alamat_inv     = $dataTrans1['alamat_inv'];
            $bankTerima1->telp_inv       = $dataTrans1['telp_inv'];
            $bankTerima1->tgl_jual       = $dataTrans1['tgl_jual'];
            $bankTerima1->tgl_jual2      = $dataTrans1['tgl_jual2'];
            $bankTerima1->kd_project     = $dataTrans1['kd_project'];
            $bankTerima1->enum_format    = $dataTrans1['enum_format'];
            $bankTerima1->tgl_proses     = $dataTrans1['tgl_proses'];
            $bankTerima1->bank_id        = $dataTrans1['bank_id'];
            $bankTerima1->save();

            //Data BankTerima2
            $existingIds = BankTerima2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            BankTerima2::whereIn('dtl2_key', $toDelete)->delete();

            //Data BankTerima2
            //BankTerima2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans2 as $i => $recTrans2) {
                $validator=Validator::make($recTrans2,[
                    'rp_bayar'=>'bail|required',
                ],[
                    'rp_bayar.required'=>'Rp Bayar harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $bankTerima2 = BankTerima2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($bankTerima2)) {
                    $bankTerima2 = new BankTerima2();
                    $bankTerima2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $bankTerima2->doc_key           = $bankTerima1->doc_key;
                $bankTerima2->no_urut           = $i + 1;
                $bankTerima2->rp_bayar          = $recTrans2['rp_bayar'];
                $bankTerima2->rp_diskon         = $recTrans2['rp_diskon'];
                $bankTerima2->base_type         = $recTrans2['base_type'];
                $bankTerima2->base_ref          = $recTrans2['base_ref'];
                $bankTerima2->catatan           = $recTrans2['catatan'];
                $bankTerima2->no_account        = $recTrans2['no_account'];
                $bankTerima2->base_no_doc       = $recTrans2['base_no_doc'];
                $bankTerima2->kd_bayar          = $recTrans2['kd_bayar'];
                $bankTerima2->enum_bayar        = $recTrans2['enum_bayar'];
                $bankTerima2->rp_jumlah         = $recTrans2['rp_jumlah'];
                $bankTerima2->persen_komisi     = $recTrans2['persen_komisi'];
                $bankTerima2->rp_komisi         = $recTrans2['rp_komisi'];
                $bankTerima2->persen_ppn_komisi = $recTrans2['persen_ppn_komisi'];
                $bankTerima2->rp_ppn_komisi     = $recTrans2['rp_ppn_komisi'];
                $bankTerima2->persen_pph_komisi = $recTrans2['persen_pph_komisi'];
                $bankTerima2->rp_pph_komisi     = $recTrans2['rp_pph_komisi'];
                $bankTerima2->rp_total_komisi   = $recTrans2['rp_total_komisi'];
                $bankTerima2->persen_charge     = $recTrans2['persen_charge'];
                $bankTerima2->rp_charge         = $recTrans2['rp_charge'];
                $bankTerima2->base_doc_key      = $recTrans2['base_doc_key'];
                $bankTerima2->save();
            }

            //Data BankTerima3
            $existingIds = BankTerima3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans3)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            BankTerima3::whereIn('dtl3_key', $toDelete)->delete();

            //Data BankTerima3
            //BankTerima3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $bankTerima3 = BankTerima3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($bankTerima3)) {
                    $bankTerima3 = new BankTerima3();
                    $bankTerima3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $bankTerima3->doc_key        = $bankTerima1->doc_key;
                $bankTerima3->no_urut        = $recTrans3['no_urut'];
                $bankTerima3->no_account     = $recTrans3['no_account'];
                $bankTerima3->nm_account     = $recTrans3['nm_account'];
                $bankTerima3->catatan        = $recTrans3['catatan'];
                $bankTerima3->rp_bayar       = $recTrans3['rp_bayar'];
                $bankTerima3->save();
            }

            BankTerimaController::updateLinkData($bankTerima1->doc_key, TRUE);
            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                BankTerimaController::generateJurnal($bankTerima1->doc_key, $user_id);
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
