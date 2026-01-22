<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Inventory\StockTransfer1;
use App\Models\Inventory\StockTransfer2;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class StockTransferController extends Controller
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
        ->leftJoin("t_stock_transfer2 as c","c.dtl2_key","=","b.base_ref")
        ->selectRaw("c.doc_key,  string_agg(DISTINCT a.no_doc,', ') AS no_doc_send")
        ->where(DB::raw("COALESCE(a.fl_batal,false)"),false)
        ->groupBy("c.doc_key");

        $data["t_stock_transfer1"]= StockTransfer1::from("t_stock_transfer1 as a")
        ->leftJoinSub($subQ1,"b", function ($join) {
            $join->on("a.doc_key","=","b.doc_key");
        })
        ->join("m_lokasi as c","a.kd_lokasi_dari","=","c.kd_lokasi")
        ->join("m_lokasi as d","a.kd_lokasi_ke","=","d.kd_lokasi")
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke,
            a.catatan, a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            c.nm_lokasi AS nm_lokasi_dari, d.nm_lokasi AS nm_lokasi_ke, b.no_doc_send")
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
        $data['t_stock_transfer1']= PO1::from('t_stock_transfer1 as a')
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
        $data['t_stock_transfer1']= StockTransfer1::from('t_stock_transfer1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, COALESCE(a.fl_batal,false) AS fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_stock_transfer1']) ? $data['t_stock_transfer1']->fl_batal : 'false';
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

    public function getListPR(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_pr2 as a')
        ->leftJoin('t_po2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('a.doc_key, MAX(b.doc_key) AS po_doc_key')
        ->groupBy('a.doc_key');

        $data['t_pr']= PR1::from('t_pr1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.tgl_datang, a.kd_lokasi, a.kd_partner, a.nm_partner,
            a.rp_total, a.catatan, COALESCE(a.fl_batal,false) AS fl_cek")
        ->where('a.kd_lokasi',$kd_lokasi)
        ->where(DB::raw('COALESCE(a.fl_batal,false)'),false)
        ->where(function ($query1) use ($doc_key) {
            $query1->where(DB::raw('COALESCE(a.fl_tutup,false)'),false)
            ->orwhere(function ($query2) use ($doc_key) {
                $query2->where(DB::raw('COALESCE(a.fl_tutup,false)'),true)
                    ->where("b.po_doc_key",$doc_key);
            });
        })
        ->orderBy("a.tgl_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemPR(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        //PR1
        $data['t_pr1']= PR1::from('t_pr1 as a')
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

        $data['t_pr2']= PO1::from('t_pr1 as a')
        ->join('t_pr2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_po2 as c','b.dtl2_key','c.base_ref')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga,
            b.persen_diskon, b.rp_diskon, b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3,
            b.persen_diskon4, b.rp_diskon4, b.kd_pajak, b.persen_pajak, b.rp_pajak, b.rp_harga_akhir,
            b.qty_sisa, b.catatan, b.fl_tutup, b.base_type, b.base_ref,
            c.doc_key AS doc_key_po, c.rp_harga AS rp_harga_po,
            c.persen_diskon AS persen_diskon_po, c.rp_diskon AS rp_diskon_po,
            c.persen_diskon2 AS persen_diskon2_po, c.rp_diskon2 AS rp_diskon2_po,
            c.persen_diskon3 AS persen_diskon3_po, c.rp_diskon3 AS rp_diskon3_po,
            c.persen_diskon4 AS persen_diskon4_po, c.rp_diskon4 AS rp_diskon4_po,
            c.persen_pajak AS persen_pajak_po, c.rp_pajak AS rp_pajak_po,
            c.rp_harga_akhir AS rp_harga_akhir_po,
            COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0) AS qty_sisa_po")
        ->where(DB::raw('COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0)'),'>',0)
        ->whereIn('a.doc_key',$doc_key)
        ->get();

        $data['t_pr3']= PO1::from('t_pr1 as a')
        ->join('t_pr3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl3_key, b.doc_key, b.no_urut, b.no_account, b.nm_account, b.catatan,
            b.rp_bayar, b.rp_sisa, b.base_type, b.base_ref")
        ->where(DB::raw('COALESCE(b.rp_sisa,0)'),'>',0)
        ->whereIn('a.doc_key',$doc_key)
        ->get();

        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //StockTransfer1
        $data['t_stock_transfer1']= StockTransfer1::from('t_stock_transfer1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi_dari, a.kd_lokasi_ke, a.catatan,
            a.rp_total, a.fl_tutup, a.fl_trds, a.fl_batal, a.tgl_proses, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.doc_key",$doc_key)
        ->first();

        //StockTransfer2
        $data['t_stock_transfer2']= StockTransfer2::from('t_stock_transfer2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga, a.rp_total,
            a.catatan, a.no_account, a.stok_fifo_key, a.qty_sisa, a.fl_tutup, a.konversi, a.satuan_dasar")
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

        //Master Bahan Stock Transfer Filter
        $data['m_bahan_stock_transfer_filter']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("fl_aktif","true")
        ->where("fl_stock_transfer","true")
        ->orderBy("a.kd_bahan")
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
        $stockTransfer1= StockTransfer1::where('doc_key',$doc_key)->first();
        if ($stockTransfer1) {
            if ($stockTransfer1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //Update StockTransfer1
            $stockTransfer1->catatan = $catatan . "\n" . $stockTransfer1->catatan;
            $stockTransfer1->fl_batal = 'true';
            $stockTransfer1->batal_tgl = date('Y-m-d H:i:s');
            $stockTransfer1->batal_userid = $request->userid;
            $stockTransfer1->batal_lokasi = $request->lokasi;
            $stockTransfer1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_stock_transfer1'];
        $dataTrans2= $data['t_stock_transfer2'];

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

            $stockTransfer1= StockTransfer1::where('doc_key',$where['doc_key'])->first();
            if (!($stockTransfer1)) {
                $stockTransfer1= new StockTransfer1();
                $stockTransfer1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $stockTransfer1->no_doc         = $dataTrans1['no_doc'];
            $stockTransfer1->tgl_doc        = $dataTrans1['tgl_doc'];
            $stockTransfer1->kd_lokasi_dari = $dataTrans1['kd_lokasi_dari'];
            $stockTransfer1->kd_lokasi_ke   = $dataTrans1['kd_lokasi_ke'];
            $stockTransfer1->catatan        = $dataTrans1['catatan'];
            $stockTransfer1->rp_total       = $dataTrans1['rp_total'];
            $stockTransfer1->fl_tutup       = $dataTrans1['fl_tutup'];
            $stockTransfer1->fl_batal       = $dataTrans1['fl_batal'];
            $stockTransfer1->fl_trds        = $dataTrans1['fl_trds'];
            $stockTransfer1->tgl_proses     = $dataTrans1['tgl_proses'];
            $stockTransfer1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $stockTransfer1->create_tgl     = $dataTrans1['create_tgl'];
            $stockTransfer1->create_userid  = $dataTrans1['create_userid'];
            $stockTransfer1->create_lokasi  = $dataTrans1['create_lokasi'];
            $stockTransfer1->update_tgl     = $dataTrans1['update_tgl'];
            $stockTransfer1->update_userid  = $dataTrans1['update_userid'];
            $stockTransfer1->update_lokasi  = $dataTrans1['update_lokasi'];
            $stockTransfer1->no_account     = $dataTrans1['no_account'];
            $stockTransfer1->save();

            //Data StockTransfer2
            $existingIds = StockTransfer2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            StockTransfer2::whereIn('dtl2_key', $toDelete)->delete();

            //Data StockTransfer2
            //StockTransfer2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $stockTransfer2 = StockTransfer2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($stockTransfer2)) {
                    $stockTransfer2 = new StockTransfer2();
                    $stockTransfer2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $stockTransfer2->doc_key        = $stockTransfer1->doc_key;
                $stockTransfer2->no_urut        = $recTrans2['no_urut'];
                $stockTransfer2->kd_bahan       = $recTrans2['kd_bahan'];
                $stockTransfer2->satuan         = $recTrans2['satuan'];
                $stockTransfer2->qty            = $recTrans2['qty'];
                $stockTransfer2->rp_harga       = $recTrans2['rp_harga'];
                $stockTransfer2->rp_total       = $recTrans2['rp_total'];
                $stockTransfer2->catatan        = $recTrans2['catatan'];
                $stockTransfer2->no_account     = $recTrans2['no_account'];
                $stockTransfer2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $stockTransfer2->qty_sisa       = $recTrans2['qty_sisa'];
                $stockTransfer2->fl_tutup       = $recTrans2['fl_tutup'];
                $stockTransfer2->konversi       = $recTrans2['konversi'];
                $stockTransfer2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $stockTransfer2->save();
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
