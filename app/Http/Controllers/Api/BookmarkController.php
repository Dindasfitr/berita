<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Bookmark;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Bookmark",
 *     description="API Endpoints untuk bookmark berita favorit"
 * )
 */
class BookmarkController extends Controller
{
    /**
     * @OA\Get(
     *     path="/bookmarks",
     *     tags={"Bookmark"},
     *     summary="Get semua bookmark user",
     *     description="Mengambil daftar berita yang di-bookmark oleh user yang sedang login",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="List bookmark",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_bookmark", type="integer"),
     *                 @OA\Property(property="berita", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();

        $bookmarks = Bookmark::with('berita.kategori')
            ->where('id_user', $user->id_user)
            ->get()
            ->map(function ($bookmark) {
                $penulis = User::find($bookmark->berita->id_user);
                return [
                    'id_bookmark' => $bookmark->id_bookmark,
                    'berita' => [
                        'id_berita' => $bookmark->berita->id_berita,
                        'judul' => $bookmark->berita->judul,
                        'isi' => substr($bookmark->berita->isi, 0, 200) . '...',
                        'gambar' => $bookmark->berita->gambar,
                        'penulis' => $penulis ? $penulis->name : null,
                        'kategori' => $bookmark->berita->kategori ? $bookmark->berita->kategori->nama_kategori : null,
                        'tgl_terbit' => $bookmark->berita->tgl_terbit
                    ],
                    'created_at' => $bookmark->created_at
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $bookmarks,
            'total' => $bookmarks->count()
        ]);
    }

    /**
     * @OA\Post(
     *     path="/bookmarks",
     *     tags={"Bookmark"},
     *     summary="Tambah bookmark",
     *     description="Menambah berita ke bookmark user",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_berita"},
     *             @OA\Property(property="id_berita", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(response=201, description="Bookmark berhasil ditambahkan")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'id_berita' => 'required|integer|exists:berita,id_berita'
        ]);

        // Cek apakah berita ada
        $berita = Berita::find($request->id_berita);
        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        // Cek apakah sudah di-bookmark
        $existing = Bookmark::where('id_user', $user->id_user)
            ->where('id_berita', $request->id_berita)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Berita sudah di-bookmark'
            ], 400);
        }

        $bookmark = Bookmark::create([
            'id_user' => $user->id_user,
            'id_berita' => $request->id_berita
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Bookmark berhasil ditambahkan',
            'data' => $bookmark
        ], 201);
    }

    /**
     * @OA\Delete(
     *     path="/bookmarks/{id_bookmark}",
     *     tags={"Bookmark"},
     *     summary="Hapus bookmark",
     *     description="Menghapus berita dari bookmark user",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_bookmark",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Bookmark berhasil dihapus")
     * )
     */
    public function destroy($id_bookmark): JsonResponse
    {
        $user = Auth::user();

        $bookmark = Bookmark::where('id_bookmark', $id_bookmark)
            ->where('id_user', $user->id_user)
            ->first();

        if (!$bookmark) {
            return response()->json([
                'success' => false,
                'message' => 'Bookmark tidak ditemukan'
            ], 404);
        }

        $bookmark->delete();

        return response()->json([
            'success' => true,
            'message' => 'Bookmark berhasil dihapus'
        ]);
    }
}
