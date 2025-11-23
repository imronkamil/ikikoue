<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Paket1;
use App\Models\Master\Paket2;

class PaketController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1= Lokasi::from('m_paket1 as a')
        ->selectRaw("a.kd_paket, a.nm_paket, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_paket','like',$filter)
                    ->orwhere('a.nm_paket','like',$filter);
            });
        };
        //$data['m_paket1']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_paket1']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_paket1']= Lokasi::from('m_paket1 as a')
        ->selectRaw("a.kd_paket, a.nm_paket, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->orderBy("a.kd_paket","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_paket=isset($request->kd_paket) ? $request->kd_paket : '';
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : '';
        $data['m_paket1']= Paket1::from('m_paket1 as a')
        ->leftJoin('m_customer_paket as b',function ($join) use ($kd_customer) {
            $join->on('b.kd_paket','=','a.kd_paket')
                 ->where('b.kd_customer',$kd_customer);
        })
        ->selectRaw("a.kd_paket, a.nm_paket, a.satuan, a.rp_harga, a.keterangan, a.fl_aktif, a.fl_harga,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.rp_harga as rp_harga_ocp")
        ->where("a.kd_paket",$kd_paket)
        ->first();
        $data['m_paket2']= Paket2::from('m_paket2 as a')
        ->selectRaw("a.kd_paket, a.kd_bahan, a.no_urut, a.satuan, a.qty, a.rp_harga, a.keterangan, a.fl_cetak, a.fl_perform,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.kd_paket",$kd_paket)
        ->orderBy("a.no_urut","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_paket=isset($request->kd_paket) ? $request->kd_paket : 0;
        Lokasi::where('kd_paket',$kd_paket)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_paket1'];

        $validator=Validator::make($rec,[
            'kd_paket'=>'bail|required',
            'nm_paket'=>'bail|required',
        ],[
            'kd_paket.required'=>'Kode harus diisi',
            'nm_paket.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $lokasi = Lokasi::where('kd_paket',$where['kd_paket'])->first();
        if (!($lokasi)) {
            $lokasi = new Rak();
        }
        $lokasi->kd_paket = $rec['kd_paket'];
        $lokasi->nm_paket1 = $rec['nm_paket'];
        $lokasi->fl_aktif = true;
        $lokasi->create_tgl = $rec['create_tgl'];
        $lokasi->create_userid = $rec['create_userid'];
        $lokasi->create_lokasi = $rec['create_lokasi'];
        $lokasi->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
