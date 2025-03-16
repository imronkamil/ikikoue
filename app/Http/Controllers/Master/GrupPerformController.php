<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\GrupPerform;

class GrupPerformController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= GrupPerform::from('m_grup_perform as a')
        ->selectRaw("a.kd_grup_perform, a.nm_grup_perform, a.fl_aktif, a.no_urut,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_grup_perform','like',$filter)
                    ->orwhere('a.nm_grup_perform','like',$filter);
            });
        };
        //$data['m_grup_perform']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_grup_perform']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_grup_perform']= GrupPerform::from('m_grup_perform as a')
        ->selectRaw("a.kd_grup_perform, a.nm_grup_perform, a.fl_aktif, a.no_urut,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_grup_perform","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_grup_perform=isset($request->kd_grup_perform) ? $request->kd_grup_perform : 0;
        $data['m_grup_perform']= GrupPerform::from('m_grup_perform as a')
        ->selectRaw("a.kd_grup_perform, a.nm_grup_perform, a.fl_aktif, a.no_urut,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_grup_perform",$kd_grup_perform)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_grup_perform=isset($request->kd_grup_perform) ? $request->kd_grup_perform : 0;
        GrupPerform::where('kd_grup_perform',$kd_grup_perform)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_grup_perform'];

        $validator=Validator::make($rec,[
            'kd_grup_perform'=>'bail|required',
            'nm_grup_perform'=>'bail|required',
        ],[
            'kd_grup_perform.required'=>'Kode harus diisi',
            'nm_grup_perform.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = GrupPerform::where('kd_grup_perform',$where['kd_grup_perform'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_grup_perform = $rec['kd_grup_perform'];
        $rak->nm_grup_perform = $rec['nm_grup_perform'];
        $rak->fl_aktif = $rec['fl_aktif'];
        $rak->no_urut = $rec['no_urut'];
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
