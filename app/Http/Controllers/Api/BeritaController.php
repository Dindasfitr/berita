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
    public function index(Request $request): JsonResponse
    {
        $user = $request->user(); // Ambil user yang sedang login (jika ada)

        $query = Berita::with('kategori');

        // Jika user tidak login atau guest, hanya tampilkan berita non-premium
        if (!$user || $user->membership === 'guest') {
            $query->where('is_premium', false);
        } elseif ($user->membership === 'free') {
            // Free user: bisa lihat berita non-premium dan beberapa premium (misal limit 5)
            $query->where(function ($q) {
                $q->where('is_premium', false)
                  ->orWhere('is_premium', true); // Untuk demo, free bisa lihat semua, tapi di real bisa limit
            });
        } // Premium bisa lihat semua

        $berita = $query->get()->map(function ($item) {
            $penulis = User::find($item->id_user);

            return [
                'id_berita' => $item->id_berita,
                'id_user' => $item->id_user,
                'id_kategori' => $item->id_kategori,
                'judul' => $item->judul,
                'isi' => $item->isi,
                'gambar' => $item->gambar,
                'tgl_terbit' => $item->tgl_terbit,
                'is_premium' => $item->is_premium,
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
    public function show(Request $request, $id_berita): JsonResponse
    {
        $berita = Berita::with('kategori')->find($id_berita);

        if (!$berita) {
            return response()->json(['error' => 'Berita tidak ditemukan'], 404);
        }

        $user = $request->user();

        // Cek akses berdasarkan membership
        if ($berita->is_premium) {
            if (!$user || $user->membership === 'guest') {
                return response()->json(['error' => 'Akses ditolak: Berita premium hanya untuk user login'], 403);
            } elseif ($user->membership === 'free') {
                // Untuk demo, free bisa akses, tapi di real bisa batasi
            } // Premium bisa akses
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
            'is_premium' => $berita->is_premium,
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
     *     description="Menambahkan berita baru. Hanya penulis dan admin yang bisa membuat berita. id_user akan diambil dari user yang sedang login.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"id_kategori", "judul", "isi", "tgl_terbit"},
     *                 @OA\Property(property="id_kategori", type="integer", example=1, description="ID kategori berita"),
     *                 @OA\Property(property="judul", type="string", example="Berita Terkini", description="Judul berita"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita...", description="Konten berita"),
     *                 @OA\Property(property="gambar", type="string", format="binary", description="File gambar berita (opsional)"),
     *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01", description="Tanggal terbit berita"),
     *                 @OA\Property(property="is_premium", type="boolean", example=false, description="Apakah berita premium (default: false)"),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Berita berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1, description="ID penulis (diambil dari user login)"),
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="judul", type="string", example="Berita Terkini"),
     *             @OA\Property(property="isi", type="string", example="Isi berita..."),
     *             @OA\Property(property="gambar", type="string", example="image.jpg"),
     *             @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-01"),
     *             @OA\Property(property="is_premium", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak: Hanya penulis dan admin yang bisa membuat berita",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Akses ditolak: Hanya penulis dan admin yang bisa membuat berita")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        // Cek role: hanya penulis dan admin yang bisa buat berita
        if (!$user || !in_array($user->role, ['penulis', 'admin'])) {
            return response()->json(['error' => 'Akses ditolak: Hanya penulis dan admin yang bisa membuat berita'], 403);
        }

        $request->validate([
            'id_kategori' => 'required|integer|exists:kategori,id_kategori',
            'judul' => 'required|string|max:255',
            'isi' => 'required|string',
            'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'tgl_terbit' => 'required|date',
            'is_premium' => 'sometimes|in:0,1,true,false'
        ]);

        $data = $request->only(['id_kategori', 'judul', 'isi', 'tgl_terbit']);
        $data['is_premium'] = $request->boolean('is_premium', false); // Default false jika tidak ada
        $data['id_user'] = $user->id_user; // Set id_user dari user yang login

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
 *     summary="Update berita (POST, mendukung upload gambar)",
 *     description="Mengubah data berita. Hanya penulis pemilik berita atau admin yang bisa mengedit. Backend otomatis mengenali ini sebagai update.",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="id_berita",
 *         in="path",
 *         description="ID berita yang akan diperbarui",
 *         required=true,
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\MediaType(
 *             mediaType="multipart/form-data",
 *             @OA\Schema(
 *                 @OA\Property(property="id_kategori", type="integer", example=1, description="ID kategori berita"),
 *                 @OA\Property(property="judul", type="string", example="Berita Terkini Updated", description="Judul berita"),
 *                 @OA\Property(property="isi", type="string", example="Isi berita yang sudah diupdate...", description="Konten berita"),
 *                 @OA\Property(property="gambar", type="string", format="binary", description="File gambar berita (opsional)"),
 *                 @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-15", description="Tanggal terbit berita"),
 *                 @OA\Property(property="is_premium", type="boolean", example=false, description="Apakah berita premium")
 *             )
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Berita berhasil diupdate",
 *         @OA\JsonContent(
 *             @OA\Property(property="id_berita", type="integer", example=1),
 *             @OA\Property(property="id_user", type="integer", example=1),
 *             @OA\Property(property="id_kategori", type="integer", example=1),
 *             @OA\Property(property="judul", type="string", example="Berita Terkini Updated"),
 *             @OA\Property(property="isi", type="string", example="Isi berita yang sudah diupdate..."),
 *             @OA\Property(property="gambar", type="string", example="berita/1736345678_image.jpg"),
 *             @OA\Property(property="tgl_terbit", type="string", format="date", example="2025-01-15"),
 *             @OA\Property(property="is_premium", type="boolean", example=false),
 *             @OA\Property(
 *                 property="penulis",
 *                 type="object",
 *                 @OA\Property(property="id_user", type="integer", example=1),
 *                 @OA\Property(property="username", type="string", example="johndoe"),
 *                 @OA\Property(property="name", type="string", example="John Doe"),
 *                 @OA\Property(property="email", type="string", example="john@example.com"),
 *                 @OA\Property(property="role", type="string", example="penulis")
 *             ),
 *             @OA\Property(property="kategori", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=403,
 *         description="Akses ditolak: Hanya penulis berita atau admin yang bisa mengedit",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Akses ditolak: Hanya penulis berita atau admin yang bisa mengedit")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Berita tidak ditemukan",
 *         @OA\JsonContent(
 *             @OA\Property(property="error", type="string", example="Berita tidak ditemukan")
 *         )
 *     ),
 *     @OA\Response(
 *         response=401,
 *         description="Unauthorized",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Unauthenticated.")
 *         )
 *     )
 * )
 */
public function update(Request $request, $id_berita): JsonResponse
{
    $berita = Berita::find($id_berita);
    if (!$berita) {
        return response()->json(['error' => 'Berita tidak ditemukan'], 404);
    }

    $user = $request->user();
    if (!$user || ($user->role !== 'admin' && $berita->id_user !== $user->id_user)) {
        return response()->json(['error' => 'Akses ditolak: Hanya penulis berita atau admin yang bisa mengedit'], 403);
    }

    $request->validate([
        'id_kategori' => 'sometimes|integer|exists:kategori,id_kategori',
        'judul' => 'sometimes|string|max:255',
        'isi' => 'sometimes|string',
        'gambar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        'tgl_terbit' => 'sometimes|date',
        'is_premium' => 'sometimes|in:0,1,true,false'
    ]);

    $dataToUpdate = $request->only([
        'id_kategori', 'judul', 'isi', 'tgl_terbit'
    ]);

    if ($request->has('is_premium')) {
        $dataToUpdate['is_premium'] = $request->boolean('is_premium');
    }

    if ($request->hasFile('gambar')) {
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $file = $request->file('gambar');
        $filename = time() . '_' . $file->getClientOriginalName();
        $path = $file->storeAs('berita', $filename, 'public');
        $dataToUpdate['gambar'] = $path;
    }

    $berita->fill($dataToUpdate)->save();
    $berita->refresh()->load('kategori');
    $penulis = User::find($berita->id_user);

    return response()->json([
        'id_berita' => $berita->id_berita,
        'id_user' => $berita->id_user,
        'id_kategori' => $berita->id_kategori,
        'judul' => $berita->judul,
        'isi' => $berita->isi,
        'gambar' => $berita->gambar,
        'tgl_terbit' => $berita->tgl_terbit,
        'is_premium' => $berita->is_premium,
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
     *     path="/berita/category/{id_kategori}",
     *     tags={"Berita"},
     *     summary="Get news by category",
     *     description="Mengambil daftar berita berdasarkan kategori dengan relasi penulis dan kategori, termasuk kontrol akses premium",
     *     @OA\Parameter(
     *         name="id_kategori",
     *         in="path",
     *         description="ID kategori",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List berita berdasarkan kategori",
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
     *                 @OA\Property(property="is_premium", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="penulis",
     *                     type="object",
     *                     @OA\Property(property="id_user", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="penulis")
     *                 ),
     *                 @OA\Property(property="kategori", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan"
     *     )
     * )
     */
    public function getByCategory(Request $request, $id_kategori): JsonResponse
    {
        $user = $request->user();

        $query = Berita::with('kategori')->where('id_kategori', $id_kategori);

        // Jika user tidak login atau guest, hanya tampilkan berita non-premium
        if (!$user || $user->membership === 'guest') {
            $query->where('is_premium', false);
        } elseif ($user->membership === 'free') {
            // Free user: bisa lihat berita non-premium dan beberapa premium (misal limit 5)
            $query->where(function ($q) {
                $q->where('is_premium', false)
                  ->orWhere('is_premium', true); // Untuk demo, free bisa lihat semua, tapi di real bisa limit
            });
        } // Premium bisa lihat semua

        $berita = $query->get()->map(function ($item) {
            $penulis = User::find($item->id_user);

            return [
                'id_berita' => $item->id_berita,
                'id_user' => $item->id_user,
                'id_kategori' => $item->id_kategori,
                'judul' => $item->judul,
                'isi' => $item->isi,
                'gambar' => $item->gambar,
                'tgl_terbit' => $item->tgl_terbit,
                'is_premium' => $item->is_premium,
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
     *     path="/berita/search",
     *     tags={"Berita"},
     *     summary="Search news",
     *     description="Mencari berita berdasarkan judul atau isi dengan relasi penulis dan kategori, termasuk kontrol akses premium",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Keyword pencarian",
     *         required=true,
     *         @OA\Schema(type="string", example="politik")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List berita hasil pencarian",
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
     *                 @OA\Property(property="is_premium", type="boolean", example=false),
     *                 @OA\Property(
     *                     property="penulis",
     *                     type="object",
     *                     @OA\Property(property="id_user", type="integer", example=1),
     *                     @OA\Property(property="username", type="string", example="johndoe"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="penulis")
     *                 ),
     *                 @OA\Property(property="kategori", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Keyword pencarian diperlukan"
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $q = $request->query('q');
        if (!$q) {
            return response()->json(['error' => 'Keyword pencarian diperlukan'], 400);
        }

        $user = $request->user();

        $query = Berita::with('kategori')
            ->where('judul', 'like', '%' . $q . '%')
            ->orWhere('isi', 'like', '%' . $q . '%');

        // Jika user tidak login atau guest, hanya tampilkan berita non-premium
        if (!$user || $user->membership === 'guest') {
            $query->where('is_premium', false);
        } elseif ($user->membership === 'free') {
            // Free user: bisa lihat berita non-premium dan beberapa premium (misal limit 5)
            $query->where(function ($q) {
                $q->where('is_premium', false)
                  ->orWhere('is_premium', true); // Untuk demo, free bisa lihat semua, tapi di real bisa limit
            });
        } // Premium bisa lihat semua

        $berita = $query->get()->map(function ($item) {
            $penulis = User::find($item->id_user);

            return [
                'id_berita' => $item->id_berita,
                'id_user' => $item->id_user,
                'id_kategori' => $item->id_kategori,
                'judul' => $item->judul,
                'isi' => $item->isi,
                'gambar' => $item->gambar,
                'tgl_terbit' => $item->tgl_terbit,
                'is_premium' => $item->is_premium,
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
     * @OA\Delete(
     *     path="/berita/{id_berita}",
     *     tags={"Berita"},
     *     summary="Delete news",
     *     description="Menghapus berita. Admin bisa menghapus semua berita, penulis bisa menghapus berita sendiri.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_berita",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Berita berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Berita berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Akses ditolak: Hanya admin atau penulis pemilik berita yang bisa menghapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Akses ditolak: Hanya admin atau penulis pemilik berita yang bisa menghapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Berita tidak ditemukan",
     *         @OA\JsonContent(
     *             @OA\Property(property="error", type="string", example="Berita tidak ditemukan")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Unauthenticated.")
     *         )
     *     )
     * )
     */
    public function destroy(Request $request, $id_berita): JsonResponse
    {
        $berita = Berita::find($id_berita);
        if (!$berita) {
            return response()->json(['error' => 'Berita tidak ditemukan'], 404);
        }

        $user = $request->user();

        // Cek role: admin bisa hapus semua berita, penulis bisa hapus berita sendiri
        if (!$user || ($user->role !== 'admin' && $berita->id_user !== $user->id_user)) {
            return response()->json(['error' => 'Akses ditolak: Hanya admin atau penulis pemilik berita yang bisa menghapus'], 403);
        }

        // Delete image if exists
        if ($berita->gambar && Storage::disk('public')->exists($berita->gambar)) {
            Storage::disk('public')->delete($berita->gambar);
        }

        $berita->delete();

        return response()->json(['message' => 'Berita berhasil dihapus']);
    }
}
