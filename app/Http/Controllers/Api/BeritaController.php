<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * @OA\Tag(
 *     name="Berita",
 *     description="API Endpoints untuk manajemen berita"
 * )
 */
class BeritaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/berita",
     *     tags={"Berita"},
     *     summary="Get all news",
     *     description="Mengambil daftar semua berita dengan relasi penulis (dari user) dan kategori",
     *     @OA\Response(
     *         response=200,
     *         description="List semua berita",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_kategori", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita..."),
     *                 @OA\Property(property="gambar", type="string", example="image.jpg"),
     *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *                 @OA\Property(
     *                     property="penulis",
     *                     type="object",
     *                     @OA\Property(property="id_user", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="penulis")
     *                 ),
     *                 @OA\Property(property="kategori", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $berita = Berita::with('kategori')->get()->map(function ($item) {
            $penulis = User::find($item->id_user);

            return [
                'id_berita' => $item->id_berita,
                'id_user' => $item->id_user,
                'id_kategori' => $item->id_kategori,
                'judul' => $item->judul,
                'isi' => $item->isi,
                'gambar' => $item->gambar,
                'tgl_terbit' => $item->tgl_terbit,
                'penulis' => $penulis ? [
                    'id_user' => $penulis->id_user,
                    'username' => $penulis->username,
                    'name' => $penulis->name,
                    'email' => $penulis->email,
                    'role' => $penulis->role
                ] : null,
                'kategori' => $item->kategori
            ];
        });

        return response()->json($berita);
    }


    /**
     * @OA\Get(
     *     path="/berita/{id_berita}",
     *     tags={"Berita"},
     *     summary="Get single news",
     *     description="Mengambil detail berita berdasarkan ID dengan relasi penulis (dari user) dan kategori",
     *     @OA\Parameter(
     *         name="id_berita",
     *         in="path",
     *         description="ID berita",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail berita",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *             @OA\Property(property="isi", type="string", example="Isi berita..."),
     *             @OA\Property(property="gambar", type="string", example="image.jpg"),
     *             @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(
     *                 property="penulis",
     *                 type="object",
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="John Doe"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="penulis")
     *             ),
     *             @OA\Property(property="kategori", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Berita tidak ditemukan"
     *     )
     * )
     */
    public function show($id_berita): JsonResponse
    {
        $berita = Berita::with('kategori')->find($id_berita);

        if (!$berita) {
            return response()->json(['error' => 'Berita tidak ditemukan'], 404);
        }

        $penulis = \App\Models\User::find($berita->id_user); // Ambil penulis dari tabel user

        return response()->json([
            'id_berita' => $berita->id_berita,
            'id_user' => $berita->id_user,
            'id_kategori' => $berita->id_kategori,
            'judul' => $berita->judul,
            'isi' => $berita->isi,
            'gambar' => $berita->gambar,
            'tgl_terbit' => $berita->tgl_terbit,
            'penulis' => $penulis ? [
                'id_user' => $penulis->id_user,
                'username' => $penulis->username,
                'name' => $penulis->name,
                'email' => $penulis->email,
                'role' => $penulis->role
            ] : null,
            'kategori' => $berita->kategori
        ]);
    }


    /**
     * @OA\Get(
     *     path="/berita/user/{id_user}",
     *     tags={"Berita"},
     *     summary="Get news by user",
     *     description="Mengambil daftar semua berita yang dibuat oleh user tertentu",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         description="ID user / penulis",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List berita milik user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_kategori", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita..."),
     *                 @OA\Property(property="gambar", type="string", example="image.jpg"),
     *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *                 @OA\Property(property="kategori", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User tidak ditemukan atau tidak memiliki berita"
     *     )
     * )
     */
    public function getByUser($id_user): JsonResponse
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $berita = Berita::with('kategori')
            ->where('id_user', $id_user)
            ->get();

        return response()->json($berita);
    }



    /**
     * @OA\Post(
     *     path="/berita",
     *     tags={"Berita"},
     *     summary="Create new news",
     *     description="Menambahkan berita baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id_user", "id_kategori", "judul", "isi", "tgl_terbit"},
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_kategori", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita..."),
     *                 @OA\Property(property="gambar", type="string", format="binary"),
     *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *                 @OA\Property(property="is_premium", type="boolean", example=false),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Berita berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *             @OA\Property(property="isi", type="string", example="Isi berita..."),
     *             @OA\Property(property="gambar", type="string", example="image.jpg"),
     *             @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="is_premium", type="boolean", example=false)
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|integer|exists:user,id_user',
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_terbit' => 'required|date',
            'id_premium' => 'nullable|boolean'
        ]);

        $data = $request->only(['id_user', 'id_kategori', 'judul', 'isi', 'tgl_terbit']);

        // Handle image upload
        if ($request->hasFile('gambar')) {
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('berita', $filename, 'public');
            $data['gambar'] = $path;
        }

        $berita = Berita::create($data);

        return response()->json($berita, 201);
    }


    /**
     * @OA\Post(
     *     path="/berita/{id_berita}",
     *     tags={"Berita"},
     *     summary="Update news",
     *     description="Mengubah data berita (gunakan POST dengan _method=PUT untuk upload file)",
     *     @OA\Parameter(
     *         name="id_berita",
     *         in="path",
     *         description="ID berita",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_kategori", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita..."),
     *                 @OA\Property(property="gambar", type="string", format="binary"),
     *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita berhasil diupdate"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Berita tidak ditemukan"
     *     )
     * )
     */
    public function update(Request $request, $id_berita): JsonResponse
    {
        $berita = Berita::find($id_berita);
        if (!$berita) {
            return response()->json(['error' => 'Berita tidak ditemukan'], 404);
        }

        $request->validate([
            'id_user' => 'sometimes|required|integer',
            'id_kategori' => 'sometimes|required|integer|exists:kategori,id_kategori',
            'judul' => 'sometimes|required|string|max:255',
            'isi' => 'sometimes|required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_terbit' => 'sometimes|required|date'
        ]);

        $data = $request->all(); // ambil semua field, termasuk yang dikirim via multipart

        foreach (['id_user', 'id_kategori', 'judul', 'isi', 'tgl_terbit'] as $field) {
            if (isset($data[$field])) {
                $berita->$field = $data[$field];
            }
        }

        // Handle image upload
        if ($request->hasFile('gambar')) {
            if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
                Storage::disk('public')->delete($berita->gambar);
            }
            $file = $request->file('gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('berita', $filename, 'public');
            $berita->gambar = $path;
        }

        $berita->save();

        return response()->json($berita);
    }



    /**
     * @OA\Delete(
     *     path="/berita/{id_berita}",
     *     tags={"Berita"},
     *     summary="Delete news",
     *     description="Menghapus berita",
     *     @OA\Parameter(
     *         name="id_berita",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Berita tidak ditemukan"
     *     )
     * )
     */
    public function destroy($id_berita): JsonResponse
    {
        $berita = Berita::find($id_berita);
        if (!$berita) {
            return response()->json(['error' => 'Berita tidak ditemukan'], 404);
        }

        // Delete image if exists
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        return response()->json(['message' => 'Berita berhasil dihapus']);
    }
}
