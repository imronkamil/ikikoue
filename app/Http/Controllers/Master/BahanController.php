<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\HargaJual;
use App\Models\Master\Satuan;
use App\Models\Master\Pajak;
use App\Models\Master\Rak;
use App\Models\Master\LevelBahan;
use App\Models\Master\GrupBahan;
use App\Models\Master\TipeBahan;
use App\Models\Master\GrupPerform;
use App\Models\Master\BahanKlp;
use App\Models\Master\Supplier;

class BahanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        $limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Bahan::from('m_bahan as a')
        ->leftJoin('m_grup_bahan as b','a.kd_grup_bahan','=','b.kd_grup_bahan')
        ->leftJoin('m_tipe_bahan as c','a.kd_tipe_bahan','=','c.kd_tipe_bahan')
        ->leftJoin('m_grup_perform as d','a.kd_grup_perform','=','d.kd_grup_perform')
        ->leftJoin('m_bahan_klp as e','a.bahan_klp_id','=','e.bahan_klp_id')
        ->leftJoin('m_bahan_satuan as f', function($join)
            {
                $join->on('a.kd_bahan','=','f.kd_bahan');
                $join->on('a.satuan_beli','=','f.satuan');
            })
        ->leftJoin('m_bahan_satuan as g', function($join)
            {
                $join->on('a.kd_bahan','=','g.kd_bahan');
                $join->on('a.satuan_jual','=','g.satuan');
            })
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.update_tgl, a.update_userid,
            b.nm_grup_bahan, c.nm_tipe_bahan, d.nm_grup_perform, e.nama AS nm_klp_bahan,
            f.rp_harga_beli AS rp_harga_beli_sat, g.rp_harga_jual AS rp_harga_jual_sat");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_bahan','like',$filter)
                    ->orwhere('a.nm_bahan','like',$filter)
                    ->orwhere('a.nm_bahan','like',$filter);
            });
        }
        $data['m_bahan']=$data1->orderBy($sortBy,$sorting)->paginate($limit);
        return response()->success('Success',$data);
    }

    public function show2(Request $request) {
        $data['m_bahan']= Bahan::from('m_bahan as a')
        ->leftJoin('m_grup_bahan as b','a.kd_grup_bahan','=','b.kd_grup_bahan')
        ->leftJoin('m_tipe_bahan as c','a.kd_tipe_bahan','=','c.kd_tipe_bahan')
        ->leftJoin('m_grup_perform as d','a.kd_grup_perform','=','d.kd_grup_perform')
        ->leftJoin('m_bahan_klp as e','a.bahan_klp_id','=','e.bahan_klp_id')
        ->leftJoin('m_bahan_satuan as f', function($join)
            {
                $join->on('a.kd_bahan','=','f.kd_bahan');
                $join->on('a.satuan_beli','=','f.satuan');
            })
        ->leftJoin('m_bahan_satuan as g', function($join)
            {
                $join->on('a.kd_bahan','=','g.kd_bahan');
                $join->on('a.satuan_jual','=','g.satuan');
            })
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.update_tgl, a.update_userid,
            b.nm_grup_bahan, c.nm_tipe_bahan, d.nm_grup_perform, e.nama AS nm_klp_bahan,
            f.rp_harga_beli AS rp_harga_beli_sat, g.rp_harga_jual AS rp_harga_jual_sat")
        ->orderBy("a.kd_bahan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $data['m_bahan']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where('a.kd_bahan',$kd_bahan)
        ->first();
        return response()->success('Success',$data);
    }

    public function isBahan(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan : 0;
        $bahan= Bahan::from('m_bahan  as a')
        ->where("a.kd_bahan",$kd_bahan)
        ->where("a.fl_pakai",'=','true')
        ->exists();
        $response= ['value' => $bahan ? 'false' : 'true'];
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';

        //Master Bahan
        $data['m_bahan']= Bahan::from('m_bahan as a')
        ->selectRaw("a.kd_bahan, a.nm_bahan, a.nm_bahan2, a.satuan, a.satuan2, a.konversi2,
            a.kd_grup_bahan, a.kd_tipe_bahan, a.kd_pajak_jual, a.kd_pajak_beli, a.kd_rak, a.barcode,
            a.isi, a.catatan, a.kd_level,
            a.satuan_beli, a.satuan_jual, a.satuan_report,
            a.fl_jual, a.fl_beli, a.fl_stok, a.fl_pakai, a.fl_aktif, a.fl_harga_fix, a.fl_stock_transfer,
            a.bahan_klp_id, a.nm_bahan_barcode, a.plu_client,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where('a.kd_bahan',$kd_bahan)
        ->first();

        //Master Bahan Satuan
        $data['m_bahan_satuan']= BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where('a.kd_bahan',$kd_bahan)
        ->get();

        //Master Harga Jual
        $data['m_harga_jual']= HargaJual::from('m_harga_jual as a')
        ->selectRaw("a.harga_jual_id, a.kd_bahan, a.kd_harga, a.persen_harga, a.rp_harga, b.nm_harga")
        ->leftJoin('m_level_harga as b','a.kd_harga','=','b.kd_harga')
        ->where('a.kd_bahan',$kd_bahan)
        ->get();

        //Master Satuan
        $data['m_satuan']= Satuan::from('m_satuan as a')
        ->selectRaw("a.satuan, a.create_tgl, a.create_userid, a.create_lokasi")
        ->orderBy("a.satuan","asc")
        ->get();

        //Master Pajak
        $data['m_pajak']= Pajak::from('m_pajak as a')
        ->selectRaw("a.kd_pajak, a.nm_pajak, a.persen_pajak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_pajak","asc")
        ->get();

        //Master Rak
        $data['m_rak']= Rak::from('m_rak as a')
        ->selectRaw("a.kd_rak, a.nm_rak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_rak","asc")
        ->get();

        //Master Level Bahan
        $data['m_level_bahan']= LevelBahan::from('m_level_bahan as a')
        ->selectRaw("a.kd_level, a.nm_level")
        ->orderBy("a.kd_level","asc")
        ->get();

        //Master Grup Bahan
        $data['m_grup_bahan']= GrupBahan::from('m_grup_bahan as a')
        ->selectRaw("a.kd_grup_bahan, a.nm_grup_bahan,
            a.acc_stok, a.acc_beli, a.acc_retur_beli, a.acc_diskon_beli, a.acc_jual, a.acc_retur_jual,
            a.acc_diskon_jual, a.acc_hpp, a.acc_adj_stok, a.jenis, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_grup_bahan","asc")
        ->get();

        //Master Tipe Bahan
        $data['m_tipe_bahan']= TipeBahan::from('m_tipe_bahan as a')
        ->selectRaw("a.kd_tipe_bahan, a.nm_tipe_bahan,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_tipe_bahan","asc")
        ->get();

        //Master Grup Perform
        $data['m_grup_perform']= GrupPerform::from('m_grup_perform as a')
        ->selectRaw("a.kd_grup_perform, a.nm_grup_perform, a.fl_aktif, a.no_urut,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_grup_perform","asc")
        ->get();

        //Master Klp Bahan
        $data['m_bahan_klp']= BahanKlp::from('m_bahan_klp as a')
        ->selectRaw("a.bahan_klp_id, a.nama")
        ->orderBy("a.bahan_klp_id","asc")
        ->get();

        //Master Supplier
        $data['m_supplier'] = Supplier::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->orderBy("a.kd_supplier","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function getKodeBahanBaru(Request $request) {
        $kd_bahan=Bahan::where('kd_level',$request->kd_level)->max('kd_bahan')+1;
        if ($request->kd_level=='0'){
            $kd_bahan=substr('00000'.(string)$kd_bahan,strlen('00000'.(string)$kd_bahan)-6,6);
        }
        $response['value'] = $kd_bahan;
        return response()->success('Success',$response);
    }

    public function destroy(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        Bahan::where('kd_bahan',$kd_bahan)->delete();
        BahanSatuan::where('kd_bahan',$kd_bahan)->delete();
        HargaJual::where('kd_bahan',$kd_bahan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store2(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_bahan'];

        $validator=Validator::make($rec,[
            'kd_bahan'=>'bail|required',
            'nm_bahan'=>'bail|required',
            'satuan'=>'bail|required',
        ],[
            'kd_bahan.required'=>'Kode harus diisi',
            'nm_bahan.required'=>'Nama bahan harus diisi',
            'satuan.required'=>'Satuan bahan harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $bahan= Bahan::where('kd_bahan',$where['kd_bahan'])->first();
        if (!($bahan)) {
            $bahan= new Bahan();
        }
        $bahan->kd_bahan     = $rec['kd_bahan'];
        $bahan->nm_bahan     = $rec['nm_bahan'];
        $bahan->nm_bahan2    = $rec['nm_bahan2'];
        $bahan->satuan       = $rec['satuan'];
        $bahan->satuan2      = $rec['satuan2'];
        $bahan->konversi2    = $rec['konversi2'];
        $bahan->kd_grup_bahan= $rec['kd_grup_bahan'];
        $bahan->kd_tipe_bahan= $rec['kd_tipe_bahan'];
        $bahan->kd_pajak_jual= $rec['kd_pajak_jual'];
        $bahan->kd_pajak_beli= $rec['kd_pajak_beli'];
        $bahan->kd_rak       = $rec['kd_rak'];
        $bahan->barcode      = $rec['barcode'];
        $bahan->isi          = $rec['isi'];
        $bahan->catatan      = $rec['catatan'];
        $bahan->kd_level     = $rec['kd_level'];
        $bahan->satuan_beli  = $rec['satuan_beli'];
        $bahan->satuan_jual  = $rec['satuan_jual'];
        $bahan->satuan_report = $rec['satuan_report'];
        $bahan->fl_jual      = $rec['fl_jual'];
        $bahan->fl_beli      = $rec['fl_beli'];
        $bahan->fl_stok      = $rec['fl_stok'];
        $bahan->fl_pakai     = $rec['fl_pakai'];
        $bahan->fl_aktif     = $rec['fl_aktif'];
        $bahan->fl_harga_fix = $rec['fl_harga_fix'];
        $bahan->fl_stock_transfer = $rec['fl_stock_transfer'];
        $bahan->bahan_klp_id = $rec['bahan_klp_id'];
        $bahan->nm_bahan_barcode = $rec['nm_bahan_barcode'];
        $bahan->plu_client   = $rec['plu_client'];
        $bahan->create_tgl   = $rec['create_tgl'];
        $bahan->create_userid = $rec['create_userid'];
        $bahan->create_lokasi = $rec['create_lokasi'];
        $bahan->update_tgl = $rec['update_tgl'];
        $bahan->update_userid = $rec['update_userid'];
        $bahan->update_lokasi = $rec['update_lokasi'];
        $bahan->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $recBahan  = $data['m_bahan'];
        $recBahanSat  = $data['m_bahan_satuan'];
        $recHargaJual = $data['m_harga_jual'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($recBahan,[
                'kd_bahan'=>'bail|required',
                'nm_bahan'=>'bail|required',
                'satuan'=>'bail|required',
            ],[
                'kd_bahan.required'=>'Kode harus diisi',
                'nm_bahan.required'=>'Nama bahan harus diisi',
                'satuan.required'=>'Satuan bahan harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $bahan= Bahan::where('kd_bahan',$where['kd_bahan'])->first();
            if (!($bahan)) {
                $bahan= new Bahan();
            }
            $bahan->kd_bahan     = $recBahan['kd_bahan'];
            $bahan->nm_bahan     = $recBahan['nm_bahan'];
            $bahan->nm_bahan2    = $recBahan['nm_bahan2'];
            $bahan->satuan       = $recBahan['satuan'];
            $bahan->satuan2      = $recBahan['satuan2'];
            $bahan->konversi2    = $recBahan['konversi2'];
            $bahan->kd_grup_bahan= $recBahan['kd_grup_bahan'];
            $bahan->kd_tipe_bahan= $recBahan['kd_tipe_bahan'];
            $bahan->kd_pajak_jual= $recBahan['kd_pajak_jual'];
            $bahan->kd_pajak_beli= $recBahan['kd_pajak_beli'];
            $bahan->kd_rak       = $recBahan['kd_rak'];
            $bahan->barcode      = $recBahan['barcode'];
            $bahan->isi          = $recBahan['isi'];
            $bahan->catatan      = $recBahan['catatan'];
            $bahan->kd_level     = $recBahan['kd_level'];
            $bahan->satuan_beli  = $recBahan['satuan_beli'];
            $bahan->satuan_jual  = $recBahan['satuan_jual'];
            $bahan->satuan_report = $recBahan['satuan_report'];
            $bahan->fl_jual      = $recBahan['fl_jual'];
            $bahan->fl_beli      = $recBahan['fl_beli'];
            $bahan->fl_stok      = $recBahan['fl_stok'];
            $bahan->fl_pakai     = $recBahan['fl_pakai'];
            $bahan->fl_aktif     = $recBahan['fl_aktif'];
            $bahan->fl_harga_fix = $recBahan['fl_harga_fix'];
            $bahan->fl_stock_transfer = $recBahan['fl_stock_transfer'];
            $bahan->bahan_klp_id = $recBahan['bahan_klp_id'];
            $bahan->nm_bahan_barcode = $recBahan['nm_bahan_barcode'];
            $bahan->plu_client   = $recBahan['plu_client'];
            $bahan->create_tgl   = $recBahan['create_tgl'];
            $bahan->create_userid = $recBahan['create_userid'];
            $bahan->create_lokasi = $recBahan['create_lokasi'];
            $bahan->update_tgl = $recBahan['update_tgl'];
            $bahan->update_userid = $recBahan['update_userid'];
            $bahan->update_lokasi = $recBahan['update_lokasi'];
            $bahan->save();

            //Data BahanSatuan
            BahanSatuan::where('kd_bahan',$where['kd_bahan'])->delete(); //Hapus data existing
            foreach($recBahanSat as $lineBahanSat) {
                $validator=Validator::make($lineBahanSat,[
                    'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $bahansat = BahanSatuan::where('bahan_satuan_id',$lineBahanSat['bahan_satuan_id'])->first();
                if (!($bahansat)) {
                    $bahansat = new BahanSatuan();
                }
                $bahansat->kd_bahan = $lineBahanSat['kd_bahan'];
                $bahansat->satuan = $lineBahanSat['satuan'];
                $bahansat->rasio = $lineBahanSat['rasio'];
                $bahansat->rp_harga_beli = $lineBahanSat['rp_harga_beli'];
                $bahansat->rp_harga_beli_min = $lineBahanSat['rp_harga_beli_min'];
                $bahansat->rp_harga_beli_max = $lineBahanSat['rp_harga_beli_max'];
                $bahansat->rp_harga_beli_akhir = $lineBahanSat['rp_harga_beli_akhir'];
                $bahansat->rp_harga_jual = $lineBahanSat['rp_harga_jual'];
                $bahansat->fl_pakai = $lineBahanSat['fl_pakai'];
                $bahansat->fl_default = $lineBahanSat['fl_default'];
                $bahansat->create_tgl = $lineBahanSat['create_tgl'];
                $bahansat->create_userid = $lineBahanSat['create_userid'];
                $bahansat->create_lokasi = $lineBahanSat['create_lokasi'];
                $bahansat->update_tgl = $lineBahanSat['update_tgl'];
                $bahansat->update_userid = $lineBahanSat['update_userid'];
                $bahansat->update_lokasi = $lineBahanSat['update_lokasi'];
                $bahansat->save();
            }

            //Data HargaJual
            foreach($recHargaJual as $lineHargaJual) {
                $validator=Validator::make($lineHargaJual,[
                    'kd_bahan'=>'bail|required',
                    'kd_harga'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'kd_harga.required'=>'Kode Harga harus diisi',
                ]);
                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $hargajual= HargaJual::where('harga_jual_id',$lineHargaJual['harga_jual_id'])->first();
                if (!($hargajual)) {
                    $hargajualidnew = HargaJual::max('harga_jual_id') + 1;
                    $hargajual= new HargaJual();
                    $hargajual->harga_jual_id = $hargajualidnew;
                }
                $hargajual->kd_bahan = $lineHargaJual['kd_bahan'];
                $hargajual->kd_harga = $lineHargaJual['kd_harga'];
                $hargajual->persen_harga = $lineHargaJual['persen_harga'];
                $hargajual->rp_harga = $lineHargaJual['rp_harga'];
                $hargajual->save();
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
