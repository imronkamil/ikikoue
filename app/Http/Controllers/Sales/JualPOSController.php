<?php

namespace App\Http\Controllers\Sales;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Accounting\AccountDtl;
use App\Models\Sales\Jual;
use App\Models\Sales\JualBahan;
use App\Models\Sales\JualBahanFifo;
use App\Models\Sales\JualBayar;
use App\Models\Finance\ARDP1;
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

class JualPOSController extends Controller
{
    public function show1(Request $request) {
        $tgl1 = $request->tgl1;
        $tgl2 = $request->tgl2;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump(date($tgl1));
        $query1= DB::table('t_jual_bayar as a')
        ->selectRaw("a.doc_key, string_agg(a.kd_bayar,', ') AS kd_bayar")
        ->groupBy('a.doc_key');
        //->get();

        $subQ2= DB::table('t_jual_bayar as a')
        ->leftJoin('t_bank_terima2 as b','a.dtl3_key','=','b.base_ref')
        ->selectRaw("a.doc_key, b.doc_key AS doc_key_bi")
        ->groupBy('a.doc_key')
        ->groupBy('b.doc_key');
        //->get();
        $query2= DB::table('t_bank_terima1 as a')
        ->joinSub($subQ2,'b','a.doc_key','=','b.doc_key_bi')
        ->selectRaw("b.doc_key, CAST(string_agg(a.no_doc,', ') AS varchar(200)) AS no_doc_bi")
        ->groupBy('b.doc_key');
        //->get();

        $data['t_jual']= Jual::from('t_jual as a')
        ->leftJoinSub($query1,'b','a.doc_key','=','b.doc_key')
        ->leftJoin('m_lokasi as e','a.kd_lokasi','=','e.kd_lokasi')
        ->leftJoinSub($query2,'g','a.doc_key','=','g.doc_key')
        ->selectRaw("a.a.*, e.nm_lokasi, g.no_doc_bi")
        ->where("a.tgl_doc",">=",$tgl1)
        ->where("a.tgl_doc","<=",$tgl2)
        ->orderBy('tgl_doc','desc')
        ->orderBy('no_doc','desc')
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['t_jual']= Jual::from('t_jual as a')
        ->selectRaw("a.*")
        //->where('doc_key')
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_jual']= Jual::from('t_jual as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();
        return response()->success('Success',$data);
    }

    public function getBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_jual']= Jual::from('t_jual as a')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->first();
        $response['value']= ($data['t_jual']) ? $data['t_jual']->fl_batal : 'false';
        return response()->success('Success',$response);
    }

    public function getLinkData(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $data['t_jual']= Jual::from('t_jual as a')
        ->join('t_jual_bayar as b','a.doc_key','=','b.doc_key')
        ->join('t_bank_terima2 as c','b.dtl3_key','=','c.base_ref')
        ->join('t_bank_terima1 as d','c.doc_key','=','d.doc_key')
        ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.fl_batal")
        ->where("a.doc_key",$doc_key)
        ->where("d.fl_batal","false")
        ->get();
        $response['value']= (count($data['t_jual'])>0) ? 'true' : 'false';
        return response()->success('Success',$response);
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
        ->where('b.kd_menu','like','SO%')
        ->where(function ($query) use ($user_id, $pasGrup) {
            $query->where('a.user_id', '=', $user_id)
                ->orWhereIn('a.user_id', $pasGrup);
        })
        ->get();

        //Jual
        $data['t_jual']= Jual::from('t_jual as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->first();

        //JualBahan
        $data['t_jual_bahan']= JualBahan::from('t_jual_bahan as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->orderBy("a.no_urut")
        ->get();

        //JualBayar
        $data['t_jual_bayar']= JualBayar::from('t_jual_bayar as a')
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
        JualBayar::where('doc_key',$doc_key)->delete();
        JualBahanFifo::where('doc_key',$doc_key)->delete();
        JualBahan::where('doc_key',$doc_key)->delete();
        Jual::where('doc_key',$doc_key)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function setBatal(Request $request) {
        $doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        $catatan=isset($request->catatan) ? $request->catatan : '';
        $jual= Jual::where('doc_key',$doc_key)->first();
        if ($jual) {
            if ($jual->fl_batal == 'true') {
                $response['message'] = 'Data sudah dibatalkan';
                return response()->success('Success',$response);
            }
            //SalesOrderController::updateStok($doc_key, FALSE);
            //Update gr1
            $jual->catatan = $catatan . "\n" . $jual->catatan;
            $jual->fl_batal = 'true';
            $jual->batal_tgl = date('Y-m-d H:i:s');
            $jual->batal_userid = $request->userid;
            $jual->batal_lokasi = $request->lokasi;
            $jual->save();
        }
        $response['message'] = 'Batal data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateLinkData($doc_key = 0, $insert = FALSE) {
        if ($insert == FALSE) {
            //ARDeposit1
            $dataARDP1= ARDP1::from("t_ardp1 as a")
            ->leftJoin("t_so6 as b","a.doc_key","=","b.base_ref")
            ->leftJoin("t_jual as c","b.doc_key","=","c.doc_key")
            ->selectRaw("a.doc_key, b.dtl6_key, b.rp_jumlah")
            ->where("c.doc_key",$doc_key)
            ->where("b.base_type",41) //AR Deposit
            ->get();
            foreach($dataARDP1 as $recARDP1) {
                //Update APDeposit1
                $arDeposit = ARDP1::where('doc_key',$recARDP1->doc_key)->first();
                if ($arDeposit) {
                    if ($arDeposit->rp_sisa + $recARDP1->rp_jumlah == 0) {
                        $arDeposit->fl_tutup = TRUE;
                    } else {
                        $arDeposit->fl_tutup = FALSE;
                    }
                    $arDeposit->rp_sisa = $arDeposit->rp_sisa + $recARDP1->rp_jumlah;
                    $arDeposit->save();
                }
            }
        } elseif ($insert == TRUE) {
            //ARDeposit1
            $dataARDP1= ARDP1::from("t_ardp1 as a")
            ->leftJoin("t_so6 as b","a.doc_key","=","b.base_ref")
            ->leftJoin("t_jual as c","b.doc_key","=","c.doc_key")
            ->selectRaw("a.doc_key, b.dtl6_key, b.rp_jumlah")
            ->where("c.doc_key",$doc_key)
            ->where("b.base_type",41) //AR Deposit
            ->get();
            foreach($dataARDP1 as $recARDP1) {
                //Update APDeposit1
                $arDeposit = ARDP1::where('doc_key',$recARDP1->doc_key)->first();
                if ($arDeposit) {
                    if ($arDeposit->rp_sisa - $recARDP1->rp_jumlah == 0) {
                        $arDeposit->fl_tutup = TRUE;
                    } else {
                        $arDeposit->fl_tutup = FALSE;
                    }
                    $arDeposit->rp_sisa = $arDeposit->rp_sisa - $recARDP1->rp_jumlah;
                    $arDeposit->save();
                }
            }
        }
        $response['message'] = 'Set link data berhasil';
        return response()->success('Success',$response);
    }

    public static function updateStok($doc_key = 0, $insert = FALSE) {
        $docTrans=45; //POS Sales
        $dataStokFifoAll= [];
        $dataStokFifoAllNon= [];
        if ($insert == FALSE) {
            $dataTrans= Jual::from("t_jual as a")
            ->leftJoin("t_jual_bahan_fifo as b","a.doc_key","=","b.doc_key")
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
            //Kosongkan JualBahanFifo
            JualBahanFifo::where("doc_key",$doc_key)->delete();
            //Append JualBahanFifo
            $dataTrans= Jual::from("t_jual as a")
            ->join("t_jual_bahan as b","a.doc_key","=","b.doc_key")
            ->join("m_bahan as c","b.kd_bahan","=","c.kd_bahan")
            ->join("m_bahan_satuan as d", function ($join) {
                $join->on('b.kd_bahan','=','d.kd_bahan')
                     ->on('b.satuan','=','d.satuan');
            })
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi,
                b.*, c.satuan AS satuan_dasar2, d.rasio")
            ->where("a.doc_key",$doc_key)
            ->orderBy("b.no_urut")
            ->get();
            foreach($dataTrans as $recTrans) {
                $qty= $recTrans->qty * $recTrans->rasio;
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
                        //JualBahanFifo
                        $dataJualBahanFifo= JualBahanFifo::where("doc_key",$recTrans->doc_key)
                            ->where("dtl2_key",$recTrans->dtl2_key)
                            ->where("stok_fifo_key",$recStokFifo->stok_fifo_key)
                            ->first();
                        if (!$dataJualBahanFifo) {
                            $dataJualBahanFifo= new JualBahanFifo();
                            $dataJualBahanFifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                        }
                        $dataJualBahanFifo->doc_key = $recTrans->doc_key;
                        $dataJualBahanFifo->dtl2_key = $recTrans->dtl2_key;
                        $dataJualBahanFifo->kd_bahan = $recTrans->kd_bahan;
                        $dataJualBahanFifo->satuan = $recTrans->satuan_dasar2;
                        $dataJualBahanFifo->qty = $qtyStok;
                        $dataJualBahanFifo->stok_fifo_key = $recStokFifo->stok_fifo_key;
                        $dataJualBahanFifo->save();

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
                    //JualBahanFifo
                    $dataJualBahanFifo= JualBahanFifo::where("doc_key",$recTrans->doc_key)
                        ->where("dtl2_key",$recTrans->dtl2_key)
                        ->where("stok_fifo_key",$dataStokFifoNew->stok_fifo_key)
                        ->first();
                    if (!$dataJualBahanFifo) {
                        $dataJualBahanFifo= new JualBahanFifo();
                        $dataJualBahanFifo->dtl2_fifo_key = DocNoController::getDocKey('doc_key');
                    }
                    $dataJualBahanFifo->doc_key = $recTrans->doc_key;
                    $dataJualBahanFifo->dtl2_key = $recTrans->dtl2_key;
                    $dataJualBahanFifo->kd_bahan = $recTrans->kd_bahan;
                    $dataJualBahanFifo->satuan = $recTrans->satuan_dasar2;
                    $dataJualBahanFifo->qty = $qtyStok;
                    $dataJualBahanFifo->stok_fifo_key = $dataStokFifoNew->stok_fifo_key;
                    $dataJualBahanFifo->save();

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

    public function generateJurnal($doc_key = 0, $user_id = '') {
        //$doc_key=isset($request->doc_key) ? $request->doc_key : 0;
        //$user_id=isset($request->user_id) ? $request->user_id : '';
        $docTrans=45; //POS Sales

        //Hapus Jurnal Lama
        AccountDtl::where('base_doc_key',$doc_key)->delete();

        //Jurnal Piutang dan Penjualan
        $jurnal= Jual::from('t_jual as a')
        ->selectRaw("a.*")
        ->where("a.doc_key",$doc_key)
        ->get();
        //Jurnal Debet (Penjualan)
        foreach($jurnal as $recJurnal) {
            //Piutang
            if ($recJurnal->rp_total_harga != 0) {
                $jurnalPiutang= new AccountDtl();
                $jurnalPiutang->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPiutang->no_account = UtilityController::getAccountConfig('no_acc_pos_piutang');
                $jurnalPiutang->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_total_harga > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalPiutang->enum_debet_kredit = 'D';
                    $jurnalPiutang->rp_debet = abs($recJurnal->rp_total_harga);
                    $jurnalPiutang->rp_kredit = 0;
                } else {
                    $jurnalPiutang->enum_debet_kredit = 'K';
                    $jurnalPiutang->rp_debet = 0;
                    $jurnalPiutang->rp_kredit = abs($recJurnal->rp_total_harga);
                }
                $jurnalPiutang->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPiutang->catatan = $recJurnal->no_doc;
                $jurnalPiutang->no_ref1 = $recJurnal->no_doc;
                $jurnalPiutang->no_ref2 = '';
                $jurnalPiutang->user_id = $user_id;
                $jurnalPiutang->base_type = $docTrans; //Sales Order
                $jurnalPiutang->base_doc_key = $recJurnal->doc_key;
                //$jurnalPiutang->base_dtl_key = $recJurnal->doc_key;
                $jurnalPiutang->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPiutang->save();
            }
            //Diskon
            if ($recJurnal->rp_diskon != 0) {
                $jurnalDiskon= new AccountDtl();
                $jurnalDiskon->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalDiskon->no_account = UtilityController::getAccountConfig('no_acc_pos_diskon');
                $jurnalDiskon->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_diskon > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalDiskon->enum_debet_kredit = 'D';
                    $jurnalDiskon->rp_debet = abs($recJurnal->rp_diskon);
                    $jurnalDiskon->rp_kredit = 0;
                } else {
                    $jurnalDiskon->enum_debet_kredit = 'K';
                    $jurnalDiskon->rp_debet = 0;
                    $jurnalDiskon->rp_kredit = abs($recJurnal->rp_diskon);
                }
                $jurnalDiskon->tgl_doc = $recJurnal->tgl_doc;
                $jurnalDiskon->catatan = $recJurnal->no_doc;
                $jurnalDiskon->no_ref1 = $recJurnal->no_doc;
                $jurnalDiskon->no_ref2 = '';
                $jurnalDiskon->user_id = $user_id;
                $jurnalDiskon->base_type = $docTrans; //Sales Order
                $jurnalDiskon->base_doc_key = $recJurnal->doc_key;
                //$jurnalDiskon->base_dtl_key = $recJurnal->doc_key;
                $jurnalDiskon->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalDiskon->save();
            }
            //Rounding
            if ($recJurnal->rp_pembulatan != 0) {
                $jurnalRounding= new AccountDtl();
                $jurnalRounding->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalRounding->no_account = UtilityController::getAccountConfig('no_acc_pos_rounding');
                $jurnalRounding->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_pembulatan > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalRounding->enum_debet_kredit = 'D';
                    $jurnalRounding->rp_debet = abs($recJurnal->rp_pembulatan);
                    $jurnalRounding->rp_kredit = 0;
                } else {
                    $jurnalRounding->enum_debet_kredit = 'K';
                    $jurnalRounding->rp_debet = 0;
                    $jurnalRounding->rp_kredit = abs($recJurnal->rp_pembulatan);
                }
                $jurnalRounding->tgl_doc = $recJurnal->tgl_doc;
                $jurnalRounding->catatan = $recJurnal->no_doc;
                $jurnalRounding->no_ref1 = $recJurnal->no_doc;
                $jurnalRounding->no_ref2 = '';
                $jurnalRounding->user_id = $user_id;
                $jurnalRounding->base_type = $docTrans; //Sales Order
                $jurnalRounding->base_doc_key = $recJurnal->doc_key;
                //$jurnalRounding->base_dtl_key = $recJurnal->doc_key;
                $jurnalRounding->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalRounding->save();
            }
            //Pendapatan
            if ($recJurnal->rp_jumlah_harga != 0) {
                $jurnalIncome= new AccountDtl();
                $jurnalIncome->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalIncome->no_account = UtilityController::getAccountConfig('no_acc_pos');
                $jurnalIncome->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_jumlah_harga > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalIncome->enum_debet_kredit = 'K';
                    $jurnalIncome->rp_debet = 0;
                    $jurnalIncome->rp_kredit = abs($recJurnal->rp_jumlah_harga);
                } else {
                    $jurnalIncome->enum_debet_kredit = 'D';
                    $jurnalIncome->rp_debet = abs($recJurnal->rp_jumlah_harga);
                    $jurnalIncome->rp_kredit = 0;
                }
                $jurnalIncome->tgl_doc = $recJurnal->tgl_doc;
                $jurnalIncome->catatan = $recJurnal->no_doc;
                $jurnalIncome->no_ref1 = $recJurnal->no_doc;
                $jurnalIncome->no_ref2 = '';
                $jurnalIncome->user_id = $user_id;
                $jurnalIncome->base_type = $docTrans; //Sales Order
                $jurnalIncome->base_doc_key = $recJurnal->doc_key;
                //$jurnalIncome->base_dtl_key = $recJurnal->doc_key;
                $jurnalIncome->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalIncome->save();
            }
            //Ongkir
            if ($recJurnal->rp_biaya != 0) {
                $jurnalBiaya= new AccountDtl();
                $jurnalBiaya->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalBiaya->no_account = UtilityController::getAccountConfig('no_acc_pos_ongkir');
                $jurnalBiaya->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_biaya > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalBiaya->enum_debet_kredit = 'K';
                    $jurnalBiaya->rp_debet = 0;
                    $jurnalBiaya->rp_kredit = abs($recJurnal->rp_biaya);
                } else {
                    $jurnalBiaya->enum_debet_kredit = 'D';
                    $jurnalBiaya->rp_debet = abs($recJurnal->rp_biaya);
                    $jurnalBiaya->rp_kredit = 0;
                }
                $jurnalBiaya->tgl_doc = $recJurnal->tgl_doc;
                $jurnalBiaya->catatan = $recJurnal->no_doc;
                $jurnalBiaya->no_ref1 = $recJurnal->no_doc;
                $jurnalBiaya->no_ref2 = '';
                $jurnalBiaya->user_id = $user_id;
                $jurnalBiaya->base_type = $docTrans; //Sales Order
                $jurnalBiaya->base_doc_key = $recJurnal->doc_key;
                //$jurnalBiaya->base_dtl_key = $recJurnal->doc_key;
                $jurnalBiaya->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalBiaya->save();
            }
            //PPN
            if ($recJurnal->rp_ppn != 0) {
                $jurnalPPN= new AccountDtl();
                $jurnalPPN->dtl_key = DocNoController::getDocKey('doc_key');
                $jurnalPPN->no_account = UtilityController::getAccountConfig('no_acc_pos_ppn');
                $jurnalPPN->kd_lokasi = $recJurnal->kd_lokasi;
                if ($recJurnal->rp_ppn > 0 && $recJurnal->fl_retur == 'false') {
                    $jurnalPPN->enum_debet_kredit = 'K';
                    $jurnalPPN->rp_debet = 0;
                    $jurnalPPN->rp_kredit = abs($recJurnal->rp_ppn);
                } else {
                    $jurnalPPN->enum_debet_kredit = 'D';
                    $jurnalPPN->rp_debet = abs($recJurnal->rp_ppn);
                    $jurnalPPN->rp_kredit = 0;
                }
                $jurnalPPN->tgl_doc = $recJurnal->tgl_doc;
                $jurnalPPN->catatan = $recJurnal->no_doc;
                $jurnalPPN->no_ref1 = $recJurnal->no_doc;
                $jurnalPPN->no_ref2 = '';
                $jurnalPPN->user_id = $user_id;
                $jurnalPPN->base_type = $docTrans; //Sales Order
                $jurnalPPN->base_doc_key = $recJurnal->doc_key;
                //$jurnalPPN->base_dtl_key = $recJurnal->doc_key;
                $jurnalPPN->kd_project = UtilityController::getKodeProjectByLokasi($recJurnal->kd_lokasi);
                $jurnalPPN->save();
            }
        }

        $response['message'] = 'Set GL berhasil';
        return response()->success('Success',$response);
    }

    public function getProsesJurnal(Request $request) {
        $tgl_awal= $request->input('tgl_awal','');
        $tgl_kini= date('Y-m-d');
        $data['t_jual']= DB::table('t_jual as a')
            ->leftJoin('m_account_dtl as b','a.doc_key','=','b.base_doc_key')
            ->selectRaw("a.doc_key, a.no_doc, a.tgl_doc, a.kd_lokasi, a.rp_total_harga,
                b.dtl_key")
            ->whereRaw("a.tgl_doc >= '".$tgl_awal."' AND a.tgl_doc <= '".$tgl_kini."'")
            ->whereRaw("(COALESCE(a.fl_tutup,'false') = 'true')")
            ->whereNull('b.dtl_key')
            ->where('a.rp_total_harga','>',0)
            ->get();
        return response()->success('Success',$data);
    }

    public function setProsesJurnal(Request $request) {
        $doc_key= $request->input('doc_key',0);
        $user_id= $request->input('user_id','');
        DB::beginTransaction();
        try {
            if (UtilityController::getAutoStok() == 'true') {
                self::updateStok($doc_key, TRUE);
            }
            if (UtilityController::getAutoJurnal() == 'true') {
                self::generateJurnal($doc_key, $user_id);
            }
            DB::commit();
            $response['message'] = 'Proses Jurnal Selesai';
            return response()->success('Success',$response);
        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $doc_key=isset($where['doc_key']) ? $where['doc_key'] : 0;
        $dataTrans1= $data['t_jual'];
        $dataTrans2= $data['t_so2'];
        $dataTrans2Detail= $data['t_so2_detail'];
        $dataTrans3= $data['t_so3'];
        //$dataTrans4= $data['t_so4'];
        $dataTrans5= $data['t_jual_bayar'];
        $dataTrans6= $data['t_so6'];

        $dataTrans6= array_filter($dataTrans6, function($item) {
            return isset($item['rp_jumlah']) && $item['rp_jumlah'] > 0;
        });

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

            $jual= Jual::where('doc_key',$doc_key)->first();
            //Jika update, kembalikan stok terlebih dahulu
            if ($jual) {
                SalesOrderController::updateLinkData($doc_key, FALSE);
                if (UtilityController::getAutoStok() == 'true') {
                    if ($jual->tgl_kirim <= date('Y-m-d')) {
                        SalesOrderController::updateStok($doc_key, FALSE);
                    }
                }
            } else {
                $jual= new Jual();
                $jual->doc_key = DocNoController::getDocKey('doc_key');
            }
            $jual->no_doc             = $dataTrans1['no_doc'];
            $jual->tgl_doc            = $dataTrans1['tgl_doc'];
            $jual->tgl_order          = $dataTrans1['tgl_order'];
            $jual->tgl_kirim          = $dataTrans1['tgl_kirim'];
            $jual->jam_kirim          = $dataTrans1['jam_kirim'];
            $jual->tgl_sampai         = $dataTrans1['tgl_sampai'];
            $jual->jam_sampai         = $dataTrans1['jam_sampai'];
            $jual->jam_konsumsi       = $dataTrans1['jam_konsumsi'];
            $jual->tgl_finish         = $dataTrans1['tgl_finish'];
            $jual->jam_finish         = $dataTrans1['jam_finish'];
            $jual->kd_lokasi          = $dataTrans1['kd_lokasi'];
            $jual->no_referensi       = $dataTrans1['no_referensi'];
            $jual->lama_bayar         = $dataTrans1['lama_bayar'];
            $jual->tgl_bayar          = $dataTrans1['tgl_bayar'];
            $jual->kd_partner         = $dataTrans1['kd_partner'];
            $jual->kd_kontak          = $dataTrans1['kd_kontak'];
            $jual->rp_total_awal      = $dataTrans1['rp_total_awal'];
            $jual->persen_diskon      = $dataTrans1['persen_diskon'];
            $jual->rp_diskon          = $dataTrans1['rp_diskon'];
            $jual->persen_pajak       = $dataTrans1['persen_pajak'];
            $jual->rp_pajak           = $dataTrans1['rp_pajak'];
            $jual->persen_biaya       = $dataTrans1['persen_biaya'];
            $jual->rp_biaya           = $dataTrans1['rp_biaya'];
            $jual->rp_rounding        = $dataTrans1['rp_rounding'];
            $jual->rp_total           = $dataTrans1['rp_total'];
            $jual->rp_dp              = $dataTrans1['rp_dp'];
            $jual->rp_bayar           = $dataTrans1['rp_bayar'];
            $jual->rp_sisa            = $dataTrans1['rp_sisa'];
            $jual->kd_sales           = $dataTrans1['kd_sales'];
            $jual->catatan            = $dataTrans1['catatan'];
            $jual->catatan_jurnal     = $dataTrans1['catatan_jurnal'];
            $jual->enum_tipe_so       = $dataTrans1['enum_tipe_so'];
            $jual->fl_rounding        = $dataTrans1['fl_rounding'];
            $jual->fl_tutup           = $dataTrans1['fl_tutup'];
            $jual->fl_batal           = $dataTrans1['fl_batal'];
            $jual->fl_trds            = $dataTrans1['fl_trds'];
            $jual->fl_kirim           = $dataTrans1['fl_kirim'];
            $jual->base_type          = $dataTrans1['base_type'];
            $jual->create_tgl         = $dataTrans1['create_tgl'];
            $jual->create_userid      = $dataTrans1['create_userid'];
            $jual->create_lokasi      = $dataTrans1['create_lokasi'];
            $jual->update_tgl         = $dataTrans1['update_tgl'];
            $jual->update_userid      = $dataTrans1['update_userid'];
            $jual->update_lokasi      = $dataTrans1['update_lokasi'];
            $jual->batal_tgl          = $dataTrans1['batal_tgl'];
            $jual->batal_userid       = $dataTrans1['batal_userid'];
            $jual->batal_lokasi       = $dataTrans1['batal_lokasi'];
            $jual->nm_partner         = $dataTrans1['nm_partner'];
            $jual->alamat_inv         = $dataTrans1['alamat_inv'];
            $jual->telp_inv           = $dataTrans1['telp_inv'];
            $jual->nm_kontak          = $dataTrans1['nm_kontak'];
            $jual->no_account         = $dataTrans1['no_account'];
            $jual->propinsi_inv       = $dataTrans1['propinsi_inv'];
            $jual->kabupaten_inv      = $dataTrans1['kabupaten_inv'];
            $jual->kecamatan_inv      = $dataTrans1['kecamatan_inv'];
            $jual->kelurahan_inv      = $dataTrans1['kelurahan_inv'];
            $jual->fl_ocp             = $dataTrans1['fl_ocp'];
            $jual->nm_kontak_pengirim = $dataTrans1['nm_kontak_pengirim'];
            $jual->telp_pengirim      = $dataTrans1['telp_pengirim'];
            $jual->enum_delivery      = $dataTrans1['enum_delivery'];
            $jual->no_urut_delivery   = $dataTrans1['no_urut_delivery'];
            $jual->kd_delivery        = $dataTrans1['kd_delivery'];
            $jual->catatan_delivery   = $dataTrans1['catatan_delivery'];
            $jual->jam_siap           = $dataTrans1['jam_siap'];
            $jual->jam_berangkat      = $dataTrans1['jam_berangkat'];
            $jual->detail_text        = $dataTrans1['detail_text'];
            $jual->no_doc_urut        = $dataTrans1['no_doc_urut'];
            $jual->catatan_kwitansi   = $dataTrans1['catatan_kwitansi'];
            $jual->kd_delivery2       = $dataTrans1['kd_delivery2'];
            $jual->fl_include_pajak   = $dataTrans1['fl_include_pajak'];
            $jual->tgl_proses         = $dataTrans1['tgl_proses'];
            $jual->fl_pass            = $dataTrans1['fl_pass'];
            $jual->kd_lokasi_refer    = $dataTrans1['kd_lokasi_refer'];
            $jual->doc_key_jurnal     = $dataTrans1['doc_key_jurnal'];
            $jual->persen_pph23       = $dataTrans1['persen_pph23'];
            $jual->rp_pph23           = $dataTrans1['rp_pph23'];
            $jual->save();

            //Data JualBahan
            $existingIds = JualBahan::where('doc_key',$doc_key)
                ->where(DB::raw('COALESCE(parent_dtl2_key,0)'),0)->pluck('dtl2_key')->toArray();
            $newIds = collect($dataTrans2)
                ->where('COALESCE(parent_dtl2_key,0)',0)->pluck('dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            JualBahan::whereIn('dtl2_key', $toDelete)->delete();

            //Data JualBahanDetail
            $existingIds = JualBahan::where('doc_key',$doc_key)->pluck('parent_dtl2_key')->toArray();
            $newIds = collect($dataTrans2Detail)->pluck('parent_dtl2_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            JualBahan::whereIn('parent_dtl2_key', $toDelete)->delete();

            //JualBahan::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                $so2 = JualBahan::where('dtl2_key',$recTrans2['dtl2_key'])->first();
                if (!($so2)) {
                    $so2 = new JualBahan();
                    $so2->dtl2_key = DocNoController::getDocKey('doc_key');
                }
                $so2->doc_key        = $jual->doc_key;
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
                $so2->nomor_id       = $recTrans2['nomor_id'];
                $so2->parent_nomor_id= $recTrans2['parent_nomor_id'];
                $so2->save();

                $dataTrx2Detail = collect($dataTrans2Detail)->where('parent_nomor_id',$recTrans2['nomor_id'])->toArray();
                if ($dataTrx2Detail) {
                    //JualBahan::where('doc_key',$doc_key)->delete(); //Hapus data existing
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

                        $so2detail = JualBahan::where('dtl2_key',$recTrx2detail['dtl2_key'])->first();
                        if (!($so2detail)) {
                            $so2detail = new JualBahan();
                            $so2detail->dtl2_key = DocNoController::getDocKey('doc_key');
                        }
                        $so2detail->doc_key        = $jual->doc_key;
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
                        $so2detail->nomor_id       = $recTrx2detail['nomor_id'];
                        $so2detail->parent_nomor_id= $recTrx2detail['parent_nomor_id'];
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
                $so3->doc_key        = $jual->doc_key;
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

            //Data JualBayar
            $existingIds = JualBayar::where('doc_key',$doc_key)->pluck('dtl3_key')->toArray();
            $newIds = collect($dataTrans5)->pluck('dtl3_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            JualBayar::whereIn('dtl3_key', $toDelete)->delete();

            //JualBayar::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans5 as $recTrans5) {
                $validator=Validator::make($recTrans5,[
                    'kd_bayar'=>'bail|required',
                ],[
                    'kd_bayar.required'=>'Kode Bayar harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $so5 = JualBayar::where('dtl3_key',$recTrans5['dtl3_key'])->first();
                if (!($so5)) {
                    $so5 = new JualBayar();
                    $so5->dtl3_key = DocNoController::getDocKey('doc_key');
                }
                $so5->doc_key        = $jual->doc_key;
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

            //Data SO6
            $existingIds = SO6::where('doc_key',$doc_key)->pluck('dtl6_key')->toArray();
            $newIds = collect($dataTrans6)->pluck('dtl6_key')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            SO6::whereIn('dtl6_key', $toDelete)->delete();

            //SO6::where('doc_key',$doc_key)->delete(); //Hapus data existing
            foreach($dataTrans6 as $recTrans6) {
                $validator=Validator::make($recTrans6,[
                    'rp_jumlah'=>'bail|required',
                ],[
                    'rp_jumlah.required'=>'Rp Jumlah harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $so6 = SO6::where('dtl6_key',$recTrans6['dtl6_key'])->first();
                if (!($so6)) {
                    $so6 = new SO6();
                    $so6->dtl6_key = DocNoController::getDocKey('doc_key');
                }
                $so6->doc_key        = $jual->doc_key;
                $so6->base_type      = $recTrans6['base_type'];
                $so6->base_ref       = $recTrans6['base_ref'];
                $so6->rp_jumlah      = $recTrans6['rp_jumlah'];
                $so6->base_ref2      = $recTrans6['base_ref2'];
                $so6->save();
            }

            SalesOrderController::updateLinkData($doc_key, TRUE);
            $resp = 0;
            if (UtilityController::getAutoStok() == 'true') {
                if ($jual->tgl_kirim <= date('Y-m-d')) {
                    $resp= SalesOrderController::updateStok($jual->doc_key, TRUE);
                }
            }
            if (UtilityController::getAutoJurnal() == 'true') {
                $user_id = isset($dataTrans1['update_userid']) ? $dataTrans1['update_userid'] : $dataTrans1['create_userid'];
                SalesOrderController::generateJurnal($jual->doc_key, $user_id);
            }

            DB::commit();
            //$response['auto'] = UtilityController::getAutoStok();
            //$response['tgl_now'] = date('Y-m-d');
            //$response['tgl_kirim'] = $jual->tgl_kirim;
            //$response['stok'] = $resp;
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }
    }
}
