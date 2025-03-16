<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\HargaJual;

class HargaJualController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        //var_dump($sortBy);
        $data1 = HargaJual::from('m_harga_jual as a')
        ->selectRaw("a.harga_jual_id, a.kd_bahan, a.kd_harga, a.persen_harga, a.rp_harga, b.nm_harga")
        ->leftJoin('m_level_harga as b','a.kd_harga','=','b.kd_harga')
        /*if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_bahan','like',$filter);
            });
        }*/
        ->where('a.kd_bahan',$filter);
        //var_dump($data1);
        $data['m_harga_jual']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_harga_jual'] = HargaJual::from('m_harga_jual as a')
        ->selectRaw("a.harga_jual_id, a.kd_bahan, a.kd_harga, a.persen_harga, a.rp_harga")
        ->orderBy("a.kd_bahan","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $kd_harga=isset($request->kd_harga) ? $request->kd_harga :'';
        $data['m_harga_jual'] = HargaJual::from('m_harga_jual as a')
        ->selectRaw("a.harga_jual_id, a.kd_bahan, a.kd_harga, a.persen_harga, a.rp_harga, b.nm_harga")
        ->leftJoin('m_level_harga as b','a.kd_harga','=','b.kd_harga')
        ->where('a.kd_bahan',$kd_bahan)
        ->where('a.kd_harga',$kd_harga)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_bahan=isset($request->kd_bahan) ? $request->kd_bahan :'';
        $kd_harga=isset($request->kd_harga) ? $request->kd_harga :'';
        HargaJual::where('kd_bahan',$kd_bahan)->where('kd_harga',$kd_harga)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_harga_jual'];

        foreach($rec as $line) {
            $validator=Validator::make($line,[
                'kd_bahan'=>'bail|required',
                'kd_harga'=>'bail|required',
            ],[
                'kd_bahan.required'=>'Kode Bahan harus diisi',
                'kd_harga.required'=>'Kode Harga harus diisi',
            ]);
            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $hargajual= HargaJual::where('harga_jual_id',$line['harga_jual_id'])->first();
            if (!($hargajual)) {
                $hargajual= new HargaJual();
            }
            $hargajual->kd_bahan = $line['kd_bahan'];
            $hargajual->kd_harga = $line['kd_harga'];
            $hargajual->persen_harga = $line['persen_harga'];
            $hargajual->rp_harga = $line['rp_harga'];
            $hargajual->save();
        }
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
