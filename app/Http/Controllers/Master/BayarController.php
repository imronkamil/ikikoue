<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Bayar;

class BayarController extends Controller
{
    public function show1(Request $request) {
        $data['m_bayar']= Bayar::from('m_bayar as a')
        ->selectRaw("a.kd_bayar, a.nm_bayar, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_bayar","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_bayar']= Bayar::from('m_bayar as a')
        ->selectRaw("a.kd_bayar, a.nm_bayar, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_bayar","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bayar=isset($request->kd_bayar) ? $request->kd_bayar : 0;
        $data['m_bayar']= Bayar::from('m_bayar as a')
        ->selectRaw("a.kd_bayar, a.nm_bayar, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_bayar",$kd_bayar)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_bayar=isset($request->kd_bayar) ? $request->kd_bayar : 0;
        Bayar::where('kd_bayar',$kd_bayar)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_bayar'];

        $validator=Validator::make($rec,[
            'kd_bayar'=>'bail|required',
            'nm_bayar'=>'bail|required',
        ],[
            'kd_bayar.required'=>'Kode harus diisi',
            'nm_bayar.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $lokasi = Bayar::where('kd_bayar',$where['kd_bayar'])->first();
        if (!($lokasi)) {
            $lokasi = new Rak();
        }
        $lokasi->kd_bayar = $rec['kd_bayar'];
        $lokasi->nm_bayar = $rec['nm_bayar'];
        $lokasi->fl_aktif = true;
        $lokasi->create_tgl = $rec['create_tgl'];
        $lokasi->create_userid = $rec['create_userid'];
        $lokasi->create_lokasi = $rec['create_lokasi'];
        $lokasi->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
