<?php

namespace App\Http\Controllers\Produksi;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Produksi\Produksi1;
use App\Models\Produksi\Produksi2;
use App\Models\Produksi\Produksi2Fifo;
use App\Models\Produksi\PraProduksi1;
use App\Models\Produksi\PraProduksi2;
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

class ProduksiController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data["t_produksi1"]= Produksi1::from("t_produksi1 as a")
        ->join("m_lokasi as b","a.kd_lokasi","=","b.kd_lokasi")
        ->join("m_bahan as c","a.kd_bahan","=","c.kd_bahan")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.kd_bahan, a.satuan, a.qty_resep, a.qty_standar, a.qty_deviasi, a.rp_harga, a.rp_total,
            a.fl_complete, a.fl_jual, a.fl_trds, a.fl_balik, a.fl_batal, a.base_type, a.base_ref, a.kd_operator,
            a.tgl_proses, a.doc_key_jurnal, a.no_account,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.nm_lokasi, c.nm_bahan, a.qty_standar+a.qty_deviasi AS qty_total")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy("tgl_doc",'desc')
        ->orderBy('no_doc','desc')
        ->get();

        return response()->success('Success',$data);
        //return response()->success('Success',$query1);
    }

    public function show2() {
        $data['t_produksi1']= StockTransfer1::from('t_produksi1 as a')
        ->join("m_lokasi as b","a.kd_lokasi","=","b.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.kd_bahan, a.satuan, a.qty_resep, a.qty_standar, a.qty_deviasi, a.rp_harga, a.rp_total,
            a.fl_complete, a.fl_jual, a.fl_trds, a.fl_balik, a.fl_batal, a.base_type, a.base_ref, a.kd_operator,
            a.tgl_proses, a.doc_key_jurnal, a.no_account,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.nm_lokasi")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_produksi1']= Produksi1::from('t_produksi1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.kd_bahan, a.satuan, a.qty_resep, a.qty_standar, a.qty_deviasi, a.rp_harga, a.rp_total,
            a.fl_complete, a.fl_jual, a.fl_trds, a.fl_balik, a.fl_batal, a.base_type, a.base_ref, a.kd_operator,
            a.tgl_proses, a.doc_key_jurnal, a.no_account,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_produksi1']= Produksi1::from('t_produksi1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_produksi1']) ? $data['t_produksi1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //Produksi1
        $data['t_produksi1']= Produksi1::from('t_produksi1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.catatan, a.kd_bahan, a.satuan, a.qty_resep, a.qty_standar, a.qty_deviasi, a.rp_harga, a.rp_total,
            a.fl_complete, a.fl_jual, a.fl_trds, a.fl_balik, a.fl_batal, a.base_type, a.base_ref, a.kd_operator,
            a.tgl_proses, a.doc_key_jurnal, a.no_account,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //Produksi2
        $data['t_produksi2']= Produksi2::from('t_produksi2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.qty_kurang,
            a.rp_harga, a.rp_total, a.qty_pakai, a.rp_harga_pakai, a.fl_potong, a.rp_hpp,
            a.catatan, a.no_account, a.base_type, base_doc_key")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //Produksi2Fifo
        $data['t_produksi2_fifo']= Produksi2Fifo::from('t_produksi2_fifo as a')
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

        //Master Resep
        $data['m_resep1']= Bahan::from('m_resep1 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan_resep','=','b.kd_bahan')
        ->selectRaw("a.kd_bahan_resep, a.nm_bahan_resep, a.qty_resep, a.satuan_resep, a.catatan_resep,
            a.rp_biaya, a.fl_balik, a.fl_aktif, b.nm_bahan, a.kd_bahan_resep || ' - ' || b.nm_bahan AS ket_resep")
        ->orderBy("b.nm_bahan")
        ->get();

        //Master Bahan Filter
        $data['m_bahan_filter']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.kd_bahan || ' - ' || a.nm_bahan AS ket_bahan")
        ->where("fl_aktif","true")
        ->orderBy("a.kd_bahan")
        ->get();

        return response()->success('Success',$data);
    }

    public function getDetailResep(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan : '';
        $qty_resep=isset($request->qty_resep) ? $request->qty_resep : 0;

        $data['t_produksi2']=DB::select('SELECT * FROM getdetailproduksi(?,?,?)',
            [$doc_key, $kd_bahan, $qty_resep]);

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
        $docTrans=71; //Produksi
        ProduksiController::updateStok($doc_key,FALSE);
        Produksi2Fifo::where('doc_key',$doc_key)->delete();
        Produksi2::where('doc_key',$doc_key)->delete();
        Produksi1::where('doc_key',$doc_key)->delete();
        AccountDtl::where('base_type',$docTrans)->where('base_doc_key',$doc_key)->delete();
        /*$stokFifoDtl= StokFifoDtl::where('base_type',$docTrans)->where('base_doc_key',$doc_key)->get();
        foreach ($stokFifoDtl as $recStokFifoDtl) {
            $dataStokFifo= StokFifo::where("stok_fifo_key",$recStokFifoDtl->stok_fifo_key)->first();
            if ($dataStokFifo) {
                $dataStokFifo->qty_used = $dataStokFifo->qty_used - $recStokFifoDtl->qty;
                $dataStokFifo->save();
            }
        }
        StokFifoDtl::where('base_type',$docTrans)->where('base_doc_key',$doc_key)->get();*/
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $produksi1= Produksi1::where('doc_key',$doc_key)->first();
        if ($produksi1) {
            if ($produksi1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            ProduksiController::updateStok($doc_key,FALSE);
            //Update Produksi1
            $produksi1->catatan = $catatan . "\n" . $stockTransferSend1->catatan;
            $produksi1->fl_batal = 'true';
            $produksi1->batal_tgl = date('Y-m-d H:i:s');
            $produksi1->batal_userid = $request->userid;
            $produksi1->batal_lokasi = $request->lokasi;
            $produksi1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function setApproved(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $produksi1= Produksi1::where('doc_key',$doc_key)->first();
        if ($produksi1) {
            if ($produksi1->fl_approved == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            if ($produksi1->tgl_proses == null || $produksi1->tgl_proses == '') {
                if (UtilityController::getAutoStok() == 'true') {
                    ProduksiController::updateStok($doc_key,TRUE);
                }
                $produksi1->tgl_proses = date('Y-m-d H:i:s');
            }
            //Update Produksi1
            $produksi1->fl_approved = 'true';
            $produksi1->approved_tgl = date('Y-m-d H:i:s');
            $produksi1->approved_userid = $request->userid;
            $produksi1->approved_lokasi = $request->lokasi;
            $produksi1->save();
        }
        $response['message'] = 'Approve berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //Produksi1
            $dataProd1= Produksi1::from("t_produksi1 as a")
            ->leftJoin("t_pra_produksi1 as b","b.doc_key","=","a.base_ref")
            ->selectRaw("a.doc_key, b.doc_key as rpr_doc_key")
            ->where("a.doc_key",$doc_key)
            ->get();
            foreach($dataProd1 as $recProd1) {
                $praProd1 = PraProduksi1::where('doc_key',$recProd1->rpr_doc_key)->first();
                if ($praProd1) {
                    $praProd1->fl_tutup= FALSE;
                    $praProd1->save();
                }
            }
        } elseif ($insert == TRUE) {
            //Produksi1
            $dataProd1= Produksi1::from("t_produksi1 as a")
            ->leftJoin("t_pra_produksi1 as b","b.doc_key","=","a.base_ref")
            ->selectRaw("a.doc_key, b.doc_key as rpr_doc_key")
            ->where("a.doc_key",$doc_key)
            ->get();
            foreach($dataProd1 as $recProd1) {
                $praProd1 = PraProduksi1::where('doc_key',$recProd1->rpr_doc_key)->first();
                if ($praProd1) {
                    $praProd1->fl_tutup= TRUE;
                    $praProd1->save();
                }
            }
            //var_dump($rpr_doc_key);
        }
        //var_dump($recProd1->doc_key,$rp_sisa);
        //var_dump($qty,$qty_sisa,$rp_sisa);
        $response['message'] = 'Update link data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=71; //Produksi
        if ($insert == FALSE) {
            $dataTrans= Produksi1::from("t_produksi1 as a")
            ->join("t_produksi2_fifo as b","a.doc_key","=","b.doc_key")
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
                        $dataStokFifoDtl->qty = $dataStokFifoDtl->qty + $recTrans->qty;
                        $dataStokFifoDtl->save();
                    }
                }
            }
        } elseif ($insert == TRUE) {
            $qty= 0;
            $qtyStok=0;
            //Kosongkan Produksi2Fifo
            Produksi2Fifo::where("doc_key",$doc_key)->delete();
            //Append Produksi2Fifo
            $dataTrans= Produksi1::from("t_produksi1 as a")
            ->join("t_produksi2 as b","a.doc_key","=","b.doc_key")
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
                        //Produksi2Fifo
                        $dataProduksi2Fifo= Produksi2Fifo::where("doc_key",$recTrans->doc_key)
                            ->where("dtl2_key",$recTrans->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataProduksi2Fifo) {
                            $dataProduksi2Fifo= new Produksi2Fifo();
                            $dataProduksi2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataProduksi2Fifo->doc_key = $recTrans->doc_key;
                        $dataProduksi2Fifo->dtl2_key = $recTrans->dtl2_key;
                        $dataProduksi2Fifo->kd_bahan = $recTrans->kd_bahan;
                        $dataProduksi2Fifo->satuan = $recTrans->satuan;
                        $dataProduksi2Fifo->qty = $qtyStok;
                        $dataProduksi2Fifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataProduksi2Fifo->save();

                        //StokFIFO Detail
                        $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan)
                            ->where("base_type",$docTrans)
                            ->where("base_doc_key",$recTrans->doc_key)
                            ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                        if (!$dataStokFifoDtl) {
                            $dataStokFifoDtl= new StokFifoDtl();
                            $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        }
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
                    //Produksi2Fifo
                    $dataProduksi2Fifo= Produksi2Fifo::where("doc_key",$recTrans->doc_key)
                        ->where("dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataProduksi2Fifo) {
                        $dataProduksi2Fifo= new Produksi2Fifo();
                        $dataProduksi2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataProduksi2Fifo->doc_key = $recTrans->doc_key;
                    $dataProduksi2Fifo->dtl2_key = $recTrans->dtl2_key;
                    $dataProduksi2Fifo->kd_bahan = $recTrans->kd_bahan;
                    $dataProduksi2Fifo->satuan = $recTrans->satuan;
                    $dataProduksi2Fifo->qty = $qtyStok;
                    $dataProduksi2Fifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataProduksi2Fifo->save();

                    //StokFIFO Detail
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                    if (!$dataStokFifoDtl) {
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    }
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
        //return $dataProduksi2Fifo;
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_produksi1'];
        $dataTrans2= $data['t_produksi2'];

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

            $produksi1= Produksi1::where('doc_key',$where['doc_key'])->first();

            //Jika ada data lama, kurangi stok terlebih dahulu
            if ($produksi1) {
                ProduksiController::updateLinkData($doc_key, FALSE);
                ProduksiController::updateStok($doc_key, FALSE);
            } else {
                $produksi1= new Produksi1();
                $produksi1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $produksi1->no_doc         = $dataTrans1['no_doc'];
            $produksi1->tgl_doc        = $dataTrans1['tgl_doc'];
            $produksi1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $produksi1->catatan        = $dataTrans1['catatan'];
            $produksi1->kd_bahan       = $dataTrans1['kd_bahan'];
            $produksi1->satuan         = $dataTrans1['satuan'];
            $produksi1->qty_resep      = $dataTrans1['qty_resep'];
            $produksi1->qty_standar    = $dataTrans1['qty_standar'];
            $produksi1->qty_deviasi    = $dataTrans1['qty_deviasi'];
            $produksi1->rp_harga       = $dataTrans1['rp_harga'];
            $produksi1->rp_total       = $dataTrans1['rp_total'];
            $produksi1->fl_complete    = $dataTrans1['fl_complete'];
            $produksi1->fl_jual        = $dataTrans1['fl_jual'];
            $produksi1->fl_trds        = $dataTrans1['fl_trds'];
            $produksi1->fl_balik       = $dataTrans1['fl_balik'];
            $produksi1->fl_batal       = $dataTrans1['fl_batal'];
            $produksi1->base_type      = $dataTrans1['base_type'];
            $produksi1->base_ref       = $dataTrans1['base_ref'];
            $produksi1->kd_operator    = $dataTrans1['kd_operator'];
            $produksi1->tgl_proses     = $dataTrans1['tgl_proses'];
            $produksi1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $produksi1->no_account     = $dataTrans1['no_account'];
            $produksi1->create_tgl     = $dataTrans1['create_tgl'];
            $produksi1->create_userid  = $dataTrans1['create_userid'];
            $produksi1->create_lokasi  = $dataTrans1['create_lokasi'];
            $produksi1->update_tgl     = $dataTrans1['update_tgl'];
            $produksi1->update_userid  = $dataTrans1['update_userid'];
            $produksi1->update_lokasi  = $dataTrans1['update_lokasi'];
            $produksi1->save();

            //Data Produksi2
            $existingIds = Produksi2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Produksi2::whereIn('dtl2_key', $toDelete)->delete();

            //Data Produksi2
            //Produksi2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $produksi2 = Produksi2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($produksi2)) {
                    $produksi2 = new Produksi2();
                    $produksi2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $produksi2->doc_key        = $produksi1->doc_key;
                $produksi2->no_urut        = $recTrans2['no_urut'];
                $produksi2->kd_bahan       = $recTrans2['kd_bahan'];
                $produksi2->satuan         = $recTrans2['satuan'];
                $produksi2->qty            = $recTrans2['qty'];
                $produksi2->qty_kurang     = $recTrans2['qty_kurang'];
                $produksi2->rp_harga       = $recTrans2['rp_harga'];
                $produksi2->rp_total       = $recTrans2['rp_total'];
                $produksi2->qty_pakai      = $recTrans2['qty_pakai'];
                $produksi2->rp_harga_pakai = $recTrans2['rp_harga_pakai'];
                $produksi2->fl_potong      = $recTrans2['fl_potong'];
                $produksi2->rp_hpp         = $recTrans2['rp_hpp'];
                $produksi2->catatan        = $recTrans2['catatan'];
                $produksi2->no_account     = $recTrans2['no_account'];
                $produksi2->base_type      = $recTrans2['base_type'];
                $produksi2->base_doc_key   = $recTrans2['base_doc_key'];
                $produksi2->save();
            }

            ProduksiController::updateLinkData($produksi1->doc_key, TRUE);
            ProduksiController::updateStok($produksi1->doc_key, TRUE);

            DB::commit();
            //$resp= $produksi1;
            //$resp= $dataTrans2;
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
