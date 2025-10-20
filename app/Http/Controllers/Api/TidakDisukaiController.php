<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\TidakDisukai;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="TidakDisukai",
 *     description="API Endpoints untuk manajemen dislike/tidak disukai"
 * )
 */
class TidakDisukaiController extends Controller
{
    /**
     * @OA\Get(
     *     path="/dislikes",
     *     tags={"TidakDisukai"},
     *     summary="Get all dislikes",
     *     description="Mengambil semua data tidak disukai",
     *     @OA\Response(
     *         response=200,
     *         description="List semua data tidak disukai",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_tidaksuka", type="integer", example=1),
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="tidak_suka", type="boolean", example=true),
     *                 @OA\Property(property="created_at", type="string", format="date-time", example="2025-01-01T00:00:00Z"),
     *                 @OA\Property(property="updated_at", type="string", format="date-time", example="2025-01-01T00:00:00Z")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $dislikes = TidakDisukai::all();
        return response()->json($dislikes);
    }

    /**
     * @OA\Post(
     *     path="/dislikes",
     *     tags={"TidakDisukai"},
     *     summary="Create or toggle dislike",
     *     description="Menambahkan data tidak disukai baru atau update jika sudah ada",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"id_user","id_berita"},
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="id_berita", type="integer", example=1),
     *                 @OA\Property(property="tidak_suka", type="boolean", example=true)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Dislike berhasil dibuat atau diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_tidaksuka", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="tidak_suka", type="boolean", example=true)
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'id_user' => 'required|integer|exists:users,id',
            'id_berita' => 'required|integer|exists:beritas,id_berita',
            'tidak_suka' => 'nullable|boolean'
        ]);

        $dislike = TidakDisukai::updateOrCreate(
            ['id_user' => $request->id_user, 'id_berita' => $request->id_berita],
            ['tidak_suka' => $request->tidak_suka ?? true]
        );

        return response()->json($dislike, 201);
    }

    /**
     * @OA\Put(
     *     path="/dislikes/{id_tidaksuka}",
     *     tags={"TidakDisukai"},
     *     summary="Update dislike",
     *     description="Mengubah status tidak disukai",
     *     @OA\Parameter(
     *         name="id_tidaksuka",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"tidak_suka"},
     *                 @OA\Property(property="tidak_suka", type="boolean", example=false)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dislike berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_tidaksuka", type="integer", example=1),
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="tidak_suka", type="boolean", example=false)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dislike tidak ditemukan"
     *     )
     * )
     */
    public function update(Request $request, $id_tidaksuka): JsonResponse
    {
        $dislike = TidakDisukai::find($id_tidaksuka);
        if (!$dislike) {
            return response()->json(['error' => 'Dislike tidak ditemukan'], 404);
        }

        $request->validate([
            'tidak_suka' => 'required|boolean'
        ]);

        $dislike->tidak_suka = $request->tidak_suka;
        $dislike->save();

        return response()->json($dislike);
    }

    /**
     * @OA\Delete(
     *     path="/dislikes/{id_tidaksuka}",
     *     tags={"TidakDisukai"},
     *     summary="Delete dislike",
     *     description="Menghapus data tidak disukai",
     *     @OA\Parameter(
     *         name="id_tidaksuka",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dislike berhasil dihapus",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Dislike berhasil dihapus")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Dislike tidak ditemukan"
     *     )
     * )
     */
    public function destroy($id_tidaksuka): JsonResponse
    {
        $dislike = TidakDisukai::find($id_tidaksuka);
        if (!$dislike) {
            return response()->json(['error' => 'Dislike tidak ditemukan'], 404);
        }

        $dislike->delete();
        return response()->json(['message' => 'Dislike berhasil dihapus']);
    }
}
