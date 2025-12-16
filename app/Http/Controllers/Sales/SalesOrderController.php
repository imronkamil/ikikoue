<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Sales\SO1;
use App\Models\Sales\SO2;
use App\Models\Sales\SO2Fifo;
use App\Models\Sales\SO3;
use App\Models\Sales\SO4;
use App\Models\Sales\SO5;
use App\Models\Master\Lokasi;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Paket1;
use App\Models\Master\Account;
use App\Models\Master\Pajak;
use App\Models\Master\Bayar;
use App\Models\Master\Customer;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Models\Tools\PasUsers;
use App\Models\Tools\PasObjects;
use App\Models\Tools\PasAccess;
use App\Models\Stok\StokFifo;
use App\Models\Stok\StokFifoDtl;
use App\Http\Controllers\Tools\DocNoController;
use App\Http\Controllers\Tools\UtilityController;

class SalesOrderController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $query1= DB::table('t_so5 as a')
        ->selectRaw("a.doc_key, string_agg(a.kd_bayar,', ') AS kd_bayar")
        ->groupBy('a.doc_key');
        //->get();

        $subQ2= DB::table('t_so5 as a')
        ->leftJoin('t_bank_terima2 as b','a.dtl5_key','=','b.base_ref')
        ->selectRaw("a.doc_key, b.doc_key AS doc_key_bi")
        ->groupBy('a.doc_key')
        ->groupBy('b.doc_key');
        //->get();
        $query2= DB::table('t_bank_terima1 as a')
        ->joinSub($subQ2,'b','a.doc_key','=','b.doc_key_bi')
        ->selectRaw("b.doc_key, CAST(string_agg(a.no_doc,', ') AS varchar(200)) AS no_doc_bi")
        ->groupBy('b.doc_key');
        //->get();

        $data['t_so1']= SO1::from('t_so1 as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->leftJoin('m_customer as c','a.kd_partner','=','c.kd_customer')
        ->leftJoin('m_staf as d','a.kd_sales','=','d.kd_staf')
        ->leftJoin('m_lokasi as e','a.kd_lokasi','=','e.kd_lokasi')
        ->leftJoin('m_customer_grup as f','c.kd_customer_grup','=','f.kd_customer_grup')
        ->leftJoinSub($query2,'g','a.doc_key','=','g.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.tgl_order, a.tgl_kirim, a.jam_kirim, a.tgl_sampai, a.jam_sampai,
            a.jam_konsumsi, a.tgl_finish, a.jam_finish, a.kd_lokasi, a.no_referensi, a.lama_bayar, a.tgl_bayar,
            a.kd_partner, a.kd_kontak,
            a.rp_total_awal, a.persen_diskon, a.rp_diskon, a.persen_pajak, a.rp_pajak, a.persen_biaya, a.rp_biaya,
            a.rp_rounding, a.rp_total, a.rp_dp, a.rp_bayar, a.rp_sisa,
            a.kd_sales, a.catatan, a.catatan_jurnal, a.enum_tipe_so,
            a.fl_rounding, a.fl_tutup, a.fl_batal, a.fl_trds, a.fl_kirim,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.batal_tgl, a.batal_userid, a.batal_lokasi,
            a.nm_partner, a.alamat_inv, a.telp_inv, a.nm_kontak, a.no_account,
            a.propinsi_inv, a.kabupaten_inv, a.kecamatan_inv, a.kelurahan_inv, a.fl_ocp,
            a.nm_kontak_pengirim, a.telp_pengirim, a.enum_delivery, a.no_urut_delivery, a.kd_delivery,
            a.jam_siap, a.catatan_delivery, a.jam_berangkat, a.detail_text,
            a.no_doc_urut, a.catatan_kwitansi, a.kd_delivery2, a.fl_include_pajak,
            a.tgl_proses, a.fl_pass, a.kd_lokasi_refer, a.doc_key_jurnal,
            a.persen_pph23, a.rp_pph23,
            b.kd_bayar, c.nm_customer, c.alamat, c.telp, d.nm_staf AS nm_sales, e.nm_lokasi,
            f.kd_customer_grup, f.nm_customer_grup, g.no_doc_bi")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['t_so1']= SO1::from('t_so1 as a')
        ->selectRaw("a.*")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_so1']= SO1::from('t_so1 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getHargaJual(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan : 0;
        $subQ1= DB::table('m_harga_jual as a')
        ->whereIn('kd_harga', function($query) use ($kd_customer) {
            $query->select('b.kd_harga')
            ->from('m_customer as b')
            ->where('b.kd_customer', $kd_customer);
        })
        ->selectRaw("a.kd_bahan, a.kd_harga, a.rp_harga");
        $data['m_harga_jual']= Bahan::from('m_bahan as a')
        ->leftJoin('m_bahan_satuan as b',function ($join) {
            $join->on('a.kd_bahan','=','b.kd_bahan')
                 ->on('a.satuan','=','b.satuan');
        })
        ->leftJoinSub($subQ1,'c','a.kd_bahan','=','c.kd_bahan')
        ->leftJoin('m_customer_bahan as d',function ($join) use ($kd_customer) {
            $join->on('a.kd_bahan','=','d.kd_bahan')
                 ->where('d.kd_customer','=',$kd_customer);
        })
        ->selectRaw("c.kd_harga, b.rp_harga_jual, c.rp_harga AS rp_harga_khusus, d.rp_harga AS rp_harga_ocp, a.satuan")
        ->where("a.kd_bahan",$kd_bahan)
        ->get();
        return response()->success('Success',$data);
    }

    public function getCustomer(Request $request) {
        $filter=Str::lower(isset($request->filter) ? $request->filter : '');
        $data['m_customer']= Customer::from('m_customer as a')
        ->leftJoin('m_customer_alamat as b','a.kd_customer','=','b.kd_customer')
        ->selectRaw("a.kd_customer, a.nm_customer, a.kd_customer_grup,
            b.kd_alamat, b.nm_alamat, b.alamat, b.contact, b.telp, b.fax,
            b.propinsi, b.kota, b.kecamatan, b.kelurahan, a.fl_ocp, a.nm_kontak_pengirim, a.telp AS telp_pengirim")
        ->where("a.fl_aktif","true")
        ->where(function ($query) use ($filter) {
            $query->where(DB::raw('lower(a.kd_customer)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.nm_customer)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(b.alamat)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(b.contact)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(b.telp)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(b.fax)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.telp)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.nm_kontak_pengirim)'), 'like', '%' . $filter . '%')
                  ->orWhereRaw('? = ?', [$filter, '']);
        })->get();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_so1']= SO1::from('t_so1 as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_so1']) ? $data['t_so1']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_so1']= SO1::from('t_so1 as a')
        ->join('t_so5 as b','a.doc_key','=','b.doc_key')
        ->join('t_bank_terima2 as c','b.dtl5_key','=','c.base_ref')
        ->join('t_bank_terima1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_so1'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $base_type=isset($request->base_type) ? $request->base_type : 0;
        $user_id=isset($request->user_id) ? $request->user_id : '';

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
        ->where('b.kd_menu','like','SO%')
        ->where(function ($query) use ($user_id, $pasGrup) {
            $query->where('a.user_id', '=', $user_id)
                ->orWhereIn('a.user_id', $pasGrup);
        })
        ->get();

        //SO1
        $data['t_so1']= SO1::from('t_so1 as a')
        ->leftJoin('m_customer as b','a.kd_partner','=','b.kd_customer')
        ->selectRaw("a.*, b.kd_customer_grup")
        ->where("a.doc_key",$doc_key)
        ->first();

        //SO2
        $data['t_so2']= SO2::from('t_so2 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->where(DB::raw("COALESCE(a.parent_dtl2_key,0)"),0)
        ->orderBy("a.no_urut")
        ->get();

        //SO2Detail
        $data['t_so2_detail']= SO2::from('t_so2 as a')
        ->leftJoin('t_so2 as b','b.dtl2_key','=','a.parent_dtl2_key')
        ->selectRaw("a.*, b.no_urut as no_urut_parent")
        ->where("a.doc_key",$doc_key)
        ->whereNotNull("a.parent_dtl2_key")
        ->orderBy("b.no_urut")
        ->orderBy("a.no_urut")
        ->get();

        //SO3
        $data['t_so3']= SO3::from('t_so3 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //SO4
        $data['t_so4']= SO4::from('t_so4 as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->get();

        //SO5
        $data['t_so5']= SO5::from('t_so5 as a')
        ->selectRaw("a.*")
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
        ->orderBy('a.kd_lokasi')
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

        //Master Paket1
        $data['m_paket1']= Paket1::from('m_paket1 as a')
        ->selectRaw("*")
        ->orderBy("kd_paket")
        ->get();

        //Master Pajak
        $subPajak= DB::table(DB::raw("(SELECT CAST(NULL AS varchar(20)) AS kd_pajak,
            '(null)'::varchar AS nm_pajak, 0 AS persen_pajak, 'true'::boolean AS fl_aktif) AS b"));
        $data['m_pajak']= Pajak::from('m_pajak')
        ->selectRaw("kd_pajak, nm_pajak, persen_pajak, fl_aktif")
        ->unionAll($subPajak)
        ->orderByRaw("kd_pajak NULLS FIRST")
        ->get();

        //Master Bayar
        $data['m_bayar']= Bayar::from('m_bayar')
        ->selectRaw("*")
        ->orderBy("no_urut")
        ->orderBy("kd_bayar")
        ->get();

        //Master Customer
        /*$data['m_customer']= Customer::from('m_customer')
        ->selectRaw("*")
        ->orderBy("kd_customer")
        ->get();*/

        //Master Bahan Jual Filter
        $data['m_bahan_jual_filter']= Bahan::from('m_bahan as a')
        ->leftJoin('m_bahan_satuan as b',function ($join) {
            $join->on('a.kd_bahan','=','b.kd_bahan')
                 ->on('a.satuan_jual','=','b.satuan');
        })
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.rp_harga_jual")
        ->where("a.fl_aktif","true")
        ->where("a.fl_jual","true")
        ->orderBy("a.kd_bahan")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //SalesOrderController::updateStok($doc_key, FALSE);
        SO5::where('doc_key',$doc_key)->delete();
        SO4::where('doc_key',$doc_key)->delete();
        SO3::where('doc_key',$doc_key)->delete();
        SO2Fifo::where('doc_key',$doc_key)->delete();
        SO2::where('doc_key',$doc_key)->delete();
        SO1::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $so1= SO1::where('doc_key',$doc_key)->first();
        if ($so1) {
            if ($so1->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //SalesOrderController::updateStok($doc_key, FALSE);
            //Update gr1
            $so1->catatan = $catatan . "\n" . $so1->catatan;
            $so1->fl_batal = 'true';
            $so1->batal_tgl = date('Y-m-d H:i:s');
            $so1->batal_userid = $request->userid;
            $so1->batal_lokasi = $request->lokasi;
            $so1->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=31; //Sales Order
        $dataStokFifoAll= [];
        $dataStokFifoAllNon= [];
        if ($insert == FALSE) {
            $dataTrans= SO1::from("t_so1 as a")
            ->leftJoin("t_so2_fifo as b","a.doc_key","=","b.doc_key")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.dtl2_fifo_key, b.dtl2_key, b.kd_bahan, b.satuan, b.qty, b.stok_fifo_key")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.dtl2_fifo_key")
            ->get();
            foreach($dataTrans as $recTrans) {
                //FIFO Header
                $dataStokFifo= StokFifo::where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                if ($dataStokFifo) {
                    $dataStokFifo->qty_used= $dataStokFifo->qty_used - ($recTrans->qty);
                    $dataStokFifo->save();
                    //FIFO Detail
                    $stokFifoKey = $dataStokFifo->stok_fifo_key;
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_fifo_key)
                        ->where("stok_fifo_key",$recTrans->stok_fifo_key)->first();
                    if ($dataStokFifoDtl) {
                        $dataStokFifoDtl->qty_used = $dataStokFifoDtl->qty_used + $recTrans->qty;
                        $dataStokFifoDtl->save();
                    }
                }
            }
        } elseif ($insert == TRUE) {
            $qty= 0;
            $qtyStok=0;
            //Kosongkan SO2Fifo
            SO2Fifo::where("doc_key",$doc_key)->delete();
            //Append SO2Fifo
            $dataTrans= SO1::from("t_so1 as a")
            ->join("t_so2 as b","a.doc_key","=","b.doc_key")
            ->join("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->join("m_bahan_satuan as d", function ($join) {
                $join->on('b.kd_bahan','=','d.kd_bahan')
                     ->on('b.satuan','=','d.satuan');
            })
            ->leftJoin("t_so2 as e","b.parent_dtl2_key","=","e.dtl2_key")
            //->leftJoin("t_so2 as e","b.no_urut_parent","=","e.no_urut")
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.*, c.satuan AS satuan_dasar2, d.rasio,
                CAST(COALESCE(b.qty,b.sub_qty*e.qty) AS numeric(18,4)) AS qty_sales")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $qty= $recTrans->qty_sales * $recTrans->rasio;
                //FIFO Header
                $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                    ->where("kd_bahan",$recTrans->kd_bahan)
                    ->where("satuan",$recTrans->satuan_dasar2)
                    ->where(DB::raw("COALESCE(qty_on_hand)"),">",DB::raw("COALESCE(qty_used)"))
                    ->orderBy("tgl_doc","asc")
                    ->orderBy("stok_fifo_key","asc")
                    ->get();
                //$dataStokFifoAll= $dataStokFifoAll + $dataStokFifo->toArray(); //untuk tracking stok fifo available
                foreach ($dataStokFifo as $recStokFifo) {
                    if ($qty>0) {
                        if ($recStokFifo->qty_on_hand - $recStokFifo->qty_used >= $qty) {
                            $qtyStok= $qty;
                            $qty= 0;
                        } else {
                            $qtyStok= $recStokFifo->qty_on_hand - $recStokFifo->qty_used;
                            $qty= $qty - $qtyStok;
                        }
                        //SO2Fifo
                        $dataSO2Fifo= SO2Fifo::where("doc_key",$recTrans->doc_key)
                            ->where("dtl2_key",$recTrans->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataSO2Fifo) {
                            $dataSO2Fifo= new SO2Fifo();
                            $dataSO2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataSO2Fifo->doc_key = $recTrans->doc_key;
                        $dataSO2Fifo->dtl2_key = $recTrans->dtl2_key;
                        $dataSO2Fifo->kd_bahan = $recTrans->kd_bahan;
                        $dataSO2Fifo->satuan = $recTrans->satuan_dasar2;
                        $dataSO2Fifo->qty = $qtyStok;
                        $dataSO2Fifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataSO2Fifo->save();

                        //StokFIFO Detail
                        $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan_dasar2)
                            ->where("base_type",$docTrans)
                            ->where("base_doc_key",$recTrans->doc_key)
                            ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                        if (!$dataStokFifoDtl) {
                            $dataStokFifoDtl= new StokFifoDtl();
                            $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                        }
                        $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                        $dataStokFifoDtl->satuan = $recTrans->satuan_dasar2;
                        $dataStokFifoDtl->kd_lokasi = $recTrans->kd_lokasi;
                        $dataStokFifoDtl->tgl_doc = $recTrans->tgl_doc;
                        $dataStokFifoDtl->no_doc = $recTrans->no_doc;
                        $dataStokFifoDtl->base_type = $docTrans;
                        $dataStokFifoDtl->base_doc_key = $recTrans->doc_key;
                        $dataStokFifoDtl->base_dtl2_key = $recTrans->dtl2_key;
                        $dataStokFifoDtl->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataStokFifoDtl->qty = -$qtyStok;
                        $dataStokFifoDtl->save();
                    }
                }

                if ($qty>0) {
                    //Stok FIFO Header
                    $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan_dasar2)
                        ->where("qty_on_hand",">",0)
                        ->where("rp_harga",">",0)
                        ->orderBy("tgl_doc","desc")
                        ->orderBy("stok_fifo_key","desc")
                        ->first();
                    if(!$dataStokFifo) {
                        $dataStokFifo= StokFifo::where("kd_lokasi",$recTrans->kd_lokasi)
                            ->where("kd_bahan",$recTrans->kd_bahan)
                            ->where("satuan",$recTrans->satuan_dasar2)
                            ->where("rp_harga",">",0)
                            ->orderBy("tgl_doc","desc")
                            ->orderBy("stok_fifo_key","desc")
                            ->first();
                    }
                    /*if (!$dataStokFifo || $dataStokFifo!=NULL) {
                        $dataStokFifoAllNon= $dataStokFifoAllNon + $dataStokFifo->toArray();
                    }*/ //untuk tracking stok fifo non available
                    $qtyStok= $qty;
                    //New StokFIFO Header
                    $dataStokFifoNew= new StokFifo();
                    $dataStokFifoNew->stok_fifo_key = StokFifo::max('stok_fifo_key') + 1;
                    $dataStokFifoNew->kd_bahan = $recTrans->kd_bahan;
                    $dataStokFifoNew->satuan = $recTrans->satuan_dasar2;
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
                    //SO2Fifo
                    $dataSO2Fifo= SO2Fifo::where("doc_key",$recTrans->doc_key)
                        ->where("dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataSO2Fifo) {
                        $dataSO2Fifo= new SO2Fifo();
                        $dataSO2Fifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataSO2Fifo->doc_key = $recTrans->doc_key;
                    $dataSO2Fifo->dtl2_key = $recTrans->dtl2_key;
                    $dataSO2Fifo->kd_bahan = $recTrans->kd_bahan;
                    $dataSO2Fifo->satuan = $recTrans->satuan_dasar2;
                    $dataSO2Fifo->qty = $qtyStok;
                    $dataSO2Fifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataSO2Fifo->save();

                    //StokFIFO Detail
                    $dataStokFifoDtl= StokFifoDtl::where("kd_lokasi",$recTrans->kd_lokasi)
                        ->where("kd_bahan",$recTrans->kd_bahan)
                        ->where("satuan",$recTrans->satuan_dasar2)
                        ->where("base_type",$docTrans)
                        ->where("base_doc_key",$recTrans->doc_key)
                        ->where("base_dtl2_key",$recTrans->dtl2_key)->first();
                    if (!$dataStokFifoDtl) {
                        $dataStokFifoDtl= new StokFifoDtl();
                        $dataStokFifoDtl->stok_fifo_dtl_key = StokFifoDtl::max('stok_fifo_dtl_key') + 1;
                    }
                    $dataStokFifoDtl->kd_bahan = $recTrans->kd_bahan;
                    $dataStokFifoDtl->satuan = $recTrans->satuan_dasar2;
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
        /*$data['trans']= $dataTrans;
        $data['stok_fifo']= $dataStokFifoAll;
        $data['stok_fifo_non']= $dataStokFifoAllNon;
        return $data;*/
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_so1'];
        $dataTrans2= $data['t_so2'];
        $dataTrans2Detail= $data['t_so2_detail'];
        $dataTrans3= $data['t_so3'];
        //$dataTrans4= $data['t_so4'];
        $dataTrans5= $data['t_so5'];

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

            $so1= SO1::where('doc_key',$doc_key)->first();
            //Jika update, kembalikan stok terlebih dahulu
            if ($so1) {
                if (UtilityController::getAutoStok() == 'true') {
                    if ($so1->tgl_kirim <= date('Y-m-d')) {
                        SalesOrderController::updateStok($doc_key, FALSE);
                    }
                }
            } else {
                $so1= new SO1();
                $so1->doc_key = DocNoController::getDocKey('doc_key');
            }
            $so1->no_doc         = $dataTrans1['no_doc'];
            $so1->tgl_doc        = $dataTrans1['tgl_doc'];
            $so1->tgl_order      = $dataTrans1['tgl_order'];
            $so1->tgl_kirim      = $dataTrans1['tgl_kirim'];
            $so1->jam_kirim      = $dataTrans1['jam_kirim'];
            $so1->tgl_sampai     = $dataTrans1['tgl_sampai'];
            $so1->jam_sampai     = $dataTrans1['jam_sampai'];
            $so1->jam_konsumsi   = $dataTrans1['jam_konsumsi'];
            $so1->tgl_finish     = $dataTrans1['tgl_finish'];
            $so1->jam_finish     = $dataTrans1['jam_finish'];
            $so1->kd_lokasi      = $dataTrans1['kd_lokasi'];
            $so1->no_referensi   = $dataTrans1['no_referensi'];
            $so1->lama_bayar     = $dataTrans1['lama_bayar'];
            $so1->tgl_bayar      = $dataTrans1['tgl_bayar'];
            $so1->kd_partner     = $dataTrans1['kd_partner'];
            $so1->kd_kontak      = $dataTrans1['kd_kontak'];
            $so1->rp_total_awal  = $dataTrans1['rp_total_awal'];
            $so1->persen_diskon  = $dataTrans1['persen_diskon'];
            $so1->rp_diskon      = $dataTrans1['rp_diskon'];
            $so1->persen_pajak   = $dataTrans1['persen_pajak'];
            $so1->rp_pajak       = $dataTrans1['rp_pajak'];
            $so1->persen_biaya   = $dataTrans1['persen_biaya'];
            $so1->rp_biaya       = $dataTrans1['rp_biaya'];
            $so1->rp_rounding    = $dataTrans1['rp_rounding'];
            $so1->rp_total       = $dataTrans1['rp_total'];
            $so1->rp_dp          = $dataTrans1['rp_dp'];
            $so1->rp_bayar       = $dataTrans1['rp_bayar'];
            $so1->rp_sisa        = $dataTrans1['rp_sisa'];
            $so1->kd_sales       = $dataTrans1['kd_sales'];
            $so1->catatan        = $dataTrans1['catatan'];
            $so1->catatan_jurnal = $dataTrans1['catatan_jurnal'];
            $so1->enum_tipe_so   = $dataTrans1['enum_tipe_so'];
            $so1->fl_rounding    = $dataTrans1['fl_rounding'];
            $so1->fl_tutup       = $dataTrans1['fl_tutup'];
            $so1->fl_batal       = $dataTrans1['fl_batal'];
            $so1->fl_trds        = $dataTrans1['fl_trds'];
            $so1->fl_kirim       = $dataTrans1['fl_kirim'];
            $so1->create_tgl     = $dataTrans1['create_tgl'];
            $so1->create_userid  = $dataTrans1['create_userid'];
            $so1->create_lokasi  = $dataTrans1['create_lokasi'];
            $so1->update_tgl     = $dataTrans1['update_tgl'];
            $so1->update_userid  = $dataTrans1['update_userid'];
            $so1->update_lokasi  = $dataTrans1['update_lokasi'];
            $so1->batal_tgl      = $dataTrans1['batal_tgl'];
            $so1->batal_userid   = $dataTrans1['batal_userid'];
            $so1->batal_lokasi   = $dataTrans1['batal_lokasi'];
            $so1->nm_partner     = $dataTrans1['nm_partner'];
            $so1->alamat_inv     = $dataTrans1['alamat_inv'];
            $so1->telp_inv       = $dataTrans1['telp_inv'];
            $so1->nm_kontak      = $dataTrans1['nm_kontak'];
            $so1->no_account     = $dataTrans1['no_account'];
            $so1->propinsi_inv   = $dataTrans1['propinsi_inv'];
            $so1->kabupaten_inv  = $dataTrans1['kabupaten_inv'];
            $so1->kecamatan_inv  = $dataTrans1['kecamatan_inv'];
            $so1->kelurahan_inv  = $dataTrans1['kelurahan_inv'];
            $so1->fl_ocp         = $dataTrans1['fl_ocp'];
            $so1->nm_kontak_pengirim = $dataTrans1['nm_kontak_pengirim'];
            $so1->enum_delivery  = $dataTrans1['enum_delivery'];
            $so1->no_urut_delivery = $dataTrans1['no_urut_delivery'];
            $so1->kd_delivery    = $dataTrans1['kd_delivery'];
            $so1->catatan_delivery = $dataTrans1['catatan_delivery'];
            $so1->jam_siap       = $dataTrans1['jam_siap'];
            $so1->jam_berangkat  = $dataTrans1['jam_berangkat'];
            $so1->detail_text    = $dataTrans1['detail_text'];
            $so1->no_doc_urut    = $dataTrans1['no_doc_urut'];
            $so1->catatan_kwitansi = $dataTrans1['catatan_kwitansi'];
            $so1->kd_delivery2   = $dataTrans1['kd_delivery2'];
            $so1->fl_include_pajak = $dataTrans1['fl_include_pajak'];
            $so1->tgl_proses     = $dataTrans1['tgl_proses'];
            $so1->fl_pass        = $dataTrans1['fl_pass'];
            $so1->kd_lokasi_refer= $dataTrans1['kd_lokasi_refer'];
            $so1->doc_key_jurnal = $dataTrans1['doc_key_jurnal'];
            $so1->persen_pph23   = $dataTrans1['persen_pph23'];
            $so1->rp_pph23       = $dataTrans1['rp_pph23'];
            $so1->save();

            //Data SO2
            $existingIds = SO2::where('doc_key',$doc_key)
                ->where(DB::raw('COALESCE(parent_dtl2_key,0)'),0)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)
                ->where('COALESCE(parent_dtl2_key,0)',0)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            SO2::whereIn('dtl2_key', $toDelete)->delete();

            //SO2::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans2 as $recTrans2) {
                $validator=Validator::make($recTrans2,[
                    //'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    //'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $so2 = SO2::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($so2)) {
                    $so2 = new SO2();
                    $so2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $so2->doc_key        = $so1->doc_key;
                $so2->no_urut        = $recTrans2['no_urut'];
                $so2->kd_bahan       = $recTrans2['kd_bahan'];
                $so2->satuan         = $recTrans2['satuan'];
                $so2->qty            = $recTrans2['qty'];
                $so2->rp_harga       = $recTrans2['rp_harga'];
                $so2->persen_diskon  = $recTrans2['persen_diskon'];
                $so2->rp_diskon      = $recTrans2['rp_diskon'];
                $so2->persen_diskon2 = $recTrans2['persen_diskon2'];
                $so2->rp_diskon2     = $recTrans2['rp_diskon2'];
                $so2->persen_diskon3 = $recTrans2['persen_diskon3'];
                $so2->rp_diskon3     = $recTrans2['rp_diskon3'];
                $so2->persen_diskon4 = $recTrans2['persen_diskon4'];
                $so2->rp_diskon4     = $recTrans2['rp_diskon4'];
                $so2->kd_pajak       = $recTrans2['kd_pajak'];
                $so2->persen_pajak   = $recTrans2['persen_pajak'];
                $so2->rp_pajak       = $recTrans2['rp_pajak'];
                $so2->rp_harga_akhir = $recTrans2['rp_harga_akhir'];
                $so2->qty_sisa       = $recTrans2['qty_sisa'];
                $so2->catatan        = $recTrans2['catatan'];
                $so2->fl_tutup       = $recTrans2['fl_tutup'];
                $so2->base_type      = $recTrans2['base_type'];
                $so2->base_ref       = $recTrans2['base_ref'];
                $so2->fl_void        = $recTrans2['fl_void'];
                $so2->kd_paket       = $recTrans2['kd_paket'];
                $so2->parent_dtl2_key= $recTrans2['parent_dtl2_key'];
                $so2->sub_qty        = $recTrans2['sub_qty'];
                $so2->fl_cetak_detail= $recTrans2['fl_cetak_detail'];
                $so2->catatan_harga  = $recTrans2['catatan_harga'];
                $so2->save();

                $dataTrx2Detail = collect($dataTrans2Detail)->where('no_urut_parent',$recTrans2['no_urut'])->toArray();
                if ($dataTrx2Detail) {
                    //Data SO2Detail
                    $existingIds = SO2::where('parent_dtl2_key',$so2->dtl2_key)->pluck('dtl2_key')->toArray();
                    $newIds = collect($dataTrx2Detail)->pluck('dtl2_key')->filter()->toArray();
                    // Delete items that are not in request
                    $toDelete = array_diff($existingIds, $newIds);
                    SO2::whereIn('dtl2_key', $toDelete)->delete();

                    //SO2::where('doc_key',$doc_key)->delete(); //Hapus data existing
                    foreach($dataTrx2Detail as $recTrx2detail) {
                        $validator=Validator::make($recTrx2detail,[
                            'kd_bahan'=>'bail|required',
                            'satuan'=>'bail|required',
                        ],[
                            'kd_bahan.required'=>'Kode Bahan harus diisi',
                            'satuan.required'=>'Satuan harus diisi',
                        ]);

                        if ($validator->fails()){
                            return response()->error('',501,$validator->errors()->first());
                        }

                        $so2detail = SO2::where('dtl2_key',$recTrx2detail['dtl2_key'])->first();
                        if (!($so2detail)) {
                            $so2detail = new SO2();
                            $so2detail->dtl2_key = DocNoController::getDocKey('doc_key');
                        }
                        $so2detail->doc_key        = $so1->doc_key;
                        $so2detail->no_urut        = $recTrx2detail['no_urut'];
                        $so2detail->kd_bahan       = $recTrx2detail['kd_bahan'];
                        $so2detail->satuan         = $recTrx2detail['satuan'];
                        $so2detail->qty            = $recTrx2detail['qty'];
                        $so2detail->rp_harga       = $recTrx2detail['rp_harga'];
                        $so2detail->persen_diskon  = $recTrx2detail['persen_diskon'];
                        $so2detail->rp_diskon      = $recTrx2detail['rp_diskon'];
                        $so2detail->persen_diskon2 = $recTrx2detail['persen_diskon2'];
                        $so2detail->rp_diskon2     = $recTrx2detail['rp_diskon2'];
                        $so2detail->persen_diskon3 = $recTrx2detail['persen_diskon3'];
                        $so2detail->rp_diskon3     = $recTrx2detail['rp_diskon3'];
                        $so2detail->persen_diskon4 = $recTrx2detail['persen_diskon4'];
                        $so2detail->rp_diskon4     = $recTrx2detail['rp_diskon4'];
                        $so2detail->kd_pajak       = $recTrx2detail['kd_pajak'];
                        $so2detail->persen_pajak   = $recTrx2detail['persen_pajak'];
                        $so2detail->rp_pajak       = $recTrx2detail['rp_pajak'];
                        $so2detail->rp_harga_akhir = $recTrx2detail['rp_harga_akhir'];
                        $so2detail->qty_sisa       = $recTrx2detail['qty_sisa'];
                        $so2detail->catatan        = $recTrx2detail['catatan'];
                        $so2detail->fl_tutup       = $recTrx2detail['fl_tutup'];
                        $so2detail->base_type      = $recTrx2detail['base_type'];
                        $so2detail->base_ref       = $recTrx2detail['base_ref'];
                        $so2detail->fl_void        = $recTrx2detail['fl_void'];
                        $so2detail->kd_paket       = $recTrx2detail['kd_paket'];
                        $so2detail->parent_dtl2_key= $so2->dtl2_key;
                        $so2detail->sub_qty        = $recTrx2detail['sub_qty'];
                        $so2detail->fl_cetak_detail= $recTrx2detail['fl_cetak_detail'];
                        $so2detail->catatan_harga  = $recTrx2detail['catatan_harga'];
                        $so2detail->save();
                    }
                }
            }

            //Data SO3
            $existingIds = SO3::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans3)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            SO3::whereIn('dtl3_key', $toDelete)->delete();

            //SO3::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans3 as $recTrans3) {
                $validator=Validator::make($recTrans3,[
                    'no_account'=>'bail|required',
                ],[
                    'no_account.required'=>'No Account harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $so3 = SO3::where('dtl3_key',$recTrans3['dtl3_key'])->first();
                if (!($so3)) {
                    $so3 = new SO3();
                    $so3->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $so3->doc_key        = $so1->doc_key;
                $so3->no_urut        = $recTrans3['no_urut'];
                $so3->no_account     = $recTrans3['no_account'];
                $so3->nm_account     = $recTrans3['nm_account'];
                $so3->catatan        = $recTrans3['catatan'];
                $so3->rp_bayar       = $recTrans3['rp_bayar'];
                $so3->rp_sisa        = $recTrans3['rp_sisa'];
                $so3->base_type      = $recTrans3['base_type'];
                $so3->base_ref       = $recTrans3['base_ref'];
                $so3->save();
            }

            //Data SO5
            $existingIds = SO5::where('doc_key',$doc_key)->pluck('dtl5_key')->toArray();
            $newIds = collect($dataTrans5)->pluck('dtl5_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            SO5::whereIn('dtl5_key', $toDelete)->delete();

            //SO5::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans5 as $recTrans5) {
                $validator=Validator::make($recTrans5,[
                    'kd_bayar'=>'bail|required',
                ],[
                    'kd_bayar.required'=>'Kode Bayar harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $so5 = SO5::where('dtl5_key',$recTrans5['dtl5_key'])->first();
                if (!($so5)) {
                    $so5 = new SO5();
                    $so5->dtl5_key = DocNoController::getDocKey('doc_key');
                }
                $so5->doc_key        = $so1->doc_key;
                $so5->no_urut        = $recTrans5['no_urut'];
                $so5->enum_bayar     = $recTrans5['enum_bayar'];
                $so5->kd_bayar       = $recTrans5['kd_bayar'];
                $so5->no_referensi   = $recTrans5['no_referensi'];
                $so5->nm_pelanggan   = $recTrans5['nm_pelanggan'];
                $so5->tgl_bayar      = $recTrans5['tgl_bayar'];
                $so5->tgl_cair       = $recTrans5['tgl_cair'];
                $so5->tgl_transfer   = $recTrans5['tgl_transfer'];
                $so5->no_kartu       = $recTrans5['no_kartu'];
                $so5->nm_kartu       = $recTrans5['nm_kartu'];
                $so5->catatan_bank   = $recTrans5['catatan_bank'];
                $so5->rp_tagihan     = $recTrans5['rp_tagihan'];
                $so5->rp_diskon      = $recTrans5['rp_diskon'];
                $so5->rp_bayar       = $recTrans5['rp_bayar'];
                $so5->rp_sisa        = $recTrans5['rp_sisa'];
                $so5->rp_cair        = $recTrans5['rp_cair'];
                $so5->persen_admin   = $recTrans5['persen_admin'];
                $so5->rp_admin       = $recTrans5['rp_admin'];
                $so5->catatan        = $recTrans5['catatan'];
                $so5->fl_tutup       = $recTrans5['fl_tutup'];
                $so5->create_tgl     = $recTrans5['create_tgl'];
                $so5->create_userid  = $recTrans5['create_userid'];
                $so5->create_lokasi  = $recTrans5['create_lokasi'];
                $so5->update_tgl     = $recTrans5['update_tgl'];
                $so5->update_userid  = $recTrans5['update_userid'];
                $so5->update_lokasi  = $recTrans5['update_lokasi'];
                $so5->bayar_id       = $recTrans5['bayar_id'];
                $so5->bank_id        = $recTrans5['bank_id'];
                $so5->save();
            }

            $resp = 0;
            if (UtilityController::getAutoStok() == 'true') {
                if ($so1->tgl_kirim <= date('Y-m-d')) {
                    $resp= SalesOrderController::updateStok($so1->doc_key, TRUE);
                }
            }

            DB::commit();
            //$response['auto'] = UtilityController::getAutoStok();
            //$response['tgl_now'] = date('Y-m-d');
            //$response['tgl_kirim'] = $so1->tgl_kirim;
            //$response['stok'] = $resp;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
