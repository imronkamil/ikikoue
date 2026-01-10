<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Inventory\StockTransfer1;
use App\Models\Inventory\StockTransfer2;
use App\Models\Inventory\StockTransferSend1;
use App\Models\Inventory\StockTransferSend2;
use App\Models\Inventory\StockTransferSend2Fifo;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class StockTransferSendController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table("t_stock_transfer1 as a")
        ->leftJoin("t_stock_transfer2 as b","a.doc_key","=","b.doc_key")
        ->leftJoin("t_stock_transfer_send2 as c","b.dtl2_key","=","c.base_ref")
        ->selectRaw("c.doc_key, string_agg(DISTINCT a.no_doc,', ') AS no_doc_request")
        ->where(DB::raw("COALESCE(a.fl_batal,false)"),false)
        ->groupBy("c.doc_key");

        $subQ2= DB::table("t_stock_transfer_receive1 as a")
        ->leftJoin("t_stock_transfer_receive2 as b","a.doc_key","=","b.doc_key")
        ->leftJoin("t_stock_transfer_send2 as c","c.dtl2_key","=","b.base_ref")
        ->selectRaw("c.doc_key, string_agg(DISTINCT a.no_doc,', ') AS no_doc_receive")
        ->where(DB::raw("COALESCE(a.fl_batal,false)"),false)
        ->groupBy("c.doc_key");

        $data["t_stock_transfer_send1"]= StockTransferSend1::from("t_stock_transfer_send1 as a")
        ->leftJoinSub($subQ1,"b", function ($join) {
            $join->on("a.doc_key","=","b.doc_key");
        })
        ->leftJoinSub($subQ2,"c", function ($join) {
            $join->on("a.doc_key","=","c.doc_key");
        })
        ->join("m_lokasi as d","a.kd_lokasi_dari","=","d.kd_lokasi")
        ->join("m_lokasi as e","a.kd_lokasi_ke","=","e.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.no_doc_request, c.no_doc_receive,
            d.nm_lokasi AS nm_lokasi_dari, e.nm_lokasi AS nm_lokasi_ke")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy("tgl_doc",'desc')
        ->orderBy('no_doc','desc')
        ->get();

        return response()->success('Success',$data);
        //return response()->success('Success',$query1);
    }

    public function show2() {
        $data['t_stock_transfer1']= StockTransfer1::from('t_stock_transfer1 as a')
        ->join("m_lokasi as c","a.kd_lokasi_dari","=","c.kd_lokasi")
        ->join("m_lokasi as d","a.kd_lokasi_ke","=","d.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            c.nm_lokasi AS nm_lokasi_dari, d.nm_lokasi AS nm_lokasi_ke")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_stock_transfer1']= StockTransferSend1::from('t_stock_transfer1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_stock_transfer_send1']= StockTransferSend1::from('t_stock_transfer_send1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_stock_transfer_send1']) ? $data['t_stock_transfer_send1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_stock_transfer1']= StockTransfer1::from('t_stock_transfer1 as a')
        ->join('t_stock_transfer2 as b','a.doc_key','=','b.doc_key')
        ->join('t_stock_transfer_send2 as c','b.dtl2_key','=','c.base_ref')
        ->join('t_stock_transfer_send1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_stock_transfer1'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
    }

    public function getListRequest(Request $request) {
        $kd_lokasi1=isset($request->kd_lokasi1) ? $request->kd_lokasi1 : '0';
        $kd_lokasi2=isset($request->kd_lokasi2) ? $request->kd_lokasi2 : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_stock_transfer2 as a')
        ->leftJoin('t_stock_transfer_send2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('a.doc_key, MAX(b.doc_key) AS doc_key_request')
        ->groupBy('a.doc_key');

        $data['t_stock_transfer']= StockTransfer1::from('t_stock_transfer1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->leftJoin('m_lokasi as c','a.kd_lokasi_dari','=','c.kd_lokasi')
        ->leftJoin('m_lokasi as d','a.kd_lokasi_ke','=','d.kd_lokasi')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.rp_total, a.catatan, c.nm_lokasi AS nm_lokasi_dari, d.nm_lokasi AS nm_lokasi_ke,
            COALESCE(a.fl_batal,false) AS fl_cek")
        ->where('a.kd_lokasi_dari',$kd_lokasi1)
        ->where('a.kd_lokasi_ke',$kd_lokasi2)
        ->where(DB::raw('COALESCE(a.fl_batal,false)'),false)
        ->where(function ($query1) use ($doc_key) {
            $query1->where(DB::raw('COALESCE(a.fl_tutup,false)'),false)
            ->orwhere(function ($query2) use ($doc_key) {
                $query2->where(DB::raw('COALESCE(a.fl_tutup,false)'),true)
                    ->where("b.doc_key_request",$doc_key);
            });
        })
        ->orderBy("a.tgl_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemRequest(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        //StockTransfer1
        $data['t_stock_transfer1']= StockTransfer1::from('t_stock_transfer1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke, a.catatan,
            a.rp_total, a.fl_tutup, a.fl_batal, a.fl_trds, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //StockTransfer2
        $data['t_stock_transfer2']= StockTransfer1::from('t_stock_transfer1 as a')
        ->join('t_stock_transfer2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_stock_transfer_send2 as c','b.dtl2_key','c.base_ref')
        ->selectRaw("a.kd_lokasi_dari, a.kd_lokasi_ke, a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.rp_total,
            b.catatan, b.no_account, b.stok_fifo_key, b.qty_sisa, b.fl_tutup, b.konversi, b.satuan_dasar,
            c.dtl2_key AS dtl2_key_send, c.doc_key AS doc_key_send,
            c.rp_harga AS rp_harga_send, c.rp_total AS rp_total_send,
            COALESCE(c.qty,0) AS qty_send, COALESCE(c.qty_sisa,0) AS qty_sisa_send,
            c.qty-COALESCE(c.qty_sisa,0) AS qty_max_send,
            COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0) AS qty_sisa_st,
            c.fl_tutup AS fl_tutup_send")
        ->where(DB::raw('COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0)'),'>',0)
        ->whereIn('a.doc_key',$doc_key)
        ->get();

        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //StockTransferSend1
        $data['t_stock_transfer_send1']= StockTransferSend1::from('t_stock_transfer_send1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke, a.catatan, a.enum_tran,
            a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //StockTransferSend2
        $data['t_stock_transfer_send2']= StockTransferSend2::from('t_stock_transfer_send2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty_req, a.qty, a.qty_sisa,
            a.rp_harga, a.rp_total, a.catatan, a.no_account, a.stok_fifo_key, a.base_type, a.base_ref, a.base_no_doc,
            a.qty_sisa, a.fl_tutup, a.konversi, a.satuan_dasar, a.qty-COALESCE(a.qty_sisa,0) AS qty_max")
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
        ->orderBy("a.kd_lokasi","asc")
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
        ->orderBy("a.kd_bahan")
        ->get();

        //Master Bahan Satuan
        $data['m_bahan_satuan']= BahanSatuan::from('m_bahan_satuan as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.satuan AS satuan_dasar")
        ->orderBy("a.kd_bahan","asc")
        ->orderBy("a.rasio","asc")
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

        return response()->success('Success',$data);
    }

    public function getStockStatus(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_stock_transfer_send1'];
        $dataTrans2= $data['t_stock_transfer_send2'];

        foreach ($dataTrans2 as $recTrans2) {
            $stockFifo= StokFifo::from('m_stok_fifo as a')
                ->selectRaw('a.kd_lokasi, a.kd_bahan, a.satuan, SUM(COALESCE(a.qty_sisa,0)) AS qty_sisa')
                ->where('kd_lokasi',$dataTrans1['kd_lokasi_dari'])
                ->where('kd_bahan',$recTrans2['kd_bahan'])
                ->where('satuan',$recTrans2['satuan'])
                ->where('tgl_doc','<=',$dataTrans1['tgl_doc'])
                ->groupBy('a.kd_lokasi','a.kd_bahan','a.satuan')
                ->first();
        }

        $subQ1= DB::table('t_stock_transfer_send1 as a')
        ->join('t_stock_transfer_send2 as b','a.doc_key','=','b.doc_key')
        ->join('m_bahan_satuan as c',function ($join) {
            $join->on('b.kd_bahan','=','c.kd_bahan')
                 ->on('b.satuan','=','c.satuan');
        })
        ->join('m_bahan as d','b.kd_bahan','=','d.kd_bahan')
        ->selectRaw('a.kd_lokasi_dari as kd_lokasi, b.kd_bahan, b.satuan, SUM(COALESCE(b.qty,0)*COALESCE(c.rasio,0)) AS qty')
        ->where('a.kd_lokasi_dari',$dataTrans1['kd_lokasi_dari'])
        ->groupBy('a.kd_lokasi_dari','b.kd_bahan', 'b.satuan');


        $data['stock_status']= StockTransferSend2::from('t_stock_transfer_send2 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.kd_bahan','=','b.kd_bahan')
                 ->on('a.satuan','=','b.satuan');
        })
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        StockTransfer3::where('doc_key',$doc_key)->delete();
        StockTransfer2::where('doc_key',$doc_key)->delete();
        StockTransfer1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $stockTransferSend1= StockTransferSend1::where('doc_key',$doc_key)->first();
        if ($stockTransferSend1) {
            if ($stockTransferSend1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //StockTransferController::updateLinkData($doc_key,FALSE);
            //Update StockTransfer1
            $stockTransferSend1->catatan = $catatan . "\n" . $stockTransferSend1->catatan;
            $stockTransferSend1->fl_batal = 'true';
            $stockTransferSend1->batal_tgl = date('Y-m-d H:i:s');
            $stockTransferSend1->batal_userid = $request->userid;
            $stockTransferSend1->batal_lokasi = $request->lokasi;
            $stockTransferSend1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //StockTransfer1
            $dataRequest1= StockTransfer2::from("t_stock_transfer2 as a")
            ->join("t_stock_transfer_send2 as b","a.dtl2_key","=","b.base_ref")
            ->selectRaw("a.doc_key")
            ->where("b.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataRequest1 as $recRequest1) {
                $dataRequest2= StockTransfer2::from("t_stock_transfer2 as a")
                ->join("t_stock_transfer_send2 as b","a.dtl2_key","=","b.base_ref")
                ->selectRaw("a.doc_key, a.dtl2_key, a.qty_sisa, b.qty")
                ->where("a.doc_key",$recRequest1->doc_key)
                ->get();
                foreach($dataRequest2 as $recRequest2) {
                    //Update StockTransfer2
                    $stockTransfer2 = StockTransfer2::where('dtl2_key',$recRequest2->dtl2_key)->first();
                    if ($stockTransfer2) {
                        $stockTransfer2->qty_sisa = $stockTransfer2->qty_sisa + $recRequest2->qty;
                        if ($stockTransfer2->qty_sisa == $stockTransfer2->qty) {
                            $stockTransfer2->fl_tutup = FALSE;
                        } else {
                            $stockTransfer2->fl_tutup = TRUE;
                        }
                        $stockTransfer2->save();
                    }
                }
                //Update StockTransfer1
                $updRequest1= StockTransfer1::where("doc_key",$recRequest1->doc_key)->first();
                if ($updRequest1) {
                    //Cek Sisa Qty
                    $dataCek= StockTransfer2::from("t_stock_transfer2 as a")
                    ->selectRaw("SUM(COALESCE(a.qty_sisa,0)) AS qty_sisa_total")
                    ->where("a.doc_key",$recRequest1->doc_key)
                    ->groupBy("a.doc_key")
                    ->first();
                    $qty_sisa = ($dataCek) ? $dataCek->qty_sisa_total : 0;
                    if ($qty_sisa == 0) {
                        $updRequest1->fl_tutup= TRUE;
                    } else {
                        $updRequest1->fl_tutup= FALSE;
                    };
                    $updRequest1->save();
                }
            }
        } elseif ($insert == TRUE) {
            //StockTransfer1
            $dataRequest1= StockTransfer2::from("t_stock_transfer2 as a")
            ->join("t_stock_transfer_send2 as b","a.dtl2_key","=","b.base_ref")
            ->selectRaw("a.doc_key")
            ->where("b.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataRequest1 as $recRequest1) {
                $dataRequest2= StockTransfer2::from("t_stock_transfer2 as a")
                ->join("t_stock_transfer_send2 as b","a.dtl2_key","=","b.base_ref")
                ->selectRaw("a.doc_key, a.dtl2_key, a.qty_sisa, COALESCE(b.qty,0) as qty")
                ->where("a.doc_key",$recRequest1->doc_key)
                ->get();
                foreach($dataRequest2 as $recRequest2) {
                    //Update StockTransfer2
                    $stockTransfer2 = StockTransfer2::where('dtl2_key',$recRequest2->dtl2_key)->first();
                    if ($stockTransfer2) {
                        $stockTransfer2->qty_sisa = $stockTransfer2->qty_sisa - $recRequest2->qty;
                        $stockTransfer2->fl_tutup = TRUE;
                        $stockTransfer2->save();
                    }
                }
                //Update StockTransfer1
                $updRequest1= StockTransfer1::where("doc_key",$recRequest1->doc_key)->first();
                if ($updRequest1) {
                    //Cek Sisa Qty
                    $dataCek= StockTransfer2::from("t_stock_transfer2 as a")
                    ->selectRaw("SUM(COALESCE(a.qty_sisa,0)) AS qty_sisa_total")
                    ->where("a.doc_key",$recRequest1->doc_key)
                    ->groupBy("a.doc_key")
                    ->first();
                    $qty_sisa = ($dataCek) ? $dataCek->qty_sisa_total : 0;
                    if ($qty_sisa == 0) {
                        $updRequest1->fl_tutup= TRUE;
                    } else {
                        $updRequest1->fl_tutup= FALSE;
                    };
                    $updRequest1->save();
                }
            }
            //$response['value'] = $dataRequest1;
        }
        //var_dump($recRequest1->doc_key,$rp_sisa);
        //$response['value1'] = $doc_key;
        //$response['value2'] = $insert;
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=49; //Transfer Send
        if ($insert == FALSE) {
            $dataTrans= StockTransferSend1::from("t_stock_transfer_send1 as a")
            ->join("t_stock_transfer_send2_fifo as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari AS kd_lokasi, a.kd_lokasi_ke,
                b.dtl2_fifo_key, b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.dtl2_fifo_key")
            ->get();
            foreach($dataTrans as $recTrans) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_used = $dataStokFifo->qty_used - ($recTrans->qty);
                    $dataStokFifo->save();
                    //FIFO Detail
                    $stokFifoKey = $dataStokFifo->stok_fifo_key;
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan_dasar)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                    if ($dataStokFifoDtl) {
                        $dataStokFifoDtl->qty = $dataStokFifoDtl->qty + $recTrans->qty;
                        $dataStokFifoDtl->save();
                    }
                }
            }
        } elseif ($insert == TRUE) {
            $qty= 0;
            $qtyStok=0;
            //Kosongkan StockTransferSend2Fifo
            StockTransferSend2Fifo::where("doc_key",$doc_key)->delete();
            //Append StockTransferSend2Fifo
            $dataTrans= StockTransferSend1::from("t_stock_transfer_send1 as a")
            ->join("t_stock_transfer_send2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari AS kd_lokasi, a.kd_lokasi_ke,
                b.dtl2_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.rp_total,
                b.konversi, b.satuan_dasar, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $qty= $recTrans->qty * $recTrans->konversi;
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan_dasar)
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
                        //StockTransferSend2Fifo
                        $dataStockTransferSend2Fifo= StockTransferSend2Fifo::where("doc_key",$recTrans->doc_key)
                            ->where("dtl2_key",$recTrans->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataStockTransferSend2Fifo) {
                            $dataStockTransferSend2Fifo= new StockTransferSend2Fifo();
                            $dataStockTransferSend2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataStockTransferSend2Fifo->doc_key = $recTrans->doc_key;
                        $dataStockTransferSend2Fifo->dtl2_key = $recTrans->dtl2_key;
                        $dataStockTransferSend2Fifo->kd_bahan = $recTrans->kd_bahan;
                        $dataStockTransferSend2Fifo->satuan = $recTrans->satuan_dasar;
                        $dataStockTransferSend2Fifo->qty = $qtyStok;
                        $dataStockTransferSend2Fifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataStockTransferSend2Fifo->save();

                        //StokFIFO Detail
                        $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan_dasar)
                            ->where("base_type",$docTrans)
                            ->where("base_doc_key",$recTrans->doc_key)
                            ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                        if (!$dataStokFifoDtl) {
                            $dataStokFifoDtl= new StokFifoDtl();
                            $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        }
                        $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                        $dataStokFifoDtl->satuan = $recTrans->satuan_dasar;
                        $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                        $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                        $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                        $dataStokFifoDtl->base_type = $docTrans;
                        $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                        $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                        $dataStokFifoDtl->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataStokFifoDtl->qty = -$qtyStok;
                        $dataStokFifoDtl->save();

                        //Update harga stock transfer2
                        $ts2= StockTransferSend2::where("dtl2_key",$recTrans->dtl2_key)->first();
                        if ($ts2) {
                            $ts2->rp_harga = $recStokFifo->rp_harga;
                            $ts2->rp_total = $ts2->rp_harga * $ts2->qty;
                            $ts2->save();
                        }
                    } else {
                        break;
                    }
                }

                if ($qty>0) {
                    //Stok FIFO Header
                    $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan_dasar)
                        ->where("qty_on_hand",">",0)
                        ->where("rp_harga",">",0)
                        ->orderBy("tgl_doc","desc")
                        ->orderBy("stok_fifo_key","desc")
                        ->first();
                    if(!$dataStokFifo) {
                        $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan_dasar)
                            ->where("rp_harga",">",0)
                            ->orderBy("tgl_doc","desc")
                            ->orderBy("stok_fifo_key","desc")
                            ->first();
                    }
                    $qtyStok= $qty;
                    //New StokFIFO Header
                    $dataStokFifoNew= new StokFifo();
                    $dataStokFifoNew->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                    $dataStokFifoNew->kd_bahan = $recTrans->kd_bahan;
                    $dataStokFifoNew->satuan = $recTrans->satuan_dasar;
                    $dataStokFifoNew->kd_lokasi = $recTrans->kd_lokasi;
                    $dataStokFifoNew->tgl_doc = $recTrans->tgl_doc;
                    $dataStokFifoNew->qty_on_hand = 0;
                    $dataStokFifoNew->qty_in = 0;
                    $dataStokFifoNew->qty_used = $qtyStok;
                    $dataStokFifoNew->rp_harga = ($dataStokFifo) ? $dataStokFifo->rp_harga : 0;
                    $dataStokFifoNew->base_type = $docTrans;
                    $dataStokFifoNew->base_doc_key = $recTrans->doc_key;
                    $dataStokFifoNew->base_dtl2_key = $recTrans->dtl2_key;
                    $dataStokFifoNew->save();
                    //StockTransferSend2Fifo
                    $dataStockTransferSend2Fifo= StockTransferSend2Fifo::where("doc_key",$recTrans->doc_key)
                        ->where("dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataStockTransferSend2Fifo) {
                        $dataStockTransferSend2Fifo= new StockTransferSend2Fifo();
                        $dataStockTransferSend2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataStockTransferSend2Fifo->doc_key = $recTrans->doc_key;
                    $dataStockTransferSend2Fifo->dtl2_key = $recTrans->dtl2_key;
                    $dataStockTransferSend2Fifo->kd_bahan = $recTrans->kd_bahan;
                    $dataStockTransferSend2Fifo->satuan = $recTrans->satuan_dasar;
                    $dataStockTransferSend2Fifo->qty = $qtyStok;
                    $dataStockTransferSend2Fifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataStockTransferSend2Fifo->save();

                    //StokFIFO Detail
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan_dasar)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                    if (!$dataStokFifoDtl) {
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    }
                    $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                    $dataStokFifoDtl->satuan = $recTrans->satuan_dasar;
                    $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                    $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                    $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                    $dataStokFifoDtl->base_type = $docTrans;
                    $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                    $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                    $dataStokFifoDtl->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataStokFifoDtl->qty = -$qtyStok;
                    $dataStokFifoDtl->save();

                    //Update harga stock transfer2
                    $ts2= StockTransferSend2::where("dtl2_key",$recTrans->dtl2_key)->first();
                    if ($ts2) {
                        $ts2->rp_harga = ($dataStokFifo) ? $dataStokFifo->rp_harga : 0;
                        $ts2->rp_total = $recTrans->rp_harga * $recTrans->qty;
                        $ts2->save();
                    }
                }
            }
        }
        //var_dump($recPO1->doc_key,$rp_sisa);
        //$response['message'] = 'Update stok berhasil';
        //return response()->success('Success',$response);
        //return "Update Stok Berhasil";
        //return $dataTrans;
        //return $dataStokFifo;
        //return $dataStokFifoDtl;
        //return $dataStockTransferSend2Fifo;
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_stock_transfer_send1'];
        $dataTrans2= $data['t_stock_transfer_send2'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'no_doc'=>'bail|required',
            ],[
                'no_doc.required'=>'Nomor harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $stockTransferSend1= StockTransferSend1::where('doc_key',$where['doc_key'])->first();

            //Jika ada perubahan data, maka rollback stok terlebih dahulu
            if ($stockTransferSend1) {
                StockTransferSendController::updateLinkData($doc_key, FALSE);
                if (UtilityController::getAutoStok() == 'true') {
                    StockTransferSendController::updateStok($doc_key, FALSE);
                }
            } else {
                $stockTransferSend1= new StockTransferSend1();
                $stockTransferSend1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $stockTransferSend1->no_doc         = $dataTrans1['no_doc'];
            $stockTransferSend1->tgl_doc        = $dataTrans1['tgl_doc'];
            $stockTransferSend1->kd_lokasi_dari = $dataTrans1['kd_lokasi_dari'];
            $stockTransferSend1->kd_lokasi_ke   = $dataTrans1['kd_lokasi_ke'];
            $stockTransferSend1->catatan        = $dataTrans1['catatan'];
            $stockTransferSend1->rp_total       = $dataTrans1['rp_total'];
            $stockTransferSend1->enum_tran      = $dataTrans1['enum_tran'];
            $stockTransferSend1->fl_tutup       = $dataTrans1['fl_tutup'];
            $stockTransferSend1->fl_batal       = $dataTrans1['fl_batal'];
            $stockTransferSend1->fl_trds        = $dataTrans1['fl_trds'];
            $stockTransferSend1->tgl_proses     = $dataTrans1['tgl_proses'];
            $stockTransferSend1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $stockTransferSend1->create_tgl     = $dataTrans1['create_tgl'];
            $stockTransferSend1->create_userid  = $dataTrans1['create_userid'];
            $stockTransferSend1->create_lokasi  = $dataTrans1['create_lokasi'];
            $stockTransferSend1->update_tgl     = $dataTrans1['update_tgl'];
            $stockTransferSend1->update_userid  = $dataTrans1['update_userid'];
            $stockTransferSend1->update_lokasi  = $dataTrans1['update_lokasi'];
            $stockTransferSend1->no_account     = $dataTrans1['no_account'];
            $stockTransferSend1->save();

            //Data StockTransfer2
            $existingIds = StockTransferSend2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            StockTransferSend2::whereIn('dtl2_key', $toDelete)->delete();

            //Data StockTransfer2
            //StockTransferSend2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $stockTransferSend2 = StockTransferSend2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($stockTransferSend2)) {
                    $stockTransferSend2 = new StockTransferSend2();
                    $stockTransferSend2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $stockTransferSend2->doc_key        = $stockTransferSend1->doc_key;
                $stockTransferSend2->no_urut        = $recTrans2['no_urut'];
                $stockTransferSend2->kd_bahan       = $recTrans2['kd_bahan'];
                $stockTransferSend2->satuan         = $recTrans2['satuan'];
                $stockTransferSend2->qty_req        = $recTrans2['qty_req'];
                $stockTransferSend2->qty            = $recTrans2['qty'];
                $stockTransferSend2->qty_sisa       = $recTrans2['qty_sisa'];
                $stockTransferSend2->rp_harga       = $recTrans2['rp_harga'];
                $stockTransferSend2->rp_total       = $recTrans2['rp_total'];
                $stockTransferSend2->catatan        = $recTrans2['catatan'];
                $stockTransferSend2->no_account     = $recTrans2['no_account'];
                $stockTransferSend2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $stockTransferSend2->base_type      = $recTrans2['base_type'];
                $stockTransferSend2->base_ref       = $recTrans2['base_ref'];
                $stockTransferSend2->base_no_doc    = $recTrans2['base_no_doc'];
                $stockTransferSend2->fl_tutup       = $recTrans2['fl_tutup'];
                $stockTransferSend2->konversi       = $recTrans2['konversi'];
                $stockTransferSend2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $stockTransferSend2->save();
            }

            $resp2= StockTransferSendController::updateLinkData($stockTransferSend1->doc_key, TRUE);
            if (UtilityController::getAutoStok() == 'true') {
                $respstok= StockTransferSendController::updateStok($stockTransferSend1->doc_key, TRUE);
            }

            DB::commit();
            //$response['resp'] = $resp2;
            //$response['respstok'] = $respstok;
            //$response['doc_key'] = $stockTransferSend1->doc_key;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
