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
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Http\Controllers\Tools\DocNoController;

class PurchaseRequestController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $data['t_pr1']= PR1::from('t_pr1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_uangmuka, a.rp_bayar, a.rp_sisa,
            a.tgl_datang, a.tgl_berlaku, a.kd_buyer, a.catatan, a.catatan_jurnal, a.enum_tipe_po,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim,
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

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

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
            a.qty_sisa, a.catatan, a.fl_tutup, a.base_type, a.base_ref")
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
        PR3::where('doc_key',$doc_key)->delete();
        PR2::where('doc_key',$doc_key)->delete();
        PR1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $recTrans1= $data['t_pr1'];
        $recTrans2= $data['t_pr2'];
        $recTrans3= $data['t_pr3'];

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

            $pr1= PR1::where('doc_key',$where['doc_key'])->first();
            if (!($pr1)) {
                $pr1= new PR1();
                $pr1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $pr1->no_doc         = $recTrans1['no_doc'];
            $pr1->tgl_doc        = $recTrans1['tgl_doc'];
            $pr1->kd_lokasi      = $recTrans1['kd_lokasi'];
            $pr1->no_referensi   = $recTrans1['no_referensi'];
            $pr1->lama_bayar     = $recTrans1['lama_bayar'];
            $pr1->tgl_bayar      = $recTrans1['tgl_bayar'];
            $pr1->kd_partner     = $recTrans1['kd_partner'];
            $pr1->kd_kontak      = $recTrans1['kd_kontak'];
            $pr1->rp_total_awal  = $recTrans1['rp_total_awal'];
            $pr1->persen_diskon  = $recTrans1['persen_diskon'];
            $pr1->rp_diskon      = $recTrans1['rp_diskon'];
            $pr1->persen_pajak   = $recTrans1['persen_pajak'];
            $pr1->rp_pajak       = $recTrans1['rp_pajak'];
            $pr1->persen_biaya   = $recTrans1['persen_biaya'];
            $pr1->rp_biaya       = $recTrans1['rp_biaya'];
            $pr1->rp_rounding    = $recTrans1['rp_rounding'];
            $pr1->rp_total       = $recTrans1['rp_total'];
            $pr1->rp_uangmuka    = $recTrans1['rp_uangmuka'];
            $pr1->rp_bayar       = $recTrans1['rp_bayar'];
            $pr1->rp_sisa        = $recTrans1['rp_sisa'];
            $pr1->tgl_datang     = $recTrans1['tgl_datang'];
            $pr1->tgl_berlaku    = $recTrans1['tgl_berlaku'];
            $pr1->kd_buyer       = $recTrans1['kd_buyer'];
            $pr1->catatan        = $recTrans1['catatan'];
            $pr1->catatan_jurnal = $recTrans1['catatan_jurnal'];
            $pr1->enum_tipe_po   = $recTrans1['enum_tipe_po'];
            $pr1->fl_rounding    = $recTrans1['fl_rounding'];
            $pr1->fl_tutup       = $recTrans1['fl_tutup'];
            $pr1->fl_batal       = $recTrans1['fl_batal'];
            $pr1->fl_trds        = $recTrans1['fl_trds'];
            $pr1->fl_approved    = $recTrans1['fl_approved'];
            $pr1->create_tgl     = $recTrans1['create_tgl'];
            $pr1->create_userid  = $recTrans1['create_userid'];
            $pr1->create_lokasi  = $recTrans1['create_lokasi'];
            $pr1->update_tgl     = $recTrans1['update_tgl'];
            $pr1->update_userid  = $recTrans1['update_userid'];
            $pr1->update_lokasi  = $recTrans1['update_lokasi'];
            $pr1->batal_tgl      = $recTrans1['batal_tgl'];
            $pr1->batal_userid   = $recTrans1['batal_userid'];
            $pr1->batal_lokasi   = $recTrans1['batal_lokasi'];
            $pr1->nm_partner     = $recTrans1['nm_partner'];
            $pr1->alamat_inv     = $recTrans1['alamat_inv'];
            $pr1->telp_inv       = $recTrans1['telp_inv'];
            $pr1->nm_kontak      = $recTrans1['nm_kontak'];
            $pr1->cetak          = $recTrans1['cetak'];
            $pr1->nm_kirim       = $recTrans1['nm_kirim'];
            $pr1->alamat_kirim   = $recTrans1['alamat_kirim'];
            $pr1->save();

            //Data PR2
            PR2::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
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

                $pr2 = PR2::where('dtl2_key',$lineTrans2['dtl2_key'])->first();
                if (!($pr2)) {
                    $pr2 = new PR2();
                    $pr2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $pr2->doc_key        = $pr1->doc_key;
                $pr2->no_urut        = $lineTrans2['no_urut'];
                $pr2->kd_bahan       = $lineTrans2['kd_bahan'];
                $pr2->satuan         = $lineTrans2['satuan'];
                $pr2->qty            = $lineTrans2['qty'];
                $pr2->rp_harga       = $lineTrans2['rp_harga'];
                $pr2->persen_diskon  = $lineTrans2['persen_diskon'];
                $pr2->rp_diskon      = $lineTrans2['rp_diskon'];
                $pr2->persen_diskon2 = $lineTrans2['persen_diskon2'];
                $pr2->rp_diskon2     = $lineTrans2['rp_diskon2'];
                $pr2->persen_diskon3 = $lineTrans2['persen_diskon3'];
                $pr2->rp_diskon3     = $lineTrans2['rp_diskon3'];
                $pr2->persen_diskon4 = $lineTrans2['persen_diskon4'];
                $pr2->rp_diskon4     = $lineTrans2['rp_diskon4'];
                $pr2->kd_pajak       = $lineTrans2['kd_pajak'];
                $pr2->persen_pajak   = $lineTrans2['persen_pajak'];
                $pr2->rp_pajak       = $lineTrans2['rp_pajak'];
                $pr2->rp_harga_akhir = $lineTrans2['rp_harga_akhir'];
                $pr2->qty_sisa       = $lineTrans2['qty_sisa'];
                $pr2->catatan        = $lineTrans2['catatan'];
                $pr2->fl_tutup       = $lineTrans2['fl_tutup'];
                $pr2->base_type      = $lineTrans2['base_type'];
                $pr2->base_ref       = $lineTrans2['base_ref'];
                $pr2->save();
            }

            //Data PR3
            PR3::where('doc_key',$where['doc_key'])->delete(); //Hapus data existing
            foreach($recTrans3 as $lineTrans3) {
                $validator=Validator::make($lineTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $pr3 = PR3::where('dtl3_key',$lineTrans3['dtl3_key'])->first();
                if (!($pr3)) {
                    $pr3 = new PR3();
                    $pr3->dtl3_key = DocNoController::getDocKey('dtl3_key');
                }
                $pr3->doc_key        = $pr1->doc_key;
                $pr3->no_urut        = $lineTrans3['no_urut'];
                $pr3->no_account     = $lineTrans3['no_account'];
                $pr3->nm_account     = $lineTrans3['nm_account'];
                $pr3->catatan        = $lineTrans3['catatan'];
                $pr3->rp_bayar       = $lineTrans3['rp_bayar'];
                $pr3->rp_sisa        = $lineTrans3['rp_sisa'];
                $pr3->base_type      = $lineTrans3['base_type'];
                $pr3->base_ref       = $lineTrans3['base_ref'];
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
