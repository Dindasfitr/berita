<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\User;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="History",
 *     description="API Endpoints untuk history berita yang dibaca user"
 * )
 */
class HistoryController extends Controller
{
    /**
     * @OA\Get(
     *     path="/history",
     *     tags={"History"},
     *     summary="Get all history",
     *     description="Mengambil daftar semua history pembaca",
     *     @OA\Response(
     *         response=200,
     *         description="List semua history",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_history", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="berita", type="object"),
     *                 @OA\Property(property="user", type="object"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $history = History::with(['user', 'berita'])->get();
        return response()->json($history);
    }

    /**
     * @OA\Post(
     *     path="/history",
     *     tags={"History"},
     *     summary="Create history",
     *     description="Menambahkan history berita yang dibaca user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_user","id_berita"},
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="History berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_history", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1)
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|integer|exists:user,id_user',
            'id_berita' => 'required|integer|exists:berita,id_berita'
        ]);

        $history = History::create($request->only('id_user', 'id_berita'));
        return response()->json($history, 201);
    }

    /**
     * @OA\Get(
     *     path="/history/{id_history}",
     *     tags={"History"},
     *     summary="Get single history",
     *     description="Mengambil detail history berdasarkan ID",
     *     @OA\Parameter(
     *         name="id_history",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail history",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_history", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="created_at", type="string", format="date-time"),
     *             @OA\Property(property="updated_at", type="string", format="date-time")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="History tidak ditemukan"
     *     )
     * )
     */
    public function show($id_history): JsonResponse
    {
        $history = History::with(['user', 'berita'])->find($id_history);
        if (!$history) {
            return response()->json(['error' => 'History tidak ditemukan'], 404);
        }
        return response()->json($history);
    }

    /**
     * @OA\Get(
     *     path="/history/user/{id_user}",
     *     tags={"History"},
     *     summary="Get history by user",
     *     description="Mengambil semua history berita yang dibaca oleh user tertentu",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         required=true,
     *         description="ID user",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List history user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_history", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="berita", type="object"),
     *                 @OA\Property(property="created_at", type="string", format="date-time"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User tidak ditemukan atau tidak memiliki history"
     *     )
     * )
     */
    public function getByUser($id_user): JsonResponse
    {
        $history = History::with('berita')->where('id_user', $id_user)->get();

        if ($history->isEmpty()) {
            return response()->json(['error' => 'History tidak ditemukan untuk user ini'], 404);
        }

        return response()->json($history);
    }





    /**
     * @OA\Delete(
     *     path="/history/{id_history}",
     *     tags={"History"},
     *     summary="Delete history",
     *     description="Menghapus history",
     *     @OA\Parameter(
     *         name="id_history",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="History berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="History tidak ditemukan"
     *     )
     * )
     */


    
    public function destroy($id_history): JsonResponse
    {
        $history = History::find($id_history);
        if (!$history) {
            return response()->json(['error' => 'History tidak ditemukan'], 404);
        }

        $history->delete();
        return response()->json(['message' => 'History berhasil dihapus']);
    }
}
