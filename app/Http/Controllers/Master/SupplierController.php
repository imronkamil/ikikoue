<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Supplier;

class SupplierController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1 = Supplier::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi");
        if (!($filter=='')){
            $filter='%'.trim($filter).'%';
            $data1=$data1->where(function($q) use ($filter) {
                    $q->where('a.kd_supplier','like',$filter)
                    ->orwhere('a.nm_supplier','like',$filter);
            });
        };
        //$data['m_rak']=$data->orderBy($sortBy,$sorting)->paginate($limit);
        $data['m_supplier']=$data1->orderBy($sortBy,$sorting)->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_supplier'] = Supplier::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->orderBy("a.kd_supplier","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_supplier=isset($request->kd_supplier) ? $request->kd_supplier : 0;
        $data['m_supplier'] = Rak::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->where("a.kd_supplier",$kd_supplier)
        ->first();
        return response()->success('Success',$data);
    }

    public function destroy(Request $request) {
        $kd_supplier=isset($request->kd_supplier) ? $request->kd_supplier : 0;
        Supplier::where('kd_supplier',$kd_supplier)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['m_supplier'];

        $validator=Validator::make($rec,[
            'kd_supplier'=>'bail|required',
            'nm_supplier'=>'bail|required',
        ],[
            'kd_supplier.required'=>'Kode harus diisi',
            'nm_supplier.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $rak = Rak::where('kd_supplier',$where['kd_rak'])->first();
        if (!($rak)) {
            $rak = new Rak();
        }
        $rak->kd_rak = $rec['kd_supplier'];
        $rak->nm_rak = $rec['nm_supplier'];
        $rak->fl_aktif = true;
        $rak->create_tgl = $rec['create_tgl'];
        $rak->create_userid = $rec['create_userid'];
        $rak->create_lokasi = $rec['create_lokasi'];
        $rak->save();
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
