<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Satuan;

class SatuanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Satuan::from('m_satuan as a')
        ->selectRaw("a.satuan, a.create_tgl, a.create_userid, a.create_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.satuan',$filter);
            });
        }
        //var_dump($data1);
        $data['m_satuan']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_satuan']= Satuan::from('m_satuan as a')
        ->selectRaw("a.satuan, a.create_tgl, a.create_userid, a.create_lokasi")
        ->orderBy("a.satuan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $bahan_id=isset($request->bahan_id) ? $request->bahan_id : 0;
        //var_dump($bahan_id);
        $data['m_satuan']= Satuan::from('m_satuan as a')
        ->selectRaw("a.satuan, a.create_tgl, a.create_userid, a.create_lokasi")
        ->where("a.satuan",$satuan)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $satuan=isset($request->satuan) ? $request->satuan : 0;
        Satuan::where('satuan',$satuan)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_satuan'];

        $validator=Validator::make($rec,[
            'satuan'=>'bail|required',
        ],[
            'satuan.required'=>'Satuan harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $satuan= Satuan::where('satuan',$where['satuan'])->first();
        if (!($satuan)) {
            $satuan = new Satuan();
        }
        $satuan->satuan   = $rec['satuan'];
        $satuan->create_tgl = $rec['create_tgl'];
        $satuan->create_userid = $rec['create_userid'];
        $satuan->create_lokasi = $rec['create_lokasi'];
        $satuan->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
