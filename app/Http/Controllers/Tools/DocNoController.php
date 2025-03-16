<?php

namespace App\Http\Controllers\Tools;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\Tools\DocNo;
use App\Models\Tools\DocNoThn;
use App\Models\Tools\NoTran;

class DocNoController extends Controller
{
    public function show1(Request $request) {
        $filter = $request->filter;
        //$limit = $request->limit;
        $sorting = ($request->descending=="true") ? "desc" :"asc";
        $sortBy = $request->sortBy;
        $data['i_docno']= DocNo::from('i_docno as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.enum_counter, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.base_name, CASE WHEN a.enum_counter=0 THEN 'Lanjut' WHEN a.enum_counter=1 THEN 'Per Tahun'
            ELSE 'Per Bulan' END as ket_counter")
        ->orderBy($sortBy,$sorting)
        ->get();
        return response()->success('Success',$data);
    }

    public function show2() {
        $data['i_docno']= DocNo::from('i_docno as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.enum_counter, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.base_name, CASE WHEN a.enum_counter=0 THEN 'Lanjut' WHEN a.enum_counter=1 THEN 'Per Tahun'
            ELSE 'Per Bulan' END as ket_counter")
        ->orderBy("a.base_type")
        ->get();
        return response()->success('Success',$data);
    }

    public function get(Request $request) {
        $doc_name=isset($request->doc_name) ? $request->doc_name : '';
        $data['t_notran']= NoTran::from('t_notran as a')
        ->selectRaw("a.doc_name, a.doc_key")
        ->where("a.doc_name",$doc_name)
        ->first();
        return response()->success('Success',$data);
    }

    public function getAllRef(Request $request) {
        $docno_id=isset($request->docno_id) ? $request->docno_id : 0;

        $data['i_docno']= DocNo::from('i_docno as a')
        ->leftJoin('i_doctype as b','a.base_type','=','b.base_type')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.enum_counter, a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi,
            b.base_name, CASE WHEN a.enum_counter=0 THEN 'Lanjut' WHEN a.enum_counter=1 THEN 'Per Tahun'
            ELSE 'Per Bulan' END as ket_counter")
        ->where("a.docno_id",$docno_id)
        ->first();

        $data['i_docno_thn']= DocNo::from('i_docno_thn as a')
        ->selectRaw("a.docno_thn_id, a.docno_id, a.tahun, a.bulan1, a.bulan2, a.bulan3, a.bulan4,
            a.bulan5, a.bulan6, a.bulan7, a.bulan8, a.bulan9, a.bulan10, a.bulan11, a.bulan12, a.no_urut")
        ->where("a.docno_id",$docno_id)
        ->get();

        $data['i_doctype']= DocNo::from('i_doctype as a')
        ->selectRaw("a.base_type, a.base_name")
        ->get();

        return response()->success('Success',$data);
    }

    public static function getDocKey($key_word = "") {
        $doc_name=isset($key_word) ? $key_word : 'doc_key';

        $data= NoTran::from('t_notran as a')
        ->selectRaw("a.doc_name, a.doc_key")
        ->where("a.doc_name",$doc_name)
        ->first();

        if (!($data)) {
            $data= new NoTran();
            $data->doc_name = $key_word;
            $data->doc_key = 1;
        }
        $doc_key=$data['doc_key'];
        $next_key=$doc_key+1;

        $data->doc_key = $next_key;
        $data->save();

        return $doc_key;
    }

    public static function getDocNoTest(Request $request) {
        $dept=$request->dept;
        $partner=$request->partner;
        $tgl=$request->tanggal;
        $format='PR/[D]/[P]/[X]/{yyMM}<0000>';
        $hasil=$format;

        if(str_contains($hasil,"[D]")) {
            $hasil=str_replace("[D]",$dept,$hasil);
        }

        if(str_contains($hasil,"[P]")) {
            $hasil=str_replace("[P]",$partner,$hasil);
        }

        $awal=strpos($hasil,'{');
        $akhir=strpos($hasil,'}');
        $fmttgl=substr($hasil,$awal,$akhir-$awal+1);

        $awal=strpos($hasil,'<');
        $akhir=strpos($hasil,'>');
        $fmtcount=substr($hasil,$awal,$akhir-$awal+1);

        $tgldoc = date_create($tgl);
        $stgl = date_format($tgldoc,'ym');

        //return $hasil . strval($awal) . strval($akhir) . $fmttgl . $fmtcount . $tgl . $stgl;
        return $hasil; //. strval($awal) . strval($akhir) . $fmttgl . $fmtcount . $tgl . $stgl;
    }

    public function getDocNo(Request $request) {
        $docno_id=isset($request->docno_id) ? $request->docno_id : '';
        $tahun=isset($request->tahun) ? $request->tahun : 0;
        $bulan=isset($request->bulan) ? $request->bulan : 0;

        $data['i_docno']= NoTran::from('i_docno as a')
        ->selectRaw("a.docno_id, a.nm_docno, a.base_type, a.doc_type, a.nomor, a.format, a.contoh,
            a.fl_default,
            a.create_tgl, a.create_userid, a.create_lokasi, a.update_tgl, a.update_userid, a.update_lokasi")
        ->where("a.docno_id",$docno_id)
        ->first();

        $data['i_docno_thn']= NoTran::from('i_docno_thn as a')
        ->selectRaw("a.docno_thn_id, a.docno_id, a.tahun, a.bulan1, a.bulan2, a.bulan3, a.bulan4,
            a.bulan5, a.bulan6, a.bulan7, a.bulan8, a.bulan9, a.bulan10, a.bulan11, a.bulan12")
        ->where("a.docno_id",$docno_id)
        ->where("a.tahun",$tahun)
        ->first();
        return response()->success('Success',$data);
    }

    public function setDocNo(Request $request) {
        $docno_id=isset($request->docno_id) ? $request->docno_id : '';
        $tahun=isset($request->tahun) ? $request->tahun : 0;
        $bulan=isset($request->bulan) ? $request->bulan : 0;

        $notran = DocNo::where('docno_id',$docno_id)->first();
        if (!($notran)) {
            $notran = new DocNo();
            $notran->docno_id = $docno_id;
            $notran->nm_docno = $nm_docno;
            $notran->nomor = 1;
            $notran->save();
        }
        else if ($notran->fl_perbulan == TRUE) {
            $notranthn = DocNoThn::where('nm_docno',$nm_docno)
            ->where('tahun',$tahun)->first();
            if (!($notranthn)) {
                $notranthn = new DocNoThn();
                $notranthn->nm_docno = $nm_docno;
                $notranthn->tahun = $tahun;
            }
            switch($bulan) {
                case 1:
                    $notranthn->bulan1 = $notranthn->bulan1+1;
                    break;
                case 2:
                    $notranthn->bulan2 = $notranthn->bulan2+1;
                    break;
                case 3:
                    $notranthn->bulan3 = $notranthn->bulan3+1;
                    break;
                case 4:
                    $notranthn->bulan4 = $notranthn->bulan4+1;
                    break;
                case 5:
                    $notranthn->bulan5 = $notranthn->bulan5+1;
                    break;
                case 6:
                    $notranthn->bulan6 = $notranthn->bulan6+1;
                    break;
                case 7:
                    $notranthn->bulan7 = $notranthn->bulan7+1;
                    break;
                case 8:
                    $notranthn->bulan8 = $notranthn->bulan8+1;
                    break;
                case 9:
                    $notranthn->bulan9 = $notranthn->bulan9+1;
                    break;
                case 10:
                    $notranthn->bulan10 = $notranthn->bulan10+1;
                    break;
                case 11:
                    $notranthn->bulan11 = $notranthn->bulan11+1;
                    break;
                case 12:
                    $notranthn->bulan12 = $notranthn->bulan12+1;
                    break;
                default:
                    $notranthn->no_urut = $notranthn->no_urut+1;
                    break;
            }
            $notranthn->save();
        }
        else {
            $notran->nomor = $notran->nomor+1;
            $notran->save();
        }
        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$notran);
    }

    public function destroy(Request $request) {
        $doc_name=isset($request->doc_name) ? $request->doc_name : '';
        NoTran::where('doc_name',$doc_name)->delete();
        $response['message'] = 'Hapus data berhasil';
        return response()->success('Success',$response);
    }

    public function store(Request $request) {
        $data = $request->json()->all();
        $where= $data['where'];
        $rec  = $data['i_docno'];
        $rec_thn = $data['i_docno_thn'];

        $validator=Validator::make($rec,[
            'nm_docno'=>'bail|required',
        ],[
            'nm_docno.required'=>'Nama dokumen harus diisi',
        ]);
        if ($validator->fails()){
            return response()->error('',501,$validator->errors()->first());
        }

        $i_docno = DocNo::where('docno_id',$where['docno_id'])->first();
        if (!($i_docno)) {
            $i_docno = new DocNo();
        }
        $i_docno->nm_docno       = $rec['nm_docno'];
        $i_docno->base_type      = $rec['base_type'];
        $i_docno->doc_type       = $rec['doc_type'];
        $i_docno->nomor          = $rec['nomor'];
        $i_docno->format         = $rec['format'];
        $i_docno->contoh         = $rec['contoh'];
        $i_docno->enum_counter   = $rec['enum_counter'];
        $i_docno->fl_default     = $rec['fl_default'];
        $i_docno->create_tgl     = $rec['create_tgl'];
        $i_docno->create_userid  = $rec['create_userid'];
        $i_docno->create_lokasi  = $rec['create_lokasi'];
        $i_docno->update_tgl     = $rec['update_tgl'];
        $i_docno->update_userid  = $rec['update_userid'];
        $i_docno->update_lokasi  = $rec['update_lokasi'];
        $i_docno->save();
        $lastInsertId= DB::getPdo()->lastInsertId();

        //Data DocNoThn
        foreach($rec_thn as $recthn) {
            $i_docno_thn = DocNoThn::where('docno_thn_id',$recthn['docno_thn_id'])->first();
            if (!($i_docno_thn)) {
                $i_docno_thn = new DocNoThn();
                $i_docno_thn->docno_id = $lastInsertId;
            }
            $i_docno_thn->tahun       = $recthn['tahun'];
            $i_docno_thn->bulan1      = $recthn['bulan1'];
            $i_docno_thn->bulan2      = $recthn['bulan2'];
            $i_docno_thn->bulan3      = $recthn['bulan3'];
            $i_docno_thn->bulan4      = $recthn['bulan4'];
            $i_docno_thn->bulan5      = $recthn['bulan5'];
            $i_docno_thn->bulan6      = $recthn['bulan6'];
            $i_docno_thn->bulan7      = $recthn['bulan7'];
            $i_docno_thn->bulan8      = $recthn['bulan8'];
            $i_docno_thn->bulan9      = $recthn['bulan9'];
            $i_docno_thn->bulan10     = $recthn['bulan10'];
            $i_docno_thn->bulan11     = $recthn['bulan11'];
            $i_docno_thn->bulan12     = $recthn['bulan12'];
            $i_docno_thn->no_urut     = $recthn['no_urut'];
            $i_docno_thn->save();
        }

        $response['message'] = 'Simpan data berhasil';
        return response()->success('Success',$response);
    }
}
