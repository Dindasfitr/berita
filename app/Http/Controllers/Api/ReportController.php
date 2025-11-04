<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Berita;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

/**
 * @OA\Tag(
 *     name="Report",
 *     description="API Endpoints untuk melaporkan konten"
 * )
 */
class ReportController extends Controller
{
    /**
     * @OA\Post(
     *     path="/reports",
     *     tags={"Report"},
     *     summary="Laporkan berita",
     *     description="Melaporkan berita yang dianggap melanggar aturan",
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"id_berita","reason"},
     *             @OA\Property(property="id_berita", type="integer", example=1),
     *             @OA\Property(property="reason", type="string", enum={"spam","konten_tidak_pantast","hoax","pelanggaran_hak_cipta","lainnya"}, example="hoax"),
     *             @OA\Property(property="description", type="string", example="Berita ini mengandung informasi yang salah")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Laporan berhasil dikirim")
     * )
     */
    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();

        $request->validate([
            'id_berita' => 'required|integer|exists:berita,id_berita',
            'reason' => 'required|in:spam,konten_tidak_pantast,hoax,pelanggaran_hak_cipta,lainnya',
            'description' => 'nullable|string|max:500'
        ]);

        // Cek apakah berita ada
        $berita = Berita::find($request->id_berita);
        if (!$berita) {
            return response()->json([
                'success' => false,
                'message' => 'Berita tidak ditemukan'
            ], 404);
        }

        // Cek apakah user sudah pernah melaporkan berita ini
        $existing = Report::where('id_user', $user->id_user)
            ->where('id_berita', $request->id_berita)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah pernah melaporkan berita ini'
            ], 400);
        }

        $report = Report::create([
            'id_user' => $user->id_user,
            'id_berita' => $request->id_berita,
            'reason' => $request->reason,
            'description' => $request->description,
            'status' => 'pending' // pending, reviewed, resolved
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Laporan berhasil dikirim. Terima kasih atas partisipasi Anda.',
            'data' => $report
        ], 201);
    }

    /**
     * @OA\Get(
     *     path="/reports",
     *     tags={"Report"},
     *     summary="Get laporan user (khusus admin)",
     *     description="Mengambil daftar laporan untuk admin review",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Filter berdasarkan status",
     *         @OA\Schema(type="string", enum={"pending","reviewed","resolved"})
     *     ),
     *     @OA\Response(response=200, description="List laporan")
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();

        // Hanya admin yang bisa lihat laporan
        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $query = Report::with(['user', 'berita'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $reports = $query->get()->map(function ($report) {
            return [
                'id_report' => $report->id_report,
                'user' => [
                    'id_user' => $report->user->id_user,
                    'name' => $report->user->name,
                    'email' => $report->user->email
                ],
                'berita' => [
                    'id_berita' => $report->berita->id_berita,
                    'judul' => $report->berita->judul,
                    'penulis' => $report->berita->penulis ? $report->berita->penulis->name : null
                ],
                'reason' => $report->reason,
                'description' => $report->description,
                'status' => $report->status,
                'created_at' => $report->created_at
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $reports,
            'total' => $reports->count()
        ]);
    }

    /**
     * @OA\Put(
     *     path="/reports/{id_report}",
     *     tags={"Report"},
     *     summary="Update status laporan (khusus admin)",
     *     description="Admin mengupdate status laporan",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_report",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"status"},
     *             @OA\Property(property="status", type="string", enum={"pending","reviewed","resolved"})
     *         )
     *     ),
     *     @OA\Response(response=200, description="Status laporan berhasil diupdate")
     * )
     */
    public function update(Request $request, $id_report): JsonResponse
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $request->validate([
            'status' => 'required|in:pending,reviewed,resolved'
        ]);

        $report = Report::find($id_report);
        if (!$report) {
            return response()->json([
                'success' => false,
                'message' => 'Laporan tidak ditemukan'
            ], 404);
        }

        $report->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Status laporan berhasil diupdate',
            'data' => $report
        ]);
    }
}
