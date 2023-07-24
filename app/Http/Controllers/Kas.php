<?php

namespace App\Http\Controllers;
use App\Models\Kas as KasModels;
use Illuminate\Http\Request;
use File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class Kas extends Controller
{
    
    public function kas(Request $request){
        
        $data_saldo = KasModels::select('saldo')->orderBy('created_at', 'desc')->first();

        $data_pemasukkan = KasModels::select(DB::raw('sum(case when pemasukkan != 0 then pemasukkan else 0 end) as jml_pemasukkan'))->where('pemasukkan', '!=', 0)->get();

        $data_pengeluaran = KasModels::select(DB::raw('sum(case when pengeluaran != 0 then pengeluaran else 0 end) as jml_pengeluaran'))->where('pengeluaran', '!=', 0)->get();

        $data_kas = KasModels::orderBy('created_at', 'desc')->take(10)->get();        

        $data_tgl_grafik = [];
        $data_grafik_nilai = [];
        $data_grafik_pemasukkan = [];
        $data_grafik_pengeluaran = [];

        foreach($data_kas as $data){
            array_push($data_tgl_grafik, $data->tanggal);
            array_push($data_grafik_nilai, $data->saldo);
            array_push($data_grafik_pemasukkan, $data->pemasukkan);
            array_push($data_grafik_pengeluaran, $data->pengeluaran);
        }

        return response()->json([
            'message' => 'Data semua Kas',
            'success' => true,
            'data' => $data_kas,
            'data_saldo' => $data_saldo,
            'data_pemasukkan' => $data_pemasukkan,
            'data_pengeluaran' => $data_pengeluaran,
            'data_tgl_grafik' => $data_tgl_grafik,
            'data_grafik_nilai' => $data_grafik_nilai,
            'data_grafik_pemasukkan' => $data_grafik_pemasukkan,
            'data_grafik_pengeluaran' => $data_grafik_pengeluaran
        ], 200);
    }

    public function report_pemasukkan(Request $request){

        $tanggal1 = $request->tanggal1;
        $tanggal2 = $request->tanggal2;

        $data_kas = KasModels::select('*');
        if($tanggal1){
            $data_kas = $data_kas->whereDate('tanggal', '>=', $tanggal1);
        }
        if($tanggal2){
            $data_kas = $data_kas->whereDate('tanggal', '<=', $tanggal2);
        }
        $data_kas = $data_kas
        ->where('pemasukkan', '!=', 0)
        ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data Pemasukkan Kas',
            'success' => true,
            'data' => $data_kas
        ], 200);
    }

    
    public function report_pengeluaran(Request $request){

        $tanggal1 = $request->tanggal1;
        $tanggal2 = $request->tanggal2;

        $data_kas = KasModels::select('*');
        if($tanggal1){
            $data_kas = $data_kas->whereDate('tanggal', '>=', $tanggal1);
        }
        if($tanggal2){
            $data_kas = $data_kas->whereDate('tanggal', '<=', $tanggal2);
        }
        $data_kas = $data_kas
        ->where('pengeluaran', '!=', 0)
        ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data Pengeluaran Kas',
            'success' => true,
            'data' => $data_kas,
            'tanggal' => [$tanggal1, $tanggal2]
        ], 200);
    }

    public function report_saldo(Request $request){

        $tanggal1 = $request->tanggal1;
        $tanggal2 = $request->tanggal2;

        $data_kas = KasModels::select('*');
        if($tanggal1){
            $data_kas = $data_kas->whereDate('tanggal', '>=', $tanggal1);
        }
        if($tanggal2){
            $data_kas = $data_kas->whereDate('tanggal', '<=', $tanggal2);
        }
        $data_kas = $data_kas
        ->orderBy('created_at', 'desc')->get();

        return response()->json([
            'message' => 'Data semua Kas',
            'success' => true,
            'data' => $data_kas
        ], 200);
    }

    public function detail($id){

        $kas = KasModels::where('id_kas', $id)->firstOrFail();

        return response()->json([
            'success' => true,
            'message' => 'Detail kas',
            'data' => $kas,
            'id' => $id
        ], 200);
    }

    public function input_kas(Request $request){

        $validator = Validator::make($request->all(), [
            'gambar' =>'required|image|mimes:jpg,jpeg,svg,png,gif|max:2048',
            'deskripsi' => 'required',
            'pemasukkan' => 'nullable|integer',
            'pengeluaran' => 'nullable|integer'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $pemasukkan = $request->pemasukkan;
            $pengeluaran = $request->pengeluaran;
            $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');

            if($pemasukkan == null){
                $pemasukkan = 0;
            }

            if($pengeluaran == null){
                $pengeluaran = 0;
            }

            $image = $request->file('gambar');
            $namaImg = 'pic_' . uniqid();
            $path = $image->getClientOriginalExtension();
            $image->move('bukti_kas', $namaImg . '.' . $path);

            $kas = KasModels::create([
                'gambar' => $namaImg . '.' . $path,
                'tanggal' => $tanggal,
                'deskripsi' => $request->deskripsi,
                'pemasukkan' => $pemasukkan,
                'pengeluaran' => $pengeluaran
             ]);

            $data_pemasukkan = KasModels::select(DB::raw('sum(case when pemasukkan is not null then pemasukkan else null end) as jml_pemasukkan'))->where('pemasukkan', '!=', null)->get();
            $data_pengeluaran = KasModels::select(DB::raw('sum(case when pengeluaran is not null then pengeluaran else null end) as jml_pengeluaran'))->where('pengeluaran', '!=', null)->get();
            $data_saldo = ((int)$data_pemasukkan[0]->jml_pemasukkan) - ((int)$data_pengeluaran[0]->jml_pengeluaran);

            $kas_saldo = KasModels::where('id_kas', $kas->id)->update([
            'saldo' => $data_saldo
            ]);

            if($kas){

                return response()->json([
                    'success' => true,
                    'message' => 'Data sudah tersimpan',
                    'data' => $kas
                 ], 201);

            } else {
                return response()->json([
                    'message' => 'Data gagal tersimpan',
                    'success' => false
                ], 409);
            }
        }
    }

    public function update_kas(Request $request){

        try {
            
            $id = $request->id;

            $validator = Validator::make($request->all(), [
                'gambar' =>'nullable|image|mimes:jpg,jpeg,svg,png,gif|max:2048',
                'deskripsi' => 'required',
                'pemasukkan' => 'nullable|integer',
                'pengeluaran' => 'nullable|integer'
            ]);

            if($validator->fails()){
                return response()->json($validator->errors(), 400);
            } else {
                $kas = KasModels::where('id_kas', $id)->firstOrFail();
                $kas_array = KasModels::where('id_kas', '>=', $id)->get();

                if($kas){
                    $pemasukkan = (int)$request->pemasukkan;
                    $pengeluaran = (int)$request->pengeluaran;
                    $tanggal = Carbon::now('Asia/Jakarta')->format('Y-m-d');
        
                    if($pemasukkan == null){
                        $pemasukkan = 0;
                    }
        
                    if($pengeluaran == null){
                        $pengeluaran = 0;
                    }

                    if($request->file('gambar')){

                        if(File::exists(public_path('bukti_kas/' . $kas->gambar))){
                            File::delete(public_path('bukti_kas/' . $kas->gambar));
                        }

                        $image = $request->file('gambar');
                        $namaImg = 'pic_' . uniqid();
                        $path = $image->getClientOriginalExtension();
                        $image->move('bukti_kas', $namaImg . '.' . $path);
            
                        KasModels::where('id_kas', $id)->update([
                            'gambar' => $namaImg . '.' . $path,
                            'tanggal' => $tanggal,
                            'deskripsi' => $request->deskripsi,
                            'pemasukkan' => $pemasukkan,
                            'pengeluaran' => $pengeluaran
                        ]);

                    } else {
                        KasModels::where('id_kas', $id)->update([
                            'tanggal' => $tanggal,
                            'deskripsi' => $request->deskripsi,
                            'pemasukkan' => $pemasukkan,
                            'pengeluaran' => $pengeluaran
                        ]);
                    }

                    $tambah_saldo = 0;

                    if($pemasukkan != 0){
                        if($pemasukkan > (int)$kas->pemasukkan){
                            $tambah_saldo = $pemasukkan - (int)$kas->pemasukkan;
                        } else if ($pemasukkan < (int)$kas->pemasukkan){
                            $tambah_saldo = -1 * ((int)$kas->pemasukkan - $pemasukkan);
                        }
                    }

                    if($pengeluaran != 0){
                        if($pengeluaran < (int)$kas->pengeluaran){
                            $tambah_saldo = (int)$kas->pengeluaran -  $pengeluaran;
                        } else if ($pengeluaran > (int)$kas->pengeluaran){
                            $tambah_saldo = -1 * ($pengeluaran - (int)$kas->pengeluaran);
                        }
                    }
                    
                    foreach ($kas_array as $data){
                        
                        $saldo_awal = KasModels::select('saldo')->where('id_kas', $data->id_kas)->first();
        
                        KasModels::where('id_kas', $data->id_kas)->update([
                            'saldo' => (int)$saldo_awal->saldo + $tambah_saldo
                        ]);
                    }

                    return response()->json([
                        'success' => true,
                        'message' => 'Data sudah terupdate'
                    ], 200);

                } else {
                    return response()->json([
                        'message' => 'Data gagal terupdate',
                        'success' => false
                    ], 409);
                }
            }
        } catch (exception $e) {
            return response()->json($e);
        }

    }   

    public function hapus_kas($id){

        try {
            $kas = KasModels::where('id_kas', $id)->firstOrFail();
            $kas_array = KasModels::where('id_kas', '>', $id)->get();

            $tambah_saldo = 0;

            if($kas->pemasukkan != 0){
                $tambah_saldo = -1 * ((int)$kas->pemasukkan);
            }

            if($kas->pengeluaran != 0){
                $tambah_saldo = (int)$kas->pengeluaran;
            }

            if($kas_array){
                foreach ($kas_array as $data){
                        
                    $saldo_awal = KasModels::select('saldo')->where('id_kas', $data->id_kas)->first();
    
                    KasModels::where('id_kas', $data->id_kas)->update([
                        'saldo' => (int)$saldo_awal->saldo + $tambah_saldo
                    ]);
                }
            }

            if($kas){
                KasModels::where('id_kas', $id)->delete();
                if(File::exists(public_path('bukti_kas/' . $kas->gambar))){
                    File::delete(public_path('bukti_kas/' . $kas->gambar));
                }
    
                return response()->json([
                    'success' => true,
                    'message' => 'Data sudah terhapus',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }
        } catch (exception $e) {
            return response()->json($e);
        }

    }

}
