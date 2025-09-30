<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Purchase\PO1;
use App\Models\Purchase\PO2;
use App\Models\Purchase\PO3;
use App\Models\Purchase\GR1;
use App\Models\Purchase\GR2;
use App\Models\Purchase\GR3;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Account;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Http\Controllers\Tools\DocNoController;

class GoodsReceiptController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table('t_gr2 as a')
        ->leftJoin('t_po2 as b','b.dtl2_key','=','a.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS po_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query1= DB::table('t_po1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.po_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_po")
        ->groupBy('b.doc_key');
        //->get();

        $subQ2= DB::table('t_gr2 as a')
        ->leftJoin('t_ap_invoice2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS inv_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query2= DB::table('t_ap_invoice1 as a')
        ->joinSub($subQ2,'b', function ($join) {
            $join->on('a.doc_key','=','b.inv_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_inv")
        ->groupBy('b.doc_key');

        $data['t_gr1']= GR1::from('t_gr1 as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->leftJoinSub($query2,'c','a.doc_key','=','c.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim,
            b.no_doc_po, c.no_doc_inv")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['t_gr1']= GR1::from('t_gr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_gr1']= GR1::from('t_gr1 as a')
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

    public function getListPO(Request $request) {
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_po2 as a')
        ->leftJoin('t_gr2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS gr_doc_key');

        $data['t_po']= PO1::from('t_po1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.tgl_datang, a.kd_lokasi, a.kd_partner, a.nm_partner,
            a.rp_total, a.catatan, COALESCE(a.fl_batal,false) AS fl_cek")
        ->where('a.kd_partner',$kd_partner)
        ->where(DB::raw('COALESCE(a.fl_batal,false)'),false)
        ->where(function ($query1) use ($doc_key) {
            $query1->where(DB::raw('COALESCE(a.fl_tutup,false)'),false)
            ->orwhere(function ($query2) use ($doc_key) {
                $query2->where(DB::raw('COALESCE(a.fl_tutup,false)'),true)
                    ->where("b.gr_doc_key",$doc_key);
            });
        })
        ->orderBy("a.tgl_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemPO(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];
        $data['t_po2']= PO1::from('t_po1 as a')
        ->join('t_po2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_gr2 as c','b.dtl2_key','c.base_ref')
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

        $data['t_po3']= PO1::from('t_po1 as a')
        ->join('t_po3 as b','a.doc_key','=','b.doc_key')
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

        //GR1
        $data['t_gr1']= GR1::from('t_gr1 as a')
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

        //GR2
        $data['t_gr2']= GR2::from('t_gr2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga,
            a.persen_diskon, a.rp_diskon, a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3,
            a.persen_diskon4, a.rp_diskon4, a.kd_pajak, a.persen_pajak, a.rp_pajak, a.rp_harga_akhir,
            a.qty_sisa, a.catatan, a.fl_tutup, a.base_type, a.base_ref, a.base_no_doc")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //GR3
        $data['t_gr3']= GR3::from('t_gr3 as a')
        ->selectRaw("a.dtl3_key, a.doc_key, a.no_urut, a.no_account, a.nm_account, a.catatan,
            a.rp_bayar, a.rp_sisa, a.base_type, a.base_ref")
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
            a.kd_server, a.kd_lokasi_acc,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
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
        ->get();

        //Master Bahan Satuan
        $data['m_bahan_satuan']= BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
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

        //Master Supplier Filter
        $data['m_supplier_filter']= Account::from('m_supplier as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_supplier")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        GR3::where('doc_key',$doc_key)->delete();
        GR2::where('doc_key',$doc_key)->delete();
        GR1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0) {
        //PO1
        $dataPO1= PO1::from("t_po1 as a")
        ->leftJoin("t_po2 as b","a.doc_key","=","b.doc_key")
        ->leftJoin("t_po3 as c","a.doc_key","=","c.doc_key")
        ->leftJoin("t_gr2 as d","b.dtl2_key","=","d.base_ref")
        ->selectRaw("a.doc_key")
        ->where("d.doc_key",$doc_key)
        ->groupBy("a.doc_key")
        ->get();
        foreach($dataPO1 as $recPO1) {
            //PO2
            $dataPO2= PO2::where("doc_key",$recPO1->doc_key)->get();
            $qty_sisa= 0;
            foreach($dataPO2 as $recPO2) {
                $qty_sisa = $qty_sisa + $recPO2->qty_sisa;
            }
            //PO3
            $dataPO3= PO3::where("doc_key",$recPO1->doc_key)->get();
            $rp_sisa= 0;
            foreach($dataPO3 as $recPO3) {
                $rp_sisa = $rp_sisa + $recPO3->rp_sisa;
            }
            //Update PO1
            $dataPO1= PO1::where("doc_key",$recPO1->doc_key)->first();
            if ($dataPO1) {
                if($qty_sisa == 0 && $rp_sisa == 0) {
                    $dataPO1->fl_tutup= TRUE;
                } else {
                    $dataPO1->fl_tutup= FALSE;
                }
                $dataPO1->save();
            }
        }
        //var_dump($recPO1->doc_key,$rp_sisa);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            $dataGR= GR1::from("t_gr1 as a")
            ->leftJoin("t_gr2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.tgl_doc, b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.rp_harga, a.persen_diskon, a.rp_diskon,
                a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3, a.persen_diskon4, a.rp_diskon4, a.persen_pajak, a.rp_pajak
                a.rp_harga_akhir")
            ->where("d.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataGR as $recGR) {
                $docGR=11; //Goods Receipt
                //FIFO Header
                if ($insert == TRUE) {
                    $dataStokFifo= StokFifo::where("kd_lokasi",$recGR->kd_lokasi)
                    ->where("kd_bahan",$recGR->kd_bahan)
                    ->where("satuan",$recGR->satuan)
                    ->where("base_type",$docGR)
                    ->where("base_doc_key",$recGR->doc_key)
                    ->where("base_dtl2_key",$recGR->dtl2_key)->first();
                } else {
                    $dataStokFifo= StokFifo::where("stok_fifo_key",$recGR->stok_fifo_key)->first();
                }
                if (!$dataStokFifo) {
                    $dataStokFifo= new StokFifo();
                    $dataStokFifo->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                    $dataStokFifo->kd_lokasi = $recGR->kd_lokasi;
                    $dataStokFifo->kd_bahan = $recGR->kd_bahan;
                    $dataStokFifo->satuan = $recGR->satuan;
                    $dataStokFifo->tgl_doc = $recGR->tgl_doc;
                    $dataStokFifo->qty_on_hand = $recGR->qty;
                    $dataStokFifo->qty_in = $recGR->qty;
                    $dataStokFifo->rp_harga = $recGR->rp_harga;
                    $dataStokFifo->base_type = $docGR;
                    $dataStokFifo->base_doc_key = $recGR->doc_key;
                    $dataStokFifo->base_dtl2_key = $recGR->dtl2_key;
                    $dataStokFifo->save();
                }
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
                $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recGR->kd_lokasi)
                    ->where("kd_bahan",$recGR->kd_bahan)
                    ->where("satuan",$recGR->satuan)
                    ->where("base_type",$docGR)
                    ->where("base_doc_key",$recGR->doc_key)
                    ->where("base_dtl2_key",$recGR->dtl2_key)->first();
                if (!datastokfifoDtl) {
                    $dataStokFifoDtl= new StokFifoDtl();
                    $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    $dataStokFifoDtl->stok_fifo_key = $stokFifoKey;
                    $dataStokFifoDtl->kd_lokasi = $recGR->kd_lokasi;
                    $dataStokFifoDtl->kd_bahan = $recGR->kd_bahan;
                    $dataStokFifoDtl->satuan = $recGR->satuan;
                    $dataStokFifoDtl->tgl_doc = $recGR->tgl_doc;
                    $dataStokFifoDtl->qty = $recGR->qty;
                    $dataStokFifoDtl->rp_harga = $recGR->rp_harga;
                    $dataStokFifoDtl->base_type = $docGR;
                    $dataStokFifoDtl->base_doc_key = $recGR->doc_key;
                    $dataStokFifoDtl->base_dtl2_key = $recGR->dtl2_key;
                    $dataStokFifoDtl->save();
                }
            }
        }
        //var_dump($recPO1->doc_key,$rp_sisa);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $dataTrans1= $data['t_gr1'];
        $dataTrans2= $data['t_gr2'];
        $dataTrans3= $data['t_gr3'];

        DB::beginTransaction();
        try {
            $bInsert = FALSE;
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'kd_partner'=>'bail|required',
            ],[
                'kd_partner.required'=>'Kode Supplier harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $gr1= GR1::where('doc_key',$where['doc_key'])->first();
            if (!($gr1)) {
                $bInsert = TRUE;
                $gr1= new GR1();
                $gr1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $gr1->no_doc         = $dataTrans1['no_doc'];
            $gr1->tgl_doc        = $dataTrans1['tgl_doc'];
            $gr1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $gr1->no_referensi   = $dataTrans1['no_referensi'];
            $gr1->lama_bayar     = $dataTrans1['lama_bayar'];
            $gr1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $gr1->kd_partner     = $dataTrans1['kd_partner'];
            $gr1->kd_kontak      = $dataTrans1['kd_kontak'];
            $gr1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $gr1->persen_diskon  = $dataTrans1['persen_diskon'];
            $gr1->rp_diskon      = $dataTrans1['rp_diskon'];
            $gr1->persen_pajak   = $dataTrans1['persen_pajak'];
            $gr1->rp_pajak       = $dataTrans1['rp_pajak'];
            $gr1->persen_biaya   = $dataTrans1['persen_biaya'];
            $gr1->rp_biaya       = $dataTrans1['rp_biaya'];
            $gr1->rp_rounding    = $dataTrans1['rp_rounding'];
            $gr1->rp_total       = $dataTrans1['rp_total'];
            $gr1->rp_uangmuka    = $dataTrans1['rp_uangmuka'];
            $gr1->rp_bayar       = $dataTrans1['rp_bayar'];
            $gr1->rp_sisa        = $dataTrans1['rp_sisa'];
            $gr1->tgl_datang     = $dataTrans1['tgl_datang'];
            $gr1->tgl_berlaku    = $dataTrans1['tgl_berlaku'];
            $gr1->kd_buyer       = $dataTrans1['kd_buyer'];
            $gr1->catatan        = $dataTrans1['catatan'];
            $gr1->catatan_jurnal = $dataTrans1['catatan_jurnal'];
            $gr1->enum_tipe_po   = $dataTrans1['enum_tipe_po'];
            $gr1->fl_rounding    = $dataTrans1['fl_rounding'];
            $gr1->fl_tutup       = $dataTrans1['fl_tutup'];
            $gr1->fl_batal       = $dataTrans1['fl_batal'];
            $gr1->fl_trds        = $dataTrans1['fl_trds'];
            //$gr1->fl_approved    = $dataTrans1['fl_approved'];
            $gr1->create_tgl     = $dataTrans1['create_tgl'];
            $gr1->create_userid  = $dataTrans1['create_userid'];
            $gr1->create_lokasi  = $dataTrans1['create_lokasi'];
            $gr1->update_tgl     = $dataTrans1['update_tgl'];
            $gr1->update_userid  = $dataTrans1['update_userid'];
            $gr1->update_lokasi  = $dataTrans1['update_lokasi'];
            $gr1->batal_tgl      = $dataTrans1['batal_tgl'];
            $gr1->batal_userid   = $dataTrans1['batal_userid'];
            $gr1->batal_lokasi   = $dataTrans1['batal_lokasi'];
            $gr1->nm_partner     = $dataTrans1['nm_partner'];
            $gr1->alamat_inv     = $dataTrans1['alamat_inv'];
            $gr1->telp_inv       = $dataTrans1['telp_inv'];
            $gr1->nm_kontak      = $dataTrans1['nm_kontak'];
            $gr1->cetak          = $dataTrans1['cetak'];
            $gr1->nm_kirim       = $dataTrans1['nm_kirim'];
            $gr1->alamat_kirim   = $dataTrans1['alamat_kirim'];
            $gr1->save();

            //Data GR2
            //GR2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans2 as $recPO2) {
                $validator=Validator::make($recPO2,[
                    'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                //Update PO2
                $po2 = PO2::where('dtl2_key',$recPO2['base_ref'])->first();
                if ($po2) {
                    $po2->qty_sisa = $po2->qty_sisa - $recPO2['qty'];
                    $po2->save();
                }

                $gr2 = GR2::where('dtl2_key',$recPO2['dtl2_key'])->first();
                if (!($gr2)) {
                    $gr2 = new GR2();
                    $gr2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $gr2->doc_key        = $gr1->doc_key;
                $gr2->no_urut        = $recPO2['no_urut'];
                $gr2->kd_bahan       = $recPO2['kd_bahan'];
                $gr2->satuan         = $recPO2['satuan'];
                $gr2->qty            = $recPO2['qty'];
                $gr2->rp_harga       = $recPO2['rp_harga'];
                $gr2->persen_diskon  = $recPO2['persen_diskon'];
                $gr2->rp_diskon      = $recPO2['rp_diskon'];
                $gr2->persen_diskon2 = $recPO2['persen_diskon2'];
                $gr2->rp_diskon2     = $recPO2['rp_diskon2'];
                $gr2->persen_diskon3 = $recPO2['persen_diskon3'];
                $gr2->rp_diskon3     = $recPO2['rp_diskon3'];
                $gr2->persen_diskon4 = $recPO2['persen_diskon4'];
                $gr2->rp_diskon4     = $recPO2['rp_diskon4'];
                $gr2->kd_pajak       = $recPO2['kd_pajak'];
                $gr2->persen_pajak   = $recPO2['persen_pajak'];
                $gr2->rp_pajak       = $recPO2['rp_pajak'];
                $gr2->rp_harga_akhir = $recPO2['rp_harga_akhir'];
                $gr2->qty_sisa       = $recPO2['qty_sisa'];
                $gr2->catatan        = $recPO2['catatan'];
                $gr2->fl_tutup       = $recPO2['fl_tutup'];
                $gr2->base_type      = $recPO2['base_type'];
                $gr2->base_ref       = $recPO2['base_ref'];
                $gr2->base_no_doc    = $recPO2['base_no_doc'];
                $gr2->save();
            }

            //Data GR3
            //GR3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans3 as $recPO3) {
                $validator=Validator::make($recPO3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                //Update PO3
                $po3 = PO3::where('dtl3_key',$recPO3['base_ref'])->first();
                if ($po3) {
                    $po3->rp_sisa = $po3->rp_sisa - $recPO3['rp_bayar'];
                    $po3->save();
                }

                $gr3 = GR3::where('dtl3_key',$recPO3['dtl3_key'])->first();
                if (!($gr3)) {
                    $gr3 = new PO3();
                    $gr3->dtl3_key = DocNoController::getDocKey('dtl3_key');
                }
                $gr3->doc_key        = $gr1->doc_key;
                $gr3->no_urut        = $recPO3['no_urut'];
                $gr3->no_account     = $recPO3['no_account'];
                $gr3->nm_account     = $recPO3['nm_account'];
                $gr3->catatan        = $recPO3['catatan'];
                $gr3->rp_bayar       = $recPO3['rp_bayar'];
                $gr3->rp_sisa        = $recPO3['rp_sisa'];
                $gr3->base_type      = $recPO3['base_type'];
                $gr3->base_ref       = $recPO3['base_ref'];
                $gr3->save();
            }

            GoodsReceiptController::updateLinkData($gr1->doc_key);
            GoodsReceiptController::updateStok($gr1->doc_key, $bInsert);

            DB::commit();
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
