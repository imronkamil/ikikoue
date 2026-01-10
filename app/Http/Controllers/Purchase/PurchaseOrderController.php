<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Purchase\PR1;
use App\Models\Purchase\PR2;
use App\Models\Purchase\PR3;
use App\Models\Purchase\PO1;
use App\Models\Purchase\PO2;
use App\Models\Purchase\PO3;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Supplier;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class PurchaseOrderController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table('t_po2 as a')
        ->leftJoin('t_pr2 as b','b.dtl2_key','=','a.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS pr_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query1= DB::table('t_po1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.pr_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_pr")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');
        //->get();

        $subQ2= DB::table('t_po2 as a')
        ->leftJoin('t_gr2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS gr_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query2= DB::table('t_gr1 as a')
        ->joinSub($subQ2,'b', function ($join) {
            $join->on('a.doc_key','=','b.gr_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_gr")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');

        $data['t_po1']= PO1::from('t_po1 as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->leftJoinSub($query2,'c','a.doc_key','=','c.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim,
            b.no_doc_pr, c.no_doc_gr,
            CASE WHEN a.enum_tipe_po='0' THEN 'Aktif'
                 WHEN a.enum_tipe_po='1' THEN 'Closed'
                 WHEN a.enum_tipe_po='2' THEN 'Sebagian'
                 WHEN a.enum_tipe_po='3' THEN 'Kadaluarsa'
                 WHEN a.enum_tipe_po='4' THEN 'Closed' END AS ket_enum_tipe_po")
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
        $data['t_po1']= PO1::from('t_po1 as a')
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
        $data['t_po1']= PO1::from('t_po1 as a')
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
        $data['t_po1']= PO1::from('t_po1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_po1']) ? $data['t_po1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_po1']= PO1::from('t_po1 as a')
        ->join('t_po2 as b','a.doc_key','=','b.doc_key')
        ->join('t_gr2 as c','b.dtl2_key','=','c.base_ref')
        ->join('t_gr1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_po1'])>0) ? 'true' : 'false';
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

    public function getListItemPR(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        $data['t_pr']= PO1::from('t_pr1 as a')
        ->join('t_pr2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_po2 as c','b.dtl2_key','c.base_ref')
        ->leftJoin('t_po1 as e','c.doc_key','e.doc_key')
        ->leftJoin('m_bahan as d','b.kd_bahan','d.kd_bahan')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga,
            b.persen_diskon, b.rp_diskon, b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3,
            b.persen_diskon4, b.rp_diskon4, b.kd_pajak, b.persen_pajak, b.rp_pajak, b.rp_harga_akhir,
            b.qty_sisa, b.catatan, b.fl_tutup, b.base_type, b.base_ref, b.konversi, b.satuan_dasar,
            c.doc_key AS doc_key_po, c.rp_harga AS rp_harga_po,
            c.persen_diskon AS persen_diskon_po, c.rp_diskon AS rp_diskon_po,
            c.persen_diskon2 AS persen_diskon2_po, c.rp_diskon2 AS rp_diskon2_po,
            c.persen_diskon3 AS persen_diskon3_po, c.rp_diskon3 AS rp_diskon3_po,
            c.persen_diskon4 AS persen_diskon4_po, c.rp_diskon4 AS rp_diskon4_po,
            c.persen_pajak AS persen_pajak_po, c.rp_pajak AS rp_pajak_po,
            c.rp_harga_akhir AS rp_harga_akhir_po, d.nm_bahan,
            COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0) AS qty_sisa_po,
            COALESCE(a.fl_batal,false) AS fl_cek")
        ->where(DB::raw('COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0)'),'>',0)
        ->where(DB::raw('COALESCE(e.fl_batal,false)'),'=','false')
        ->whereIn('a.doc_key',$doc_key)
        ->orderBy('a.doc_key')
        ->orderBy('b.no_urut')
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemPR(Request $request) {
        $dtl2_key=isset($request->dtl2_key) ? $request->dtl2_key : [];

        //PR1
        /*$data['t_pr1']= PR1::from('t_pr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->whereIn("b.dtl2_key",$dtl2_key)
        ->first();*/

        $data['t_pr2']= PO1::from('t_pr1 as a')
        ->join('t_pr2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_po2 as c','b.dtl2_key','c.base_ref')
        ->leftJoin('t_po1 as e','c.doc_key','e.doc_key')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan, b.qty, b.rp_harga,
            b.persen_diskon, b.rp_diskon, b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3,
            b.persen_diskon4, b.rp_diskon4, b.kd_pajak, b.persen_pajak, b.rp_pajak, b.rp_harga_akhir,
            b.qty_sisa, b.catatan, b.fl_tutup, b.base_type, b.base_ref, b.konversi, b.satuan_dasar,
            c.doc_key AS doc_key_po, c.rp_harga AS rp_harga_po,
            c.persen_diskon AS persen_diskon_po, c.rp_diskon AS rp_diskon_po,
            c.persen_diskon2 AS persen_diskon2_po, c.rp_diskon2 AS rp_diskon2_po,
            c.persen_diskon3 AS persen_diskon3_po, c.rp_diskon3 AS rp_diskon3_po,
            c.persen_diskon4 AS persen_diskon4_po, c.rp_diskon4 AS rp_diskon4_po,
            c.persen_pajak AS persen_pajak_po, c.rp_pajak AS rp_pajak_po,
            c.rp_harga_akhir AS rp_harga_akhir_po,
            COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0) AS qty_sisa_po")
        ->where(DB::raw('COALESCE(b.qty_sisa,0)+COALESCE(c.qty,0)'),'>',0)
        ->where(DB::raw('COALESCE(e.fl_batal,false)'),'=','false')
        ->whereIn("b.dtl2_key",$dtl2_key)
        ->orderBy('a.doc_key')
        ->orderBy('b.no_urut')
        ->get();

        /*$data['t_pr3']= PO1::from('t_pr1 as a')
        ->join('t_pr3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl3_key, b.doc_key, b.no_urut, b.no_account, b.nm_account, b.catatan,
            b.rp_bayar, b.rp_sisa, b.base_type, b.base_ref")
        ->where(DB::raw('COALESCE(b.rp_sisa,0)'),'>',0)
        ->whereIn('a.doc_key',$doc_key)
        ->get();*/

        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //PO1
        $data['t_po1']= PO1::from('t_po1 as a')
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

        //PO2
        $data['t_po2']= PO2::from('t_po2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga,
            a.persen_diskon, a.rp_diskon, a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3,
            a.persen_diskon4, a.rp_diskon4, a.kd_pajak, a.persen_pajak, a.rp_pajak, a.rp_harga_akhir,
            a.qty_sisa, a.catatan, a.fl_tutup, a.base_type, a.base_ref, a.base_no_doc, a.konversi, a.satuan_dasar")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //PO3
        $data['t_po3']= PO3::from('t_po3 as a')
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
        ->get();

        //Master Pajak
        $subPajak= DB::table(DB::raw("(SELECT CAST(NULL AS varchar(20)) AS kd_pajak,
            '(null)'::varchar AS nm_pajak, 0 AS persen_pajak, 'true'::boolean AS fl_aktif) AS b"));
        $data['m_pajak']= Pajak::from('m_pajak')
        ->selectRaw("kd_pajak, nm_pajak, persen_pajak, fl_aktif")
        ->unionAll($subPajak)
        ->orderByRaw("kd_pajak NULLS FIRST")
        ->get();

        //Master Bahan Beli Filter
        $data['m_bahan_beli_filter']= Bahan::from('m_bahan as a')
        ->leftJoin('m_bahan_satuan as b', function ($join) {
            $join->on('a.kd_bahan', '=', 'b.kd_bahan')
                 ->on('a.satuan_beli', '=', 'b.satuan');
        })
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.rp_harga_beli as rp_harga_beli_satuan, b.persen_beli as persen_beli_satuan,
            b.rp_harga_beli_akhir as rp_harga_beli_akhir_satuan")
        ->where("a.fl_aktif","true")
        ->where("a.fl_beli","true")
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
        $data['m_supplier_filter']= Supplier::from('m_supplier as a')
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
        pr3::where('doc_key',$doc_key)->delete();
        pr2::where('doc_key',$doc_key)->delete();
        pr1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $po1= PO1::where('doc_key',$doc_key)->first();
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
                    $qty_sisa = $qty_sisa - $po2->qty_sisa;
                }
                //Update PR3
                $pr3 = PR3::where('dtl3_key',$recPR1->dtl3_key)->first();
                if ($pr3) {
                    $pr3->rp_sisa = $pr3->rp_sisa - $recPR1->rp_bayar;
                    $pr3->save();
                    $rp_sisa = $rp_sisa - $pr3->rp_sisa;
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
        $dataTrans1= $data['t_po1'];
        $dataTrans2= $data['t_po2'];
        $dataTrans3= $data['t_po3'];

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

            PurchaseOrderController::setLinkData($doc_key, FALSE);
            $po1= PO1::where('doc_key',$where['doc_key'])->first();
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
