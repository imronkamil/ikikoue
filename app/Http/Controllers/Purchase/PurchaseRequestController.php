<?php

namespace App\Http\Controllers\Purchase;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Purchase\PR1;
use App\Models\Purchase\PR2;
use App\Models\Purchase\PR3;
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

class PurchaseRequestController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $subQ1= DB::table('t_pr2 as a')
        ->leftJoin('t_po2 as b','a.dtl2_key','=','b.base_ref')
        ->selectRaw('DISTINCT a.doc_key, b.doc_key AS po_doc_key');
        //->groupBy('a.doc_key','b.doc_key');
        $query1= DB::table('t_po1 as a')
        ->joinSub($subQ1,'b', function ($join) {
            $join->on('a.doc_key','=','b.po_doc_key');
        })
        ->selectRaw("b.doc_key, string_agg(a.no_doc,', ') AS no_doc_po")
        ->where('a.fl_batal','false')
        ->groupBy('b.doc_key');
        //->get();

        $data['t_pr1']= PR1::from('t_pr1 as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim,
            b.no_doc_po,
            CASE WHEN a.enum_tipe_po='0' THEN 'Aktif'
                 WHEN a.enum_tipe_po='1' THEN 'Closed'
                 WHEN a.enum_tipe_po='2' THEN 'Sebagian'
                 WHEN a.enum_tipe_po='3' THEN 'Kadaluarsa' END AS ket_enum_tipe_po")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        //->orderBy($sortBy,$sorting)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
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
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
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
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_pr1']= PR1::from('t_pr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_pr1']) ? $data['t_pr1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_pr1']= PR1::from('t_pr1 as a')
        ->join('t_pr2 as b','a.doc_key','=','b.doc_key')
        ->join('t_po2 as c','b.dtl2_key','=','c.base_ref')
        ->join('t_po1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_pr1'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //Master Pajak
        $subPajak= DB::table(DB::raw("(SELECT CAST(NULL AS varchar(20)) AS kd_pajak,
            '(null)'::varchar AS nm_pajak, 0 AS persen_pajak, 'true'::boolean AS fl_aktif) AS b"));
        $pajak= Pajak::from('m_pajak')
        ->selectRaw("kd_pajak, nm_pajak, persen_pajak, fl_aktif");
        $data['m_pajak']= $subPajak->union($pajak)
        //->orderBy(DB::raw("COALESCE(kd_pajak,''), kd_pajak"))
        //->orderBy(DB::raw("CAST(kd_pajak AS varchar)"))
        //->orderBy(DB::raw("kd_pajak nulls first"))
        ->orderByRaw("kd_pajak NULLS FIRST")
        //->orderBy("kd_pajak")
        ->get();

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

        //PR2
        $data['t_pr2']= PR2::from('t_pr2 as a')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga,
            a.persen_diskon, a.rp_diskon, a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3,
            a.persen_diskon4, a.rp_diskon4, a.kd_pajak, a.persen_pajak, a.rp_pajak, a.rp_harga_akhir,
            a.qty_sisa, a.catatan, a.fl_tutup, a.base_type, a.base_ref, a.konversi, a.satuan_dasar")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //PR3
        $data['t_pr3']= PR3::from('t_pr3 as a')
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
        PR3::where('doc_key',$doc_key)->delete();
        PR2::where('doc_key',$doc_key)->delete();
        PR1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $pr1= PR1::where('doc_key',$doc_key)->first();
        if ($pr1) {
            $pr1->catatan = $catatan . "\n" . $pr1->catatan;
            $pr1->fl_batal = 'true';
            $pr1->batal_tgl = date('Y-m-d H:i:s');
            $pr1->batal_userid = $request->userid;
            $pr1->batal_lokasi = $request->lokasi;
            $pr1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_pr1'];
        $dataTrans2= $data['t_pr2'];
        $dataTrans3= $data['t_pr3'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($dataTrans1,[
                'kd_lokasi'=>'bail|required',
            ],[
                'kd_lokasi.required'=>'Lokasi harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $pr1= PR1::where('doc_key',$doc_key)->first();
            if (!($pr1)) {
                $pr1= new PR1();
                $pr1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $pr1->no_doc         = $dataTrans1['no_doc'];
            $pr1->tgl_doc        = $dataTrans1['tgl_doc'];
            $pr1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $pr1->no_referensi   = $dataTrans1['no_referensi'];
            $pr1->lama_bayar     = $dataTrans1['lama_bayar'];
            $pr1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $pr1->kd_partner     = $dataTrans1['kd_partner'];
            $pr1->kd_kontak      = $dataTrans1['kd_kontak'];
            $pr1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $pr1->persen_diskon  = $dataTrans1['persen_diskon'];
            $pr1->rp_diskon      = $dataTrans1['rp_diskon'];
            $pr1->persen_pajak   = $dataTrans1['persen_pajak'];
            $pr1->rp_pajak       = $dataTrans1['rp_pajak'];
            $pr1->persen_biaya   = $dataTrans1['persen_biaya'];
            $pr1->rp_biaya       = $dataTrans1['rp_biaya'];
            $pr1->rp_rounding    = $dataTrans1['rp_rounding'];
            $pr1->rp_total       = $dataTrans1['rp_total'];
            $pr1->rp_uangmuka    = $dataTrans1['rp_uangmuka'];
            $pr1->rp_bayar       = $dataTrans1['rp_bayar'];
            $pr1->rp_sisa        = $dataTrans1['rp_sisa'];
            $pr1->tgl_datang     = $dataTrans1['tgl_datang'];
            $pr1->tgl_berlaku    = $dataTrans1['tgl_berlaku'];
            $pr1->kd_buyer       = $dataTrans1['kd_buyer'];
            $pr1->catatan        = $dataTrans1['catatan'];
            $pr1->catatan_jurnal = $dataTrans1['catatan_jurnal'];
            $pr1->enum_tipe_po   = $dataTrans1['enum_tipe_po'];
            $pr1->fl_rounding    = $dataTrans1['fl_rounding'];
            $pr1->fl_tutup       = $dataTrans1['fl_tutup'];
            $pr1->fl_batal       = $dataTrans1['fl_batal'];
            $pr1->fl_trds        = $dataTrans1['fl_trds'];
            $pr1->fl_approved    = $dataTrans1['fl_approved'];
            $pr1->create_tgl     = $dataTrans1['create_tgl'];
            $pr1->create_userid  = $dataTrans1['create_userid'];
            $pr1->create_lokasi  = $dataTrans1['create_lokasi'];
            $pr1->update_tgl     = $dataTrans1['update_tgl'];
            $pr1->update_userid  = $dataTrans1['update_userid'];
            $pr1->update_lokasi  = $dataTrans1['update_lokasi'];
            $pr1->batal_tgl      = $dataTrans1['batal_tgl'];
            $pr1->batal_userid   = $dataTrans1['batal_userid'];
            $pr1->batal_lokasi   = $dataTrans1['batal_lokasi'];
            $pr1->nm_partner     = $dataTrans1['nm_partner'];
            $pr1->alamat_inv     = $dataTrans1['alamat_inv'];
            $pr1->telp_inv       = $dataTrans1['telp_inv'];
            $pr1->nm_kontak      = $dataTrans1['nm_kontak'];
            $pr1->cetak          = $dataTrans1['cetak'];
            $pr1->nm_kirim       = $dataTrans1['nm_kirim'];
            $pr1->alamat_kirim   = $dataTrans1['alamat_kirim'];
            $pr1->save();

            //Data PR2
            $existingIds = PR2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            PR2::whereIn('dtl2_key', $toDelete)->delete();

            //Data PR2
            //PR2::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                $pr2 = PR2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($pr2)) {
                    $pr2 = new PR2();
                    $pr2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $pr2->doc_key        = $pr1->doc_key;
                $pr2->no_urut        = $recTrans2['no_urut'];
                $pr2->kd_bahan       = $recTrans2['kd_bahan'];
                $pr2->satuan         = $recTrans2['satuan'];
                $pr2->qty            = $recTrans2['qty'];
                $pr2->rp_harga       = $recTrans2['rp_harga'];
                $pr2->persen_diskon  = $recTrans2['persen_diskon'];
                $pr2->rp_diskon      = $recTrans2['rp_diskon'];
                $pr2->persen_diskon2 = $recTrans2['persen_diskon2'];
                $pr2->rp_diskon2     = $recTrans2['rp_diskon2'];
                $pr2->persen_diskon3 = $recTrans2['persen_diskon3'];
                $pr2->rp_diskon3     = $recTrans2['rp_diskon3'];
                $pr2->persen_diskon4 = $recTrans2['persen_diskon4'];
                $pr2->rp_diskon4     = $recTrans2['rp_diskon4'];
                $pr2->kd_pajak       = $recTrans2['kd_pajak'];
                $pr2->persen_pajak   = $recTrans2['persen_pajak'];
                $pr2->rp_pajak       = $recTrans2['rp_pajak'];
                $pr2->rp_harga_akhir = $recTrans2['rp_harga_akhir'];
                $pr2->qty_sisa       = $recTrans2['qty_sisa'];
                $pr2->catatan        = $recTrans2['catatan'];
                $pr2->fl_tutup       = $recTrans2['fl_tutup'];
                $pr2->base_type      = $recTrans2['base_type'];
                $pr2->base_ref       = $recTrans2['base_ref'];
                $pr2->konversi       = $recTrans2['konversi'];
                $pr2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $pr2->save();
            }

            //Data PR3
            $existingIds = PR3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            PR3::whereIn('dtl3_key', $toDelete)->delete();

            //Data PR3
            //PR3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $pr3 = PR3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($pr3)) {
                    $pr3 = new PR3();
                    $pr3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $pr3->doc_key        = $pr1->doc_key;
                $pr3->no_urut        = $recTrans3['no_urut'];
                $pr3->no_account     = $recTrans3['no_account'];
                $pr3->nm_account     = $recTrans3['nm_account'];
                $pr3->catatan        = $recTrans3['catatan'];
                $pr3->rp_bayar       = $recTrans3['rp_bayar'];
                $pr3->rp_sisa        = $recTrans3['rp_sisa'];
                $pr3->base_type      = $recTrans3['base_type'];
                $pr3->base_ref       = $recTrans3['base_ref'];
                $pr3->save();
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
