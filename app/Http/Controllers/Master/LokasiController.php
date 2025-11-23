<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Lokasi;

class LokasiController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_lokasi','like',$filter)
                    ->orwhere('a.nm_lokasi','like',$filter);
            });
        };
        //$data['m_lokasi']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_lokasi']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_lokasi']= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_lokasi","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : 0;
        $data['m_lokasi']= Lokasi::from('m_lokasi as a')
        ->selectRaw("a.kd_lokasi, a.nm_lokasi, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_lokasi",$kd_lokasi)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_lokasi=isset($request->kd_lokasi) ? $request->kd_lokasi : 0;
        Lokasi::where('kd_lokasi',$kd_lokasi)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_lokasi'];

        $validator=Validator::make($rec,[
            'kd_lokasi'=>'bail|required',
            'nm_lokasi'=>'bail|required',
        ],[
            'kd_lokasi.required'=>'Kode harus diisi',
            'nm_lokasi.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $lokasi = Lokasi::where('kd_lokasi',$where['kd_lokasi'])->first();
        if (!($lokasi)) {
            $lokasi = new Rak();
        }
        $lokasi->kd_lokasi = $rec['kd_lokasi'];
        $lokasi->nm_lokasi = $rec['nm_lokasi'];
        $lokasi->fl_aktif = true;
        $lokasi->create_tgl = $rec['create_tgl'];
        $lokasi->create_userid = $rec['create_userid'];
        $lokasi->create_lokasi = $rec['create_lokasi'];
        $lokasi->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
