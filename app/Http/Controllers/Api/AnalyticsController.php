<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Berita;
use App\Models\Disukai;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

/**
 * @OA\Tag(
 *     name="Analytics",
 *     description="API Endpoints untuk statistik admin"
 * )
 */
class AnalyticsController extends Controller
{
    /**
     * @OA\Get(
     *     path="/analytics/dashboard",
     *     tags={"Analytics"},
     *     summary="Dashboard analytics (khusus admin)",
     *     description="Statistik umum untuk dashboard admin",
     *     security={{"sanctum":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard statistics",
     *         @OA\JsonContent(
     *             @OA\Property(property="total_users", type="integer"),
     *             @OA\Property(property="total_berita", type="integer"),
     *             @OA\Property(property="total_likes", type="integer"),
     *             @OA\Property(property="total_views", type="integer"),
     *             @OA\Property(property="users_by_role", type="object"),
     *             @OA\Property(property="users_by_membership", type="object")
     *         )
     *     )
     * )
     */
    public function dashboard(): JsonResponse
    {
        // Hanya admin yang bisa akses
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $stats = [
            'total_users' => User::count(),
            'total_berita' => Berita::count(),
            'total_likes' => Disukai::where('suka', true)->count(),
            'total_views' => History::count(),
            'users_by_role' => [
                'admin' => User::where('role', 'admin')->count(),
                'penulis' => User::where('role', 'penulis')->count(),
                'pembaca' => User::where('role', 'pembaca')->count()
            ],
            'users_by_membership' => [
                'guest' => User::where('membership', 'guest')->count(),
                'free' => User::where('membership', 'free')->count(),
                'premium' => User::where('membership', 'premium')->count()
            ],
            'berita_by_kategori' => DB::table('berita')
                ->join('kategori', 'berita.id_kategori', '=', 'kategori.id_kategori')
                ->select('kategori.kategori as nama_kategori', DB::raw('count(*) as total'))
                ->groupBy('kategori.kategori')
                ->get(),
            'berita_premium' => Berita::where('is_premium', true)->count(),
            'berita_free' => Berita::where('is_premium', false)->count()
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * @OA\Get(
     *     path="/analytics/users",
     *     tags={"Analytics"},
     *     summary="Analytics user (khusus admin)",
     *     description="Statistik detail tentang user",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="User analytics")
     * )
     */
    public function users(): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $analytics = [
            'new_users_today' => User::whereDate('created_at', today())->count(),
            'new_users_this_week' => User::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'new_users_this_month' => User::whereMonth('created_at', now()->month)->count(),
            'active_users' => User::where('updated_at', '>=', now()->subDays(30))->count(),
            'top_penulis' => User::where('role', 'penulis')
                ->withCount('berita')
                ->orderBy('berita_count', 'desc')
                ->take(10)
                ->get(['id_user', 'name', 'username'])
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }

    /**
     * @OA\Get(
     *     path="/analytics/content",
     *     tags={"Analytics"},
     *     summary="Analytics konten (khusus admin)",
     *     description="Statistik detail tentang konten berita",
     *     security={{"sanctum":{}}},
     *     @OA\Response(response=200, description="Content analytics")
     * )
     */
    public function content(): JsonResponse
    {
        $user = auth()->user();
        if (!$user || !$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $analytics = [
            'berita_today' => Berita::whereDate('created_at', today())->count(),
            'berita_this_week' => Berita::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'berita_this_month' => Berita::whereMonth('created_at', now()->month)->count(),
            'most_viewed_berita' => DB::table('berita')
                ->join('history', 'berita.id_berita', '=', 'history.id_berita')
                ->select('berita.judul', DB::raw('count(history.id_history) as views'))
                ->groupBy('berita.id_berita', 'berita.judul')
                ->orderBy('views', 'desc')
                ->take(10)
                ->get(),
            'most_liked_berita' => DB::table('berita')
                ->join('disukai', 'berita.id_berita', '=', 'disukai.id_berita')
                ->where('disukai.suka', true)
                ->select('berita.judul', DB::raw('count(disukai.id_disukai) as likes'))
                ->groupBy('berita.id_berita', 'berita.judul')
                ->orderBy('likes', 'desc')
                ->take(10)
                ->get()
        ];

        return response()->json([
            'success' => true,
            'data' => $analytics
        ]);
    }
}
