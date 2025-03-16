<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\LevelBahan;

class LevelBahanController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= LevelBahan::from('m_level_bahan as a')
        ->selectRaw("a.kd_level, a.nm_level");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_level','like',$filter)
                    ->orwhere('a.nm_level','like',$filter);
            });
        };
        //$data['m_level_bahan']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_level_bahan']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_level_bahan']= LevelBahan::from('m_level_bahan as a')
        ->selectRaw("a.kd_level, a.nm_level")
        ->orderBy("a.kd_level","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_level=isset($request->kd_level) ? $request->kd_level : 0;
        $data['m_level_bahan']= LevelBahan::from('m_level_bahan as a')
        ->selectRaw("a.kd_level, a.nm_level")
        ->where("a.kd_level",$kd_level)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_level=isset($request->kd_level) ? $request->kd_level : 0;
        Rak::where('kd_level',$kd_level)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_level_bahan'];

        $validator=Validator::make($rec,[
            'kd_level'=>'bail|required',
            'nm_level'=>'bail|required',
        ],[
            'kd_level.required'=>'Kode harus diisi',
            'nm_level.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = LevelBahan::where('kd_level',$where['kd_level'])->first();
        if (!($rak)) {
            $rak = new LevelBahan();
        }
        $rak->kd_level = $rec['kd_level'];
        $rak->nm_level = $rec['nm_level'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
