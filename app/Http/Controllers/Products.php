<?php

namespace App\Http\Controllers;

use App\Models\Products as ProductsModels;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use File;

class Products extends Controller
{
    
    public function produk(Request $request){

        $product = ProductsModels::orderBy('created_at', 'desc')->paginate(5);

        return response()->json([
            'message' => 'Data semua produk',
            'success' => true,
            'data' => $product
        ], 200);
    }

    public function detail($id){

        $product = ProductsModels::findOrFail($id);

        return response()->json([
            'success' => true,
            'message' => 'Detail produk',
            'data' => $product
        ], 200);
    }

    public function simpan(Request $request){        

        $validator = Validator::make($request->all(), [
            'gambar' =>'required|image|mimes:jpg,jpeg,svg,png,gif|max:2048',
            'judul' => 'required',
            'harga' => 'required',
            'deskripsi' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 422);
        } else {

            $image = $request->file('gambar');
            $namaImg = 'pic_' . uniqid();
            $path = $image->getClientOriginalExtension();
            $image->move('produk', $namaImg . '.' . $path);

             $product = ProductsModels::create([
                'gambar' => $namaImg . '.' . $path,
                'judul' => $request->judul,
                'harga' => $request->harga,
                'deskripsi' => $request->deskripsi
             ]);

             if($product){

                return response()->json([
                    'success' => true,
                    'message' => 'Data sudah tersimpan',
                    'data' => $product
                 ], 201);

             } else {
                return response()->json([
                    'message' => 'Data gagal tersimpan',
                    'success' => false
                ], 409);
             }
        }
    }

    public function update(Request $request, ProductsModels $id){

        $validator = Validator::make($request->all(), [
            'judul' => 'required',
            'harga' => 'required',
            'deskripsi' => 'required'
        ]);

        if($validator->fails()){
            return response()->json($validator->errors(), 400);
        } else {
             $product = ProductsModels::findOrFail($id->id);

             if($product){

                if($request->file('gambar')){

                    if(File::exists(public_path('produk/' . $id->gambar))){
                        File::delete(public_path('produk/' . $id->gambar));
                    }

                    $image = $request->file('gambar');
                    $namaImg = 'pic_' . uniqid();
                    $path = $image->getClientOriginalExtension();
                    $image->move('produk', $namaImg . '.' . $path);
        
                     $product->update([
                        'gambar' => $namaImg . '.' . $path,
                        'judul' => $request->judul,
                        'harga' => $request->harga,
                        'deskripsi' => $request->deskripsi
                     ]);

                } else {
                    $product->update([
                        'judul' => $request->judul,
                        'harga' => $request->harga,
                        'deskripsi' => $request->deskripsi
                    ]);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Data sudah terupdate',
                    'data' => $product
                 ], 200);

             } else {
                return response()->json([
                    'message' => 'Data gagal terupdate',
                    'success' => false
                ], 409);
             }
        }
    }   

    public function hapus($id){

        $product = ProductsModels::findOrFail($id);

        if($product){
            $product->delete();
            if(File::exists(public_path('produk/' . $product->gambar))){
                File::delete(public_path('produk/' . $product->gambar));
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

    }

}
