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
use App\Models\Purchase\APCredit1;
use App\Models\Purchase\APCredit2;
use App\Models\Purchase\APCredit3;
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

class PurchaseReturnController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));

        $data['t_ap_credit1']= APInvoice1::from('t_ap_credit1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
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
        $data['t_ap_credit1']= APInvoice1::from('t_ap_credit1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.tgl_tempo, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_credit1']= APInvoice1::from('t_ap_credit1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.tgl_berlaku, a.kd_buyer, a.catatan,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.doc_key_jurnal,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.cetak, a.nm_kirim, a.alamat_kirim")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_credit1']= APInvoice1::from('t_ap_credit1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_ap_credit1']) ? $data['t_ap_credit1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_ap_credit1']= APInvoice1::from('t_ap_credit1 as a')
        //->join('t_ap_credit2 as b','a.doc_key','=','b.doc_key') //tidak perlu join ap_invoice2
        ->join('t_bank_keluar2 as c','a.doc_key','=','c.base_ref')
        ->join('t_bank_keluar1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_ap_credit1'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
    }

    public function getItemPI(Request $request) {
        /*$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $kd_partner=isset($request->kd_partner) ? $request->kd_partner : '0';
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : '0';*/
        $doc_key   =$request->input('doc_key',0);
        $kd_partner=$request->input('kd_partner','0');
        $kd_lokasi =$request->input('kd_lokasi','0');

        //APInvoice
        $data['t_ap_invoice']= APInvoice1::from('t_ap_invoice1 as a')
        ->leftJoin('t_ap_invoice2 as b','a.doc_key','=','b.doc_key')
        ->leftJoin('t_ap_credit2 as c',function($join) use ($doc_key) {
            $join->on('b.dtl2_key','=','c.base_ref')
                 ->where('c.doc_key','=',$doc_key);
        })
        ->leftJoin('m_bahan as d','b.kd_bahan','=','d.kd_bahan')
        ->leftJoin('m_supplier as e','a.kd_partner','=','e.kd_supplier')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.catatan, a.kd_partner,
            b.dtl2_key, b.doc_key, b.no_urut, b.kd_bahan, b.satuan,
            b.qty-(COALESCE(b.qty_retur,0)-COALESCE(c.qty,0)) AS qty,
            0 AS qty_retur, b.rp_harga,
            b.persen_diskon, b.rp_diskon, b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3,
            b.persen_diskon4, b.rp_diskon4, b.kd_pajak, b.persen_pajak, b.rp_pajak, b.rp_harga_akhir,
            b.catatan, b.fl_tutup, b.base_type, b.base_ref, b.konversi, b.satuan_dasar,
            c.persen_diskon AS persen_diskon_po, c.rp_diskon AS rp_diskon_po,
            c.persen_diskon2 AS persen_diskon2_po, c.rp_diskon2 AS rp_diskon2_po,
            c.persen_diskon3 AS persen_diskon3_po, c.rp_diskon3 AS rp_diskon3_po,
            c.persen_diskon4 AS persen_diskon4_po, c.rp_diskon4 AS rp_diskon4_po,
            c.persen_pajak AS persen_pajak_po, c.rp_pajak AS rp_pajak_po,
            c.doc_key AS doc_key_retur, c.dtl2_key AS dtl2_key_retur,
            c.qty AS qty_retur, d.nm_bahan, e.nm_supplier, FALSE AS fl_cek")
        ->where("a.kd_partner",$kd_partner)
        ->where("a.kd_lokasi",$kd_lokasi)
        ->where(DB::raw('b.qty + COALESCE(b.qty_retur, 0)'), '>', 0)
        ->orderBy("a.tgl_doc","desc")
        ->orderBy("a.no_doc","desc")
        ->orderBy("b.no_urut")
        ->get();

        /*$data1 = $request->json()->all();
        $dataTrans1= $data1['t_ap_credit'];*/
        $dataTrans1 = $request->input('t_ap_credit', []);

        foreach($data['t_ap_invoice'] as $recTrans) {
            $match = collect($dataTrans1)->first(function ($item) use ($recTrans) {
            return $item['base_ref'] == $recTrans->dtl2_key &&
                   $item['kd_bahan'] == $recTrans->kd_bahan;
            });
            if ($match) {
                $recTrans->qty_retur = $match['qty'];
                $recTrans->fl_cek = TRUE;
            } else {
                $recTrans->qty_retur = 0;
            }
        }

        $data['param1']=$kd_partner . $kd_lokasi . $doc_key;
        //$data['param1']=$kd_partner . $kd_lokasi;
        //return response()->success('Success',$response);
        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;

        //APCredit1
        $data['t_ap_credit1']= APCredit1::from('t_ap_credit1 as a')
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

        //APCredit2
        $data['t_ap_credit2']= APCredit2::from('t_ap_credit2 as a')
        ->leftJoin('t_ap_credit2 as b','a.base_ref','b.dtl2_key')
        ->selectRaw("a.dtl2_key, a.doc_key, a.no_urut, a.kd_bahan, a.satuan, a.qty, a.rp_harga,
            a.persen_diskon, a.rp_diskon, a.persen_diskon2, a.rp_diskon2, a.persen_diskon3, a.rp_diskon3,
            a.persen_diskon4, a.rp_diskon4, a.kd_pajak, a.persen_pajak, a.rp_pajak, a.rp_harga_akhir,
            a.catatan, a.fl_tutup, a.base_type, a.base_ref, a.base_no_doc, a.fl_tutup, a.fl_update_stok,
            a.stok_fifo_key, a.konversi, a.satuan_dasar")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //APCredit3
        $data['t_ap_credit3']= APCredit3::from('t_ap_credit3 as a')
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
        APCredit3::where('doc_key',$doc_key)->delete();
        APCredit2::where('doc_key',$doc_key)->delete();
        APCredit1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $apCredit1= APCredit1::where('doc_key',$doc_key)->first();
        if ($apCredit1) {
            if ($apCredit1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            PurchaseReturnController::updateStok($doc_key,FALSE);
            //Update gr1
            $apCredit1->catatan = $catatan . "\n" . $apCredit1->catatan;
            $apCredit1->fl_batal = 'true';
            $apCredit1->batal_tgl = date('Y-m-d H:i:s');
            $apCredit1->batal_userid = $request->userid;
            $apCredit1->batal_lokasi = $request->lokasi;
            $apCredit1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            $dataTrans= APCredit1::from("t_ap_credit1 as a")
            ->leftJoin("t_ap_credit2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.persen_diskon, b.rp_diskon,
                b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3, b.persen_diskon4, b.rp_diskon4,
                b.persen_pajak, b.rp_pajak, b.rp_harga_akhir, b.konversi, b.satuan_dasar, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $docAPcredit=22; //AP Credit Note
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
                        ->where("base_type",$docGR)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                    if ($dataStokFifoDtl) {
                        $dataStokFifoDtl->qty = $dataStokFifoDtl->qty - ($recTrans->qty*$recTrans->konversi);
                        $dataStokFifoDtl->save();
                    }
                }
            }
        } elseif ($insert == TRUE) {
            $dataTrans= APCredit1::from("t_ap_credit1 as a")
            ->leftJoin("t_ap_credit2 as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.rp_harga, b.persen_diskon, b.rp_diskon,
                b.persen_diskon2, b.rp_diskon2, b.persen_diskon3, b.rp_diskon3, b.persen_diskon4, b.rp_diskon4,
                b.persen_pajak, b.rp_pajak, b.rp_harga_akhir, b.konversi, b.satuan_dasar, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $docGR=11; //Goods Receipt
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan_dasar)
                    ->where("base_type",$docGR)
                    ->where("base_doc_key",$recTrans->doc_key)
                    ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                if (!$dataStokFifo) {
                    $dataStokFifo= new StokFifo();
                    $dataStokFifo->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                    $dataGR2= GR2::where("dtl2_key",$recTrans->dtl2_key)->first();
                    if ($dataGR2) {
                        $dataGR2->stok_fifo_key = $dataStokFifo->stok_fifo_key;
                        $dataGR2->save();
                    }
                }
                $dataStokFifo->kd_lokasi = $recTrans->kd_lokasi;
                $dataStokFifo->kd_bahan = $recTrans->kd_bahan;
                $dataStokFifo->satuan = $recTrans->satuan_dasar;
                $dataStokFifo->tgl_doc = $recTrans->tgl_doc;
                $dataStokFifo->qty_on_hand = $dataStokFifo->qty_on_hand + ($recTrans->qty*$recTrans->konversi);
                $dataStokFifo->qty_in = $dataStokFifo->qty_in + ($recTrans->qty*$recTrans->konversi);
                $dataStokFifo->rp_harga = $recTrans->rp_harga;
                $dataStokFifo->base_type = $docGR;
                $dataStokFifo->base_doc_key = $recTrans->doc_key;
                $dataStokFifo->base_dtl2_key = $recTrans->dtl2_key;
                $dataStokFifo->save();
                //FIFO Detail
                $stokFifoKey = $dataStokFifo->stok_fifo_key;
                $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan_dasar)
                    ->where("base_type",$docGR)
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
                $dataStokFifoDtl->base_type = $docGR;
                $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                $dataStokFifoDtl->save();
            }
        }
        //var_dump($recPO1->doc_key,$rp_sisa);
    }

    public static function setLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //APInvoice1
            $dataAPInvoice1= APInvoice1::from("t_ap_invoice1 as a")
            ->leftJoin("t_ap_invoice2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_ap_credit2 as c","b.dtl2_key","=","c.base_ref")
            ->selectRaw("a.doc_key, b.dtl2_key, b.qty_retur, c.qty")
            ->where("c.doc_key",$doc_key)
            //->groupBy("a.doc_key")
            ->get();
            foreach($dataAPInvoice1 as $recAPInvoice1) {
                //Update APInoice2
                $apInvoice2 = APInvoice2::where('dtl2_key',$recAPInvoice1->dtl2_key)->first();
                if ($apInvoice2) {
                    $apInvoice2->qty_retur = $apInvoice2->qty_retur - $recAPInvoice1->qty;
                    if ($apInvoice2->qty_retur == 0) {
                        $apInvoice2->fl_retur = FALSE;
                    }
                    $apInvoice2->save();
                }
            }
        } elseif ($insert == TRUE) {
            $ap_doc_key = 0;
            $qty= 0;
            $qty_retur= 0;
            //APInvoice1
            $dataAPInvoice1= APInvoice1::from("t_ap_invoice1 as a")
            ->leftJoin("t_ap_invoice2 as b","a.doc_key","=","b.doc_key")
            ->leftJoin("t_ap_credit2 as c","b.dtl2_key","=","c.base_ref")
            ->selectRaw("a.doc_key, b.dtl2_key, b.qty_retur, c.qty")
            ->where("c.doc_key",$doc_key)
            //->groupBy("a.doc_key")
            ->get();
            foreach($dataAPInvoice1 as $recAPInvoice1) {
                //Update APInvoice2
                $apInvoice2 = APInvoice2::where('dtl2_key',$recAPInvoice1->dtl2_key)->first();
                if ($apInvoice2) {
                    $apInvoice2->qty_retur = $apInvoice2->qty_retur + $recAPInvoice1->qty;
                    $apInvoice2->fl_retur = TRUE;
                    $apInvoice2->save();
                    $qty = $qty + $apInvoice2->qty;
                    $qty_retur = $qty_retur + $apInvoice2->qty_retur;
                }
            }
            //var_dump($ap_doc_key,$qty,$qty_sisa,$rp_sisa);
        }
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal Piutang Usaha Lainnya
        $jurnal= APCredit1::from('t_ap_credit1 as a')
        ->leftJoin('m_supplier as b','a.kd_partner','=','b.kd_supplier')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.rp_pajak, a.rp_total, b.nm_supplier AS nm_partner")
        ->where("a.doc_key",$doc_key)
        ->first();
        $recJurnal= $jurnal;
        //Jurnal Piutang Usaha Lainnya - Debet
        if ($recJurnal->rp_total != 0) {
            $jurnalPiutang= new AccountDtl();
            $jurnalPiutang->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalPiutang->no_account = UtilityController::getAccountConfig('no_acc_ap_credit');
            $jurnalPiutang->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalPiutang->enum_debet_kredit = 'D';
            $jurnalPiutang->rp_debet = $recJurnal->rp_total;
            $jurnalPiutang->rp_kredit = 0;
            $jurnalPiutang->tgl_doc = $recJurnal->tgl_doc;
            $jurnalPiutang->catatan = $recJurnal->nm_partner;
            $jurnalPiutang->no_ref1 = $recJurnal->no_doc;
            $jurnalPiutang->no_ref2 = '';
            $jurnalPiutang->user_id = $user_id;
            $jurnalPiutang->base_type = 22; //Purchase Return
            $jurnalPiutang->base_doc_key = $recJurnal->doc_key;
            //$jurnalPiutang->base_dtl_key = $recJurnal->doc_key;
            $jurnalPiutang->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalPiutang->save();
        }

        //PPN
        if ($recJurnal->rp_pajak > 0) {
            $jurnalPPN= new AccountDtl();
            $jurnalPPN->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalPPN->no_account = UtilityController::getAccountConfig('no_acc_ap_credit_ppn');
            $jurnalPPN->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalPPN->enum_debet_kredit = 'K';
            $jurnalPPN->rp_debet = 0;
            $jurnalPPN->rp_kredit = $recJurnal->rp_pajak;
            $jurnalPPN->tgl_doc = $recJurnal->tgl_doc;
            $jurnalPPN->catatan = $recJurnal->nm_partner;
            $jurnalPPN->no_ref1 = $recJurnal->no_doc;
            $jurnalPPN->no_ref2 = '';
            $jurnalPPN->user_id = $user_id;
            $jurnalPPN->base_type = 22; //Purchase Return
            $jurnalPPN->base_doc_key = $recJurnal->doc_key;
            //$jurnalPPN->base_dtl_key = $recJurnal->doc_key;
            $jurnalPPN->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalPPN->save();
        }

        //Jurnal Detil Pembelian
        $subJ1= DB::table('t_ap_credit2 as a')
        ->selectRaw("a.doc_key, SUM(a.rp_harga_akhir) AS rp_harga_akhir")
        ->where("a.doc_key",$doc_key)
        ->groupBy("a.doc_key");
        $jurnal= APCredit1::from('t_ap_credit1 as a')
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
        //Jurnal Detil Pembelian - Debet (Harga Pokok)
        foreach($jurnal as $recJurnal) {
            $jurnalAP= new AccountDtl();
            $jurnalAP->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalAP->no_account = UtilityController::getAccountConfig('no_acc_ap_credit_pembelian');
            $jurnalAP->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalAP->enum_debet_kredit = 'K';
            $jurnalAP->rp_debet = 0;
            $jurnalAP->rp_kredit = $recJurnal->rp_harga_akhir_bahan;
            $jurnalAP->tgl_doc = $recJurnal->tgl_doc;
            $jurnalAP->catatan = $recJurnal->nm_partner;
            $jurnalAP->no_ref1 = $recJurnal->no_doc;
            $jurnalAP->no_ref2 = '';
            $jurnalAP->user_id = $user_id;
            $jurnalAP->base_type = 22; //Purchase Return
            $jurnalAP->base_doc_key = $recJurnal->doc_key;
            //$jurnalAP->base_dtl_key = $recJurnal->doc_key;
            $jurnalAP->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalAP->save();
        }

        //Jurnal Biaya
        $jurnal= APCredit1::from('t_ap_credit1 as a')
        ->join('t_ap_credit3 as b','a.doc_key','=','b.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
            b.dtl3_key, b.no_account, b.nm_account, b.catatan,
            b.rp_bayar AS rp_biaya")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Biaya
        foreach($jurnal as $recJurnal) {
            $jurnalBiaya= new AccountDtl();
            $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
            $jurnalBiaya->no_account = $recJurnal->no_account;
            $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
            $jurnalBiaya->enum_debet_kredit = 'K';
            $jurnalBiaya->rp_debet = 0;
            $jurnalBiaya->rp_kredit = $recJurnal->rp_biaya;
            $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
            $jurnalBiaya->catatan = $recJurnal->catatan;
            $jurnalBiaya->no_ref1 = $recJurnal->no_doc;
            $jurnalBiaya->no_ref2 = '';
            $jurnalBiaya->user_id = $user_id;
            $jurnalBiaya->base_type = 22; //Purchase Return
            $jurnalBiaya->base_doc_key = $recJurnal->doc_key;
            //$jurnalBiaya->base_dtl_key = $recJurnal->doc_key;
            $jurnalBiaya->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
            $jurnalBiaya->save();
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
        $dataTrans1= $data['t_ap_credit1'];
        $dataTrans2= $data['t_ap_credit2'];
        $dataTrans3= $data['t_ap_credit3'];

        DB::beginTransaction();
        try {
            //Data APCredit1
            $validator=Validator::make($dataTrans1,[
                'kd_partner'=>'bail|required',
            ],[
                'kd_partner.required'=>'Kode Supplier harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $apCredit1= APCredit1::where('doc_key',$doc_key)->first();

            //Jika edit, kembalikan stok dulu
            if ($apCredit1) {
                if (UtilityController::getAutoStok() == 'true') {
                    PurchaseReturnController::updateStok($doc_key, FALSE);
                }
                //PurchaseReturnController::setLinkData($doc_key, FALSE);
            }
            if (!($apCredit1)) {
                $apCredit1= new APCredit1();
                $apCredit1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $apCredit1->no_doc         = $dataTrans1['no_doc'];
            $apCredit1->tgl_doc        = $dataTrans1['tgl_doc'];
            $apCredit1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $apCredit1->no_referensi   = $dataTrans1['no_referensi'];
            $apCredit1->lama_bayar     = $dataTrans1['lama_bayar'];
            $apCredit1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $apCredit1->kd_partner     = $dataTrans1['kd_partner'];
            $apCredit1->kd_kontak      = $dataTrans1['kd_kontak'];
            $apCredit1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $apCredit1->persen_diskon  = $dataTrans1['persen_diskon'];
            $apCredit1->rp_diskon      = $dataTrans1['rp_diskon'];
            $apCredit1->persen_pajak   = $dataTrans1['persen_pajak'];
            $apCredit1->rp_pajak       = $dataTrans1['rp_pajak'];
            $apCredit1->persen_biaya   = $dataTrans1['persen_biaya'];
            $apCredit1->rp_biaya       = $dataTrans1['rp_biaya'];
            $apCredit1->rp_rounding    = $dataTrans1['rp_rounding'];
            $apCredit1->rp_total       = $dataTrans1['rp_total'];
            $apCredit1->rp_dp          = $dataTrans1['rp_dp'];
            $apCredit1->rp_bayar       = $dataTrans1['rp_bayar'];
            $apCredit1->rp_sisa        = $dataTrans1['rp_sisa'];
            $apCredit1->tgl_tempo      = $dataTrans1['tgl_tempo'];
            $apCredit1->tgl_berlaku    = $dataTrans1['tgl_berlaku'];
            $apCredit1->kd_buyer       = $dataTrans1['kd_buyer'];
            $apCredit1->catatan        = $dataTrans1['catatan'];
            $apCredit1->fl_rounding    = $dataTrans1['fl_rounding'];
            $apCredit1->fl_tutup       = $dataTrans1['fl_tutup'];
            $apCredit1->fl_batal       = $dataTrans1['fl_batal'];
            $apCredit1->fl_lunas       = $dataTrans1['fl_lunas'];
            $apCredit1->cetak          = $dataTrans1['cetak'];
            $apCredit1->tgl_proses     = $dataTrans1['tgl_proses'];
            $apCredit1->doc_key_jurnal = $apCredit1->doc_key;
            $apCredit1->create_tgl     = $dataTrans1['create_tgl'];
            $apCredit1->create_userid  = $dataTrans1['create_userid'];
            $apCredit1->create_lokasi  = $dataTrans1['create_lokasi'];
            $apCredit1->update_tgl     = $dataTrans1['update_tgl'];
            $apCredit1->update_userid  = $dataTrans1['update_userid'];
            $apCredit1->update_lokasi  = $dataTrans1['update_lokasi'];
            $apCredit1->no_account     = $dataTrans1['no_account'];
            $apCredit1->nm_partner     = $dataTrans1['nm_partner'];
            $apCredit1->alamat_inv     = $dataTrans1['alamat_inv'];
            $apCredit1->telp_inv       = $dataTrans1['telp_inv'];
            $apCredit1->nm_kontak      = $dataTrans1['nm_kontak'];
            $apCredit1->cetak          = $dataTrans1['cetak'];
            $apCredit1->nm_kirim       = $dataTrans1['nm_kirim'];
            $apCredit1->alamat_kirim   = $dataTrans1['alamat_kirim'];
            $apCredit1->save();

            //Data APCredit2
            $existingIds = APCredit2::where('doc_key',$doc_key)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            APCredit2::whereIn('dtl2_key', $toDelete)->delete();

            //APCredit2::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                $apCredit2 = APCredit2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($apCredit2)) {
                    $apCredit2 = new APCredit2();
                    $apCredit2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $apCredit2->doc_key        = $apCredit1->doc_key;
                $apCredit2->no_urut        = $recTrans2['no_urut'];
                $apCredit2->kd_bahan       = $recTrans2['kd_bahan'];
                $apCredit2->satuan         = $recTrans2['satuan'];
                $apCredit2->qty            = $recTrans2['qty'];
                $apCredit2->rp_harga       = $recTrans2['rp_harga'];
                $apCredit2->persen_diskon  = $recTrans2['persen_diskon'];
                $apCredit2->rp_diskon      = $recTrans2['rp_diskon'];
                $apCredit2->persen_diskon2 = $recTrans2['persen_diskon2'];
                $apCredit2->rp_diskon2     = $recTrans2['rp_diskon2'];
                $apCredit2->persen_diskon3 = $recTrans2['persen_diskon3'];
                $apCredit2->rp_diskon3     = $recTrans2['rp_diskon3'];
                $apCredit2->persen_diskon4 = $recTrans2['persen_diskon4'];
                $apCredit2->rp_diskon4     = $recTrans2['rp_diskon4'];
                $apCredit2->kd_pajak       = $recTrans2['kd_pajak'];
                $apCredit2->persen_pajak   = $recTrans2['persen_pajak'];
                $apCredit2->rp_pajak       = $recTrans2['rp_pajak'];
                $apCredit2->rp_harga_akhir = $recTrans2['rp_harga_akhir'];
                $apCredit2->catatan        = $recTrans2['catatan'];
                $apCredit2->base_type      = $recTrans2['base_type'];
                $apCredit2->base_ref       = $recTrans2['base_ref'];
                $apCredit2->base_no_doc    = $recTrans2['base_no_doc'];
                $apCredit2->fl_tutup       = $recTrans2['fl_tutup'];
                $apCredit2->fl_update_stok = $recTrans2['fl_update_stok'];
                $apCredit2->stok_fifo_key  = $recTrans2['stok_fifo_key'];
                $apCredit2->no_account     = $recTrans2['no_account'];
                $apCredit2->konversi       = $recTrans2['konversi'];
                $apCredit2->satuan_dasar   = $recTrans2['satuan_dasar'];
                $apCredit2->save();
            }

            //Data APCredit3
            $existingIds = APCredit3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans3)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            APCredit3::whereIn('dtl3_key', $toDelete)->delete();

            //APCredit3::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $apCredit3 = APCredit3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($apCredit3)) {
                    $apCredit3 = new APCredit3();
                    $apCredit3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $apCredit3->doc_key        = $apCredit1->doc_key;
                $apCredit3->no_urut        = $recTrans3['no_urut'];
                $apCredit3->no_account     = $recTrans3['no_account'];
                $apCredit3->nm_account     = $recTrans3['nm_account'];
                $apCredit3->catatan        = $recTrans3['catatan'];
                $apCredit3->rp_bayar       = $recTrans3['rp_bayar'];
                $apCredit3->rp_sisa        = $recTrans3['rp_sisa'];
                $apCredit3->base_type      = $recTrans3['base_type'];
                $apCredit3->base_ref       = $recTrans3['base_ref'];
                $apCredit3->save();
            }

            $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
            PurchaseReturnController::updateStok($apCredit1->doc_key, TRUE);
            PurchaseReturnController::setLinkData($apCredit1->doc_key, TRUE);
            PurchaseReturnController::generateJurnal($apCredit1->doc_key, $user_id);

            DB::commit();
            //$response['doc_key'] = $apCredit1->doc_key;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
