<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * @OA\Tag(
 *     name="Kategori",
 *     description="API Endpoints untuk manajemen kategori"
 * )
 */
class KategoriController extends Controller
{
    /**
     * @OA\Get(
     *     path="/kategori",
     *     tags={"Kategori"},
     *     summary="Get all categories",
     *     description="Mengambil daftar semua kategori",
     *     @OA\Response(
     *         response=200,
     *         description="List semua kategori",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_kategori", type="integer", example=1),
     *                 @OA\Property(property="kategori", type="string", example="Teknologi")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $kategori = Kategori::all();
        return response()->json($kategori);
    }

    /**
     * @OA\Get(
     *     path="/kategori/{id_kategori}",
     *     tags={"Kategori"},
     *     summary="Get single category",
     *     description="Mengambil detail kategori berdasarkan ID",
     *     @OA\Parameter(
     *         name="id_kategori",
     *         in="path",
     *         description="ID kategori",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail kategori",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="kategori", type="string", example="Teknologi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan"
     *     )
     * )
     */
    public function show($id_kategori): JsonResponse
    {
        $kategori = Kategori::find($id_kategori);
        if (!$kategori) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }
        return response()->json($kategori);
    }

    /**
     * @OA\Post(
     *     path="/kategori",
     *     tags={"Kategori"},
     *     summary="Create new category",
     *     description="Menambahkan kategori baru",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kategori"},
     *             @OA\Property(property="kategori", type="string", example="Teknologi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Kategori berhasil dibuat",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="kategori", type="string", example="Teknologi")
     *         )
     *     )
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'kategori' => 'required|string|max:255|unique:kategori,kategori'
        ]);

        $kategori = Kategori::create([
            'kategori' => $request->kategori
        ]);

        return response()->json($kategori, 201);
    }

    /**
     * @OA\Put(
     *     path="/kategori/{id_kategori}",
     *     tags={"Kategori"},
     *     summary="Update category",
     *     description="Mengubah nama kategori",
     *     @OA\Parameter(
     *         name="id_kategori",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"kategori"},
     *             @OA\Property(property="kategori", type="string", example="Teknologi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_kategori", type="integer", example=1),
     *             @OA\Property(property="kategori", type="string", example="Teknologi")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan"
     *     )
     * )
     */
    public function update(Request $request, $id_kategori): JsonResponse
    {
        $kategori = Kategori::find($id_kategori);
        if (!$kategori) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }

        $request->validate([
            'kategori' => 'required|string|max:255|unique:kategori,kategori,' . $id_kategori . ',id_kategori'
        ]);

        $kategori->kategori = $request->kategori;
        $kategori->save();

        return response()->json($kategori);
    }

    /**
     * @OA\Delete(
     *     path="/kategori/{id_kategori}",
     *     tags={"Kategori"},
     *     summary="Delete category",
     *     description="Menghapus kategori",
     *     @OA\Parameter(
     *         name="id_kategori",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Kategori berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Kategori tidak ditemukan"
     *     )
     * )
     */
    public function destroy($id_kategori): JsonResponse
    {
        $kategori = Kategori::find($id_kategori);
        if (!$kategori) {
            return response()->json(['error' => 'Kategori tidak ditemukan'], 404);
        }

        $kategori->delete();

        return response()->json(['message' => 'Kategori berhasil dihapus']);
    }
}
