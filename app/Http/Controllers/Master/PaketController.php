<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Paket1;
use App\Models\Master\Paket2;
use App\Models\Master\Bahan;
use App\Models\Master\BahanSatuan;
use App\Models\Master\Satuan;

class PaketController extends Controller
{
    public function show1(Request $request) {
        $aktif = $request->aktif;

        $query1= DB::table('m_paket2 as a')
        ->selectRaw('a.kd_paket, COUNT(*) as qty_record')
        ->groupBy('a.kd_paket');
        $data1= Paket1::from('m_paket1 as a')
        ->leftJoin('m_bahan as b','a.kd_paket','=','b.kd_bahan')
        ->leftJoinSub($query1,'c', function($join)
            {
                $join->on('a.kd_paket','=','c.kd_paket');
            })
        ->selectRaw("a.*, b.nm_bahan, c.kd_paket AS kd_item");

        if (!($aktif=='')){
            $data1=$data1->where(DB::raw("COALESCE(a.fl_aktif, 'False')"),$aktif);
        };

        $data['m_paket1']=$data1->orderBy('kd_paket','asc')->get();

        $data['m_paket2']= Paket2::from('m_paket2 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.*, b.nm_bahan, b.satuan")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function show2() {
        $query1= DB::table('m_paket2 as a')
        ->selectRaw('a.kd_paket, COUNT(*) as qty_record')
        ->groupBy('a.kd_paket');
        $data1= Paket1::from('m_paket1 as a')
        ->leftJoin('m_bahan as b','a.kd_paket','=','b.kd_bahan')
        ->leftJoinSub($query1,'c', function($join)
            {
                $join->on('a.kd_paket','=','c.kd_paket');
            })
        ->selectRaw("a.*, b.nm_bahan, c.kd_paket AS kd_item");

        $data['m_paket1']=$data1->orderBy('kd_paket','asc')->get();

        $data['m_paket2']= Paket2::from('m_paket2 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.*, b.nm_bahan, b.satuan")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_paket=isset($request->kd_paket) ? $request->kd_paket : '';
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : '';
        $data['m_paket1']= Paket1::from('m_paket1 as a')
        ->leftJoin('m_customer_paket as b',function ($join) use ($kd_customer) {
            $join->on('b.kd_paket','=','a.kd_paket')
                 ->where('b.kd_customer',$kd_customer);
        })
        ->selectRaw("a.kd_paket, a.nm_paket, a.satuan, a.rp_harga, a.keterangan, a.fl_aktif, a.fl_harga,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.rp_harga as rp_harga_ocp")
        ->where("a.kd_paket",$kd_paket)
        ->first();
        $data['m_paket2']= Paket2::from('m_paket2 as a')
        ->selectRaw("a.kd_paket, a.kd_bahan, a.no_urut, a.satuan, a.qty, a.rp_harga, a.keterangan, a.fl_cetak, a.fl_perform,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_paket",$kd_paket)
        ->orderBy("a.no_urut","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';

        //Master Paket1
        $data['m_paket1']= Paket1::from('m_paket1 as a')
        ->selectRaw("a.*")
        ->where('a.kd_paket',$kd_bahan)
        ->first();

        //Master Paket2
        $data['m_paket2']= Paket2::from('m_paket2 as a')
        ->selectRaw("a.*")
        ->where('a.kd_paket',$kd_bahan)
        ->orderBy('a.no_urut','asc')
        ->get();

        //Master Bahan
        $data['m_bahan']= Paket1::from('m_bahan as a')
        ->selectRaw("a.*")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        //Master Bahan Satuan
        $data['m_bahan_satuan']= BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where('a.kd_bahan',$kd_bahan)
        ->get();

        //Master Satuan
        $data['m_satuan']= Satuan::from('m_satuan as a')
        ->selectRaw("a.satuan, a.create_tgl, a.create_userid, a.create_lokasi")
        ->orderBy("a.satuan","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_paket=isset($request->kd_paket) ? $request->kd_paket : 0;
        Paket2::where('kd_paket',$kd_paket)->delete();
        Paket1::where('kd_paket',$kd_paket)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $master1 = $data['m_paket1'];
        $master2 = $data['m_paket2'];

        DB::beginTransaction();
        try {
            //Data Paket1
            $validator=Validator::make($master1,[
                'kd_paket'=>'bail|required',
                'nm_paket'=>'bail|required',
            ],[
                'kd_paket.required'=>'Kode harus diisi',
                'nm_paket.required'=>'Nama paket harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $paket1= Paket1::where('kd_paket',$where['kd_paket'])->first();
            if (!($paket1)) {
                $paket1= new Paket1();
            }
            $paket1->kd_paket       = $master1['kd_paket'];
            $paket1->nm_paket       = $master1['nm_paket'];
            $paket1->satuan         = $master1['satuan'];
            $paket1->qty_awal       = $master1['qty_awal'];
            $paket1->qty_beli       = $master1['qty_beli'];
            $paket1->qty_jual       = $master1['qty_jual'];
            $paket1->rp_harga       = $master1['rp_harga'];
            $paket1->keterangan     = $master1['keterangan'];
            $paket1->fl_aktif       = $master1['fl_aktif'];
            $paket1->create_tgl     = $master1['create_tgl'];
            $paket1->create_userid  = $master1['create_userid'];
            $paket1->create_lokasi  = $master1['create_lokasi'];
            $paket1->update_tgl     = $master1['update_tgl'];
            $paket1->update_userid  = $master1['update_userid'];
            $paket1->update_lokasi  = $master1['update_lokasi'];
            $paket1->fl_vertikal    = $master1['fl_vertikal'];
            $paket1->kd_grup_perform= $master1['kd_grup_perform'];
            $paket1->rp_harga_dasar = $master1['rp_harga_dasar'];
            $paket1->fl_harga       = $master1['fl_harga'];
            $paket1->save();

            //Data Paket2
            $existingIds = Paket2::where('kd_paket',$where['kd_paket'])->pluck('paket2_id')->toArray();
            $newIds = collect($master2)->pluck('paket2_id')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Paket2::whereIn('paket2_id', $toDelete)->delete();

            //Paket2::where('kd_paket',$where['kd_paket'])->delete(); //Hapus data existing
            foreach($master2 as $recMaster2) {
                $validator=Validator::make($recMaster2,[
                    'kd_bahan'=>'bail|required',
                    'satuan'=>'bail|required',
                ],[
                    'kd_bahan.required'=>'Kode Bahan harus diisi',
                    'satuan.required'=>'Satuan harus diisi',
                ]);

                if ($validator->fails()){
                    return response()->error('',501,$validator->errors()->first());
                }

                $paket2 = Paket2::where('paket2_id',$recMaster2['paket2_id'])->first();
                if (!($paket2)) {
                    $paket2 = new Paket2();
                    $paket2->paket2_id = Paket2::max('paket2_id') + 1;
                }
                $paket2->kd_paket       = $recMaster2['kd_paket'];
                $paket2->kd_bahan       = $recMaster2['kd_bahan'];
                $paket2->no_urut        = $recMaster2['no_urut'];
                $paket2->satuan         = $recMaster2['satuan'];
                $paket2->qty            = $recMaster2['qty'];
                $paket2->rp_harga       = $recMaster2['rp_harga'];
                $paket2->rp_harga_beli  = $recMaster2['rp_harga_beli'];
                $paket2->keterangan     = $recMaster2['keterangan'];
                $paket2->fl_cetak       = $recMaster2['fl_cetak'];
                $paket2->fl_perform     = $recMaster2['fl_perform'];
                $paket2->create_tgl     = $recMaster2['create_tgl'];
                $paket2->create_userid  = $recMaster2['create_userid'];
                $paket2->create_lokasi  = $recMaster2['create_lokasi'];
                $paket2->save();
            }

            DB::commit();
            $response['message'] = 'Simpan data berhasil';
            return response()->success('Success',$response);

        } catch(Throwable $e) {
            DB::rollback();
            throw $e;
        }

        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
