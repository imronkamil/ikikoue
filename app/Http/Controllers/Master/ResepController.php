<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Resep1;
use App\Models\Master\Resep2;
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

class ResepController extends Controller
{
    public function show1(Request $request) {
        $level = $request->level;
        $aktif = $request->aktif;

        $query1= DB::table('m_resep2 as a')
        ->selectRaw('a.kd_bahan_resep, COUNT(*) as qty_record')
        ->groupBy('a.kd_bahan_resep');
        $query2= DB::table('m_resep2 as a')
        ->selectRaw('a.kd_bahan, COUNT(*) as qty_record')
        ->groupBy('a.kd_bahan');
        $data1= Resep1::from('m_resep1 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan_resep','=','b.kd_bahan')
        ->leftJoinSub($query1,'c', function($join)
            {
                $join->on('a.kd_bahan_resep','=','c.kd_bahan_resep');
            })
        ->leftJoinSub($query2,'d', function($join)
            {
                $join->on('a.kd_bahan_resep','=','d.kd_bahan');
            })
        ->selectRaw("a.*, b.nm_bahan, c.kd_bahan_resep AS kd_item, d.kd_bahan AS kd_item_resep");

        if (!($level=='')){
            $data1=$data1->where("b.kd_level","=",$level);
        };
        if (!($aktif=='')){
            $data1=$data1->where("a.fl_aktif","=",$aktif);
        };

        $data['m_resep1']=$data1->orderBy('kd_bahan_resep','asc')->get();

        $data['m_resep2']= Resep2::from('m_resep2 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.*, b.nm_bahan, b.satuan")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        $data['m_resep_bahan']= Resep2::from('m_resep2 as a')
        ->leftJoin('m_resep1 as b','a.kd_bahan_resep','=','b.kd_bahan_resep')
        ->leftJoin('m_bahan as c','a.kd_bahan','=','c.kd_bahan')
        ->leftJoin('m_bahan as d','a.kd_bahan_resep','=','d.kd_bahan')
        ->selectRaw("a.*, d.nm_bahan, c.satuan, b.fl_aktif, b.qty_resep, b.satuan_resep")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function show2(Request $request) {
        $query1= DB::table('m_resep2 as a')
        ->selectRaw('a.kd_bahan_resep, COUNT(*) as qty_record')
        ->groupBy('a.kd_bahan_resep');
        $query2= DB::table('m_resep2 as a')
        ->selectRaw('a.kd_bahan, COUNT(*) as qty_record')
        ->groupBy('a.kd_bahan');
        $data['m_resep1']= Resep1::from('m_resep1 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan_resep','=','b.kd_bahan')
        ->leftJoinSub($query1,'c', function($join)
            {
                $join->on('a.kd_bahan_resep','=','c.kd_bahan_resep');
            })
        ->leftJoinSub($query2,'d', function($join)
            {
                $join->on('a.kd_bahan_resep','=','d.kd_bahan');
            })
        ->selectRaw("a.*, b.nm_bahan, c.kd_bahan_resep AS kd_item, d.kd_bahan AS kd_item_resep")
        ->orderBy("a.kd_bahan_resep","asc")
        ->get();

        $data['m_resep2']= Resep2::from('m_resep2 as a')
        ->leftJoin('m_bahan as b','a.kd_bahan','=','b.kd_bahan')
        ->selectRaw("a.*, b.nm_bahan, b.satuan")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        $data['m_resep_bahan']= Resep2::from('m_resep2 as a')
        ->leftJoin('m_resep1 as b','a.kd_bahan_resep','=','b.kd_bahan_resep')
        ->leftJoin('m_bahan as c','a.kd_bahan','=','c.kd_bahan')
        ->leftJoin('m_bahan as d','a.kd_bahan_resep','=','d.kd_bahan')
        ->selectRaw("a.*, d.nm_bahan, c.satuan, b.fl_aktif, b.qty_resep, b.satuan_resep")
        ->orderBy("a.kd_bahan","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $data['m_resep1']= Resep1::from('m_resep1  as a')
        ->selectRaw("a.*")
        ->where('a.kd_bahan_resep',$kd_bahan)
        ->first();
        return response()->success('Success',$data);
    }

    public function getResepDetail(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $data['m_resep2']= Resep2::from('m_resep2  as a')
        ->selectRaw("a.*")
        ->where('a.kd_bahan_resep',$kd_bahan)
        ->orderBy('a.no_urut','asc')
        ->get();
        return response()->success('Success',$data);
    }

    public function isBahan(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan : 0;
        $bahan= Resep1::from('m_bahan  as a')
        ->where("a.kd_bahan",$kd_bahan)
        ->where("a.fl_pakai",'=','true')
        ->exists();
        $response= ['value' => $bahan ? 'false' : 'true'];
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';

        //Master Resep1
        $data['m_resep1']= Resep1::from('m_resep1 as a')
        ->selectRaw("a.*")
        ->where('a.kd_bahan_resep',$kd_bahan)
        ->first();

        //Master Resep2
        $data['m_resep2']= Resep1::from('m_resep2 as a')
        ->selectRaw("a.*")
        ->where('a.kd_bahan_resep',$kd_bahan)
        ->orderBy('a.no_urut','asc')
        ->get();

        //Master Bahan
        $data['m_bahan']= Resep1::from('m_bahan as a')
        ->selectRaw("a.*")
        ->where('a.fl_aktif','=','true')
        ->orderBy("a.kd_bahan","asc")
        ->get();

        //Master Bahan Resep
        $data['m_resep']= Resep1::from('m_bahan as a')
        ->leftJoin('m_resep1 as b','a.kd_bahan','=','b.kd_bahan_resep')
        ->selectRaw("a.*, a.kd_bahan || ' - ' || a.nm_bahan AS ket_bahan")
        ->whereNull('b.kd_bahan_resep')
        ->where('a.fl_aktif','=','true')
        ->orWhere('a.kd_bahan',$kd_bahan)
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

        //Master Level Bahan
        $data['m_level_bahan']= LevelBahan::from('m_level_bahan as a')
        ->selectRaw("a.kd_level, a.nm_level")
        ->orderBy("a.kd_level","asc")
        ->get();

        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        Resep2::where('kd_bahan_resep',$kd_bahan)->delete();
        Resep1::where('kd_bahan_resep',$kd_bahan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $kd_bahan= $where['kd_bahan'];
        $master1 = $data['m_resep1'];
        $master2 = $data['m_resep2'];

        DB::beginTransaction();
        try {
            //Data Bahan
            $validator=Validator::make($master1,[
                'kd_bahan_resep'=>'bail|required',
                'nm_bahan_resep'=>'bail|required',
                'satuan_resep'=>'bail|required',
            ],[
                'kd_bahan_resep.required'=>'Kode harus diisi',
                'nm_bahan_resep.required'=>'Nama resep harus diisi',
                'satuan_resep.required'=>'Satuan resep harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $resep1= Resep1::where('kd_bahan_resep',$where['kd_bahan'])->first();
            if (!($resep1)) {
                $resep1= new Resep1();
            }
            $resep1->kd_bahan_resep = $master1['kd_bahan_resep'];
            $resep1->nm_bahan_resep = $master1['nm_bahan_resep'];
            $resep1->qty_resep      = $master1['qty_resep'];
            $resep1->satuan_resep   = $master1['satuan_resep'];
            $resep1->catatan_resep  = $master1['catatan_resep'];
            $resep1->rp_biaya       = $master1['rp_biaya'];
            $resep1->fl_balik       = $master1['fl_balik'];
            $resep1->fl_aktif       = $master1['fl_aktif'];
            $resep1->create_tgl     = $master1['create_tgl'];
            $resep1->create_userid  = $master1['create_userid'];
            $resep1->create_lokasi  = $master1['create_lokasi'];
            $resep1->update_tgl     = $master1['update_tgl'];
            $resep1->update_userid  = $master1['update_userid'];
            $resep1->update_lokasi  = $master1['update_lokasi'];
            $resep1->rp_harga_est   = $master1['rp_harga_est'];
            $resep1->fl_potong      = $master1['fl_potong'];
            $resep1->save();

            //Data Resep2
            $existingIds = Resep2::where('kd_bahan_resep',$kd_bahan)->pluck('resep2_id')->toArray();
            $newIds = collect($master2)->pluck('resep2_id')->filter()->toArray();
            // Delete items that are not in request
            $toDelete = array_diff($existingIds, $newIds);
            Resep2::whereIn('resep2_id', $toDelete)->delete();

            //Resep2::where('kd_bahan_resep',$where['kd_bahan'])->delete(); //Hapus data existing
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

                $resep2 = Resep2::where('resep2_id',$recMaster2['resep2_id'])->first();
                if (!($resep2)) {
                    $resep2 = new Resep2();
                    $resep2->resep2_id = Resep2::max('resep2_id') + 1;
                }
                $resep2->kd_bahan_resep = $recMaster2['kd_bahan_resep'];
                $resep2->kd_bahan       = $recMaster2['kd_bahan'];
                $resep2->no_urut        = $recMaster2['no_urut'];
                $resep2->nm_bahan_lain  = $recMaster2['nm_bahan_lain'];
                $resep2->satuan         = $recMaster2['satuan'];
                $resep2->qty            = $recMaster2['qty'];
                $resep2->faktor         = $recMaster2['faktor'];
                $resep2->rp_harga       = $recMaster2['rp_harga'];
                $resep2->rp_harga_min   = $recMaster2['rp_harga_min'];
                $resep2->rp_harga_max   = $recMaster2['rp_harga_max'];
                $resep2->rp_harga_akhir = $recMaster2['rp_harga_akhir'];
                $resep2->catatan        = $recMaster2['catatan'];
                $resep2->kd_catatan     = $recMaster2['kd_catatan'];
                $resep2->fl_potong      = $recMaster2['fl_potong'];
                $resep2->create_tgl     = $recMaster2['create_tgl'];
                $resep2->create_userid  = $recMaster2['create_userid'];
                $resep2->create_lokasi  = $recMaster2['create_lokasi'];
                $resep2->update_tgl     = $recMaster2['update_tgl'];
                $resep2->update_userid  = $recMaster2['update_userid'];
                $resep2->update_lokasi  = $recMaster2['update_lokasi'];
                $resep2->fl_resep       = $recMaster2['fl_resep'];
                $resep2->save();
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
