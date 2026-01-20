<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Bank;

class BankController extends Controller
{
    public function show1(Request $request) {
        $data['m_bank']= Bank::from('m_bank as a')
        ->selectRaw("a.*")
        ->orderBy("a.kd_bank","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_bank']= Bank::from('m_bank as a')
        ->selectRaw("a.*")
        ->orderBy("a.kd_bank","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_bank=isset($request->kd_bank) ? $request->kd_bank : 0;
        $data['m_bank']= Bank::from('m_bank as a')
        ->selectRaw("a.*")
        ->where("a.kd_bank",$kd_bank)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_bank=isset($request->kd_bank) ? $request->kd_bank : 0;
        Bank::where('kd_bank',$kd_bank)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_bank'];

        $validator=Validator::make($rec,[
            'kd_bank'=>'bail|required',
            'nm_bank'=>'bail|required',
        ],[
            'kd_bank.required'=>'Kode harus diisi',
            'nm_bank.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $lokasi = Bank::where('kd_bank',$where['kd_bank'])->first();
        if (!($lokasi)) {
            $lokasi = new Bank();
        }
        $lokasi->kd_bank = $rec['kd_bank'];
        $lokasi->nm_bank = $rec['nm_bank'];
        $lokasi->fl_aktif = true;
        $lokasi->create_tgl = $rec['create_tgl'];
        $lokasi->create_userid = $rec['create_userid'];
        $lokasi->create_lokasi = $rec['create_lokasi'];
        $lokasi->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
