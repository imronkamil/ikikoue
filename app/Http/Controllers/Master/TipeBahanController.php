<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\TipeBahan;

class TipeBahanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= TipeBahan::from('m_tipe_bahan as a')
        ->selectRaw("a.kd_tipe_bahan, a.nm_tipe_bahan,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_tipe_bahan','like',$filter)
                    ->orwhere('a.nm_tipe_bahan','like',$filter);
            });
        };
        //$data['m_tipe_bahan']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_tipe_bahan']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_tipe_bahan']= TipeBahan::from('m_tipe_bahan as a')
        ->selectRaw("a.kd_tipe_bahan, a.nm_tipe_bahan,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_tipe_bahan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $rak_id=isset($request->rak_id) ? $request->rak_id : 0;
        $data['m_tipe_bahan']= TipeBahan::from('m_tipe_bahan as a')
        ->selectRaw("a.kd_tipe_bahan, a.nm_tipe_bahan,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.rak_id",$rak_id)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_tipe_bahan=isset($request->kd_tipe_bahan) ? $request->kd_tipe_bahan : 0;
        TipeBahan::where('kd_tipe_bahan',$kd_tipe_bahan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_tipe_bahan'];

        $validator=Validator::make($rec,[
            'kd_tipe_bahan'=>'bail|required',
            'nm_tipe_bahan'=>'bail|required',
        ],[
            'kd_tipe_bahan.required'=>'Kode harus diisi',
            'nm_tipe_bahan.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = TipeBahan::where('kd_tipe_bahan',$where['kd_tipe_bahan'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_tipe_bahan = $rec['kd_tipe_bahan'];
        $rak->nm_tipe_bahan = $rec['nm_tipe_bahan'];
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
