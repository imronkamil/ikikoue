<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\GrupBahan;

class GrupBahanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= GrupBahan::from('m_grup_bahan as a')
        ->selectRaw("a.kd_grup_bahan, a.nm_grup_bahan,
            a.acc_stok, a.acc_beli, a.acc_retur_beli, a.acc_diskon_beli, a.acc_jual, a.acc_retur_jual,
            a.acc_diskon_jual, a.acc_hpp, a.acc_adj_stok, a.jenis, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_grup_bahan','like',$filter)
                    ->orwhere('a.nm_grup_bahan','like',$filter);
            });
        };
        //$data['m_grup_bahan']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_grup_bahan']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_grup_bahan']= GrupBahan::from('m_grup_bahan as a')
        ->selectRaw("a.kd_grup_bahan, a.nm_grup_bahan,
            a.acc_stok, a.acc_beli, a.acc_retur_beli, a.acc_diskon_beli, a.acc_jual, a.acc_retur_jual,
            a.acc_diskon_jual, a.acc_hpp, a.acc_adj_stok, a.jenis, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_grup_bahan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_grup_bahan=isset($request->kd_grup_bahan) ? $request->kd_grup_bahan : 0;
        $data['m_grup_bahan']= GrupBahan::from('m_grup_bahan as a')
        ->selectRaw("a.kd_grup_bahan, a.nm_grup_bahan,
            a.acc_stok, a.acc_beli, a.acc_retur_beli, a.acc_diskon_beli, a.acc_jual, a.acc_retur_jual,
            a.acc_diskon_jual, a.acc_hpp, a.acc_adj_stok, a.jenis, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_grup_bahan",$kd_grup_bahan)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_grup_bahan=isset($request->kd_grup_bahan) ? $request->kd_grup_bahan : 0;
        GrupBahan::where('kd_grup_bahan',$kd_grup_bahan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_grup_bahan'];

        $validator=Validator::make($rec,[
            'kd_grup_bahan'=>'bail|required',
            'nm_grup_bahan'=>'bail|required',
        ],[
            'kd_grup_bahan.required'=>'Kode harus diisi',
            'nm_grup_bahan.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = GrupBahan::where('kd_grup_bahan',$where['kd_grup_bahan'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_grup_bahan = $rec['kd_grup_bahan'];
        $rak->nm_grup_bahan = $rec['nm_grup_bahan'];
        $rak->acc_stok = $rec['acc_stok'];
        $rak->acc_beli = $rec['acc_beli'];
        $rak->acc_retur_beli = $rec['acc_retur_beli'];
        $rak->acc_diskon_beli = $rec['acc_diskon_beli'];
        $rak->acc_jual = $rec['acc_jual'];
        $rak->acc_retur_jual = $rec['acc_retur_jual'];
        $rak->acc_diskon_jual = $rec['acc_diskon_jual'];
        $rak->acc_hpp = $rec['acc_hpp'];
        $rak->acc_adj_stok = $rec['acc_adj_stok'];
        $rak->jenis = $rec['jenis'];
        $rak->fl_aktif = $rec['fl_aktif'];
        $rak->create_tgl = $rec['create_tgl'];
        $rak->create_userid = $rec['create_userid'];
        $rak->create_lokasi = $rec['create_lokasi'];
        $rak->update_tgl = $rec['update_tgl'];
        $rak->update_userid = $rec['update_userid'];
        $rak->update_lokasi = $rec['update_lokasi'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
