<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Inventory\Koreksi1;
use App\Models\Inventory\Koreksi2;
use App\Models\Inventory\Koreksi2Fifo;
use App\Models\Inventory\Opname1;
use App\Models\Inventory\Opname2;
use App\Models\Master\Lokasi;
use App\Models\Master\Reason;
use App\Models\Master\GrupBahan;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Account;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class KoreksiStokController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->leftJoin('m_lokasi as b','a.kd_lokasi','=','b.kd_lokasi')
        ->selectRaw("a.*, b.nm_lokasi,
            CASE
              WHEN a.enum_tran=1 THEN 'Write-Off'
              WHEN a.enum_tran=2 THEN 'Salah Item'
              WHEN a.enum_tran=3 THEN 'Salah Lokasi'
              WHEN a.enum_tran=4 THEN 'Stock Opname'
              WHEN a.enum_tran=5 THEN 'Pemakaian'
            END AS ket_enum_tran")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->leftJoin('m_lokasi as b','a.kd_lokasi','=','b.kd_lokasi')
        ->selectRaw("a.*, b.nm_lokasi,
            CASE
              WHEN a.enum_tran=1 THEN 'Write-Off'
              WHEN a.enum_tran=2 THEN 'Salah Item'
              WHEN a.enum_tran=3 THEN 'Salah Lokasi'
              WHEN a.enum_tran=4 THEN 'Stock Opname'
              WHEN a.enum_tran=5 THEN 'Pemakaian'
            END AS ket_enum_tran")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, COALESCE(a.fl_batal,'false') as fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_koreksi1']) ? $data['t_koreksi1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getApproved(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.fl_batal, COALESCE(a.fl_approved,'false') as fl_approved")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_koreksi1']) ? $data['t_koreksi1']->fl_approved : 'false';
        return response()->success('Success',$response);
    }

    public function getListOpname(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;

        $subQ1= DB::table('t_opname2 as a')
        ->leftJoin('t_koreksi2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('a.doc_key, MAX(b.doc_key) AS so_doc_key')
        ->groupBy('a.doc_key');

        $data['t_opname']= Koreksi1::from('t_opname1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->selectRaw('a.*, b.doc_key AS doc_key2, b.so_doc_key, COALESCE(a.fl_trds,false) AS fl_cek')
        ->where('a.kd_lokasi',$kd_lokasi)
        //->whereNull('b.so_doc_key') //Belum dibuat koreksi stok dari opname
        ->orderBy('a.tgl_doc','desc')
        ->get();

        return response()->success('Success',$data);
    }

    public function getListItemOpname(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        $data['t_opname']= Koreksi1::from('t_opname1 as a')
        ->join('t_opname2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_koreksi2 as c','b.dtl2_key','c.base_ref')
        ->leftJoin('t_koreksi1 as d','c.doc_key','d.doc_key')
        ->leftJoin('m_reason as e','b.reason_id','e.reason_id')
        ->selectRaw("a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.nm_bahan, b.satuan,
            b.qty_kini, b.qty_stok, b.qty_selisih, b.qty_kurang,
            b.rp_harga, b.rp_harga_baru, b.rp_total,
            b.catatan, b.fl_rubah_harga, b.fl_update_stok, b.reason_id,
            c.doc_key AS so_doc_key, d.no_doc, e.nm_reason,
            CASE
                WHEN c.doc_key IS NOT NULL THEN 'true'
                ELSE 'false'
            END AS fl_cek")
        ->whereIn('a.doc_key',$doc_key)
        ->where('b.qty_selisih','<>',0)
        ->orderBy('a.doc_key','desc')
        ->orderBy('b.no_urut')
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemOpname(Request $request) {
        $dtl2_key=isset($request->dtl2_key) ? $request->dtl2_key : [];

        $data['t_opname2']= Koreksi1::from('t_opname1 as a')
        ->join('t_opname2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_koreksi2 as c','b.dtl2_key','c.base_ref')
        ->leftJoin('t_koreksi1 as e','c.doc_key','e.doc_key')
        ->selectRaw("a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan,
            b.qty_kini, b.qty_stok, b.qty_selisih, b.qty_kurang,
            b.rp_harga, b.rp_harga_baru, b.rp_total,
            b.catatan, b.fl_rubah_harga, b.fl_update_stok,
            b.nm_bahan, b.reason_id")
        ->whereIn("b.dtl2_key",$dtl2_key)
        ->where("b.qty_selisih","<>",0)
        ->orderBy('a.doc_key')
        ->orderBy('b.no_urut')
        ->get();

        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //Koreksi1
        $data['t_koreksi1']= Koreksi1::from('t_koreksi1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();

        //Koreksi2
        $data['t_koreksi2']= Koreksi2::from('t_koreksi2 as a')
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

        //Master Lokasi
        $data['m_lokasi']= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_pusat, a.fl_lokasi, a.fl_aktif, a.fl_account, a.fl_stok, a.fl_hold,
            a.kd_server, a.kd_lokasi_acc, a.kd_lokasi || ' - ' || a.nm_lokasi AS ket_lokasi,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.fl_aktif","true")
        ->orderBy('a.kd_lokasi')
        ->get();

        //Master Reason
        $data['m_reason']= Reason::from('m_reason as a')
        ->selectRaw("a.*")
        ->orderBy('a.reason_id')
        ->get();

        //Master Grup Bahan
        $data['m_grup_bahan']= GrupBahan::from('m_grup_bahan as a')
        ->selectRaw("a.*")
        ->orderBy('a.kd_grup_bahan')
        ->get();

        //Master Bahan
        $data['m_bahan']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy('a.nm_bahan')
        ->get();

        //Master Bahan
        /*$data['m_bahan']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy('a.nm_bahan')
        ->get();*/

        //Master Bahan Satuan
        $data['m_bahan_satuan']= BahanSatuan::from('m_bahan_satuan as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.satuan AS satuan_dasar")
        ->get();

        //Master Bahan Beli Filter
        $data['m_bahan_beli_filter']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("fl_aktif","true")
        ->where("fl_beli","true")
        ->orderBy("a.kd_bahan")
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
        Koreksi2::where('doc_key',$doc_key)->delete();
        Koreksi1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $koreksi1= Koreksi1::where('doc_key',$doc_key)->first();
        if ($koreksi1) {
            if ($koreksi1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            if ($koreksi1->fl_approved == 'true') {
                //Update stok
                KoreksiStokController::updateStok($doc_key, FALSE);
            }
            //Update koreksi1
            $koreksi1->catatan = $catatan . "\n" . $koreksi1->catatan;
            $koreksi1->fl_batal = 'true';
            $koreksi1->batal_tgl = date('Y-m-d H:i:s');
            $koreksi1->batal_userid = $request->userid;
            $koreksi1->batal_lokasi = $request->lokasi;
            $koreksi1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function setApproved(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $koreksi1= Koreksi1::where('doc_key',$doc_key)->first();
        if ($koreksi1) {
            if ($koreksi1->fl_approved == 'true') {
                $response['message'] = 'Data sudah di-approve';
                return response()->success('Success',$response);
            }
            //Update koreksi1
            $koreksi1->fl_approved = 'true';
            $koreksi1->approved_tgl = date('Y-m-d H:i:s');
            $koreksi1->approved_userid = $request->userid;
            $koreksi1->approved_lokasi = $request->lokasi;
            $koreksi1->save();
            //Update stok
            $resp=KoreksiStokController::updateStok($doc_key,TRUE);
        }
        //$response['koreksi1'] = $koreksi1;
        //$response['resp'] = $resp;
        $response['message'] = 'Approve data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=26; //Koreksi Stok
        if ($insert == FALSE) {
            //Qty Plus
            $dataKoreksi= Koreksi1::from("t_koreksi1 as a")
            ->leftJoin("t_koreksi2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->leftJoin("m_bahan_satuan as d",function ($join) {
                $join->on("b.kd_bahan","=","d.kd_bahan")
                ->on("b.satuan","=","d.satuan");
            })
            ->selectRaw("a.doc_key, a.tgl_doc, a.kd_lokasi, b.dtl2_key, b.kd_bahan, b.satuan,
                b.qty_selisih, b.rp_harga, b.rp_total, b.stok_fifo_key, c.satuan AS satuan_dasar, d.rasio")
            ->where("a.doc_key",$doc_key)
            ->where("b.qty_selisih",">=",0)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataKoreksi as $recKoreksi) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recKoreksi->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand - ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                    $dataStokFifo->qty_in = $dataStokFifo->qty_in - ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                    $dataStokFifo->save();
                }
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
                $dataStokFifoDtl= StokFifoDtl::where("stok_fifo_key",$stokFifoKey)->first();
                if ($dataStokFifoDtl) {
                    $dataStokFifoDtl->qty = $dataStokFifoDtl->qty - ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                    $dataStokFifoDtl->save();
                }
            }

            //Qty Minus
            $dataKoreksiMinus= Koreksi1::from("t_koreksi1 as a")
            ->leftJoin("t_koreksi2_fifo as b","a.doc_key","=","b.doc_key")
            ->leftJoin("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->leftJoin("m_bahan_satuan as d",function ($join) {
                $join->on("b.kd_bahan","=","d.kd_bahan")
                ->on("b.satuan","=","d.satuan");
            })
            ->selectRaw("a.doc_key, a.tgl_doc, a.kd_lokasi, b.dtl2_key, b.dtl2_fifo_key, b.kd_bahan, b.satuan,
                b.qty, b.stok_fifo_key, c.satuan AS satuan_dasar, d.rasio")
            ->where("a.doc_key",$doc_key)
            ->where("b.qty","<",0)
            ->orderBy("b.dtl2_fifo_key")
            ->get();
            foreach($dataKoreksiMinus as $recKoreksiMinus) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recKoreksiMinus->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand - ($recKoreksiMinus->qty*$recKoreksiMinus->konversi);
                    $dataStokFifo->qty_in = $dataStokFifo->qty_in - ($recKoreksiMinus->qty*$recKoreksiMinus->konversi);
                    $dataStokFifo->save();
                }
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
                $dataStokFifoDtl= StokFifoDtl::where("stok_fifo_key",$stokFifoKey)->first();
                if ($dataStokFifoDtl) {
                    $dataStokFifoDtl->qty = $dataStokFifoDtl->qty - ($recKoreksiMinus->qty*$recKoreksiMinus->konversi);
                    $dataStokFifoDtl->save();
                }
            }
        } else if ($insert == TRUE) {
            //Qty Plus
            $dataKoreksi= Koreksi1::from("t_koreksi1 as a")
            ->leftJoin("t_koreksi2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->leftJoin("m_bahan_satuan as d",function ($join) {
                $join->on("b.kd_bahan","=","d.kd_bahan")
                ->on("b.satuan","=","d.satuan");
            })
            ->selectRaw("a.doc_key, a.tgl_doc, a.kd_lokasi, b.dtl2_key, b.kd_bahan, b.satuan,
                b.qty_selisih, b.rp_harga, b.rp_total, c.satuan AS satuan_dasar, d.rasio")
            ->where("a.doc_key",$doc_key)
            ->where("b.qty_selisih",">=",0)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataKoreksi as $recKoreksi) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recKoreksi->kd_lokasi)
                    ->where("kd_bahan",$recKoreksi->kd_bahan)
                    ->where("satuan",$recKoreksi->satuan_dasar)
                    ->where("base_type",$docTrans)
                    ->where("base_doc_key",$recKoreksi->doc_key)
                    ->where("base_dtl2_key",$recKoreksi->dtl2_key)->first();
                if (!$dataStokFifo) {
                    $dataStokFifo= new StokFifo();
                    $dataStokFifo->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                }
                $dataStokFifo->kd_lokasi = $recKoreksi->kd_lokasi;
                $dataStokFifo->kd_bahan = $recKoreksi->kd_bahan;
                $dataStokFifo->satuan = $recKoreksi->satuan_dasar;
                $dataStokFifo->tgl_doc = $recKoreksi->tgl_doc;
                $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand + ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                $dataStokFifo->qty_in = $dataStokFifo->qty_in + ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                $dataStokFifo->rp_harga = $recKoreksi->rp_harga;
                $dataStokFifo->base_type = $docTrans;
                $dataStokFifo->base_doc_key = $recKoreksi->doc_key;
                $dataStokFifo->base_dtl2_key = $recKoreksi->dtl2_key;
                $dataStokFifo->save();
                //Update stok_fifo_key di koreksi2
                $dataKoreksi2= Koreksi2::where("dtl2_key",$recKoreksi->dtl2_key)->first();
                if ($dataKoreksi2) {
                    $dataKoreksi2->stok_fifo_key = $dataStokFifo->stok_fifo_key;
                    $dataKoreksi2->save();
                }
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
                $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recKoreksi->kd_lokasi)
                    ->where("kd_bahan",$recKoreksi->kd_bahan)
                    ->where("satuan",$recKoreksi->satuan_dasar)
                    ->where("base_type",$docTrans)
                    ->where("base_doc_key",$recKoreksi->doc_key)
                    ->where("base_dtl2_key",$recKoreksi->dtl2_key)->first();
                if (!$dataStokFifoDtl) {
                    $dataStokFifoDtl= new StokFifoDtl();
                    $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                }
                $dataStokFifoDtl->stok_fifo_key = $stokFifoKey;
                $dataStokFifoDtl->kd_lokasi = $recKoreksi->kd_lokasi;
                $dataStokFifoDtl->kd_bahan = $recKoreksi->kd_bahan;
                $dataStokFifoDtl->satuan = $recKoreksi->satuan_dasar;
                $dataStokFifoDtl->tgl_doc = $recKoreksi->tgl_doc;
                $dataStokFifoDtl->no_doc = $recKoreksi->no_doc;
                $dataStokFifoDtl->qty = $dataStokFifoDtl->qty + ($recKoreksi->qty_selisih*$recKoreksi->konversi);
                $dataStokFifoDtl->base_type = $docTrans;
                $dataStokFifoDtl->base_doc_key = $recKoreksi->doc_key;
                $dataStokFifoDtl->base_dtl2_key = $recKoreksi->dtl2_key;
                $dataStokFifoDtl->save();
            }

            //Qty Minus
            $qty= 0;
            $qtyStok=0;
            $dataKoreksiMinus= Koreksi1::from("t_koreksi1 as a")
            ->leftJoin("t_koreksi2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->leftJoin("m_bahan_satuan as d",function ($join) {
                $join->on("b.kd_bahan","=","d.kd_bahan")
                ->on("b.satuan","=","d.satuan");
            })
            ->selectRaw("a.doc_key, a.tgl_doc, a.kd_lokasi, b.dtl2_key, b.kd_bahan, b.satuan,
                b.qty_selisih, b.rp_harga, b.rp_total, c.satuan AS satuan_dasar, d.rasio")
            ->where("a.doc_key",$doc_key)
            ->where("b.qty_selisih","<",0)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataKoreksiMinus as $recKoreksiMinus) {
                $qty= abs($recKoreksiMinus->qty_selisih);
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recKoreksiMinus->kd_lokasi)
                    ->where("kd_bahan",$recKoreksiMinus->kd_bahan)
                    ->where("satuan",$recKoreksiMinus->satuan_dasar)
                    ->where(DB::raw("COALESCE(qty_on_hand)"),">",DB::raw("COALESCE(qty_used)"))
                    ->orderBy("tgl_doc","asc")
                    ->orderBy("stok_fifo_key","asc")
                    ->get();
                foreach ($dataStokFifo as $recStokFifo) {
                    if ($qty>0) {
                        if ($recStokFifo->qty_on_hand - $recStokFifo->qty_used >= $qty) {
                            $qtyStok= $qty;
                            $qty= 0;
                        } else {
                            $qtyStok= $recStokFifo->qty_on_hand - $recStokFifo->qty_used;
                            $qty= $qty - $qtyStok;
                        }
                        //Koreksi2Fifo
                        $dataKoreksi2Fifo= Koreksi2Fifo::where("doc_key",$recKoreksiMinus->doc_key)
                            ->where("dtl2_key",$recKoreksiMinus->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataKoreksi2Fifo) {
                            $dataKoreksi2Fifo= new Koreksi2Fifo();
                            $dataKoreksi2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataKoreksi2Fifo->doc_key = $recKoreksiMinus->doc_key;
                        $dataKoreksi2Fifo->dtl2_key = $recKoreksiMinus->dtl2_key;
                        $dataKoreksi2Fifo->kd_bahan = $recKoreksiMinus->kd_bahan;
                        $dataKoreksi2Fifo->satuan = $recKoreksiMinus->satuan_dasar;
                        $dataKoreksi2Fifo->qty = $qtyStok;
                        $dataKoreksi2Fifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataKoreksi2Fifo->save();
                        //StokFIFO Detail
                        $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recKoreksiMinus->kd_lokasi)
                            ->where("kd_bahan",$recKoreksiMinus->kd_bahan)
                            ->where("satuan",$recKoreksiMinus->satuan_dasar)
                            ->where("base_type",$docTrans)
                            ->where("base_doc_key",$recKoreksiMinus->doc_key)
                            ->where("base_dtl2_key",$recKoreksiMinus->dtl2_key)->first();
                        if (!$dataStokFifoDtl) {
                            $dataStokFifoDtl= new StokFifoDtl();
                            $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        }
                        $dataStokFifoDtl->kd_bahan = $recKoreksiMinus->kd_bahan;
                        $dataStokFifoDtl->satuan = $recKoreksiMinus->satuan_dasar;
                        $dataStokFifoDtl->kd_lokasi = $recKoreksiMinus->kd_lokasi;
                        $dataStokFifoDtl->tgl_doc = $recKoreksiMinus->tgl_doc;
                        $dataStokFifoDtl->no_doc = $recKoreksiMinus->no_doc;
                        $dataStokFifoDtl->base_type = $docTrans;
                        $dataStokFifoDtl->base_doc_key = $recKoreksiMinus->doc_key;
                        $dataStokFifoDtl->base_dtl2_key = $recKoreksiMinus->dtl2_key;
                        $dataStokFifoDtl->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataStokFifoDtl->qty = -$qtyStok;
                        $dataStokFifoDtl->save();

                        //Update harga stock transfer2
                        /*$recKoreksiMinus->rp_harga = $recStokFifo->rp_harga;
                        $recKoreksiMinus->rp_total = $recKoreksiMinus->rp_harga * $recKoreksiMinus->qty;
                        $recKoreksiMinus->save();*/
                    } else {
                        break;
                    }
                }

                if ($qty>0) {
                    //Stok FIFO Header
                    $dataStokFifo= StokFifo::where("kd_lokasi",$recKoreksiMinus->kd_lokasi)
                        ->where("kd_bahan",$recKoreksiMinus->kd_bahan)
                        ->where("satuan",$recKoreksiMinus->satuan_dasar)
                        ->where("qty_on_hand",">",0)
                        ->where("rp_harga",">",0)
                        ->orderBy("tgl_doc","desc")
                        ->orderBy("stok_fifo_key","desc")
                        ->first();
                    if(!$dataStokFifo) {
                        $dataStokFifo= StokFifo::where("kd_lokasi",$recKoreksiMinus->kd_lokasi)
                            ->where("kd_bahan",$recKoreksiMinus->kd_bahan)
                            ->where("satuan",$recKoreksiMinus->satuan_dasar)
                            ->where("rp_harga",">",0)
                            ->orderBy("tgl_doc","desc")
                            ->orderBy("stok_fifo_key","desc")
                            ->first();
                    }
                    $qtyStok= $qty;
                    //New StokFIFO Header
                    $dataStokFifoNew= new StokFifo();
                    $dataStokFifoNew->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                    $dataStokFifoNew->kd_bahan = $recKoreksiMinus->kd_bahan;
                    $dataStokFifoNew->satuan = $recKoreksiMinus->satuan_dasar;
                    $dataStokFifoNew->kd_lokasi = $recKoreksiMinus->kd_lokasi;
                    $dataStokFifoNew->tgl_doc = $recKoreksiMinus->tgl_doc;
                    $dataStokFifoNew->qty_on_hand = 0;
                    $dataStokFifoNew->qty_in = 0;
                    $dataStokFifoNew->qty_used = $qtyStok;
                    $dataStokFifoNew->rp_harga = ($dataStokFifo) ? $dataStokFifo->rp_harga : 0;
                    $dataStokFifoNew->base_type = $docTrans;
                    $dataStokFifoNew->base_doc_key = $recKoreksiMinus->doc_key;
                    $dataStokFifoNew->base_dtl2_key = $recKoreksiMinus->dtl2_key;
                    $dataStokFifoNew->save();
                    //Koreksi2Fifo
                    $dataKoreksi2Fifo= Koreksi2Fifo::where("doc_key",$recKoreksiMinus->doc_key)
                        ->where("dtl2_key",$recKoreksiMinus->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataKoreksi2Fifo) {
                        $dataKoreksi2Fifo= new Koreksi2Fifo();
                        $dataKoreksi2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataKoreksi2Fifo->doc_key = $recKoreksiMinus->doc_key;
                    $dataKoreksi2Fifo->dtl2_key = $recKoreksiMinus->dtl2_key;
                    $dataKoreksi2Fifo->kd_bahan = $recKoreksiMinus->kd_bahan;
                    $dataKoreksi2Fifo->satuan = $recKoreksiMinus->satuan_dasar;
                    $dataKoreksi2Fifo->qty = $qtyStok;
                    $dataKoreksi2Fifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataKoreksi2Fifo->save();

                    //StokFIFO Detail
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recKoreksiMinus->kd_lokasi)
                        ->where("kd_bahan",$recKoreksiMinus->kd_bahan)
                        ->where("satuan",$recKoreksiMinus->satuan_dasar)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recKoreksiMinus->doc_key)
                        ->where("base_dtl2_key",$recKoreksiMinus->dtl2_key)->first();
                    if (!$dataStokFifoDtl) {
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    }
                    $dataStokFifoDtl->kd_bahan = $recKoreksiMinus->kd_bahan;
                    $dataStokFifoDtl->satuan = $recKoreksiMinus->satuan_dasar;
                    $dataStokFifoDtl->kd_lokasi = $recKoreksiMinus->kd_lokasi;
                    $dataStokFifoDtl->tgl_doc = $recKoreksiMinus->tgl_doc;
                    $dataStokFifoDtl->no_doc = $recKoreksiMinus->no_doc;
                    $dataStokFifoDtl->base_type = $docTrans;
                    $dataStokFifoDtl->base_doc_key = $recKoreksiMinus->doc_key;
                    $dataStokFifoDtl->base_dtl2_key = $recKoreksiMinus->dtl2_key;
                    $dataStokFifoDtl->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataStokFifoDtl->qty = -$qtyStok;
                    $dataStokFifoDtl->save();

                    //Update harga koreksi2fifo
                    /*$recKoreksiMinus->rp_harga = ($dataStokFifo) ? $dataStokFifo->rp_harga : 0;
                    $recKoreksiMinus->rp_total = $recKoreksiMinus->rp_harga * $recKoreksiMinus->qty;
                    $recKoreksiMinus->save();*/
                }
            }
        }
        //var_dump($recPO1->doc_key,$rp_sisa);
        //$response['koreksi'] = $dataKoreksi;
        //$response['minus'] = $dataKoreksiMinus;
        //$response['dsfifo'] = $dataStokFifo;
        //$response['dsfifonew'] = $dataStokFifoNew;
        //$response['qty'] = $qty;
        $response['message'] = 'Approve data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key= $where['doc_key'];
        $dataTrans1= $data['t_koreksi1'];
        $dataTrans2= $data['t_koreksi2'];

        DB::beginTransaction();
        try {
            $bInsert = FALSE;
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'kd_lokasi'=>'bail|required',
            ],[
                'kd_lokasi.required'=>'Kode Lokasi harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $koreksi1= Koreksi1::where('doc_key',$doc_key)->first();
            if (!($koreksi1)) {
                $bInsert = TRUE;
                $koreksi1= new Koreksi1();
                $koreksi1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $koreksi1->no_doc          = $dataTrans1['no_doc'];
            $koreksi1->tgl_doc         = $dataTrans1['tgl_doc'];
            $koreksi1->kd_lokasi       = $dataTrans1['kd_lokasi'];
            $koreksi1->catatan         = $dataTrans1['catatan'];
            $koreksi1->rp_total        = $dataTrans1['rp_total'];
            $koreksi1->kd_auditor      = $dataTrans1['kd_lokasi'];
            $koreksi1->enum_tran       = $dataTrans1['kd_lokasi'];
            $koreksi1->fl_opname       = $dataTrans1['fl_opname'];
            $koreksi1->fl_batal        = $dataTrans1['fl_batal'];
            $koreksi1->fl_trds         = $dataTrans1['fl_trds'];
            $koreksi1->fl_approved     = $dataTrans1['fl_approved'];
            $koreksi1->tgl_proses      = $dataTrans1['tgl_proses'];
            $koreksi1->doc_key_jurnal  = $dataTrans1['doc_key_jurnal'];
            $koreksi1->create_tgl      = $dataTrans1['create_tgl'];
            $koreksi1->create_userid   = $dataTrans1['create_userid'];
            $koreksi1->create_lokasi   = $dataTrans1['create_lokasi'];
            $koreksi1->update_tgl      = $dataTrans1['update_tgl'];
            $koreksi1->update_userid   = $dataTrans1['update_userid'];
            $koreksi1->update_lokasi   = $dataTrans1['update_lokasi'];
            $koreksi1->approved_tgl    = $dataTrans1['approved_tgl'];
            $koreksi1->approved_userid = $dataTrans1['approved_userid'];
            $koreksi1->approved_lokasi = $dataTrans1['approved_lokasi'];
            $koreksi1->save();

            //Data Koreksi2
            $existingIds = Koreksi2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Koreksi2::whereIn('dtl2_key', $toDelete)->delete();

            //Koreksi2::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                $koreksi2 = Koreksi2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($koreksi2)) {
                    $koreksi2 = new Koreksi2();
                    $koreksi2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $koreksi2->doc_key        = $koreksi1->doc_key;
                $koreksi2->no_urut        = $recTrans2['no_urut'];
                $koreksi2->kd_bahan       = $recTrans2['kd_bahan'];
                $koreksi2->satuan         = $recTrans2['satuan'];
                $koreksi2->qty_kini       = $recTrans2['qty_kini'];
                $koreksi2->qty_stok       = $recTrans2['qty_stok'];
                $koreksi2->qty_selisih    = $recTrans2['qty_selisih'];
                $koreksi2->qty_kurang     = $recTrans2['qty_kurang'];
                $koreksi2->rp_harga       = $recTrans2['rp_harga'];
                $koreksi2->rp_harga_baru  = $recTrans2['rp_harga_baru'];
                $koreksi2->rp_total       = $recTrans2['rp_total'];
                $koreksi2->catatan        = $recTrans2['catatan'];
                $koreksi2->fl_rubah_harga = $recTrans2['fl_rubah_harga'];
                $koreksi2->fl_update_stok = $recTrans2['fl_update_stok'];
                $koreksi2->create_tgl     = $recTrans2['create_tgl'];
                $koreksi2->create_userid  = $recTrans2['create_userid'];
                $koreksi2->create_lokasi  = $recTrans2['create_lokasi'];
                $koreksi2->update_tgl     = $recTrans2['update_tgl'];
                $koreksi2->update_userid  = $recTrans2['update_userid'];
                $koreksi2->update_lokasi  = $recTrans2['update_lokasi'];
                $koreksi2->no_account     = $recTrans2['no_account'];
                $koreksi2->nm_bahan       = $recTrans2['nm_bahan'];
                $koreksi2->reason_id      = $recTrans2['reason_id'];
                $koreksi2->base_type      = $recTrans2['base_type'];
                $koreksi2->base_ref       = $recTrans2['base_ref'];
                $koreksi2->base_no_doc    = $recTrans2['base_no_doc'];
                //$koreksi2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $koreksi2->save();
            }

            //KoreksiStokController::updateLinkData($koreksi1->doc_key);

            DB::commit();
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
