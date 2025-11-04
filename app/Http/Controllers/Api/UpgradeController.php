<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Upgrade",
 *     description="API Endpoints untuk upgrade membership user"
 * )
 */
class UpgradeController extends Controller
{
    /**
     * @OA\Post(
     *     path="/upgrade",
     *     tags={"Upgrade"},
     *     summary="Upgrade user membership to premium",
     *     description="Upgrade membership user menjadi premium dengan token. Token premium dapat diperoleh dari admin atau melalui pembelian.",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token"},
     *             @OA\Property(property="token", type="string", example="PREMIUM_TOKEN_12345", description="Token premium yang valid (dapatkan dari admin atau pembelian)")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Upgrade berhasil",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Membership berhasil diupgrade ke premium"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="membership", type="string", example="premium")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Token tidak valid atau user sudah premium",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token tidak valid")
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
    public function upgrade(Request $request): JsonResponse
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 401);
        }

        $request->validate([
            'token' => 'required|string'
        ]);

        // Validasi token: token harus unik dan didapat dari transaksi pembayaran
        // Dalam real app, token ini didapat dari payment gateway setelah pembayaran berhasil
        // Untuk demo, kita validasi format token yang didapat dari endpoint /transaction
        // Format: UPGRADE_{id_user}_{timestamp}_{random}

        if (!preg_match('/^UPGRADE_\d+_\d{10}_\d{4}$/', $request->token)) {
            return response()->json([
                'success' => false,
                'message' => 'Token upgrade tidak valid. Pastikan Anda telah melakukan transaksi pembayaran terlebih dahulu di endpoint /transaction.'
            ], 400);
        }

        // Dalam real app, cek di database apakah token sudah digunakan atau belum
        // Untuk demo, kita anggap semua token dengan format benar valid

        if ($user->membership === 'premium') {
            return response()->json([
                'success' => false,
                'message' => 'User sudah memiliki membership premium'
            ], 400);
        }

        // Upgrade membership
        $user->membership = 'premium';
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Membership berhasil diupgrade ke premium',
            'data' => [
                'id_user' => $user->id_user,
                'membership' => $user->membership
            ]
        ], 200);
    }
}
