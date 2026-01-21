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
use App\Models\Purchase\APInvoice1;
use App\Models\Purchase\APInvoice2;
use App\Models\Purchase\APInvoice3;
use App\Models\Purchase\APInvoice4;
use App\Models\Finance\APDP1;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Term;
use App\Models\Master\Supplier;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Models\Tools\PasUsers;
use App\Models\Tools\PasObjects;
use App\Models\Tools\PasAccess;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Models\Accounting\AccountDtl;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class PurchaseInvoiceController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table('t_ap_invoice2 as a')
        ->leftJoin('t_gr2 as b','b.dtl2_key','=','a.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS gr_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query1= DB::table('t_gr1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.gr_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_gr")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');
        //->get();

        $subQ2= DB::table('t_ap_invoice1 as a')
        ->leftJoin('t_bank_keluar2 as b','a.doc_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS bo_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query2= DB::table('t_bank_keluar1 as a')
        ->joinSub($subQ2,'b', function ($join) {
            $join->on('a.doc_key','=','b.bo_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_bo")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');

        $subQ3= DB::table('t_ap_invoice2 as a')
        ->leftJoin('t_po2 as b','b.dtl2_key','=','a.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS po_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query3= DB::table('t_po1 as a')
        ->joinSub($subQ3,'b', function ($join) {
            $join->on('a.doc_key','=','b.po_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_po")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');
        //->get();

        $data['t_ap_invoice1']= APInvoice1::from('t_ap_invoice1 as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->leftJoinSub($query2,'c','a.doc_key','=','c.doc_key')
        ->leftJoinSub($query3,'d','a.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim,
            COALESCE(b.no_doc_gr,d.no_doc_po) AS no_doc_po, c.no_doc_bo")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['t_ap_invoice1']= APInvoice1::from('t_gr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_invoice1']= APInvoice1::from('t_gr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_invoice1']= APInvoice1::from('t_ap_invoice1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_ap_invoice1']) ? $data['t_ap_invoice1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_invoice1']= APInvoice1::from('t_ap_invoice1 as a')
        //->join('t_ap_invoice2 as b','a.doc_key','=','b.doc_key') //tidak perlu join ap_invoice2
        ->join('t_bank_keluar2 as c','a.doc_key','=','c.base_ref')
        ->join('t_bank_keluar1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_ap_invoice1'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
    }

    public function getListGR(Request $request) {
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '0';
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_gr2 as a')
        ->leftJoin('t_ap_invoice2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS ap_doc_key');
        $subQ2= DB::table('t_gr3 as a')
        ->leftJoin('t_ap_invoice3 as b','a.dtl3_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS ap_doc_key');

        $data['t_gr']= GR1::from('t_gr1 as a')
        ->leftJoinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->leftJoinSub($subQ2,'c', function ($join) {
            $join->on('a.doc_key','=','c.doc_key');
        })
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.tgl_datang, a.kd_lokasi, a.kd_partner, a.nm_partner,
            a.rp_total, a.catatan, COALESCE(a.fl_tutup,a.fl_batal,FALSE) AS fl_cek")
        ->when($kd_partner !== '0', function ($query) use ($kd_partner) {
            $query->where('a.kd_partner', $kd_partner);
        })
        ->when($kd_lokasi !== '0', function ($query) use ($kd_lokasi) {
            $query->where('a.kd_lokasi', $kd_lokasi);
        })
        ->where(DB::raw('COALESCE(a.fl_batal,false)'),false)
        ->where(function ($query1) use ($doc_key) {
            $query1->where(DB::raw('COALESCE(a.fl_tutup,false)'),false)
            ->orwhere(function ($query2) use ($doc_key) {
                $query2->where(DB::raw('COALESCE(a.fl_tutup,false)'),true)
                    ->where("b.ap_doc_key",$doc_key);
            });
        })
        ->orderBy("a.tgl_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemGR(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        //GR1
        $data['t_gr1']= GR1::from('t_gr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->where("a.doc_key",$doc_key)
        ->first();

        //GR2
        $data['t_gr2']= GR1::from('t_gr1 as a')
        ->leftJoin('t_gr2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_ap_invoice2 as c', function($join) {
            $join->on('b.dtl2_key', '=', 'c.base_ref')
                 ->where('c.base_type',11); //11=GR
        })
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
        ->whereIn('a.doc_key',$doc_key)
        ->where(DB::raw('b.qty + COALESCE(c.qty, 0)'), '>', 0)
        ->get();

        //GR3
        $data['t_gr3']= GR1::from('t_gr1 as a')
        ->leftJoin('t_gr3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.kd_partner, a.no_doc,
            b.dtl3_key, b.doc_key, b.no_urut, b.no_account, b.nm_account, b.catatan,
            b.rp_bayar, b.rp_sisa, b.base_type, b.base_ref")
        ->whereIn('a.doc_key',$doc_key)
        ->where(DB::raw('COALESCE(b.rp_sisa,0)'),'>',0)
        ->get();

        $response['message']='Success';
        return response()->success('Success',$data);
    }

    public function getItemPO(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];

        //PO1
        $data['t_po1']= PO1::from('t_po1 as a')
        ->selectRaw("*")
        ->where("a.doc_key",$doc_key)
        ->first();

        //PO2
        $data['t_po2']= PO1::from('t_po1 as a')
        ->leftJoin('t_po2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_ap_invoice2 as c', function($join) {
            $join->on('b.dtl2_key', '=', 'c.base_ref')
                 ->where('c.base_type',10); //10=PO
        })
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
        ->whereIn('a.doc_key',$doc_key)
        ->where(DB::raw('b.qty + COALESCE(c.qty, 0)'), '>', 0)
        ->get();

        //PO3
        $data['t_po3']= PO1::from('t_po1 as a')
        ->leftJoin('t_po3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.kd_partner, a.no_doc, b.*")
        ->whereIn('a.doc_key',$doc_key)
        ->where(DB::raw('COALESCE(b.rp_sisa,0)'),'>',0)
        ->get();

        $response['message']='Success';
        return response()->success('Success',$data);
    }

    public function getInvoiceDPRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '';
        $jenis=isset($request->jenis) ? $request->jenis : 0;
        $base_type=20; //Purchase Invoice DP

        //APInvoice4
        $data['t_ap_invoice4']= APInvoice4::from('t_ap_invoice4 as a')
        ->rightJoin('t_apdp1 as b', function($join) use ($doc_key) {
            $join->on('a.base_ref','=','b.doc_key');
            $join->where('a.doc_key','=',$doc_key);
        })
        ->leftJoin('m_supplier as c','b.kd_partner','=','c.kd_supplier')
        ->selectRaw("a.dtl4_key, a.doc_key, ".$base_type." as base_type, b.doc_key as base_ref,
            COALESCE(a.rp_jumlah,0) AS rp_jumlah, b.no_doc AS base_ref2,
            b.no_doc AS no_doc_apdp, b.tgl_doc AS tgl_doc_apdp, b.kd_partner AS kd_partner_apdp,
            c.nm_supplier AS nm_partner_apdp, b.rp_total AS rp_total_apdp, COALESCE(a.rp_jumlah,0)+COALESCE(b.rp_sisa,0) AS rp_sisa_apdp")
        ->where("b.kd_partner",$kd_partner)
        ->where(DB::raw('b.rp_sisa + COALESCE(a.rp_jumlah,0)'),'>',0)
        ->orderBy("b.tgl_doc","desc")
        ->orderBy("b.no_doc","desc")
        ->get();

        if ($jenis==1) {
            return response()->success('Success',$data);
        } else {
            return $data['t_ap_invoice4'];
        }
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;
        $user_id=isset($request->user_id) ? $request->user_id : '';
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '';
        $jenis=isset($request->jenis) ? $request->jenis : 0;

        //PasAccess
        $pasGrup= PasUsers::from('pas_users as a')
        ->selectRaw('a.kd_grup')
        ->where('a.user_id','=',$user_id);
        $data['pas_access']= PasAccess::from('pas_access as a')
        ->leftJoin('pas_objects as b','a.menu_id','=','b.menu_id')
        ->leftJoin('pas_users as c','a.user_id','=','c.user_id')
        ->selectRaw('a.kd_menu')
        ->where('b.x_group','=',10)
        ->where('a.x_select','=','true')
        ->where('b.kd_menu','like','PI%')
        ->where(function ($query) use ($user_id, $pasGrup) {
            $query->where('a.user_id', '=', $user_id)
                ->orWhereIn('a.user_id', $pasGrup);
        })
        ->get();

        //APInvoice1
        $data['t_ap_invoice1']= APInvoice1::from('t_ap_invoice1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->where("a.doc_key",$doc_key)
        ->first();

        //APInvoice2
        $data['t_ap_invoice2']= APInvoice2::from('t_ap_invoice2 as a')
        ->leftJoin('t_gr2 as b','a.base_ref','b.dtl2_key')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga,
            a.persen_diskon, a.rp_diskon, a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3,
            a.persen_diskon4, a.rp_diskon4, a.kd_pajak, a.persen_pajak, a.rp_pajak, a.rp_harga_akhir,
            a.catatan, a.fl_tutup, a.fl_retur, a.qty_retur,
            a.base_type, a.base_ref, a.base_no_doc, a.konversi, a.satuan_dasar,
            a.qty_po, a.qty_gr, b.qty_sisa+a.qty AS qty_sisa_po")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //APInvoice3
        $data['t_ap_invoice3']= APInvoice3::from('t_ap_invoice3 as a')
        ->selectRaw("a.dtl3_key, a.doc_key, a.no_urut, a.no_account, a.nm_account, a.catatan,
            a.rp_bayar, a.rp_sisa, a.base_type, a.base_ref")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //APInvoice4
        $data['t_ap_invoice4']= PurchaseInvoiceController::getInvoiceDPRef($request);

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

        //Master Bahan
        $data['m_bahan']= Bahan::from('m_bahan as a')
        ->leftJoin('m_grup_bahan as b','a.kd_grup_bahan','=','b.kd_grup_bahan')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client, b.acc_stok,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy('a.nm_bahan')
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

        //Master Term
        $data['m_term']= Term::from('m_term')
        ->selectRaw("kd_term, nm_term, disc_hari, net_hari, fl_cod, persen_disc, fl_aktif")
        ->orderBy("kd_term")
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
        ->leftJoin('m_supplier_grup as b','a.kd_supplier_grup','=','b.kd_supplier_grup')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi, b.no_account")
        ->where("a.fl_aktif","true")
        ->orderBy("a.kd_supplier")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        PurchaseInvoiceController::updateLinkData($doc_key,FALSE);
        APInvoice3::where('doc_key',$doc_key)->delete();
        APInvoice2::where('doc_key',$doc_key)->delete();
        APInvoice1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $apInvoice1= APInvoice1::where('doc_key',$doc_key)->first();
        if ($apInvoice1) {
            if ($apInvoice1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            PurchaseInvoiceController::updateLinkData($doc_key,FALSE);
            //Update gr1
            $apInvoice1->catatan = $catatan . "\n" . $apInvoice1->catatan;
            $apInvoice1->fl_batal = 'true';
            $apInvoice1->batal_tgl = date('Y-m-d H:i:s');
            $apInvoice1->batal_userid = $request->userid;
            $apInvoice1->batal_lokasi = $request->lokasi;
            $apInvoice1->save();
        }
        $response['message'] = $doc_key;
        $response['message1'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //GR1
            $dataGR= GR1::from("t_gr1 as a")
            ->leftJoin("t_gr2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_gr3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key")
            ->where("d.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->get();
            foreach($dataGR as $recGR) {
                $updateGR1= GR1::from("t_gr1 as a")
                ->leftJoin("t_gr2 as b","a.doc_key","=","b.doc_key")
                ->leftJoin("t_gr3 as c","a.doc_key","=","c.doc_key")
                ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
                ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
                ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
                ->where("a.doc_key",$recGR->doc_key)
                ->where("d.doc_key",$doc_key)
                ->get();
                foreach($updateGR1 as $updGR1) {
                    //Update GR2
                    $gr2 = GR2::where('dtl2_key',$updGR1->dtl2_key)->first();
                    if ($gr2) {
                        $gr2->qty_sisa = $gr2->qty_sisa + $updGR1->qty;
                        $gr2->save();
                    }
                    //Update GR3
                    $gr3 = GR3::where('dtl3_key',$updGR1->dtl3_key)->first();
                    if ($gr3) {
                        $gr3->rp_sisa = $gr3->rp_sisa + $updGR1->rp_bayar;
                        $gr3->save();
                    }
                }
                //Update GR1
                $qty_sisa= GR2::where('doc_key',$recGR->doc_key)->sum('qty_sisa');
                $rp_sisa= GR3::where('doc_key',$recGR->doc_key)->sum('rp_sisa');
                $gr1= GR1::where("doc_key",$recGR->doc_key)->first();
                if ($gr1) {
                    if ($qty_sisa == 0 && $rp_sisa == 0) {
                        $gr1->fl_tutup= TRUE;
                    } else {
                        $gr1->fl_tutup= FALSE;
                    };
                    $gr1->save();
                }
            }
            //PO1
            $dataPO= PO1::from("t_po1 as a")
            ->leftJoin("t_po2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_po3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key")
            ->where("d.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->get();
            foreach($dataPO as $recPO) {
                $updatePO1= PO1::from("t_po1 as a")
                ->leftJoin("t_po2 as b","a.doc_key","=","b.doc_key")
                ->leftJoin("t_po3 as c","a.doc_key","=","c.doc_key")
                ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
                ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
                ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
                ->where("a.doc_key",$recPO->doc_key)
                ->where("d.doc_key",$doc_key)
                ->get();
                foreach($updatePO1 as $updPO1) {
                    //Update PO2
                    $po2 = PO2::where('dtl2_key',$updPO1->dtl2_key)->first();
                    if ($po2) {
                        $po2->qty_sisa = $po2->qty_sisa + $updPO1->qty;
                        $po2->save();
                    }
                    //Update PO3
                    $po3 = PO3::where('dtl3_key',$updPO1->dtl3_key)->first();
                    if ($po3) {
                        $po3->rp_sisa = $po3->rp_sisa + $updPO1->rp_bayar;
                        $po3->save();
                    }
                }
                //Update PO1
                $qty= PO2::where('doc_key',$recPO->doc_key)->sum('qty');
                $qty_sisa= PO2::where('doc_key',$recPO->doc_key)->sum('qty_sisa');
                $rp_sisa= PO3::where('doc_key',$recPO->doc_key)->sum('rp_sisa');
                $po1 = PO1::where("doc_key",$recPO->doc_key)->first();
                if ($po1) {
                    if ($qty_sisa == 0 && $rp_sisa == 0) {
                        $po1->fl_tutup= TRUE;
                    } else {
                        $po1->fl_tutup= FALSE;
                    };
                    if ($qty_sisa == 0) {
                        $po1->enum_tipe_po = 1; //Complete
                    } elseif ($qty_sisa == $qty) {
                        $po1->enum_tipe_po = 0; //Aktif
                    } else {
                        $po1->enum_tipe_po = 2; //Sebagian
                    }
                    $po1->save();
                }
            }
            //APDeposit1
            $dataAPDP1= APDP1::from("t_apdp1 as a")
            ->leftJoin("t_ap_invoice4 as b","a.doc_key","=","b.base_ref")
            ->leftJoin("t_ap_invoice1 as c","b.doc_key","=","c.doc_key")
            ->selectRaw("a.doc_key, b.dtl4_key, b.rp_jumlah")
            ->where("c.doc_key",$doc_key)
            ->where("b.base_type",20) //AP Deposit
            ->get();
            foreach($dataAPDP1 as $recAPDP1) {
                //Update APDeposit1
                $apDeposit = APDP1::where('doc_key',$recAPDP1->doc_key)->first();
                if ($apDeposit) {
                    if ($apDeposit->rp_sisa + $recAPDP1->rp_jumlah == 0) {
                        $apDeposit->fl_tutup = TRUE;
                    } else {
                        $apDeposit->fl_tutup = FALSE;
                    }
                    $apDeposit->rp_sisa = $apDeposit->rp_sisa + $recAPDP1->rp_jumlah;
                    $apDeposit->save();
                }
            }
        } elseif ($insert == TRUE) {
            //GR1
            $dataGR= GR1::from("t_gr1 as a")
            ->leftJoin("t_gr2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_gr3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key")
            ->where("d.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->get();
            foreach($dataGR as $recGR) {
                $updateGR1= GR1::from("t_gr1 as a")
                ->leftJoin("t_gr2 as b","a.doc_key","=","b.doc_key")
                ->leftJoin("t_gr3 as c","a.doc_key","=","c.doc_key")
                ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
                ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
                ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
                ->where("a.doc_key",$recGR->doc_key)
                ->where("d.doc_key",$doc_key)
                ->get();
                foreach($updateGR1 as $updGR1) {
                    //Update GR2
                    $gr2 = GR2::where('dtl2_key',$updGR1->dtl2_key)->first();
                    if ($gr2) {
                        $gr2->qty_sisa = $gr2->qty_sisa - $updGR1->qty;
                        $gr2->save();
                    }
                    //Update GR3
                    $gr3 = GR3::where('dtl3_key',$updGR1->dtl3_key)->first();
                    if ($gr3) {
                        $gr3->rp_sisa = $gr3->rp_sisa - $updGR1->rp_bayar;
                        $gr3->save();
                    }
                }
                //Update GR1
                $qty_sisa= GR2::where('doc_key',$recGR->doc_key)->sum('qty_sisa');
                $rp_sisa= GR3::where('doc_key',$recGR->doc_key)->sum('rp_sisa');
                $gr1= GR1::where("doc_key",$recGR->doc_key)->first();
                if ($gr1) {
                    if ($qty_sisa == 0 && $rp_sisa == 0) {
                        $gr1->fl_tutup= TRUE;
                    } else {
                        $gr1->fl_tutup= FALSE;
                    };
                    $gr1->save();
                }
            }
            //PO1
            $dataPO= PO1::from("t_po1 as a")
            ->leftJoin("t_po2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_po3 as c","a.doc_key","=","c.doc_key")
            ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
            ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
            ->selectRaw("a.doc_key")
            ->where("d.doc_key",$doc_key)
            ->groupBy("a.doc_key")
            ->get();
            foreach($dataPO as $recPO) {
                $updatePO1= PO1::from("t_po1 as a")
                ->leftJoin("t_po2 as b","a.doc_key","=","b.doc_key")
                ->leftJoin("t_po3 as c","a.doc_key","=","c.doc_key")
                ->leftJoin("t_ap_invoice2 as d","b.dtl2_key","=","d.base_ref")
                ->leftJoin("t_ap_invoice3 as e","c.dtl3_key","=","e.base_ref")
                ->selectRaw("a.doc_key, b.dtl2_key, c.dtl3_key, b.qty_sisa, c.rp_sisa, d.qty, e.rp_bayar")
                ->where("a.doc_key",$recPO->doc_key)
                ->where("d.doc_key",$doc_key)
                ->get();
                foreach($updatePO1 as $updPO1) {
                    //Update PO2
                    $po2 = PO2::where('dtl2_key',$updPO1->dtl2_key)->first();
                    if ($po2) {
                        $po2->qty_sisa = $po2->qty_sisa - $updPO1->qty;
                        $po2->save();
                    }
                    //Update PO3
                    $po3 = PO3::where('dtl3_key',$updPO1->dtl3_key)->first();
                    if ($po3) {
                        $po3->rp_sisa = $po3->rp_sisa - $updPO1->rp_bayar;
                        $po3->save();
                    }
                }
                //Update PO1
                $qty= PO2::where('doc_key',$recPO->doc_key)->sum('qty');
                $qty_sisa= PO2::where('doc_key',$recPO->doc_key)->sum('qty_sisa');
                $rp_sisa= PO3::where('doc_key',$recPO->doc_key)->sum('rp_sisa');
                $po1 = PO1::where("doc_key",$recPO->doc_key)->first();
                if ($po1) {
                    if ($qty_sisa == 0 && $rp_sisa == 0) {
                        $po1->fl_tutup= TRUE;
                    } else {
                        $po1->fl_tutup= FALSE;
                    };
                    if ($qty_sisa == 0) {
                        $po1->enum_tipe_po = 1; //Complete
                    } elseif ($qty_sisa == $qty) {
                        $po1->enum_tipe_po = 0; //Aktif
                    } else {
                        $po1->enum_tipe_po = 2; //Sebagian
                    }
                    $po1->save();
                }
            }
            //APDeposit1
            $dataAPDP1= APDP1::from("t_apdp1 as a")
            ->leftJoin("t_ap_invoice4 as b","a.doc_key","=","b.base_ref")
            ->leftJoin("t_ap_invoice1 as c","b.doc_key","=","c.doc_key")
            ->selectRaw("a.doc_key, b.dtl4_key, b.rp_jumlah")
            ->where("c.doc_key",$doc_key)
            ->where("b.base_type",20) //AP Deposit
            ->get();
            foreach($dataAPDP1 as $recAPDP1) {
                //Update APDeposit1
                $apDeposit = APDP1::where('doc_key',$recAPDP1->doc_key)->first();
                if ($apDeposit) {
                    if ($apDeposit->rp_sisa - $recAPDP1->rp_jumlah == 0) {
                        $apDeposit->fl_tutup = TRUE;
                    } else {
                        $apDeposit->fl_tutup = FALSE;
                    }
                    $apDeposit->rp_sisa = $apDeposit->rp_sisa - $recAPDP1->rp_jumlah;
                    $apDeposit->save();
                }
            }
        }
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //APInvoice1
            $dataAPInv1= APInvoice1::from("t_ap_invoice1 as a")
            ->join("t_ap_invoice2 as b","a.doc_key","=","b.doc_key")
            ->join("t_gr2 as c","b.base_ref","=","c.dtl2_key")
            ->selectRaw("a.doc_key, b.dtl2_key, b.qty, b.rp_harga, b.konversi, b.satuan_dasar, c.dtl2_key as dtl2_key_gr")
            ->where("a.doc_key",$doc_key)
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataAPInv1 as $recAPInv1) {
                //Update GR2
                $gr2 = GR2::where('dtl2_key',$recAPInv1->dtl2_key_gr)->first();
                if ($gr2) {
                    $gr2->rp_harga = $recAPInv1->rp_harga;
                    $gr2->rp_harga_akhir = $recAPInv1->rp_harga * $gr2->qty;
                    $gr2->save();
                }
                $stokFifo = StokFifo::where('base_dtl2_key',$recAPInv1->dtl2_key_gr)->first();
                if ($stokFifo) {
                    $stokFifo->rp_harga = $recAPInv1->rp_harga/$recAPInv1->konversi;
                    $stokFifo->save();
                }
            }
            //var_dump($gr_doc_key,$qty,$qty_sisa,$rp_sisa);
        } elseif ($insert == TRUE) {
            //APInvoice1
            $dataAPInv1= APInvoice1::from("t_ap_invoice1 as a")
            ->join("t_ap_invoice2 as b","a.doc_key","=","b.doc_key")
            ->join("t_gr2 as c","b.base_ref","=","c.dtl2_key")
            ->selectRaw("a.doc_key, a.tgl_doc, b.dtl2_key, b.kd_bahan, b.qty, b.rp_harga,
                b.konversi, b.satuan_dasar, c.dtl2_key as dtl2_key_gr")
            ->where("a.doc_key",$doc_key)
            ->orderBy("a.doc_key")
            ->get();
            foreach($dataAPInv1 as $recAPInv1) {
                //Update GR2
                $gr2 = GR2::where('dtl2_key',$recAPInv1->dtl2_key_gr)->first();
                if ($gr2) {
                    $gr2->rp_harga = $recAPInv1->rp_harga;
                    $gr2->rp_harga_akhir = $recAPInv1->rp_harga * $gr2->qty;
                    $gr2->save();
                }
                $stokFifo = StokFifo::where('base_dtl2_key',$recAPInv1->dtl2_key_gr)->first();
                if ($stokFifo) {
                    $stokFifo->rp_harga = $recAPInv1->rp_harga/$recAPInv1->konversi;
                    $stokFifo->save();
                }
                //Bahan Satuan
                $bahanSatuan= BahanSatuan::where("kd_bahan",$recAPInv1->kd_bahan)->get();
                foreach($bahanSatuan as $recBahanSatuan) {
                    if ($recBahanSatuan->tgl_beli_akhir <= $recAPInv1->tgl_doc) {
                        $harga = $recAPInv1->rp_harga * ($recBahanSatuan->rasio/$recAPInv1->konversi);
                        $recBahanSatuan->tgl_beli_akhir = $recAPInv1->tgl_doc;
                        $recBahanSatuan->rp_harga_beli_akhir = $harga;
                        if ($recBahanSatuan->rp_harga_beli_min > $harga || $recBahanSatuan->rp_harga_beli_min <= 0) {
                            $recBahanSatuan->rp_harga_beli_min = $harga;
                        }
                        if ($recBahanSatuan->rp_harga_beli_max < $harga) {
                            $recBahanSatuan->rp_harga_beli_max = $harga;
                        }
                        $recBahanSatuan->save();
                    }
                }
            }
            //var_dump($gr_doc_key,$qty,$qty_sisa,$rp_sisa);
        }

        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public function setJurnal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $user_id=isset($request->user_id) ? $request->user_id : '';
        PurchaseInvoiceController::generateJurnal($doc_key, $user_id);
        $response['message'] = 'Set jurnal berhasil';
        return response()->success('Success',$response);
    }

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal Pembelian
        $subJ1= DB::table('t_ap_invoice2 as a')
        ->selectRaw("a.doc_key, SUM(a.rp_harga_akhir) AS rp_harga_akhir")
        ->where("a.doc_key",$doc_key)
        ->groupBy("a.doc_key");
        $jurnal= APInvoice1::from('t_ap_invoice1 as a')
        ->leftJoinSub($subJ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.doc_key');
        })
        ->leftJoin('m_supplier as c','a.kd_partner','=','c.kd_supplier')
        ->leftJoin('m_supplier_grup as d','c.kd_supplier_grup','=','d.kd_supplier_grup')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon,
            a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            b.rp_harga_akhir AS rp_harga_akhir_bahan,
            c.nm_supplier AS nm_partner, d.no_account AS no_account_supplier")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Debet (Pembelian)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_harga_akhir_bahan <> 0) {
                $jurnalAP= new AccountDtl();
                $jurnalAP->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalAP->no_account = $recJurnal->no_account_supplier;
                $jurnalAP->kd_lokasi = $recJurnal->kd_lokasi;
                $jurnalAP->enum_debet_kredit = 'D';
                $jurnalAP->rp_debet = $recJurnal->rp_harga_akhir_bahan;
                $jurnalAP->rp_kredit = 0;
                $jurnalAP->tgl_doc = $recJurnal->tgl_doc;
                $jurnalAP->catatan = $recJurnal->nm_partner;
                $jurnalAP->no_ref1 = $recJurnal->no_doc;
                $jurnalAP->no_ref2 = '';
                $jurnalAP->user_id = $user_id;
                $jurnalAP->base_type = 21; //Purchase Invoice
                $jurnalAP->base_doc_key = $recJurnal->doc_key;
                //$jurnalAP->base_dtl_key = $recJurnal->doc_key;
                $jurnalAP->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalAP->save();
            }
        }

        //Jurnal Biaya
        $jurnal= APInvoice1::from('t_ap_invoice1 as a')
        ->join('t_ap_invoice3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            b.dtl3_key, b.no_account, b.nm_account, b.catatan,
            b.rp_bayar AS rp_biaya")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Debet (Biaya)
        foreach($jurnal as $recJurnal) {
            if ($recJurnal->rp_biaya <> 0) {
                $jurnalBiaya= new AccountDtl();
                $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBiaya->no_account = $recJurnal->no_account;
                $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_biaya > 0) {
                    $jurnalBiaya->enum_debet_kredit = 'D';
                    $jurnalBiaya->rp_debet = abs($recJurnal->rp_biaya);
                    $jurnalBiaya->rp_kredit = 0;
                } else {
                    $jurnalBiaya->enum_debet_kredit = 'K';
                    $jurnalBiaya->rp_debet = 0;
                    $jurnalBiaya->rp_kredit = abs($recJurnal->rp_biaya);
                }
                $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBiaya->catatan = $recJurnal->catatan;
                $jurnalBiaya->no_ref1 = $recJurnal->no_doc;
                $jurnalBiaya->no_ref2 = '';
                $jurnalBiaya->user_id = $user_id;
                $jurnalBiaya->base_type = 21; //Purchase Invoice
                $jurnalBiaya->base_doc_key = $recJurnal->doc_key;
                //$jurnalBiaya->base_dtl_key = $recJurnal->doc_key;
                $jurnalBiaya->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBiaya->save();
            }
        }

        //Jurnal PPN, Rounding
        $jurnal= APInvoice1::from('t_ap_invoice1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            a.rp_rounding, a.rp_pajak")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Kredit (PPN)
        foreach($jurnal as $recJurnal) {
            //PPN
            if ($recJurnal->rp_pajak > 0) {
                $jurnalPPN= new AccountDtl();
                $jurnalPPN->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPPN->no_account = UtilityController::getAccountConfig('no_acc_ap_invoice');
                $jurnalPPN->kd_lokasi = $recJurnal->kd_lokasi;
                $jurnalPPN->enum_debet_kredit = 'D';
                $jurnalPPN->rp_debet = $recJurnal->rp_pajak;
                $jurnalPPN->rp_kredit = 0;
                $jurnalPPN->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPPN->catatan = $recJurnal->nm_partner;
                $jurnalPPN->no_ref1 = $recJurnal->no_doc;
                $jurnalPPN->no_ref2 = '';
                $jurnalPPN->user_id = $user_id;
                $jurnalPPN->base_type = 21; //Purchase Invoice
                $jurnalPPN->base_doc_key = $recJurnal->doc_key;
                //$jurnalPPN->base_dtl_key = $recJurnal->doc_key;
                $jurnalPPN->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPPN->save();
            }
            //Rounding
            if ($recJurnal->rp_rounding != 0) {
                $jurnalRounding= new AccountDtl();
                $jurnalRounding->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalRounding->no_account = UtilityController::getAccountConfig('no_acc_ap_invoice_rounding');
                $jurnalRounding->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_rounding > 0) {
                    $jurnalRounding->enum_debet_kredit = 'D';
                    $jurnalRounding->rp_debet = $recJurnal->rp_rounding;
                    $jurnalRounding->rp_kredit = 0;
                } else {
                    $jurnalRounding->enum_debet_kredit = 'K';
                    $jurnalRounding->rp_debet = 0;
                    $jurnalRounding->rp_kredit = abs($recJurnal->rp_rounding);
                }
                $jurnalRounding->tgl_doc = $recJurnal->tgl_doc;
                $jurnalRounding->catatan = $recJurnal->nm_partner;
                $jurnalRounding->no_ref1 = $recJurnal->no_doc;
                $jurnalRounding->no_ref2 = '';
                $jurnalRounding->user_id = $user_id;
                $jurnalRounding->base_type = 21; //Purchase Invoice
                $jurnalRounding->base_doc_key = $recJurnal->doc_key;
                //$jurnalRounding->base_dtl_key = $recJurnal->doc_key;
                $jurnalRounding->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalRounding->save();
            }
        }

        //Jurnal Uang Muka
        $jurnal= APInvoice1::from('t_ap_invoice1 as a')
        ->join('t_ap_invoice4 as b','a.doc_key','=','b.doc_key')
        ->join('t_apdp1 as c','b.base_ref','=','c.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            b.dtl4_key, b.base_type, b.base_ref, b.rp_jumlah, c.no_doc AS no_doc_apdp, c.catatan")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Uang Muka
        foreach($jurnal as $recJurnal) {
            $jurnalDP= new AccountDtl();
            $jurnalDP->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalDP->no_account = UtilityController::getAccountConfig('no_acc_ap_deposit');
            $jurnalDP->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalDP->enum_debet_kredit = 'K';
            $jurnalDP->rp_debet = 0;
            $jurnalDP->rp_kredit = $recJurnal->rp_jumlah;
            $jurnalDP->tgl_doc = $recJurnal->tgl_doc;
            $jurnalDP->catatan = $recJurnal->catatan;
            $jurnalDP->no_ref1 = $recJurnal->no_doc;
            $jurnalDP->no_ref2 = '';
            $jurnalDP->user_id = $user_id;
            $jurnalDP->base_type = 21; //Purchase Invoice
            $jurnalDP->base_doc_key = $recJurnal->doc_key;
            //$jurnalDP->base_dtl_key = $recJurnal->doc_key;
            $jurnalDP->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalDP->save();
        }

        //Jurnal Total Kredit
        $jurnal= APInvoice1::from('t_ap_invoice1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.rp_total, b.nm_supplier AS nm_partner")
        ->where("a.doc_key",$doc_key)
        ->first();
        $recJurnal= $jurnal;
        //Jurnal Total Kredit
        if ($recJurnal->rp_total != 0) {
            $jurnalTotal= new AccountDtl();
            $jurnalTotal->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalTotal->no_account = UtilityController::getAccountConfig('no_acc_ap_invoice');
            $jurnalTotal->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalTotal->enum_debet_kredit = 'K';
            $jurnalTotal->rp_debet = 0;
            $jurnalTotal->rp_kredit = $recJurnal->rp_total;
            $jurnalTotal->tgl_doc = $recJurnal->tgl_doc;
            $jurnalTotal->catatan = $recJurnal->nm_partner;
            $jurnalTotal->no_ref1 = $recJurnal->no_doc;
            $jurnalTotal->no_ref2 = '';
            $jurnalTotal->user_id = $user_id;
            $jurnalTotal->base_type = 21; //Purchase Invoice
            $jurnalTotal->base_doc_key = $recJurnal->doc_key;
            //$jurnalTotal->base_dtl_key = $recJurnal->doc_key;
            $jurnalTotal->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalTotal->save();
        }

        $hasil['m_account_dtl']= AccountDtl::from('m_account_dtl as a')
        ->selectRaw("a.dtl_key, a.no_account, a.kd_lokasi, a.enum_debet_kredit,
            a.rp_debet, a.rp_kredit , a.tgl_doc, a.catatan,
            a.no_ref1, a.no_ref2, a.user_id, a.base_type, a.base_doc_key, a.base_dtl_key, a.kd_project")
        ->where("a.base_doc_key",$doc_key)
        ->orderBy("a.dtl_key")
        ->get();

        $response = $hasil;
        $response['doc_key'] = $doc_key;
        $response['message'] = 'Set GL berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_ap_invoice1'];
        $dataTrans2= $data['t_ap_invoice2'];
        $dataTrans3= $data['t_ap_invoice3'];
        $dataTrans4= $data['t_ap_invoice4'];

        $dataTrans2= array_filter($dataTrans2, function ($item) {
            return isset($item['kd_bahan']) && $item['kd_bahan'] !== null && $item['kd_bahan'] !== '';
        });
        $dataTrans4= array_filter($dataTrans4, function ($item) {
            return isset($item['rp_jumlah']) && $item['rp_jumlah'] > 0;
        });

        DB::beginTransaction();
        try {
            //Data APInvoice1
            $validator=Validator::make($dataTrans1,[
                'kd_partner'=>'bail|required',
            ],[
                'kd_partner.required'=>'Kode Supplier harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $apInvoice1= APInvoice1::where('doc_key',$doc_key)->first();

            if ($apInvoice1) {
                PurchaseInvoiceController::updateLinkData($doc_key, FALSE);
                //Update Stok Harga Beli
                $respStok= PurchaseInvoiceController::updateStok($doc_key, FALSE);
            } else {
                $apInvoice1= new APInvoice1();
                $apInvoice1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $apInvoice1->no_doc         = $dataTrans1['no_doc'];
            $apInvoice1->tgl_doc        = $dataTrans1['tgl_doc'];
            $apInvoice1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $apInvoice1->no_referensi   = $dataTrans1['no_referensi'];
            $apInvoice1->lama_bayar     = $dataTrans1['lama_bayar'];
            //$apInvoice1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $apInvoice1->kd_partner     = $dataTrans1['kd_partner'];
            $apInvoice1->kd_kontak      = $dataTrans1['kd_kontak'];
            $apInvoice1->kd_buyer       = $dataTrans1['kd_buyer'];
            $apInvoice1->kd_term        = $dataTrans1['kd_term'];
            $apInvoice1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $apInvoice1->persen_diskon  = $dataTrans1['persen_diskon'];
            $apInvoice1->rp_diskon      = $dataTrans1['rp_diskon'];
            $apInvoice1->persen_pajak   = $dataTrans1['persen_pajak'];
            $apInvoice1->rp_pajak       = $dataTrans1['rp_pajak'];
            $apInvoice1->persen_biaya   = $dataTrans1['persen_biaya'];
            $apInvoice1->rp_biaya       = $dataTrans1['rp_biaya'];
            $apInvoice1->rp_rounding    = $dataTrans1['rp_rounding'];
            $apInvoice1->rp_total       = $dataTrans1['rp_total'];
            $apInvoice1->rp_dp          = $dataTrans1['rp_dp'];
            $apInvoice1->rp_bayar       = $dataTrans1['rp_bayar'];
            $apInvoice1->rp_sisa        = $dataTrans1['rp_sisa'];
            $apInvoice1->tgl_tempo      = $dataTrans1['tgl_tempo'];
            $apInvoice1->tgl_berlaku    = $dataTrans1['tgl_berlaku'];
            $apInvoice1->catatan        = $dataTrans1['catatan'];
            $apInvoice1->fl_rounding    = $dataTrans1['fl_rounding'];
            $apInvoice1->fl_tutup       = $dataTrans1['fl_tutup'];
            $apInvoice1->fl_batal       = $dataTrans1['fl_batal'];
            $apInvoice1->fl_lunas       = $dataTrans1['fl_lunas'];
            $apInvoice1->cetak          = $dataTrans1['cetak'];
            $apInvoice1->tgl_proses     = $dataTrans1['tgl_proses'];
            $apInvoice1->doc_key_jurnal = $apInvoice1->doc_key;
            $apInvoice1->create_tgl     = $dataTrans1['create_tgl'];
            $apInvoice1->create_userid  = $dataTrans1['create_userid'];
            $apInvoice1->create_lokasi  = $dataTrans1['create_lokasi'];
            $apInvoice1->update_tgl     = $dataTrans1['update_tgl'];
            $apInvoice1->update_userid  = $dataTrans1['update_userid'];
            $apInvoice1->update_lokasi  = $dataTrans1['update_lokasi'];
            $apInvoice1->no_account     = $dataTrans1['no_account'];
            $apInvoice1->nm_partner     = $dataTrans1['nm_partner'];
            $apInvoice1->alamat_inv     = $dataTrans1['alamat_inv'];
            $apInvoice1->telp_inv       = $dataTrans1['telp_inv'];
            $apInvoice1->nm_kontak      = $dataTrans1['nm_kontak'];
            $apInvoice1->cetak          = $dataTrans1['cetak'];
            $apInvoice1->nm_kirim       = $dataTrans1['nm_kirim'];
            $apInvoice1->alamat_kirim   = $dataTrans1['alamat_kirim'];
            $apInvoice1->save();

            //Data APInvoice2
            $existingIds = APInvoice2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            APInvoice2::whereIn('dtl2_key', $toDelete)->delete();

            //APInvoice2::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                $apInvoice2 = APInvoice2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($apInvoice2)) {
                    $apInvoice2 = new APInvoice2();
                    $apInvoice2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $apInvoice2->doc_key        = $apInvoice1->doc_key;
                $apInvoice2->no_urut        = $recTrans2['no_urut'];
                $apInvoice2->kd_bahan       = $recTrans2['kd_bahan'];
                $apInvoice2->satuan         = $recTrans2['satuan'];
                $apInvoice2->qty            = $recTrans2['qty'];
                $apInvoice2->rp_harga       = $recTrans2['rp_harga'];
                $apInvoice2->persen_diskon  = $recTrans2['persen_diskon'];
                $apInvoice2->rp_diskon      = $recTrans2['rp_diskon'];
                $apInvoice2->persen_diskon2 = $recTrans2['persen_diskon2'];
                $apInvoice2->rp_diskon2     = $recTrans2['rp_diskon2'];
                $apInvoice2->persen_diskon3 = $recTrans2['persen_diskon3'];
                $apInvoice2->rp_diskon3     = $recTrans2['rp_diskon3'];
                $apInvoice2->persen_diskon4 = $recTrans2['persen_diskon4'];
                $apInvoice2->rp_diskon4     = $recTrans2['rp_diskon4'];
                $apInvoice2->kd_pajak       = $recTrans2['kd_pajak'];
                $apInvoice2->persen_pajak   = $recTrans2['persen_pajak'];
                $apInvoice2->rp_pajak       = $recTrans2['rp_pajak'];
                $apInvoice2->rp_harga_akhir = $recTrans2['rp_harga_akhir'];
                $apInvoice2->catatan        = $recTrans2['catatan'];
                $apInvoice2->base_type      = $recTrans2['base_type'];
                $apInvoice2->base_ref       = $recTrans2['base_ref'];
                $apInvoice2->fl_tutup       = $recTrans2['fl_tutup'];
                $apInvoice2->fl_update_stok = $recTrans2['fl_update_stok'];
                $apInvoice2->fl_retur       = $recTrans2['fl_retur'];
                $apInvoice2->qty_retur      = $recTrans2['qty_retur'];
                $apInvoice2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $apInvoice2->no_account     = $recTrans2['no_account'];
                $apInvoice2->tgl_kadaluarsa = $recTrans2['tgl_kadaluarsa'];
                $apInvoice2->base_no_doc    = $recTrans2['base_no_doc'];
                $apInvoice2->konversi       = $recTrans2['konversi'];
                $apInvoice2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $apInvoice2->qty_po         = $recTrans2['qty_po'];
                $apInvoice2->qty_gr         = $recTrans2['qty_gr'];
                $apInvoice2->save();
            }

            //Data APInvoice3
            $existingIds = APInvoice3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans3)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            APInvoice3::whereIn('dtl3_key', $toDelete)->delete();

            //APInvoice3::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $apInvoice3 = APInvoice3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($apInvoice3)) {
                    $apInvoice3 = new APInvoice3();
                    $apInvoice3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $apInvoice3->doc_key        = $apInvoice1->doc_key;
                $apInvoice3->no_urut        = $recTrans3['no_urut'];
                $apInvoice3->no_account     = $recTrans3['no_account'];
                $apInvoice3->nm_account     = $recTrans3['nm_account'];
                $apInvoice3->catatan        = $recTrans3['catatan'];
                $apInvoice3->rp_bayar       = $recTrans3['rp_bayar'];
                $apInvoice3->rp_sisa        = $recTrans3['rp_sisa'];
                $apInvoice3->base_type      = $recTrans3['base_type'];
                $apInvoice3->base_ref       = $recTrans3['base_ref'];
                $apInvoice3->save();
            }

            //Data APInvoice4
            $existingIds = APInvoice4::where('doc_key',$doc_key)->pluck('dtl4_key')->toArray();
            $newIds = collect($dataTrans4)->pluck('dtl4_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            APInvoice4::whereIn('dtl4_key', $toDelete)->delete();

            //APInvoice4::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans4 as $recTrans4) {
                $validator=Validator::make($recTrans4,[
                    'rp_jumlah'=>'bail|required',
                ],[
                    'rp_jumlah.required'=>'Rp Jumlah harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $apInvoice4 = APInvoice4::where('dtl4_key',$recTrans4['dtl4_key'])->first();
                if (!($apInvoice4)) {
                    $apInvoice4 = new APInvoice4();
                    $apInvoice4->dtl4_key = DocNoController::getDocKey('doc_key');
                }
                $apInvoice4->doc_key        = $apInvoice1->doc_key;
                $apInvoice4->base_type      = $recTrans4['base_type'];
                $apInvoice4->base_ref       = $recTrans4['base_ref'];
                $apInvoice4->rp_jumlah      = $recTrans4['rp_jumlah'];
                $apInvoice4->base_ref2      = $recTrans4['base_ref2'];
                $apInvoice4->save();
            }

            PurchaseInvoiceController::updateLinkData($apInvoice1->doc_key, TRUE);
            PurchaseInvoiceController::updateStok($apInvoice1->doc_key, TRUE);
            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                PurchaseInvoiceController::generateJurnal($apInvoice1->doc_key, $user_id);
            }

            DB::commit();
            //$response['resp1'] = $resp1;
            //$response['doc_key'] = $apInvoice1->doc_key;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
