<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Master\Supplier;
use App\Models\Master\SupplierBahan;
use App\Models\Master\Kontak;

class SupplierController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data1 = Supplier::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.telp2, a.telp3, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
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
            a.telp, a.telp2, a.telp3, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->orderBy("a.kd_supplier","asc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_supplier=isset($request->kd_supplier) ? $request->kd_supplier : 0;
        $data['m_supplier'] = Supplier::from('m_supplier  as a')
        ->selectRaw("a.kd_supplier, a.nm_supplier, a.alamat, a.kota, a.propinsi, a.kodepos, a.negara, a.contact,
            a.telp, a.fax, a.email, a.webpage, a.kd_term, a.kd_supplier_grup, a.notes, a.fl_aktif,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            a.no_rekening, a.nm_rekening, a.fl_konsinyasi")
        ->where("a.kd_supplier",$kd_supplier)
        ->first();
        return response()->success('Success',$data);
    }

    public function isSupplier(Request $request) {
        $kd_supplier=isset($request->kd_supplier) ? $request->kd_supplier : 0;
        $supplier= Supplier::from('m_supplier as a')
        ->join('t_ap_invoice1 as b','a.kd_supplier','=','b.kd_partner')
        ->where("a.kd_supplier",$kd_supplier)
        ->exists();
        $response= ['value' => $supplier ? 'false' : 'true'];
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $kd_supplier=isset($request->kd_supplier) ? $request->kd_supplier : 0;

        $data['m_supplier'] = Supplier::from('m_supplier as a')
        ->selectRaw("*")
        ->where("a.kd_supplier",$kd_supplier)
        ->first();

        $data['m_supplier_bahan'] = Supplier::from('m_supplier_bahan as a')
        ->selectRaw("*")
        ->where("a.kd_supplier",$kd_supplier)
        ->get();

        $data['m_supplier_grup'] = Supplier::from('m_supplier_grup as a')
        ->selectRaw("*")
        ->get();

        $data['m_kontak'] = Supplier::from('m_kontak as a')
        ->selectRaw("*")
        ->where("a.kd_partner",$kd_supplier)
        ->get();

        $data['m_term'] = Supplier::from('m_term as a')
        ->selectRaw("*")
        ->orderBy('a.kd_term','asc')
        ->get();

        $data['m_bahan'] = Supplier::from('m_bahan as a')
        ->selectRaw("*")
        ->orderBy('a.nm_bahan','asc')
        ->get();

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
        $kd_supplier= $where;
        $master  = $data['m_supplier'];
        $master2 = $data['m_supplier_bahan'];
        $master3 = $data['m_kontak'];

        $validator=Validator::make($master,[
            'kd_supplier'=>'bail|required',
            'nm_supplier'=>'bail|required',
        ],[
            'kd_supplier.required'=>'Kode harus diisi',
            'nm_supplier.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $supplier = Supplier::where('kd_supplier',$where['kd_supplier'])->first();
        if (!($supplier)) {
            $supplier = new Supplier();
        }
        $supplier->kd_supplier  = $master['kd_supplier'];
        $supplier->nm_supplier   = $master['nm_supplier'];
        $supplier->alamat        = $master['alamat'];
        $supplier->kota          = $master['kota'];
        $supplier->propinsi      = $master['propinsi'];
        $supplier->kodepos       = $master['kodepos'];
        $supplier->contact       = $master['contact'];
        $supplier->telp          = $master['telp'];
        $supplier->telp2         = $master['telp2'];
        $supplier->telp3         = $master['telp3'];
        $supplier->fax           = $master['fax'];
        $supplier->email         = $master['email'];
        $supplier->webpage       = $master['webpage'];
        $supplier->kd_term       = $master['kd_term'];
        $supplier->kd_supplier_grup = $master['kd_supplier_grup'];
        $supplier->notes         = $master['notes'];
        $supplier->fl_aktif      = $master['fl_aktif'];
        $supplier->create_tgl    = $master['create_tgl'];
        $supplier->create_userid = $master['create_userid'];
        $supplier->create_lokasi = $master['create_lokasi'];
        $supplier->update_tgl    = $master['update_tgl'];
        $supplier->update_userid = $master['update_userid'];
        $supplier->update_lokasi = $master['update_lokasi'];
        $supplier->no_rekening   = $master['no_rekening'];
        $supplier->nm_rekening   = $master['nm_rekening'];
        $supplier->fl_konsinyasi = $master['fl_konsinyasi'];
        $supplier->save();

        //Data SupplierBahan
        $existingIds = SupplierBahan::where('kd_supplier',$kd_supplier)->pluck('supplier_bahan_id')->toArray();
        $newIds = collect($master3)->pluck('supplier_bahan_id')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        SupplierBahan::whereIn('supplier_bahan_id', $toDelete)->delete();

        //SupplierBahan::where('supplier_bahan_id',$doc_key)->delete(); //Hapus data existing
        foreach($master2 as $recMaster2) {
            $validator=Validator::make($recMaster2,[
                'kd_bahan'=>'bail|required',
                //'alamat'=>'bail|required',
            ],[
                'kd_bahan.required'=>'Kode harus diisi',
                //'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $suppBahan = SupplierBahan::where('supplier_bahan_id',$recMaster2['supplier_bahan_id'])->first();
            if (!($suppBahan)) {
                $suppBahan = new SupplierBahan();
                $suppBahan->supplier_bahan_id = SupplierBahan::max('supplier_bahan_id') + 1;
            }
            $suppBahan->kd_supplier    = $supplier->kd_supplier;
            $suppBahan->kd_bahan       = $recMaster2['kd_bahan'];
            $suppBahan->catatan        = $recMaster2['catatan'];
            $suppBahan->save();
        }

        //Data Kontak
        $existingIds = Kontak::where('kd_partner',$kd_supplier)->pluck('kd_kontak')->toArray();
        $newIds = collect($master3)->pluck('kd_kontak')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        Kontak::whereIn('kd_kontak', $toDelete)->delete();

        //Kontak::where('kd_kontak',$doc_key)->delete(); //Hapus data existing
        foreach($master3 as $recMaster3) {
            $validator=Validator::make($recMaster3,[
                'nm_kontak'=>'bail|required',
                //'alamat'=>'bail|required',
            ],[
                'nm_kontak.required'=>'Kode harus diisi',
                //'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $kontak = Kontak::where('kd_kontak',$recMaster3['kd_kontak'])->first();
            if (!($kontak)) {
                $kontak = new Kontak();
                $kontak->kd_kontak = Kontak::max('kd_kontak') + 1;
            }
            $kontak->kd_partner     = $supplier->kd_supplier;
            $kontak->nm_kontak      = $recMaster3['nm_kontak'];
            $kontak->titel          = $recMaster3['titel'];
            $kontak->dept           = $recMaster3['dept'];
            $kontak->posisi         = $recMaster3['posisi'];
            $kontak->alamat         = $recMaster3['alamat'];
            $kontak->telp1          = $recMaster3['telp1'];
            $kontak->telp2          = $recMaster3['telp2'];
            $kontak->telp_hp        = $recMaster3['telp_hp'];
            $kontak->fax            = $recMaster3['fax'];
            $kontak->email          = $recMaster3['email'];
            $kontak->catatan        = $recMaster3['catatan'];
            $kontak->enum_kelamin   = $recMaster3['enum_kelamin'];
            $kontak->fl_default     = $recMaster3['fl_default'];
            $kontak->fl_aktif       = $recMaster3['fl_aktif'];
            $kontak->create_tgl     = $recMaster3['create_tgl'];
            $kontak->create_userid  = $recMaster3['create_userid'];
            $kontak->create_lokasi  = $recMaster3['create_lokasi'];
            $kontak->update_tgl     = $recMaster3['update_tgl'];
            $kontak->update_userid  = $recMaster3['update_userid'];
            $kontak->update_lokasi  = $recMaster3['update_lokasi'];
            $kontak->save();
        }

        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
