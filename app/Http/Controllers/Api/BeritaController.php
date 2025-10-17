<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\berita;

class BeritaController extends Controller
{
        // List semua berita
    public function index()
    {
        $berita = berita::with(['penulis', 'kategori'])->get(); // eager load relasi
        return response()->json(['status' => 'success', 'data' => $berita], 200);
    }

    // Tampilkan detail berita
    public function show($id)
    {
        $berita = berita::with(['penulis', 'kategori'])->find($id);
        if (!$berita) {
            return response()->json(['status' => 'error', 'message' => 'Berita tidak ditemukan'], 404);
        }
        return response()->json(['status' => 'success', 'data' => $berita], 200);
    }

    // Buat berita baru
    public function store(Request $request)
    {
        $request->validate([
            'id_penulis' => 'required|exists:users,id',
            'id_kategori' => 'required|exists:kategori,id',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|max:2048',
            'tgl_terbit' => 'required|date',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        $berita = berita::create($data);

        return response()->json(['status' => 'success', 'data' => $berita], 201);
    }

    // Update berita
    public function update(Request $request, $id)
    {
        $berita = berita::find($id);
        if (!$berita) {
            return response()->json(['status' => 'error', 'message' => 'Berita tidak ditemukan'], 404);
        }

        $request->validate([
            'id_penulis' => 'sometimes|exists:users,id',
            'id_kategori' => 'sometimes|exists:kategori,id',
            'judul' => 'sometimes|string|max:255',
            'isi' => 'sometimes|string',
            'gambar' => 'nullable|image|max:2048',
            'tgl_terbit' => 'sometimes|date',
        ]);

        $data = $request->all();

        if ($request->hasFile('gambar')) {
            $data['gambar'] = $request->file('gambar')->store('berita', 'public');
        }

        $berita->update($data);

        return response()->json(['status' => 'success', 'data' => $berita], 200);
    }

    // Hapus berita
    public function destroy($id)
    {
        $berita = berita::find($id);
        if (!$berita) {
            return response()->json(['status' => 'error', 'message' => 'Berita tidak ditemukan'], 404);
        }

        $berita->delete();
        return response()->json(['status' => 'success', 'message' => 'Berita berhasil dihapus'], 200);
    }

}
