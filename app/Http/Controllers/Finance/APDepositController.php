<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Accounting\AccountDtl;
use App\Models\Finance\APDP1;
use App\Models\Master\Lokasi;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bank;
use App\Models\Master\Supplier;
use App\Models\Master\Customer;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class APDepositController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data['t_apdp1']= APDP1::from('t_apdp1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_supplier, b.telp, c.nm_bank, d.nm_lokasi")
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
        $data['t_apdp1']= APDP1::from('t_apdp1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_supplier, b.telp, c.nm_bank, d.nm_lokasi")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_apdp1']= APDP1::from('t_apdp1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->leftJoin('m_bank as c','a.bank_id','=','c.bank_id')
        ->leftJoin('m_lokasi as d','a.kd_lokasi','=','d.kd_lokasi')
        ->selectRaw("a.*, b.nm_supplier, b.telp, c.nm_bank, d.nm_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_apdp1']= APDP1::from('t_apdp1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_apdp1']) ? $data['t_apdp1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //APDP1
        $data['t_apdp1']= APDP1::from('t_apdp1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();

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
        ->leftJoin('m_supplier_grup as b','a.kd_supplier_grup','=','b.kd_supplier_grup')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi, b.no_account")
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
        APDP1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $apDP1= APDP1::where('doc_key',$doc_key)->first();
        if ($apDP1) {
            if ($apDP1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //APDepositController::updateLinkData($doc_key,FALSE);
            //Update APDP1
            $apDP1->catatan = $catatan . "\n" . $apDP1->catatan;
            $apDP1->fl_batal = 'true';
            $apDP1->batal_tgl = date('Y-m-d H:i:s');
            $apDP1->batal_userid = $request->userid;
            $apDP1->batal_lokasi = $request->lokasi;
            $apDP1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';
        $docTrans=20; //AP Deposit

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal AP Deposit
        $jurnal= APDP1::from('t_apdp1 as a')
        ->leftJoin('m_bank as b','a.bank_id','=','b.bank_id')
        ->selectRaw("a.*, b.no_account")
        ->where("a.doc_key",$doc_key)
        ->whereRaw("COALESCE(a.fl_batal,false) = false")
        ->get();
        //Jurnal Debet (AP Deposit)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_total != 0) {
                //Debet AP Deposit
                $jurnalAPDP= new AccountDtl();
                $jurnalAPDP->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalAPDP->no_account = UtilityController::getAccountConfig('no_acc_ap_deposit');
                $jurnalAPDP->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_total > 0) {
                    $jurnalAPDP->enum_debet_kredit = 'D';
                    $jurnalAPDP->rp_debet = abs($recJurnal->rp_total);
                    $jurnalAPDP->rp_kredit = 0;
                } else {
                    $jurnalAPDP->enum_debet_kredit = 'K';
                    $jurnalAPDP->rp_debet = 0;
                    $jurnalAPDP->rp_kredit = abs($recJurnal->rp_total);
                }
                $jurnalAPDP->tgl_doc = $recJurnal->tgl_doc;
                $jurnalAPDP->catatan = $recJurnal->catatan;
                $jurnalAPDP->no_ref1 = $recJurnal->no_doc;
                $jurnalAPDP->no_ref2 = '';
                $jurnalAPDP->user_id = $user_id;
                $jurnalAPDP->base_type = $docTrans; //AP Deposit
                $jurnalAPDP->base_doc_key = $recJurnal->doc_key;
                //$jurnalAPDP->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalAPDP->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalAPDP->save();

                //Kredit AP Deposit
                $jurnalBank= new AccountDtl();
                $jurnalBank->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBank->no_account = $recJurnal->no_account;
                $jurnalBank->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_total > 0) {
                    $jurnalBank->enum_debet_kredit = 'K';
                    $jurnalBank->rp_debet = 0;
                    $jurnalBank->rp_kredit = abs($recJurnal->rp_total);
                } else {
                    $jurnalBank->enum_debet_kredit = 'D';
                    $jurnalBank->rp_debet = abs($recJurnal->rp_total);
                    $jurnalBank->rp_kredit = 0;
                }
                $jurnalBank->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBank->catatan = $recJurnal->catatan;
                $jurnalBank->no_ref1 = $recJurnal->no_doc;
                $jurnalBank->no_ref2 = '';
                $jurnalBank->user_id = $user_id;
                $jurnalBank->base_type = $docTrans; //AP Deposit
                $jurnalBank->base_doc_key = $recJurnal->doc_key;
                //$jurnalBank->base_dtl_key = $recJurnal->dtl2_key;
                $jurnalBank->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBank->save();
            }
        }

        //Update Doc Key Jurnal di APDP1
        APDP1::where('doc_key',$recJurnal->doc_key)->update(['doc_key_jurnal'=>$doc_key]);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_apdp1'];

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

            $apDP1= APDP1::where('doc_key',$where['doc_key'])->first();
            if ($apDP1) {
                //BankKeluarController::updateLinkData($doc_key, FALSE);
            } else {
                $apDP1= new APDP1();
                $apDP1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $apDP1->no_doc         = $dataTrans1['no_doc'];
            $apDP1->tgl_doc        = $dataTrans1['tgl_doc'];
            $apDP1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $apDP1->no_referensi   = $dataTrans1['no_referensi'];
            $apDP1->kd_partner     = $dataTrans1['kd_partner'];
            $apDP1->kd_kontak      = $dataTrans1['kd_kontak'];
            $apDP1->catatan        = $dataTrans1['catatan'];
            $apDP1->kd_bayar       = $dataTrans1['kd_bayar'];
            $apDP1->no_cek         = $dataTrans1['no_cek'];
            $apDP1->tgl_cair       = $dataTrans1['tgl_cair'];
            $apDP1->rp_total       = $dataTrans1['rp_total'];
            $apDP1->rp_sisa        = $dataTrans1['rp_sisa'];
            $apDP1->cara_bayar     = $dataTrans1['cara_bayar'];
            $apDP1->fl_tutup       = $dataTrans1['fl_tutup'];
            $apDP1->fl_batal       = $dataTrans1['fl_batal'];
            $apDP1->cetak          = $dataTrans1['cetak'];
            $apDP1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $apDP1->create_tgl     = $dataTrans1['create_tgl'];
            $apDP1->create_userid  = $dataTrans1['create_userid'];
            $apDP1->create_lokasi  = $dataTrans1['create_lokasi'];
            $apDP1->update_tgl     = $dataTrans1['update_tgl'];
            $apDP1->update_userid  = $dataTrans1['update_userid'];
            $apDP1->update_lokasi  = $dataTrans1['update_lokasi'];
            $apDP1->no_account     = $dataTrans1['no_account'];
            $apDP1->nm_partner     = $dataTrans1['nm_partner'];
            $apDP1->alamat_inv     = $dataTrans1['alamat_inv'];
            $apDP1->telp_inv       = $dataTrans1['telp_inv'];
            $apDP1->nm_kontak      = $dataTrans1['nm_kontak'];
            $apDP1->bank_id        = $dataTrans1['bank_id'];
            $apDP1->save();

            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                APDepositController::generateJurnal($apDP1->doc_key, $user_id);
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
