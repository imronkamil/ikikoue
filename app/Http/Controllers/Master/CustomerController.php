<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Customer;

class CustomerController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data['m_customer']= Customer::from('m_customer  as a')
        ->leftJoin('m_customer_grup as b','a.kd_customer_grup','=','b.kd_customer_grup')
        ->leftJoin('m_level_harga as c','a.kd_harga','=','c.kd_harga')
        ->selectRaw("a.*, b.nm_customer_grup, c.nm_harga")
        ->orderBy("a.kd_customer","asc")
        //->take(1000)
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_customer'] = Customer::from('m_customer  as a')
        ->selectRaw("a.*")
        ->orderBy("a.kd_customer","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;
        $data['m_customer'] = Rak::from('m_customer  as a')
        ->selectRaw("*")
        ->where("a.kd_customer",$kd_customer)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;
        Customer::where('kd_customer',$kd_customer)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_customer'];

        $validator=Validator::make($rec,[
            'kd_customer'=>'bail|required',
            'nm_customer'=>'bail|required',
        ],[
            'kd_customer.required'=>'Kode harus diisi',
            'nm_customer.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = Rak::where('kd_customer',$where['kd_rak'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_rak = $rec['kd_customer'];
        $rak->nm_rak = $rec['nm_customer'];
        $rak->fl_aktif = true;
        $rak->create_tgl = $rec['create_tgl'];
        $rak->create_userid = $rec['create_userid'];
        $rak->create_lokasi = $rec['create_lokasi'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
