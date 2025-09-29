<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\BahanSatuan;

class BahanSatuanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump($sortBy);
        $data1= BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        /*if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_bahan','like',$filter);
            });
        }*/
        ->where('a.kd_bahan',$filter);
        //var_dump($data1);
        $data['m_bahan_satuan']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_bahan_satuan'] = BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_bahan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $satuan=isset($request->satuan) ? $request->satuan :'';
        $data['m_bahan_satuan'] = BahanSatuan::from('m_bahan_satuan as a')
        ->selectRaw("a.bahan_satuan_id, a.kd_bahan, a.satuan, a.rasio, a.qty, a.qty_min, a.qty_max, a.qty_normal, a.qty_order_by,
            a.rp_harga_beli, a.persen_beli, a.rp_harga_beli_toleransi, a.rp_harga_beli_min, a.rp_harga_beli_max, a.rp_harga_beli_akhir, a.tgl_beli_akhir,
            a.rp_harga_jual, a.rp_harga_jual_min, a.rp_harga_jual_max, a.rp_harga_jual2, a.fl_pakai, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where('a.kd_bahan',$kd_bahan)
        ->where('a.satuan',$satuan)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $satuan=isset($request->satuan) ? $request->satuan :'';
        BahanSatuan::where('kd_bahan',$kd_bahan)->where('satuan',$satuan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_bahan_satuan'];

        BahanSatuan::where('kd_bahan',$where['kd_bahan'])->delete();

        foreach($rec as $line) {
            $validator=Validator::make($line,[
                'kd_bahan'=>'bail|required',
                'satuan'=>'bail|required',
            ],[
                'kd_bahan.required'=>'Kode Bahan harus diisi',
                'satuan.required'=>'Satuan harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $bahansat = BahanSatuan::where('bahan_satuan_id',$line['bahan_satuan_id'])->first();
            if (!($bahansat)) {
                $bahansatidnew = BahanSatuan::max('bahan_satuan_id') + 1;
                $bahansat = new BahanSatuan();
                $bahansat['bahan_satuan_id'] = $bahansatidnew;
            }
            $bahansat->kd_bahan = $line['kd_bahan'];
            $bahansat->satuan = $line['satuan'];
            $bahansat->rasio = $line['rasio'];
            $bahansat->rp_harga_beli = $line['rp_harga_beli'];
            $bahansat->rp_harga_beli_min = $line['rp_harga_beli_min'];
            $bahansat->rp_harga_beli_max = $line['rp_harga_beli_max'];
            $bahansat->rp_harga_beli_akhir = $line['rp_harga_beli_akhir'];
            $bahansat->rp_harga_jual = $line['rp_harga_jual'];
            $bahansat->fl_pakai = $line['fl_pakai'];
            $bahansat->fl_default = $line['fl_default'];
            $bahansat->create_tgl = $line['create_tgl'];
            $bahansat->create_userid = $line['create_userid'];
            $bahansat->create_lokasi = $line['create_lokasi'];
            $bahansat->update_tgl = $line['update_tgl'];
            $bahansat->update_userid = $line['update_userid'];
            $bahansat->update_lokasi = $line['update_lokasi'];
            $bahansat->save();
        }
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
