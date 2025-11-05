<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Berita;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Search",
 *     description="API Endpoints untuk pencarian berita"
 * )
 */
class SearchController extends Controller
{
    /**
     * @OA\Get(
     *     path="/search",
     *     tags={"Search"},
     *     summary="Search berita berdasarkan query",
     *     description="Pencarian berita berdasarkan judul, isi, atau penulis",
     *     @OA\Parameter(
     *         name="q",
     *         in="query",
     *         description="Query pencarian",
     *         required=true,
     *         @OA\Schema(type="string", example="politik")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="Jumlah hasil per halaman",
     *         @OA\Schema(type="integer", example=10, default=10)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Hasil pencarian",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="judul", type="string", example="Berita Politik Terbaru"),
     *                 @OA\Property(property="isi", type="string", example="Isi berita..."),
     *                 @OA\Property(property="penulis", type="object")
     *             )
     *         )
     *     )
     * )
     */
    public function search(Request $request): JsonResponse
    {
        $request->validate([
            'q' => 'required|string|min:2',
            'limit' => 'nullable|integer|min:1|max:50'
        ]);

        $query = $request->q;
        $limit = $request->limit ?? 10;

        $berita = Berita::with('kategori')
            ->where(function ($q) use ($query) {
                $q->where('judul', 'LIKE', "%{$query}%")
                  ->orWhere('isi', 'LIKE', "%{$query}%");
            })
            ->orWhereHas('penulis', function ($q) use ($query) {
                $q->where('name', 'LIKE', "%{$query}%")
                  ->orWhere('username', 'LIKE', "%{$query}%");
            })
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $penulis = User::find($item->id_user);
                return [
                    'id_berita' => $item->id_berita,
                    'judul' => $item->judul,
                    'isi' => substr($item->isi, 0, 200) . '...', // preview isi
                    'gambar' => $item->gambar,
                    'tgl_terbit' => $item->tgl_terbit,
                    'is_premium' => $item->is_premium,
                    'penulis' => $penulis ? [
                        'id_user' => $penulis->id_user,
                        'name' => $penulis->name,
                        'username' => $penulis->username
                    ] : null,
                    'kategori' => $item->kategori ? [
                        'id_kategori' => $item->kategori->id_kategori,
                        'nama_kategori' => $item->kategori->nama_kategori
                    ] : null
                ];
            });

        return response()->json([
            'success' => true,
            'message' => 'Hasil pencarian',
            'data' => $berita,
            'total' => $berita->count()
        ]);
    }

    /**
     * @OA\Get(
     *     path="/search/advanced",
     *     tags={"Search"},
     *     summary="Pencarian advanced berita",
     *     description="Pencarian berita dengan filter kategori, penulis, tanggal",
     *     @OA\Parameter(name="judul", in="query", @OA\Schema(type="string")),
     *     @OA\Parameter(name="kategori", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="penulis", in="query", @OA\Schema(type="integer")),
     *     @OA\Parameter(name="tanggal_dari", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Parameter(name="tanggal_sampai", in="query", @OA\Schema(type="string", format="date")),
     *     @OA\Response(response=200, description="Hasil pencarian advanced")
     * )
     */
    public function advancedSearch(Request $request): JsonResponse
    {
        $berita = Berita::with(['kategori', 'penulis']);

        if ($request->filled('judul')) {
            $berita->where('judul', 'LIKE', "%{$request->judul}%");
        }

        if ($request->filled('kategori')) {
            $berita->where('id_kategori', $request->kategori);
        }

        if ($request->filled('penulis')) {
            $berita->where('id_user', $request->penulis);
        }

        if ($request->filled('tanggal_dari')) {
            $berita->whereDate('tgl_terbit', '>=', $request->tanggal_dari);
        }

        if ($request->filled('tanggal_sampai')) {
            $berita->whereDate('tgl_terbit', '<=', $request->tanggal_sampai);
        }

        $results = $berita->get()->map(function ($item) {
            $penulis = User::find($item->id_user);
            return [
                'id_berita' => $item->id_berita,
                'judul' => $item->judul,
                'isi' => substr($item->isi, 0, 200) . '...',
                'penulis' => $penulis ? $penulis->name : null,
                'kategori' => $item->kategori ? $item->kategori->nama_kategori : null,
                'tgl_terbit' => $item->tgl_terbit
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $results,
            'total' => $results->count()
        ]);
    }



    
}
