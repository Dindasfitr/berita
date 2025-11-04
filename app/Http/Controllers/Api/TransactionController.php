<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Transaction",
 *     description="API Endpoints untuk transaksi pembayaran"
 * )
 */
class TransactionController extends Controller
{
    /**
     * @OA\Post(
     *     path="/transaction",
     *     tags={"Transaction"},
     *     summary="Simulasi transaksi pembayaran untuk upgrade premium",
     *     description="Simulasi pembayaran untuk mendapatkan token upgrade premium. Dalam real app, ini terintegrasi dengan payment gateway.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"amount"},
     *             @OA\Property(property="amount", type="number", format="float", example=50000, description="Jumlah pembayaran dalam Rupiah")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Transaksi berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Transaksi berhasil. Gunakan token ini untuk upgrade."),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="token", type="string", example="UPGRADE_TOKEN_001", description="Token upgrade yang bisa digunakan di endpoint /upgrade")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Jumlah pembayaran tidak valid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Jumlah pembayaran minimal 50000")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     )
     * )
     */
    public function transaction(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'amount' => 'required|numeric|min:50000' // Minimal pembayaran 50k untuk demo
        ]);

        // Simulasi transaksi berhasil
        // Dalam real app, integrasi dengan payment gateway seperti Midtrans, Gopay, dll.

        // Generate token upgrade unik berdasarkan user ID dan timestamp
        $token = 'UPGRADE_' . $user->id_user . '_' . time() . '_' . rand(1000, 9999);

        // Untuk demo, kita return token ini
        // Dalam real app, simpan token di database dengan status belum digunakan

        return response()->json([
            'success' => true,
            'message' => 'Transaksi berhasil. Gunakan token ini untuk upgrade ke premium.',
            'data' => [
                'token' => $token
            ]
        ], 200);
    }
}
