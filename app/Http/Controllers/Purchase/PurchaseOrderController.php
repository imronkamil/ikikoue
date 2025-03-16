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
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;

class PurchaseOrderController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
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
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
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

    public function getListPR(Request $request) {
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '0';
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $subQ1= DB::table('t_pr2 as a')
        ->leftJoin('t_po2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS po_doc_key');

        $data['t_pr']= PR1::from('t_pr1 as a')
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
                    ->where("b.po_doc_key",$doc_key);
            });
        })
        ->orderBy("a.tgl_doc","desc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getItemPR(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : [];
        $data['t_pr2']= PR1::from('t_pr1 as a')
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

        $data['t_pr3']= PR1::from('t_pr1 as a')
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
            a.qty_sisa, a.catatan, a.fl_tutup, a.base_type, a.base_ref, a.base_no_doc")
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
        PO3::where('doc_key',$doc_key)->delete();
        PO2::where('doc_key',$doc_key)->delete();
        PO1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0) {
        //PR1
        $list_pr1= PR1::from("t_pr1 as a")
        ->leftJoin("t_pr2 as b","a.doc_key","=","b.doc_key")
        ->leftJoin("t_pr3 as c","a.doc_key","=","c.doc_key")
        ->leftJoin("t_po2 as d","b.dtl2_key","=","d.base_ref")
        ->selectRaw("a.doc_key")
        ->where("d.doc_key",$doc_key)
        ->groupBy("a.doc_key")
        ->get();
        foreach($list_pr1 as $lineTrans1) {
            //PR2
            $data_pr2= PR2::where("doc_key",$lineTrans1->doc_key)->get();
            $qty_sisa= 0;
            foreach($data_pr2 as $lineTrans2) {
                $qty_sisa = $qty_sisa + $lineTrans2->qty_sisa;
            }
            //PR3
            $data_pr3= PR3::where("doc_key",$lineTrans1->doc_key)->get();
            $rp_sisa= 0;
            foreach($data_pr3 as $lineTrans3) {
                $rp_sisa = $rp_sisa + $lineTrans3->rp_sisa;
            }
            //Update PR1
            $data_pr1= PR1::where("doc_key",$lineTrans1->doc_key)->first();
            if ($data_pr1) {
                if($qty_sisa == 0 && $rp_sisa == 0) {
                    $data_pr1->fl_tutup= TRUE;
                } else {
                    $data_pr1->fl_tutup= FALSE;
                }
                $data_pr1->save();
            }
        }
        //var_dump($lineTrans1->doc_key,$rp_sisa);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $recTrans1= $data['t_po1'];
        $recTrans2= $data['t_po2'];
        $recTrans3= $data['t_po3'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($recTrans1,[
                'kd_partner'=>'bail|required',
            ],[
                'kd_partner.required'=>'Kode Supplier harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $po1= PO1::where('doc_key',$where['doc_key'])->first();
            if (!($po1)) {
                $po1= new PO1();
                $po1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $po1->no_doc         = $recTrans1['no_doc'];
            $po1->tgl_doc        = $recTrans1['tgl_doc'];
            $po1->kd_lokasi      = $recTrans1['kd_lokasi'];
            $po1->no_referensi   = $recTrans1['no_referensi'];
            $po1->lama_bayar     = $recTrans1['lama_bayar'];
            $po1->tgl_bayar      = $recTrans1['tgl_bayar'];
            $po1->kd_partner     = $recTrans1['kd_partner'];
            $po1->kd_kontak      = $recTrans1['kd_kontak'];
            $po1->rp_total_awal  = $recTrans1['rp_total_awal'];
            $po1->persen_diskon  = $recTrans1['persen_diskon'];
            $po1->rp_diskon      = $recTrans1['rp_diskon'];
            $po1->persen_pajak   = $recTrans1['persen_pajak'];
            $po1->rp_pajak       = $recTrans1['rp_pajak'];
            $po1->persen_biaya   = $recTrans1['persen_biaya'];
            $po1->rp_biaya       = $recTrans1['rp_biaya'];
            $po1->rp_rounding    = $recTrans1['rp_rounding'];
            $po1->rp_total       = $recTrans1['rp_total'];
            $po1->rp_uangmuka    = $recTrans1['rp_uangmuka'];
            $po1->rp_bayar       = $recTrans1['rp_bayar'];
            $po1->rp_sisa        = $recTrans1['rp_sisa'];
            $po1->tgl_datang     = $recTrans1['tgl_datang'];
            $po1->tgl_berlaku    = $recTrans1['tgl_berlaku'];
            $po1->kd_buyer       = $recTrans1['kd_buyer'];
            $po1->catatan        = $recTrans1['catatan'];
            $po1->catatan_jurnal = $recTrans1['catatan_jurnal'];
            $po1->enum_tipe_po   = $recTrans1['enum_tipe_po'];
            $po1->fl_rounding    = $recTrans1['fl_rounding'];
            $po1->fl_tutup       = $recTrans1['fl_tutup'];
            $po1->fl_batal       = $recTrans1['fl_batal'];
            $po1->fl_trds        = $recTrans1['fl_trds'];
            //$po1->fl_approved    = $recTrans1['fl_approved'];
            $po1->create_tgl     = $recTrans1['create_tgl'];
            $po1->create_userid  = $recTrans1['create_userid'];
            $po1->create_lokasi  = $recTrans1['create_lokasi'];
            $po1->update_tgl     = $recTrans1['update_tgl'];
            $po1->update_userid  = $recTrans1['update_userid'];
            $po1->update_lokasi  = $recTrans1['update_lokasi'];
            $po1->batal_tgl      = $recTrans1['batal_tgl'];
            $po1->batal_userid   = $recTrans1['batal_userid'];
            $po1->batal_lokasi   = $recTrans1['batal_lokasi'];
            $po1->nm_partner     = $recTrans1['nm_partner'];
            $po1->alamat_inv     = $recTrans1['alamat_inv'];
            $po1->telp_inv       = $recTrans1['telp_inv'];
            $po1->nm_kontak      = $recTrans1['nm_kontak'];
            $po1->cetak          = $recTrans1['cetak'];
            $po1->nm_kirim       = $recTrans1['nm_kirim'];
            $po1->alamat_kirim   = $recTrans1['alamat_kirim'];
            $po1->save();

            //Data PO2
            //PO2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($recTrans2 as $lineTrans2) {
                $validator=Validator::make($lineTrans2,[
                    'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                //Update PR2
                $pr2 = PR2::where('dtl2_key',$lineTrans2['base_ref'])->first();
                if ($pr2) {
                    $pr2->qty_sisa = $pr2->qty_sisa - $lineTrans2['qty'];
                    $pr2->save();
                }

                $po2 = PO2::where('dtl2_key',$lineTrans2['dtl2_key'])->first();
                if (!($po2)) {
                    $po2 = new PO2();
                    $po2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $po2->doc_key        = $po1->doc_key;
                $po2->no_urut        = $lineTrans2['no_urut'];
                $po2->kd_bahan       = $lineTrans2['kd_bahan'];
                $po2->satuan         = $lineTrans2['satuan'];
                $po2->qty            = $lineTrans2['qty'];
                $po2->rp_harga       = $lineTrans2['rp_harga'];
                $po2->persen_diskon  = $lineTrans2['persen_diskon'];
                $po2->rp_diskon      = $lineTrans2['rp_diskon'];
                $po2->persen_diskon2 = $lineTrans2['persen_diskon2'];
                $po2->rp_diskon2     = $lineTrans2['rp_diskon2'];
                $po2->persen_diskon3 = $lineTrans2['persen_diskon3'];
                $po2->rp_diskon3     = $lineTrans2['rp_diskon3'];
                $po2->persen_diskon4 = $lineTrans2['persen_diskon4'];
                $po2->rp_diskon4     = $lineTrans2['rp_diskon4'];
                $po2->kd_pajak       = $lineTrans2['kd_pajak'];
                $po2->persen_pajak   = $lineTrans2['persen_pajak'];
                $po2->rp_pajak       = $lineTrans2['rp_pajak'];
                $po2->rp_harga_akhir = $lineTrans2['rp_harga_akhir'];
                $po2->qty_sisa       = $lineTrans2['qty_sisa'];
                $po2->catatan        = $lineTrans2['catatan'];
                $po2->fl_tutup       = $lineTrans2['fl_tutup'];
                $po2->base_type      = $lineTrans2['base_type'];
                $po2->base_ref       = $lineTrans2['base_ref'];
                $po2->base_no_doc    = $lineTrans2['base_no_doc'];
                $po2->save();
            }

            //Data PO3
            //PO3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($recTrans3 as $lineTrans3) {
                $validator=Validator::make($lineTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                //Update PR3
                $pr3 = PR3::where('dtl3_key',$lineTrans3['base_ref'])->first();
                if ($pr3) {
                    $pr3->rp_sisa = $pr3->rp_sisa - $lineTrans3['rp_bayar'];
                    $pr3->save();
                }

                $po3 = PO3::where('dtl3_key',$lineTrans3['dtl3_key'])->first();
                if (!($po3)) {
                    $po3 = new PO3();
                    $po3->dtl3_key = DocNoController::getDocKey('dtl3_key');
                }
                $po3->doc_key        = $po1->doc_key;
                $po3->no_urut        = $lineTrans3['no_urut'];
                $po3->no_account     = $lineTrans3['no_account'];
                $po3->nm_account     = $lineTrans3['nm_account'];
                $po3->catatan        = $lineTrans3['catatan'];
                $po3->rp_bayar       = $lineTrans3['rp_bayar'];
                $po3->rp_sisa        = $lineTrans3['rp_sisa'];
                $po3->base_type      = $lineTrans3['base_type'];
                $po3->base_ref       = $lineTrans3['base_ref'];
                $po3->save();
            }

            PurchaseOrderController::updateLinkData($po1->doc_key);

            DB::commit();
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
