<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\BahanKlp;

class BahanKlpController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= BahanKlp::from('m_bahan_klp as a')
        ->selectRaw("a.bahan_klp_id, a.nama");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.bahan_klp_id','like',$filter)
                    ->orwhere('a.nama','like',$filter);
            });
        };
        //$data['m_bahan_klp']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_bahan_klp']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_bahan_klp']= BahanKlp::from('m_bahan_klp as a')
        ->selectRaw("a.bahan_klp_id, a.nama")
        ->orderBy("a.bahan_klp_id","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $bahan_klp_id=isset($request->bahan_klp_id) ? $request->bahan_klp_id : 0;
        $data['m_bahan_klp']= BahanKlp::from('m_bahan_klp as a')
        ->selectRaw("a.bahan_klp_id, a.nama")
        ->where("a.bahan_klp_id",$bahan_klp_id)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $bahan_klp_id=isset($request->bahan_klp_id) ? $request->bahan_klp_id : 0;
        Rak::where('bahan_klp_id',$bahan_klp_id)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_bahan_klp'];

        $validator=Validator::make($rec,[
            'bahan_klp_id'=>'bail|required',
            'nama'=>'bail|required',
        ],[
            'bahan_klp_id.required'=>'Kode harus diisi',
            'nama.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = BahanKlp::where('bahan_klp_id',$where['bahan_klp_id'])->first();
        if (!($rak)) {
            $rak = new LevelBahan();
        }
        $rak->bahan_klp_id = $rec['bahan_klp_id'];
        $rak->nama = $rec['nama'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
