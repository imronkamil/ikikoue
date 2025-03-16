<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Rak;

class RakController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Rak::from('m_rak as a')
        ->selectRaw("a.kd_rak, a.nm_rak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_rak','like',$filter)
                    ->orwhere('a.nm_rak','like',$filter);
            });
        };
        //$data['m_rak']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_rak']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_rak']= Rak::from('m_rak as a')
        ->selectRaw("a.kd_rak, a.nm_rak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_rak","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $rak_id=isset($request->rak_id) ? $request->rak_id : 0;
        $data['m_rak']= Rak::from('m_rak as a')
        ->selectRaw("a.kd_rak, a.nm_rak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.rak_id",$rak_id)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_rak=isset($request->kd_rak) ? $request->kd_rak : 0;
        Rak::where('kd_rak',$kd_rak)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_rak'];

        $validator=Validator::make($rec,[
            'kd_rak'=>'bail|required',
            'nm_rak'=>'bail|required',
        ],[
            'kd_rak.required'=>'Kode harus diisi',
            'nm_rak.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = Rak::where('kd_rak',$where['kd_rak'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_rak = $rec['kd_rak'];
        $rak->nm_rak = $rec['nm_rak'];
        $rak->fl_aktif = true;
        $rak->create_tgl = $rec['create_tgl'];
        $rak->create_userid = $rec['create_userid'];
        $rak->create_lokasi = $rec['create_lokasi'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
