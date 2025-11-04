<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Notification",
 *     description="API Endpoints untuk notifikasi"
 * )
 */
class NotificationController extends Controller
{
    /**
     * @OA\Get(
     *     path="/notifications",
     *     tags={"Notification"},
     *     summary="Get notifikasi user",
     *     description="Mengambil daftar notifikasi user yang sedang login",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="read",
     *         in="query",
     *         description="Filter berdasarkan status baca (0=belum dibaca, 1=sudah dibaca)",
     *         @OA\Schema(type="integer", enum={0,1})
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List notifikasi",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_notification", type="integer"),
     *                 @OA\Property(property="title", type="string"),
     *                 @OA\Property(property="message", type="string"),
     *                 @OA\Property(property="type", type="string"),
     *                 @OA\Property(property="is_read", type="boolean"),
     *                 @OA\Property(property="created_at", type="string")
     *             )
     *         )
     *     )
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        $query = Notification::where('id_user', $user->id_user)
            ->orderBy('created_at', 'desc');

        if ($request->has('read')) {
            $query->where('is_read', $request->read);
        }

        $notifications = $query->get();

        return response()->json([
            'success' => true,
            'data' => $notifications,
            'total' => $notifications->count(),
            'unread_count' => $notifications->where('is_read', false)->count()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/notifications/{id_notification}/read",
     *     tags={"Notification"},
     *     summary="Tandai notifikasi sebagai sudah dibaca",
     *     description="Menandai notifikasi tertentu sebagai sudah dibaca",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_notification",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Notifikasi berhasil ditandai sebagai sudah dibaca")
     * )
     */
    public function markAsRead($id_notification): JsonResponse
    {
        $user = Auth::user();

        $notification = Notification::where('id_notification', $id_notification)
            ->where('id_user', $user->id_user)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notification->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil ditandai sebagai sudah dibaca'
        ]);
    }

    /**
     * @OA\Put(
     *     path="/notifications/read-all",
     *     tags={"Notification"},
     *     summary="Tandai semua notifikasi sebagai sudah dibaca",
     *     description="Menandai semua notifikasi user sebagai sudah dibaca",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Semua notifikasi berhasil ditandai sebagai sudah dibaca")
     * )
     */
    public function markAllAsRead(): JsonResponse
    {
        $user = Auth::user();

        Notification::where('id_user', $user->id_user)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Semua notifikasi berhasil ditandai sebagai sudah dibaca'
        ]);
    }

    /**
     * @OA\Delete(
     *     path="/notifications/{id_notification}",
     *     tags={"Notification"},
     *     summary="Hapus notifikasi",
     *     description="Menghapus notifikasi tertentu",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_notification",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Notifikasi berhasil dihapus")
     * )
     */
    public function destroy($id_notification): JsonResponse
    {
        $user = Auth::user();

        $notification = Notification::where('id_notification', $id_notification)
            ->where('id_user', $user->id_user)
            ->first();

        if (!$notification) {
            return response()->json([
                'success' => false,
                'message' => 'Notifikasi tidak ditemukan'
            ], 404);
        }

        $notification->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notifikasi berhasil dihapus'
        ]);
    }
}
