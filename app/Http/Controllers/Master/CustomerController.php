<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\Master\Customer;
use App\Models\Master\CustomerAlamat;
use App\Models\Master\CustomerBahan;
use App\Models\Master\CustomerPaket;
use App\Models\Master\Kontak;

class CustomerController extends Controller
{
    public function show1(Request $request) {
        //$filter = $request->filter;
        //$limit = $request->limit;
        //$sorting = ($request->descending=="true") ? "desc" :"asc";
        //$sortBy = $request->sortBy;
        $data['m_customer']= Customer::from('m_customer  as a')
        ->leftJoin('m_customer_grup as b','a.kd_customer_grup','=','b.kd_customer_grup')
        ->leftJoin('m_level_harga as c','a.kd_harga','=','c.kd_harga')
        ->selectRaw("a.*, b.nm_customer_grup, b.no_account, c.nm_harga")
        ->orderBy("a.kd_customer","desc")
        //->take(10000)
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['m_customer'] = Customer::from('m_customer  as a')
        ->leftJoin('m_customer_grup as b','a.kd_customer_grup','=','b.kd_customer_grup')
        ->selectRaw("a.*, b.nm_customer_grup, b.no_account")
        ->orderBy("a.kd_customer","desc")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;
        $data['m_customer'] = Customer::from('m_customer  as a')
        ->leftJoin('i_docno as b','a.nm_docno','=','b.nm_docno')
        ->selectRaw("a.*, b.docno_id")
        ->where("a.kd_customer",$kd_customer)
        ->first();
        return response()->success('Success',$data);
    }

    public function getCustomer(Request $request) {
        $filter=Str::lower(isset($request->filter) ? $request->filter : '');
        $data['m_customer'] = Customer::from('m_customer  as a')
        ->selectRaw("*")
        ->where(function ($query) use ($filter) {
            $query->where(DB::raw('lower(a.kd_customer)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.nm_customer)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.alamat)'), 'like', '%' . $filter . '%')
                  //->orWhere(DB::raw('lower(b.contact)'), 'like', '%' . $filter . '%')
                  //->orWhere(DB::raw('lower(b.telp)'), 'like', '%' . $filter . '%')
                  //->orWhere(DB::raw('lower(b.fax)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.telp)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.nm_kontak_pengirim)'), 'like', '%' . $filter . '%')
                  ->orWhere(DB::raw('lower(a.email)'), 'like', '%' . $filter . '%')
                  ->orWhereRaw('? = ?', [$filter, '']);
        })->get();
        return response()->success('Success',$data);
    }

    public function isCustomer(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;
        $customer= Customer::from('m_customer  as a')
        ->join('t_so1 as b','a.kd_customer','=','b.kd_partner')
        ->where("a.kd_customer",$kd_customer)
        ->exists();
        $response= ['value' => $customer ? 'false' : 'true'];
        return response()->success('Success',$response);
    }

    public function getAllRef(Request $request) {
        $kd_customer=isset($request->kd_customer) ? $request->kd_customer : 0;

        $DocSO= 31;
        $data['i_docno'] = Customer::from('i_docno as a')
        ->selectRaw("*")
        ->where('a.base_type',$DocSO)
        ->first();

        $data['m_customer'] = Customer::from('m_customer as a')
        ->selectRaw("*")
        ->where("a.kd_customer",$kd_customer)
        ->first();

        $data['m_customer_bahan'] = Customer::from('m_customer_bahan as a')
        ->selectRaw("*")
        ->where("a.kd_customer",$kd_customer)
        ->get();

        $data['m_customer_alamat'] = Customer::from('m_customer_alamat as a')
        ->selectRaw("*")
        ->where("a.kd_customer",$kd_customer)
        ->orderBy('a.kd_customer')
        ->orderBy('a.kd_alamat')
        ->get();

        $data['m_customer_paket'] = Customer::from('m_customer_paket as a')
        ->selectRaw("*")
        ->where("a.kd_customer",$kd_customer)
        ->get();

        $data['m_customer_grup'] = Customer::from('m_customer_grup as a')
        ->selectRaw("*")
        ->get();

        $data['m_kontak'] = Customer::from('m_kontak as a')
        ->selectRaw("*")
        ->where("a.kd_partner",$kd_customer)
        ->get();

        $data['m_level_harga'] = Customer::from('m_level_harga as a')
        ->selectRaw("*")
        ->orderBy('a.kd_harga')
        ->get();

        $data['m_bayar'] = Customer::from('m_bayar as a')
        ->selectRaw("*")
        ->where('a.fl_aktif','=','true')
        ->orderBy('a.kd_bayar')
        ->get();

        $data['m_term'] = Customer::from('m_term as a')
        ->selectRaw("*")
        ->orderBy('a.kd_term')
        ->get();

        $data['m_grup_harga'] = Customer::from('m_grup_harga as a')
        ->selectRaw("*")
        ->orderBy('a.kd_grup_harga')
        ->get();

        $data['m_bahan'] = Customer::from('m_bahan as a')
        ->leftJoin('m_bahan_satuan as b',function ($join) {
            $join->on('a.kd_bahan','=','b.kd_bahan')
                 ->on('a.satuan','=','b.satuan');
        })
        ->selectRaw("a.*, b.rp_harga_jual AS rp_harga_jual_satuan")
        ->orderBy('a.kd_bahan')
        ->get();

        $data['m_bahan_jual'] = Customer::from('m_bahan as a')
        ->leftJoin('m_bahan_satuan as b',function ($join) {
            $join->on('a.kd_bahan','=','b.kd_bahan')
                 ->on('a.satuan','=','b.satuan');
        })
        ->selectRaw("a.*, b.rp_harga_jual AS rp_harga_jual_satuan")
        ->where('a.fl_aktif','=','true')
        ->where('a.fl_jual','=','true')
        ->orderBy('a.nm_bahan')
        ->get();

        $data['m_paket1'] = Customer::from('m_paket1 as a')
        ->selectRaw("*")
        ->where('a.fl_aktif','=','true')
        ->orderBy('a.nm_paket')
        ->get();

        $data['m_staf'] = Customer::from('m_staf as a')
        ->selectRaw("*")
        ->where('a.fl_aktif','=','true')
        ->where('a.fl_sales','=','true')
        ->orderBy('a.nm_staf')
        ->get();

        $data['m_propinsi'] = Customer::from('m_propinsi as a')
        ->selectRaw("*")
        //->whereIn('a.kd_propinsi',[25823,26141,51578]) //DKI,Jabar,Banten
        ->where('a.fl_aktif','=','true')
        ->orderBy('a.kd_propinsi')
        ->get();

        $propinsi= $data['m_propinsi']->pluck('kd_propinsi')->toArray();
        $data['m_kabupaten'] = Customer::from('m_kabupaten as a')
        ->selectRaw("*")
        ->whereIn('a.kd_propinsi',$propinsi)
        ->orderBy('a.kd_kabupaten')
        ->get();

        $kabupaten= $data['m_kabupaten']->pluck('kd_kabupaten')->toArray();
        $data['m_kecamatan'] = Customer::from('m_kecamatan as a')
        ->selectRaw("*")
        ->whereIn('a.kd_kabupaten',$kabupaten)
        ->orderBy('a.kd_kecamatan')
        ->get();

        $kecamatan= $data['m_kecamatan']->pluck('kd_kecamatan')->toArray();
        $data['m_kelurahan'] = Customer::from('m_kelurahan as a')
        ->selectRaw("*")
        ->whereIn('kd_kecamatan',$kecamatan)
        ->orderBy('a.kd_kelurahan')
        ->get();

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
        $kd_customer= $where;
        $master  = $data['m_customer'];
        $master2 = $data['m_customer_alamat'];
        $master3 = $data['m_customer_bahan'];
        $master4 = $data['m_customer_paket'];
        $master5 = $data['m_kontak'];

        $validator=Validator::make($master,[
            //'kd_customer'=>'bail|required',
            'nm_customer'=>'bail|required',
        ],[
            //'kd_customer.required'=>'Kode harus diisi',
            'nm_customer.required'=>'Nama harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $cust_id= 0;
        $customer = Customer::where('kd_customer',$where['kd_customer'])->first();
        if (!($customer)) {
            $cust_id = Customer::max('customer_id') + 1;
            $customer = new Customer();
            $customer->customer_id = $cust_id;
            $customer->kd_customer = 'C'.substr('000000'.$cust_id,-6);
            $customer->id = $cust_id;
        }
        $customer->nm_customer    = $master['nm_customer'];
        $customer->alamat         = $master['alamat'];
        $customer->propinsi       = $master['propinsi'];
        $customer->kota           = $master['kota'];
        $customer->kecamatan      = $master['kecamatan'];
        $customer->kodepos        = $master['kodepos'];
        $customer->alamat_kirim   = $master['alamat_kirim'];
        $customer->propinsi_kirim = $master['propinsi_kirim'];
        $customer->kota_kirim     = $master['kota_kirim'];
        $customer->kecamatan_kirim= $master['kecamatan_kirim'];
        $customer->kodepos_kirim  = $master['kodepos_kirim'];
        $customer->negara         = $master['negara'];
        $customer->contact        = $master['contact'];
        $customer->telp           = $master['telp'];
        $customer->fax            = $master['fax'];
        $customer->email          = $master['email'];
        $customer->webpage        = $master['webpage'];
        $customer->kd_term        = $master['kd_term'];
        $customer->kd_customer_grup= $master['kd_customer_grup'];
        $customer->kd_salesman    = $master['kd_salesman'];
        $customer->notes          = $master['notes'];
        $customer->npwp           = $master['npwp'];
        $customer->npwpaddress    = $master['npwpaddress'];
        $customer->discount       = $master['discount'];
        $customer->fl_aktif       = $master['fl_aktif'];
        $customer->create_tgl     = $master['create_tgl'];
        $customer->create_userid  = $master['create_userid'];
        $customer->create_lokasi  = $master['create_lokasi'];
        $customer->update_tgl     = $master['update_tgl'];
        $customer->update_userid  = $master['update_userid'];
        $customer->update_lokasi  = $master['update_lokasi'];
        $customer->nm_docno       = $master['nm_docno'];
        $customer->nm_docno_retur = $master['nm_docno_retur'];
        $customer->id             = $master['id'];
        $customer->kd_survey      = $master['kd_survey'];
        $customer->nm_survey      = $master['nm_survey'];
        $customer->kd_harga       = $master['kd_harga'];
        $customer->kelurahan      = $master['kelurahan'];
        $customer->kelurahan_kirim= $master['kelurahan_kirim'];
        $customer->fl_ocp         = $master['fl_ocp'];
        $customer->nm_kontak_pengirim= $master['nm_kontak_pengirim'];
        $customer->telp_kirim     = $master['telp_kirim'];
        $customer->fax_kirim      = $master['fax_kirim'];
        $customer->kd_bayar       = $master['kd_bayar'];
        $customer->nm_customer_kirim= $master['nm_customer_kirim'];
        $customer->save();

        //Data CustomerAlamat
        $existingIds = CustomerAlamat::where('kd_customer',$kd_customer)->pluck('customer_alamat_id')->toArray();
        $newIds = collect($master2)->pluck('customer_alamat_id')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        CustomerAlamat::whereIn('customer_alamat_id', $toDelete)->delete();

        //CustomerAlamat::where('customer_alamat_id',$doc_key)->delete(); //Hapus data existing
        foreach($master2 as $recMaster2) {
            $validator=Validator::make($recMaster2,[
                'nm_alamat'=>'bail|required',
                'alamat'=>'bail|required',
            ],[
                'nm_alamat.required'=>'Kode harus diisi',
                'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $custAlamat = CustomerAlamat::where('customer_alamat_id',$recMaster2['customer_alamat_id'])->first();
            if (!($custAlamat)) {
                $custAlamat = new CustomerAlamat();
                $custAlamat->customer_alamat_id = CustomerAlamat::max('customer_alamat_id') + 1;
                $custAlamat->kd_alamat = CustomerAlamat::where('kd_customer',$customer->kd_customer)->max('kd_alamat') + 1;
            }
            $custAlamat->kd_customer    = $customer->kd_customer;
            $custAlamat->nm_alamat      = $recMaster2['nm_alamat'];
            $custAlamat->alamat         = $recMaster2['alamat'];
            $custAlamat->propinsi       = $recMaster2['propinsi'];
            $custAlamat->kota           = $recMaster2['kota'];
            $custAlamat->kecamatan      = $recMaster2['kecamatan'];
            $custAlamat->kelurahan      = $recMaster2['kelurahan'];
            $custAlamat->kodepos        = $recMaster2['kodepos'];
            $custAlamat->negara         = $recMaster2['negara'];
            $custAlamat->contact        = $recMaster2['contact'];
            $custAlamat->telp           = $recMaster2['telp'];
            $custAlamat->fax            = $recMaster2['fax'];
            $custAlamat->email          = $recMaster2['email'];
            $custAlamat->catatan        = $recMaster2['catatan'];
            $custAlamat->fl_aktif       = $recMaster2['fl_aktif'];
            $custAlamat->create_tgl     = $recMaster2['create_tgl'];
            $custAlamat->create_userid  = $recMaster2['create_userid'];
            $custAlamat->create_lokasi  = $recMaster2['create_lokasi'];
            $custAlamat->update_tgl     = $recMaster2['update_tgl'];
            $custAlamat->update_userid  = $recMaster2['update_userid'];
            $custAlamat->update_lokasi  = $recMaster2['update_lokasi'];
            $custAlamat->save();
        }

        //Data CustomerBahan
        $existingIds = CustomerBahan::where('kd_customer',$kd_customer)->pluck('customer_bahan_id')->toArray();
        $newIds = collect($master3)->pluck('customer_bahan_id')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        CustomerBahan::whereIn('customer_bahan_id', $toDelete)->delete();

        //CustomerBahan::where('customer_bahan_id',$doc_key)->delete(); //Hapus data existing
        foreach($master3 as $recMaster3) {
            $validator=Validator::make($recMaster3,[
                'kd_bahan'=>'bail|required',
                //'alamat'=>'bail|required',
            ],[
                'kd_bahan.required'=>'Kode harus diisi',
                //'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $custBahan = CustomerBahan::where('customer_bahan_id',$recMaster3['customer_bahan_id'])->first();
            if (!($custBahan)) {
                $custBahan = new CustomerBahan();
                $custBahan->customer_bahan_id = CustomerBahan::max('customer_bahan_id') + 1;
            }
            $custBahan->kd_customer    = $customer->kd_customer;
            $custBahan->kd_bahan       = $recMaster3['kd_bahan'];
            $custBahan->catatan        = $recMaster3['catatan'];
            $custBahan->rp_harga       = $recMaster3['rp_harga'];
            $custBahan->save();
        }

        //Data CustomerPaket
        $existingIds = CustomerPaket::where('kd_customer',$kd_customer)->pluck('customer_paket_id')->toArray();
        $newIds = collect($master4)->pluck('customer_paket_id')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        CustomerPaket::whereIn('customer_paket_id', $toDelete)->delete();

        //CustomerPaket::where('customer_bahan_id',$doc_key)->delete(); //Hapus data existing
        foreach($master4 as $recMaster4) {
            $validator=Validator::make($recMaster4,[
                'kd_paket'=>'bail|required',
                //'alamat'=>'bail|required',
            ],[
                'kd_paket.required'=>'Kode harus diisi',
                //'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $custPaket = CustomerPaket::where('customer_paket_id',$recMaster4['customer_paket_id'])->first();
            if (!($custPaket)) {
                $custPaket = new CustomerPaket();
                $custPaket->customer_paket_id = CustomerPaket::max('customer_paket_id') + 1;
            }
            $custPaket->kd_customer    = $customer->kd_customer;
            $custPaket->kd_paket       = $recMaster4['kd_paket'];
            $custPaket->catatan        = $recMaster4['catatan'];
            $custPaket->rp_harga       = $recMaster4['rp_harga'];
            $custPaket->save();
        }

        //Data Kontak
        $existingIds = Kontak::where('kd_partner',$kd_customer)->pluck('kd_kontak')->toArray();
        $newIds = collect($master5)->pluck('kd_kontak')->filter()->toArray();
        // Delete items that are not in request
        $toDelete = array_diff($existingIds, $newIds);
        Kontak::whereIn('kd_kontak', $toDelete)->delete();

        //Kontak::where('kd_kontak',$doc_key)->delete(); //Hapus data existing
        foreach($master5 as $recMaster5) {
            $validator=Validator::make($recMaster5,[
                'nm_kontak'=>'bail|required',
                //'alamat'=>'bail|required',
            ],[
                'nm_kontak.required'=>'Kode harus diisi',
                //'alamat.required'=>'Alamat harus diisi',
            ]);

            if ($validator->fails()){
                return response()->error('',501,$validator->errors()->first());
            }

            $kontak = Kontak::where('kd_kontak',$recMaster5['kd_kontak'])->first();
            if (!($kontak)) {
                $kontak = new Kontak();
                $kontak->kd_kontak = Kontak::max('kd_kontak') + 1;
            }
            $kontak->kd_partner     = $customer->kd_partner;
            $kontak->nm_kontak      = $recMaster5['nm_kontak'];
            $kontak->titel          = $recMaster5['titel'];
            $kontak->dept           = $recMaster5['dept'];
            $kontak->posisi         = $recMaster5['posisi'];
            $kontak->alamat         = $recMaster5['alamat'];
            $kontak->telp1          = $recMaster5['telp1'];
            $kontak->telp2          = $recMaster5['telp2'];
            $kontak->telp_hp        = $recMaster5['telp_hp'];
            $kontak->fax            = $recMaster5['fax'];
            $kontak->email          = $recMaster5['email'];
            $kontak->catatan        = $recMaster5['catatan'];
            $kontak->enum_kelamin   = $recMaster5['enum_kelamin'];
            $kontak->fl_default     = $recMaster5['fl_default'];
            $kontak->fl_aktif       = $recMaster5['fl_aktif'];
            $kontak->create_tgl     = $recMaster5['create_tgl'];
            $kontak->create_userid  = $recMaster5['create_userid'];
            $kontak->create_lokasi  = $recMaster5['create_lokasi'];
            $kontak->update_tgl     = $recMaster5['update_tgl'];
            $kontak->update_userid  = $recMaster5['update_userid'];
            $kontak->update_lokasi  = $recMaster5['update_lokasi'];
            $kontak->save();
        }

        $response['value'] = $customer->kd_customer;
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
