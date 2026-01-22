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
use App\Models\Inventory\StockTransferReceive1;
use App\Models\Inventory\StockTransferReceive2;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class StockTransferReceiveController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table("t_stock_transfer_send1 as a")
        ->leftJoin("t_stock_transfer_send2 as b","a.doc_key","=","b.doc_key")
        ->leftJoin("t_stock_transfer_receive2 as c","b.dtl2_key","=","c.base_ref")
        ->selectRaw("c.doc_key, string_agg(DISTINCT a.no_doc,', ') AS no_doc_send")
        ->where(DB::raw("COALESCE(a.fl_batal,false)"),false)
        ->groupBy("c.doc_key");

        $data["t_stock_transfer_receive1"]= StockTransferReceive1::from("t_stock_transfer_receive1 as a")
        ->leftJoinSub($subQ1,"b", function ($join) {
            $join->on("a.doc_key","=","b.doc_key");
        })
        ->join("m_lokasi as d","a.kd_lokasi_dari","=","d.kd_lokasi")
        ->join("m_lokasi as e","a.kd_lokasi_ke","=","e.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.no_doc_send, d.nm_lokasi AS nm_lokasi_dari, e.nm_lokasi AS nm_lokasi_ke")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy("tgl_doc",'desc')
        ->orderBy('no_doc','desc')
        ->get();

        return response()->success('Success',$data);
        //return response()->success('Success',$query1);
    }

    public function show2() {
        $data['t_stock_transfer_receive1']= StockTransferReceive1::from('t_stock_transfer_receive1 as a')
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
        $data['t_stock_transfer_receive1']= StockTransferReceive1::from('t_stock_transfer_receive1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_stock_transfer_receive1']= StockTransferReceive1::from('t_stock_transfer_receive1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, COALESCE(a.fl_batal,false) AS fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_stock_transfer_receive1']) ? $data['t_stock_transfer_receive1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getListTransfer(Request $request) {
        $kd_lokasi1=isset($request->kd_lokasi1) ? $request->kd_lokasi1 : '0';
        $kd_lokasi2=isset($request->kd_lokasi2) ? $request->kd_lokasi2 : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_stock_transfer_send2 as a')
        ->leftJoin('t_stock_transfer_receive2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('a.doc_key, MAX(b.doc_key) AS doc_key_request')
        ->groupBy('a.doc_key');

        $data['t_stock_transfer_send']= StockTransferSend1::from('t_stock_transfer_send1 as a')
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
        ->orderBy("a.no_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemTransfer(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        //StockTransferSend1
        $data['t_stock_transfer_send1']= StockTransferSend1::from('t_stock_transfer_send1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke, a.catatan,
            a.rp_total, a.fl_tutup, a.fl_batal, a.fl_trds, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //StockTransferSend2
        $data['t_stock_transfer_send2']= StockTransferSend1::from('t_stock_transfer_send1 as a')
        ->join('t_stock_transfer_send2 as b','a.doc_key','=','b.doc_key')
        //->leftJoin('t_stock_transfer_receive2 as c','b.dtl2_key','c.base_ref')
        ->selectRaw("a.kd_lokasi_dari, a.kd_lokasi_ke, a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan, b.qty_req, b.qty, b.rp_harga, b.rp_total,
            b.catatan, b.no_account, b.stok_fifo_key, b.qty_sisa, b.fl_tutup, b.konversi, b.satuan_dasar")
            /*c.dtl2_key AS dtl2_key_send, c.doc_key AS doc_key_send,
            c.rp_harga AS rp_harga_send, c.rp_total AS rp_total_send,
            COALESCE(c.qty,0) AS qty_send, COALESCE(b.qty_sisa,0) AS qty_sisa_send,
            COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0) AS qty_sisa_st,
            b.fl_tutup AS fl_tutup_send")*/
        //->where(DB::raw('COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0)'),'>',0)
        ->where(DB::raw('COALESCE(b.qty_sisa,0)'),'>',0)
        ->whereIn('a.doc_key',$doc_key)
        ->orderBy("b.no_urut")
        ->get();

        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //StockTransferReceive1
        $data['t_stock_transfer_receive1']= StockTransferReceive1::from('t_stock_transfer_receive1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //StockTransferReceive2
        $data['t_stock_transfer_receive2']= StockTransferReceive2::from('t_stock_transfer_receive2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty_req, a.qty, a.qty_send,
            a.rp_harga, a.rp_total, a.catatan, a.no_account, a.stok_fifo_key, a.base_type, a.base_ref, a.base_no_doc,
            a.konversi, a.satuan_dasar")
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
        ->where("a.fl_aktif","true")
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

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        StockTransferReceive1::where('doc_key',$doc_key)->delete();
        StockTransferReceive2::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $stockTransferReceive1= StockTransferReceive1::where('doc_key',$doc_key)->first();
        if ($stockTransferReceive1) {
            if ($stockTransferReceive1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //StockTransferController::updateLinkData($doc_key,FALSE);
            //Update StockTransferReceive1
            $stockTransferReceive1->catatan = $catatan . "\n" . $stockTransferReceive1->catatan;
            $stockTransferReceive1->fl_batal = 'true';
            $stockTransferReceive1->batal_tgl = date('Y-m-d H:i:s');
            $stockTransferReceive1->batal_userid = $request->userid;
            $stockTransferReceive1->batal_lokasi = $request->lokasi;
            $stockTransferReceive1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //StockTransferSend1
            $dataTransfer1= StockTransferSend2::from("t_stock_transfer_send2 as a")
            ->join("t_stock_transfer_receive2 as b","a.dtl2_key","=","b.base_ref")
            ->selectRaw("a.doc_key")
            ->where("b.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataTransfer1 as $recTransfer1) {
                $dataTransfer2= StockTransferSend2::from("t_stock_transfer_send2 as a")
                ->join("t_stock_transfer_receive2 as b","a.dtl2_key","=","b.base_ref")
                ->selectRaw("a.doc_key, a.dtl2_key, a.qty_sisa, b.qty")
                ->where("a.doc_key",$recTransfer1->doc_key)
                ->get();
                foreach($dataTransfer2 as $recTransfer2) {
                    //Update StockTransferSend2
                    $stockTransfer2 = StockTransferSend2::where('dtl2_key',$recTransfer2->dtl2_key)->first();
                    if ($stockTransfer2) {
                        $stockTransfer2->qty_sisa = $stockTransfer2->qty_sisa + $recTransfer2->qty;
                        if ($stockTransfer2->qty_sisa == $stockTransfer2->qty) {
                            $stockTransfer2->fl_tutup = FALSE;
                        } else {
                            $stockTransfer2->fl_tutup = TRUE;
                        }
                        $stockTransfer2->save();
                    }
                }
                //Update StockTransferSend1
                $updTransfer1= StockTransferSend1::where("doc_key",$recTransfer1->doc_key)->first();
                if ($updTransfer1) {
                    //Cek Sisa Qty
                    $dataCek= StockTransferSend2::from("t_stock_transfer_send2 as a")
                    ->selectRaw("SUM(COALESCE(a.qty_sisa,0)) AS qty_sisa_total")
                    ->where("a.doc_key",$recTransfer1->doc_key)
                    ->groupBy("a.doc_key")
                    ->first();
                    $qty_sisa = ($dataCek) ? $dataCek->qty_sisa_total : 0;
                    if ($qty_sisa == 0) {
                        $updTransfer1->fl_tutup= TRUE;
                    } else {
                        $updTransfer1->fl_tutup= FALSE;
                    };
                    $updTransfer1->save();
                }
            }
        } elseif ($insert == TRUE) {
            //StockTransferSend1
            $dataTransfer1= StockTransferSend2::from("t_stock_transfer_send2 as a")
            ->join("t_stock_transfer_receive2 as b","a.dtl2_key","=","b.base_ref")
            ->selectRaw("a.doc_key")
            ->where("b.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataTransfer1 as $recTransfer1) {
                $dataTransfer2= StockTransferSend2::from("t_stock_transfer_send2 as a")
                ->join("t_stock_transfer_receive2 as b","a.dtl2_key","=","b.base_ref")
                ->selectRaw("a.doc_key, a.dtl2_key, a.qty_sisa, b.qty")
                ->where("a.doc_key",$recTransfer1->doc_key)
                ->get();
                foreach($dataTransfer2 as $recTransfer2) {
                    //Update StockTransferSend2
                    $stockTransfer2 = StockTransferSend2::where('dtl2_key',$recTransfer2->dtl2_key)->first();
                    if ($stockTransfer2) {
                        $stockTransfer2->qty_sisa = $stockTransfer2->qty_sisa - $recTransfer2->qty;
                        if ($stockTransfer2->qty_sisa == $stockTransfer2->qty) {
                            $stockTransfer2->fl_tutup = FALSE;
                        } else {
                            $stockTransfer2->fl_tutup = TRUE;
                        }
                        $stockTransfer2->save();
                    }
                }
                //Update StockTransferSend1
                $updTransfer1= StockTransferSend1::where("doc_key",$recTransfer1->doc_key)->first();
                if ($updTransfer1) {
                    //Cek Sisa Qty
                    $dataCek= StockTransferSend2::from("t_stock_transfer_send2 as a")
                    ->selectRaw("SUM(COALESCE(a.qty_sisa,0)) AS qty_sisa_total")
                    ->where("a.doc_key",$recTransfer1->doc_key)
                    ->groupBy("a.doc_key")
                    ->first();
                    $qty_sisa = ($dataCek) ? $dataCek->qty_sisa_total : 0;
                    if ($qty_sisa == 0) {
                        $updTransfer1->fl_tutup= TRUE;
                    } else {
                        $updTransfer1->fl_tutup= FALSE;
                    };
                    $updTransfer1->save();
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
        $docTrans=50; //Transfer Receive
        if ($insert == FALSE) {
            $dataTrans= StockTransferReceive1::from("t_stock_transfer_receive1 as a")
            ->join("t_stock_transfer_receive2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke AS kd_lokasi,
                b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.konversi, b.satuan_dasar, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand - ($recTrans->qty*$recTrans->konversi);
                    $dataStokFifo->qty_in = $dataStokFifo->qty_in - ($recTrans->qty*$recTrans->konversi);
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
            $dataTrans= StockTransferReceive1::from("t_stock_transfer_receive1 as a")
            ->join("t_stock_transfer_receive2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke AS kd_lokasi,
                b.dtl2_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.rp_total,
                b.konversi, b.satuan_dasar, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan_dasar)
                    ->where("base_type",$docTrans)
                    ->where("base_doc_key",$recTrans->doc_key)
                    ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                if (!$dataStokFifo) {
                    $dataStokFifo= new StokFifo();
                    $dataStokFifo->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                }
                $dataStokFifo->kd_lokasi = $recTrans->kd_lokasi;
                $dataStokFifo->kd_bahan = $recTrans->kd_bahan;
                $dataStokFifo->satuan = $recTrans->satuan_dasar;
                $dataStokFifo->tgl_doc = $recTrans->tgl_doc;
                $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand + ($recTrans->qty*$recTrans->konversi);
                $dataStokFifo->qty_in = $dataStokFifo->qty_in + ($recTrans->qty*$recTrans->konversi);
                $dataStokFifo->rp_harga = $recTrans->rp_harga;
                $dataStokFifo->base_type = $docTrans;
                $dataStokFifo->base_doc_key = $recTrans->doc_key;
                $dataStokFifo->base_dtl2_key = $recTrans->dtl2_key;
                $dataStokFifo->save();
                //Update StockTransferReceive2 Stok_Fifo_Key
                $dataTrans2= StockTransfer2::where("dtl2_key",$recTrans->dtl2_key)->first();
                if ($dataTrans2) {
                    $dataTrans2->stok_fifo_key = $dataStokFifo->stok_fifo_key;
                    $dataTrans2->save();
                }
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
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
                $dataStokFifoDtl->stok_fifo_key = $stokFifoKey;
                $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                $dataStokFifoDtl->satuan = $recTrans->satuan_dasar;
                $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                $dataStokFifoDtl->qty = $dataStokFifoDtl->qty + ($recTrans->qty*$recTrans->konversi);
                $dataStokFifoDtl->base_type = $docTrans;
                $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                $dataStokFifoDtl->save();
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
        $dataTrans1= $data['t_stock_transfer_receive1'];
        $dataTrans2= $data['t_stock_transfer_receive2'];

        $dataTrans2= array_filter($dataTrans2, function ($item) {
            return isset($item['kd_bahan']) && $item['kd_bahan'] !== null && $item['kd_bahan'] !== '';
        });

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

            $stockTransferReceive1= StockTransferReceive1::where('doc_key',$where['doc_key'])->first();

            //Jika sudah ada, maka rollback stok dulu
            if ($stockTransferReceive1) {
                StockTransferReceiveController::updateLinkData($doc_key, FALSE);
                if (UtilityController::getAutoStok() == 'true') {
                    StockTransferReceiveController::updateStok($doc_key, FALSE);
                }
            } else {
                $stockTransferReceive1= new StockTransferReceive1();
                $stockTransferReceive1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $stockTransferReceive1->no_doc         = $dataTrans1['no_doc'];
            $stockTransferReceive1->tgl_doc        = $dataTrans1['tgl_doc'];
            $stockTransferReceive1->kd_lokasi_dari = $dataTrans1['kd_lokasi_dari'];
            $stockTransferReceive1->kd_lokasi_ke   = $dataTrans1['kd_lokasi_ke'];
            $stockTransferReceive1->catatan        = $dataTrans1['catatan'];
            $stockTransferReceive1->rp_total       = $dataTrans1['rp_total'];
            $stockTransferReceive1->fl_tutup       = $dataTrans1['fl_tutup'];
            $stockTransferReceive1->fl_batal       = $dataTrans1['fl_batal'];
            $stockTransferReceive1->fl_trds        = $dataTrans1['fl_trds'];
            $stockTransferReceive1->tgl_proses     = $dataTrans1['tgl_proses'];
            $stockTransferReceive1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $stockTransferReceive1->create_tgl     = $dataTrans1['create_tgl'];
            $stockTransferReceive1->create_userid  = $dataTrans1['create_userid'];
            $stockTransferReceive1->create_lokasi  = $dataTrans1['create_lokasi'];
            $stockTransferReceive1->update_tgl     = $dataTrans1['update_tgl'];
            $stockTransferReceive1->update_userid  = $dataTrans1['update_userid'];
            $stockTransferReceive1->update_lokasi  = $dataTrans1['update_lokasi'];
            $stockTransferReceive1->no_account     = $dataTrans1['no_account'];
            $stockTransferReceive1->save();

            //Data StockTransfer2
            $existingIds = StockTransferReceive2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            StockTransferReceive2::whereIn('dtl2_key', $toDelete)->delete();

            //Data StockTransfer2
            //StockTransferReceive2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $stockTransferReceive2 = StockTransferReceive2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($stockTransferReceive2)) {
                    $stockTransferReceive2 = new StockTransferReceive2();
                    $stockTransferReceive2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $stockTransferReceive2->doc_key        = $stockTransferReceive1->doc_key;
                $stockTransferReceive2->no_urut        = $recTrans2['no_urut'];
                $stockTransferReceive2->kd_bahan       = $recTrans2['kd_bahan'];
                $stockTransferReceive2->satuan         = $recTrans2['satuan'];
                $stockTransferReceive2->qty_req        = $recTrans2['qty_req'];
                $stockTransferReceive2->qty            = $recTrans2['qty'];
                $stockTransferReceive2->qty_send       = $recTrans2['qty_send'];
                $stockTransferReceive2->rp_harga       = $recTrans2['rp_harga'];
                $stockTransferReceive2->rp_total       = $recTrans2['rp_total'];
                $stockTransferReceive2->catatan        = $recTrans2['catatan'];
                $stockTransferReceive2->no_account     = $recTrans2['no_account'];
                $stockTransferReceive2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $stockTransferReceive2->base_type      = $recTrans2['base_type'];
                $stockTransferReceive2->base_ref       = $recTrans2['base_ref'];
                $stockTransferReceive2->base_no_doc    = $recTrans2['base_no_doc'];
                $stockTransferReceive2->konversi       = $recTrans2['konversi'];
                $stockTransferReceive2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $stockTransferReceive2->save();
            }

            $resp2= StockTransferReceiveController::updateLinkData($stockTransferReceive1->doc_key, TRUE);
            if (UtilityController::getAutoStok() == 'true') {
                $respstok= StockTransferReceiveController::updateStok($stockTransferReceive1->doc_key, TRUE);
            }

            DB::commit();
            //$response['resp'] = $resp2;
            //$response['respstok'] = $respstok;
            //$response['doc_key'] = $stockTransferReceive1->doc_key;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
