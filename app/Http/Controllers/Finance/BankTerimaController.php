<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Finance\BankTerima1;
use App\Models\Finance\BankTerima2;
use App\Models\Finance\BankTerima3;
use App\Models\Master\Lokasi;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bank;
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
        ->where("b.tgl_bayar",">=",$tgl1)
        ->where("b.tgl_bayar","<=",$tgl2)
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
        ->where("b.tgl_bayar",">=",$tgl1)
        ->where("b.tgl_bayar","<=",$tgl2)
        ->where("c.enum_format",$enum_format)
        ->orderBy("b.no_urut")
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
        ->selectRaw("a.*, b.base_name as base_type_name,
            COALESCE(c.tgl_bayar,d.tgl_bayar) AS ref_tgl_doc, COALESCE(c.catatan,d.catatan) as ref_catatan,
            COALESCE(c.rp_tagihan,d.rp_tagihan) AS ref_rp_total,
            COALESCE((c.rp_tagihan-c.rp_cair)+a.rp_bayar+a.rp_diskon,(d.rp_tagihan-d.rp_cair)+a.rp_bayar+a.rp_diskon) AS ref_rp_jatah,
            COALESCE((c.rp_tagihan-c.rp_cair)+a.rp_bayar,(d.rp_tagihan-d.rp_cair)+a.rp_bayar) AS ref_rp_sisa,
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

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        pr3::where('doc_key',$doc_key)->delete();
        pr2::where('doc_key',$doc_key)->delete();
        pr1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $po1= BankTerima1::where('doc_key',$doc_key)->first();
        if ($po1) {
            if ($po1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            PurchaseOrderController::setLinkData($doc_key,FALSE);
            //Update PO1
            $po1->catatan = $catatan . "\n" . $po1->catatan;
            $po1->fl_batal = 'true';
            $po1->batal_tgl = date('Y-m-d H:i:s');
            $po1->batal_userid = $request->userid;
            $po1->batal_lokasi = $request->lokasi;
            $po1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function setLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            $pr_doc_key = 0;
            $qty= 0;
            $qty_sisa= 0;
            $rp_sisa= 0;
            //PR1
            $dataPR= PR1::from("t_pr1 as a")
            ->leftJoin("t_pr2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_pr3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_po2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_po3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
            ->where("d.doc_key",$doc_key)
            //->groupBy("a.doc_key")
            ->get();
            foreach($dataPR as $recPR) {
                $pr_doc_key = $recPR->doc_key;
                //Update PR2
                $pr2 = PR2::where('dtl2_key',$recPR->dtl2_key)->first();
                if ($pr2) {
                    $pr2->qty_sisa = $pr2->qty_sisa + $recPR->qty;
                    $pr2->save();
                    $qty = $qty + $pr2->qty;
                    $qty_sisa = $qty_sisa + $pr2->qty_sisa;
                }
                //Update PR3
                $pr3 = PR3::where('dtl3_key',$recPR->dtl3_key)->first();
                if ($pr3) {
                    $pr3->rp_sisa = $pr3->rp_sisa + $recPR->rp_bayar;
                    $pr3->save();
                    $rp_sisa = $rp_sisa + $pr3->rp_sisa;
                }
            }

            //Update PR1
            $updPR1= PR1::where("doc_key",$pr_doc_key)->first();
            if ($updPR1) {
                if ($qty_sisa == 0 && $rp_sisa == 0) {
                    $updPR1->fl_tutup= TRUE;
                } else {
                    $updPR1->fl_tutup= FALSE;
                };
                if ($qty_sisa == 0) {
                    $updPR1->enum_tipe_po = 1; //Complete
                } elseif ($qty_sisa == $qty) {
                    $updPR1->enum_tipe_po = 0; //Aktif
                } else {
                    $updPR1->enum_tipe_po = 2; //Sebagian
                }
                $updPR1->save();
            }
        } elseif ($insert == TRUE) {
            $pr_doc_key = 0;
            $qty= 0;
            $qty_sisa= 0;
            $rp_sisa= 0;
            //PR1
            $dataPR1= PR1::from("t_pr1 as a")
            ->leftJoin("t_pr2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_pr3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_po2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_po3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
            ->where("d.doc_key",$doc_key)
            //->groupBy("a.doc_key")
            ->get();
            foreach($dataPR1 as $recPR1) {
                $pr_doc_key = $recPR1->doc_key;
                //Update PR2
                $po2 = PR2::where('dtl2_key',$recPR1->dtl2_key)->first();
                if ($po2) {
                    $po2->qty_sisa = $po2->qty_sisa - $recPR1->qty;
                    $po2->save();
                    $qty = $qty + $po2->qty;
                    $qty_sisa = $qty_sisa + $po2->qty_sisa;
                }
                //Update PR3
                $pr3 = PR3::where('dtl3_key',$recPR1->dtl3_key)->first();
                if ($pr3) {
                    $pr3->rp_sisa = $pr3->rp_sisa - $recPR1->rp_bayar;
                    $pr3->save();
                    $rp_sisa = $rp_sisa + $pr3->rp_sisa;
                }
            }

            //Update PR1
            $updPR1= PR1::where("doc_key",$pr_doc_key)->first();
            if ($updPR1) {
                if ($qty_sisa == 0 && $rp_sisa == 0) {
                    $updPR1->fl_tutup= TRUE;
                } else {
                    $updPR1->fl_tutup= FALSE;
                };
                if ($qty_sisa == 0) {
                    $updPR1->enum_tipe_po = 1; //Complete
                } elseif ($qty_sisa == $qty) {
                    $updPR1->enum_tipe_po = 0; //Aktif
                } else {
                    $updPR1->enum_tipe_po = 2; //Sebagian
                }
                $updPR1->save();
            }
        }
        //var_dump($recPR1->doc_key,$rp_sisa);
        $response['doc_key'] = $doc_key;
        $response['pr_doc_key'] = $pr_doc_key;
        $response['qty'] = $qty;
        $response['qty_sisa'] = $qty_sisa;
        $response['rp_sisa'] = $rp_sisa;
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_bank_terima1'];
        $dataTrans2= $data['t_bank_terima2'];
        $dataTrans3= $data['t_bank_terima3'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'kd_partner'=>'bail|required',
            ],[
                'kd_partner.required'=>'Kode Supplier harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            PurchaseOrderController::setLinkData($doc_key, TRUE);
            $po1= BankTerima1::where('doc_key',$where['doc_key'])->first();
            if (!($po1)) {
                $po1= new PO1();
                $po1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $po1->no_doc         = $dataTrans1['no_doc'];
            $po1->tgl_doc        = $dataTrans1['tgl_doc'];
            $po1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $po1->no_referensi   = $dataTrans1['no_referensi'];
            $po1->lama_bayar     = $dataTrans1['lama_bayar'];
            $po1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $po1->kd_partner     = $dataTrans1['kd_partner'];
            $po1->kd_kontak      = $dataTrans1['kd_kontak'];
            $po1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $po1->persen_diskon  = $dataTrans1['persen_diskon'];
            $po1->rp_diskon      = $dataTrans1['rp_diskon'];
            $po1->persen_pajak   = $dataTrans1['persen_pajak'];
            $po1->rp_pajak       = $dataTrans1['rp_pajak'];
            $po1->persen_biaya   = $dataTrans1['persen_biaya'];
            $po1->rp_biaya       = $dataTrans1['rp_biaya'];
            $po1->rp_rounding    = $dataTrans1['rp_rounding'];
            $po1->rp_total       = $dataTrans1['rp_total'];
            $po1->rp_uangmuka    = $dataTrans1['rp_uangmuka'];
            $po1->rp_bayar       = $dataTrans1['rp_bayar'];
            $po1->rp_sisa        = $dataTrans1['rp_sisa'];
            $po1->tgl_datang     = $dataTrans1['tgl_datang'];
            $po1->tgl_berlaku    = $dataTrans1['tgl_berlaku'];
            $po1->kd_buyer       = $dataTrans1['kd_buyer'];
            $po1->catatan        = $dataTrans1['catatan'];
            $po1->catatan_jurnal = $dataTrans1['catatan_jurnal'];
            $po1->enum_tipe_po   = $dataTrans1['enum_tipe_po'];
            $po1->fl_rounding    = $dataTrans1['fl_rounding'];
            $po1->fl_tutup       = $dataTrans1['fl_tutup'];
            $po1->fl_batal       = $dataTrans1['fl_batal'];
            $po1->fl_trds        = $dataTrans1['fl_trds'];
            //$po1->fl_approved    = $dataTrans1['fl_approved'];
            $po1->create_tgl     = $dataTrans1['create_tgl'];
            $po1->create_userid  = $dataTrans1['create_userid'];
            $po1->create_lokasi  = $dataTrans1['create_lokasi'];
            $po1->update_tgl     = $dataTrans1['update_tgl'];
            $po1->update_userid  = $dataTrans1['update_userid'];
            $po1->update_lokasi  = $dataTrans1['update_lokasi'];
            $po1->batal_tgl      = $dataTrans1['batal_tgl'];
            $po1->batal_userid   = $dataTrans1['batal_userid'];
            $po1->batal_lokasi   = $dataTrans1['batal_lokasi'];
            $po1->nm_partner     = $dataTrans1['nm_partner'];
            $po1->alamat_inv     = $dataTrans1['alamat_inv'];
            $po1->telp_inv       = $dataTrans1['telp_inv'];
            $po1->nm_kontak      = $dataTrans1['nm_kontak'];
            $po1->cetak          = $dataTrans1['cetak'];
            $po1->nm_kirim       = $dataTrans1['nm_kirim'];
            $po1->alamat_kirim   = $dataTrans1['alamat_kirim'];
            $po1->save();

            //Data PO2
            $existingIds = PO2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            PO2::whereIn('dtl2_key', $toDelete)->delete();

            //Data PO2
            //PO2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans2 as $recTrans2) {
                $validator=Validator::make($recTrans2,[
                    'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $po2 = PO2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($po2)) {
                    $po2 = new PO2();
                    $po2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $po2->doc_key        = $po1->doc_key;
                $po2->no_urut        = $recTrans2['no_urut'];
                $po2->kd_bahan       = $recTrans2['kd_bahan'];
                $po2->satuan         = $recTrans2['satuan'];
                $po2->qty            = $recTrans2['qty'];
                $po2->rp_harga       = $recTrans2['rp_harga'];
                $po2->persen_diskon  = $recTrans2['persen_diskon'];
                $po2->rp_diskon      = $recTrans2['rp_diskon'];
                $po2->persen_diskon2 = $recTrans2['persen_diskon2'];
                $po2->rp_diskon2     = $recTrans2['rp_diskon2'];
                $po2->persen_diskon3 = $recTrans2['persen_diskon3'];
                $po2->rp_diskon3     = $recTrans2['rp_diskon3'];
                $po2->persen_diskon4 = $recTrans2['persen_diskon4'];
                $po2->rp_diskon4     = $recTrans2['rp_diskon4'];
                $po2->kd_pajak       = $recTrans2['kd_pajak'];
                $po2->persen_pajak   = $recTrans2['persen_pajak'];
                $po2->rp_pajak       = $recTrans2['rp_pajak'];
                $po2->rp_harga_akhir = $recTrans2['rp_harga_akhir'];
                $po2->qty_sisa       = $recTrans2['qty_sisa'];
                $po2->catatan        = $recTrans2['catatan'];
                $po2->fl_tutup       = $recTrans2['fl_tutup'];
                $po2->base_type      = $recTrans2['base_type'];
                $po2->base_ref       = $recTrans2['base_ref'];
                $po2->base_no_doc    = $recTrans2['base_no_doc'];
                $po2->konversi       = $recTrans2['konversi'];
                $po2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $po2->save();
            }

            //Data PO3
            $existingIds = PO3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            PO3::whereIn('dtl3_key', $toDelete)->delete();

            //Data PO3
            //PO3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $po3 = PO3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($po3)) {
                    $po3 = new PO3();
                    $po3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $po3->doc_key        = $po1->doc_key;
                $po3->no_urut        = $recTrans3['no_urut'];
                $po3->no_account     = $recTrans3['no_account'];
                $po3->nm_account     = $recTrans3['nm_account'];
                $po3->catatan        = $recTrans3['catatan'];
                $po3->rp_bayar       = $recTrans3['rp_bayar'];
                $po3->rp_sisa        = $recTrans3['rp_sisa'];
                $po3->base_type      = $recTrans3['base_type'];
                $po3->base_ref       = $recTrans3['base_ref'];
                $po3->save();
            }

            PurchaseOrderController::setLinkData($po1->doc_key, TRUE);

            DB::commit();
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
