<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Disukai;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Disukai",
 *     description="API Endpoints untuk manajemen likes/disukai"
 * )
 */
class DisukaiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/likes",
     *     tags={"Disukai"},
     *     summary="Get all likes",
     *     description="Mengambil semua like/disukai",
     *     @OA\Response(
     *         response=200,
     *         description="List semua like",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_disukai", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="suka", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $likes = Disukai::all();
        return response()->json($likes);
    }

    /**
     * @OA\Post(
     *     path="/likes",
     *     tags={"Disukai"},
     *     summary="Create or toggle like",
     *     description="Menambahkan like baru atau update jika sudah ada",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"id_user","id_berita"},
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="suka", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Like berhasil dibuat atau diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_disukai", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="suka", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|integer|exists:user,id_user',
            'id_berita' => 'required|integer|exists:berita,id_berita',
            'suka' => 'nullable|boolean'
        ]);

        $like = Disukai::updateOrCreate(
            ['id_user' => $request->id_user, 'id_berita' => $request->id_berita],
            ['suka' => $request->suka ?? true]
        );

        return response()->json($like, 201);
    }

    /**
     * @OA\Put(
     *     path="/likes/{id_disukai}",
     *     tags={"Disukai"},
     *     summary="Update like",
     *     description="Mengubah status like/disukai",
     *     @OA\Parameter(
     *         name="id_disukai",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"suka"},
     *                 @OA\Property(property="suka", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_disukai", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="suka", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Like tidak ditemukan"
     *     )
     * )
     */
    public function update(Request $request, $id_disukai): JsonResponse
    {
        $like = Disukai::find($id_disukai);
        if (!$like) {
            return response()->json(['error' => 'Like tidak ditemukan'], 404);
        }

        $request->validate([
            'suka' => 'required|boolean'
        ]);

        $like->suka = $request->suka;
        $like->save();

        return response()->json($like);
    }

    /**
     * @OA\Delete(
     *     path="/likes/{id_disukai}",
     *     tags={"Disukai"},
     *     summary="Delete like",
     *     description="Menghapus like/disukai",
     *     @OA\Parameter(
     *         name="id_disukai",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Like berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Like berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Like tidak ditemukan"
     *     )
     * )
     */
    public function destroy($id_disukai): JsonResponse
    {
        $like = Disukai::find($id_disukai);
        if (!$like) {
            return response()->json(['error' => 'Like tidak ditemukan'], 404);
        }

        $like->delete();
        return response()->json(['message' => 'Like berhasil dihapus']);
    }
}
