<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Pajak;

class PajakController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Pajak::from('m_pajak as a')
        ->selectRaw("a.kd_pajak, a.nm_pajak, a.persen_pajak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_pajak','like',$filter)
                    ->orwhere('a.nm_pajak','like',$filter);
            });
        };
        //$data['m_pajak']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_pajak']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_pajak']= Pajak::from('m_pajak as a')
        ->selectRaw("a.kd_pajak, a.nm_pajak, a.persen_pajak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_pajak","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_pajak=isset($request->kd_pajak) ? $request->kd_pajak : 0;
        $data['m_pajak']= Pajak::from('m_pajak as a')
        ->selectRaw("a.kd_pajak, a.nm_pajak, a.persen_pajak, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_pajak",$kd_pajak)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_pajak=isset($request->kd_pajak) ? $request->kd_pajak : 0;
        Pajak::where('kd_pajak',$kd_pajak)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_pajak'];

        $validator=Validator::make($rec,[
            'kd_pajak'=>'bail|required',
            'nm_pajak'=>'bail|required',
        ],[
            'kd_pajak.required'=>'Kode harus diisi',
            'nm_pajak.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $pajak = Pajak::where('kd_pajak',$where['kd_pajak'])->first();
        if (!($pajak)) {
            $pajak = new Pajak();
        }
        $pajak->kd_pajak = $rec['kd_pajak'];
        $pajak->nm_pajak = $rec['nm_pajak'];
        $pajak->persen_pajak = $rec['persen_pajak'];
        $pajak->fl_aktif = true;
        $pajak->create_tgl = $rec['create_tgl'];
        $pajak->create_userid = $rec['create_userid'];
        $pajak->create_lokasi = $rec['create_lokasi'];
        $pajak->update_tgl = $rec['update_tgl'];
        $pajak->update_userid = $rec['update_userid'];
        $pajak->update_lokasi = $rec['update_lokasi'];
        $pajak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
