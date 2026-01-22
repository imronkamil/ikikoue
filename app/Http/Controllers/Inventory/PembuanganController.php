<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Inventory\Buang1;
use App\Models\Inventory\Buang2;
use App\Models\Inventory\Buang2Fifo;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Waste;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class PembuanganController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data["t_buang1"]= Buang1::from("t_buang1 as a")
        ->join("m_lokasi as b","a.kd_lokasi","=","b.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.rp_total, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            b.nm_lokasi")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy("tgl_doc",'desc')
        ->orderBy('no_doc','desc')
        ->get();

        return response()->success('Success',$data);
        //return response()->success('Success',$query1);
    }

    public function show2() {
        $data['t_buang1']= StockTransfer1::from('t_buang1 as a')
        ->join("m_lokasi as b","a.kd_lokasi","=","b.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            b.nm_lokasi")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_buang1']= Buang1::from('t_buang1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.rp_total, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_buang1']= Buang1::from('t_buang1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, COALESCE(a.fl_batal,'false') as fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_buang1']) ? $data['t_buang1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getApproved(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_buang1']= Buang1::from('t_buang1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.fl_batal, a.fl_approved")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_buang1']) ? $data['t_buang1']->fl_approved : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //Buang1
        $data['t_buang1']= Buang1::from('t_buang1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.catatan, a.rp_total, a.kd_auditor,
            a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal, a.no_account, a.fl_approved,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.approved_tgl, a.approved_userid, a.approved_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //Buang2
        $data['t_buang2']= Buang2::from('t_buang2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga, a.rp_total,
            a.catatan, a.no_account, a.waste_id")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //Buang2Fifo
        $data['t_buang2_fifo']= Buang2Fifo::from('t_buang2_fifo as a')
        ->selectRaw("a.dtl2_fifo_key, a.dtl2_key, a.doc_key, a.kd_bahan, a.satuan, a.qty, a.stok_fifo_key")
        ->where("a.doc_key",$doc_key)
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

        //Master Waste
        $data['m_waste']= Waste::from('m_waste as a')
        ->selectRaw("a.waste_id, a.nm_waste, a.catatan, a.fl_aktif, a.no_urut,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.fl_aktif","true")
        ->orderBy("waste_id","asc")
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
        PembuanganController::updateStok($doc_key,FALSE);
        Buang2Fifo::where('doc_key',$doc_key)->delete();
        Buang2::where('doc_key',$doc_key)->delete();
        Buang1::where('doc_key',$doc_key)->delete();
        AccountDtl::where('base_type',27)->where('base_doc_key',$doc_key)->delete();
        $stokFifoDtl= StokFifoDtl::where('base_type',27)->where('base_doc_key',$doc_key)->get();
        foreach ($stokFifoDtl as $recStokFifoDtl) {
            $dataStokFifo= StokFifo::where("stok_fifo_key",$recStokFifoDtl->stok_fifo_key)->first();
            if ($dataStokFifo) {
                $dataStokFifo->qty_used = $dataStokFifo->qty_used - $recStokFifoDtl->qty;
                $dataStokFifo->save();
            }
        }
        StokFifoDtl::where('base_type',27)->where('base_doc_key',$doc_key)->get();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $buang1= Buang1::where('doc_key',$doc_key)->first();
        if ($buang1) {
            if ($buang1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            PembuanganController::updateStok($doc_key,FALSE);
            //Update Buang1
            $buang1->catatan = $catatan . "\n" . $buang1->catatan;
            $buang1->fl_batal = 'true';
            $buang1->batal_tgl = date('Y-m-d H:i:s');
            $buang1->batal_userid = $request->userid;
            $buang1->batal_lokasi = $request->lokasi;
            $buang1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function setApproved(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $buang1= Buang1::where('doc_key',$doc_key)->first();
        if ($buang1) {
            if ($buang1->fl_approved == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            if ($buang1->tgl_proses == null || $buang1->tgl_proses == '') {
                if (UtilityController::getAutoStok() == 'true') {
                    PembuanganController::updateStok($doc_key,TRUE);
                }
                $buang1->tgl_proses = date('Y-m-d H:i:s');
            }
            //Update Buang1
            $buang1->fl_approved = 'true';
            $buang1->approved_tgl = date('Y-m-d H:i:s');
            $buang1->approved_userid = $request->userid;
            $buang1->approved_lokasi = $request->lokasi;
            $buang1->save();
        }
        $response['message'] = 'Approve berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=27; //Buang/Waste
        if ($insert == FALSE) {
            $dataTrans= Buang1::from("t_buang1 as a")
            ->join("t_buang2_fifo as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.dtl2_fifo_key, b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.dtl2_fifo_key")
            ->get();
            foreach($dataTrans as $recTrans) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_used = $dataStokFifo->qty_used - $recTrans->qty;
                    $dataStokFifo->save();
                    //FIFO Detail
                    $stokFifoKey = $dataStokFifo->stok_fifo_key;
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                    if ($dataStokFifoDtl) {
                        if ($dataStokFifoDtl->qty + $recTrans->qty <= 0) {
                            $dataStokFifoDtl->delete();
                        } else {
                            $dataStokFifoDtl->qty = $dataStokFifoDtl->qty + $recTrans->qty;
                            $dataStokFifoDtl->save();
                        }
                    }
                }
            }
        } elseif ($insert == TRUE) {
            $qty= 0;
            $qtyStok=0;
            //Kosongkan Buang2Fifo
            Buang2Fifo::where("doc_key",$doc_key)->delete();
            //Append Buang2Fifo
            $dataTrans= Buang1::from("t_buang1 as a")
            ->join("t_buang2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.dtl2_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.rp_total")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $qty= $recTrans->qty;
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan)
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
                        //Buang2Fifo
                        $dataBuang2Fifo= Buang2Fifo::where("doc_key",$recTrans->doc_key)
                            ->where("dtl2_key",$recTrans->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataBuang2Fifo) {
                            $dataBuang2Fifo= new Buang2Fifo();
                            $dataBuang2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataBuang2Fifo->doc_key = $recTrans->doc_key;
                        $dataBuang2Fifo->dtl2_key = $recTrans->dtl2_key;
                        $dataBuang2Fifo->kd_bahan = $recTrans->kd_bahan;
                        $dataBuang2Fifo->satuan = $recTrans->satuan;
                        $dataBuang2Fifo->qty = $qtyStok;
                        $dataBuang2Fifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataBuang2Fifo->save();

                        //StokFIFO Detail
                        /*$dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan)
                            ->where("base_type",$docTrans)
                            ->where("base_doc_key",$recTrans->doc_key)
                            ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                        if (!$dataStokFifoDtl) {
                            $dataStokFifoDtl= new StokFifoDtl();
                            $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        }*/
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                        $dataStokFifoDtl->satuan = $recTrans->satuan;
                        $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                        $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                        $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                        $dataStokFifoDtl->base_type = $docTrans;
                        $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                        $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                        $dataStokFifoDtl->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataStokFifoDtl->qty = -$qtyStok;
                        $dataStokFifoDtl->save();
                    } else {
                        break;
                    }
                }

                if ($qty>0) {
                    //Stok FIFO Header
                    $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan)
                        ->where("qty_on_hand",">",0)
                        ->where("rp_harga",">",0)
                        ->orderBy("tgl_doc","desc")
                        ->orderBy("stok_fifo_key","desc")
                        ->first();
                    if(!$dataStokFifo) {
                        $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan)
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
                    $dataStokFifoNew->satuan = $recTrans->satuan;
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
                    //Buang2Fifo
                    $dataBuang2Fifo= Buang2Fifo::where("doc_key",$recTrans->doc_key)
                        ->where("dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataBuang2Fifo) {
                        $dataBuang2Fifo= new Buang2Fifo();
                        $dataBuang2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataBuang2Fifo->doc_key = $recTrans->doc_key;
                    $dataBuang2Fifo->dtl2_key = $recTrans->dtl2_key;
                    $dataBuang2Fifo->kd_bahan = $recTrans->kd_bahan;
                    $dataBuang2Fifo->satuan = $recTrans->satuan;
                    $dataBuang2Fifo->qty = $qtyStok;
                    $dataBuang2Fifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataBuang2Fifo->save();

                    //StokFIFO Detail
                    /*$dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                    if (!$dataStokFifoDtl) {
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    }*/
                    $dataStokFifoDtl= new StokFifoDtl();
                    $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                    $dataStokFifoDtl->satuan = $recTrans->satuan;
                    $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                    $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                    $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                    $dataStokFifoDtl->base_type = $docTrans;
                    $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                    $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                    $dataStokFifoDtl->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataStokFifoDtl->qty = -$qtyStok;
                    $dataStokFifoDtl->save();
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
        //return $dataBuang2Fifo;
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_buang1'];
        $dataTrans2= $data['t_buang2'];

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

            $buang1= Buang1::where('doc_key',$where['doc_key'])->first();

            //Jika ada data lama, kurangi stok terlebih dahulu
            if ($buang1) {
                PembuanganController::updateStok($doc_key, FALSE);
            } else {
                $buang1= new Buang1();
                $buang1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $buang1->no_doc         = $dataTrans1['no_doc'];
            $buang1->tgl_doc        = $dataTrans1['tgl_doc'];
            $buang1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $buang1->catatan        = $dataTrans1['catatan'];
            $buang1->rp_total       = $dataTrans1['rp_total'];
            $buang1->kd_auditor     = $dataTrans1['kd_auditor'];
            $buang1->fl_tutup       = $dataTrans1['fl_tutup'];
            $buang1->fl_batal       = $dataTrans1['fl_batal'];
            $buang1->fl_trds        = $dataTrans1['fl_trds'];
            $buang1->fl_approved    = $dataTrans1['fl_approved'];
            $buang1->tgl_proses     = $dataTrans1['tgl_proses'];
            $buang1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $buang1->create_tgl     = $dataTrans1['create_tgl'];
            $buang1->create_userid  = $dataTrans1['create_userid'];
            $buang1->create_lokasi  = $dataTrans1['create_lokasi'];
            $buang1->update_tgl     = $dataTrans1['update_tgl'];
            $buang1->update_userid  = $dataTrans1['update_userid'];
            $buang1->update_lokasi  = $dataTrans1['update_lokasi'];
            $buang1->approved_tgl     = $dataTrans1['approved_tgl'];
            $buang1->approved_userid  = $dataTrans1['approved_userid'];
            $buang1->approved_lokasi  = $dataTrans1['approved_lokasi'];
            $buang1->no_account     = $dataTrans1['no_account'];
            $buang1->save();

            //Data Buang2
            $existingIds = Buang2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Buang2::whereIn('dtl2_key', $toDelete)->delete();

            //Data Buang2
            //Buang2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $buang2 = Buang2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($buang2)) {
                    $buang2 = new Buang2();
                    $buang2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $buang2->doc_key        = $buang1->doc_key;
                $buang2->no_urut        = $recTrans2['no_urut'];
                $buang2->kd_bahan       = $recTrans2['kd_bahan'];
                $buang2->satuan         = $recTrans2['satuan'];
                $buang2->qty            = $recTrans2['qty'];
                $buang2->rp_harga       = $recTrans2['rp_harga'];
                $buang2->rp_total       = $recTrans2['rp_total'];
                $buang2->catatan        = $recTrans2['catatan'];
                $buang2->no_account     = $recTrans2['no_account'];
                $buang2->waste_id       = $recTrans2['waste_id'];
                $buang2->save();
            }

            $respstok= PembuanganController::updateStok($buang1->doc_key, TRUE);

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
