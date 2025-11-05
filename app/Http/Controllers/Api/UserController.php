<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;

/**
 * @OA\Tag(
 *     name="Users",
 *     description="API Endpoints untuk manajemen user"
 * )
 */
class UserController extends Controller
{

    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Get all users",
     *     description="Mengambil daftar semua user",
     *     @OA\Response(
     *         response=200,
     *         description="List semua user",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="pembaca"),
     *                 @OA\Property(property="membership", type="string", example="free"),
     *                 @OA\Property(property="foto_profil", type="string", example="profile.jpg")
     *             )
     *         )
     *     )
     * )
     */
    public function index(): JsonResponse
    {
        $users = User::all()->map(fn($user) => [
            'id_user' => $user->id_user,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'membership' => $user->membership,
            'foto_profil' => $user->foto_profil
        ]);

        return response()->json($users);
    }

    /**
     * @OA\Get(
     *     path="/users/{id_user}",
     *     tags={"Users"},
     *     summary="Get single user",
     *     description="Mengambil detail user berdasarkan ID",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         description="ID user",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detail user",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="role", type="string", example="pembaca"),
     *             @OA\Property(property="membership", type="string", example="free"),
     *             @OA\Property(property="foto_profil", type="string", example="profile.jpg")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User tidak ditemukan"
     *     )
     * )
     */
    public function show($id_user): JsonResponse
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        return response()->json([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'membership' => $user->membership,
            'foto_profil' => $user->foto_profil
        ]);
    }


    /**
     * @OA\Post(
     *     path="/users/{id_user}",
     *     tags={"Users"},
     *     summary="Update user profile",
     *     description="Mengupdate username, name, email, password, dan foto profil. Tidak perlu field _method, cukup kirim multipart/form-data biasa.",
     *     security={{"sanctum":{}}},
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         description="ID user",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="old_password", type="string", format="password", example="P@ssW0rd3"),
     *                 @OA\Property(property="new_password", type="string", format="password", example="newP@ssW0rd3"),
     *                 @OA\Property(property="foto_profil", type="string", format="binary")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="role", type="string", example="pembaca"),
     *             @OA\Property(property="membership", type="string", example="free"),
     *             @OA\Property(property="foto_profil", type="string", example="profil/profile.jpg")
     *         )
     *     ),
     *     @OA\Response(response=400, description="Password lama salah"),
     *     @OA\Response(response=404, description="User tidak ditemukan"),
     *     @OA\Response(response=401, description="Unauthenticated")
     * )
     */
    public function update(Request $request, $id_user): JsonResponse
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        // ✅ Validasi input
        $validated = $request->validate([
            'username' => 'sometimes|string|max:255|unique:user,username,' . $id_user . ',id_user',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'old_password' => 'sometimes|required_with:new_password|string',
            'new_password' => [
                'sometimes',
                'string',
                'min:8',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&.,#^()_\-+=])[A-Za-z\d@$!%*?&.,#^()_\-+=]{8,}$/'
            ],
            'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        // 🔐 Ubah password jika diminta
        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['error' => 'Password lama salah'], 400);
            }
            $user->password = Hash::make($request->new_password);
        }

        // 🧩 Update field text biasa
        $user->fill($request->only(['username', 'name', 'email']));

        // 🖼️ Upload foto profil baru jika dikirim
        if ($request->hasFile('foto_profil')) {
            // Hapus foto lama kalau ada
            if ($user->foto_profil && Storage::disk('public')->exists($user->foto_profil)) {
                Storage::disk('public')->delete($user->foto_profil);
            }

            $file = $request->file('foto_profil');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('profil', $filename, 'public');
            $user->foto_profil = $path;
        }

        $user->save();

        return response()->json([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'membership' => $user->membership,
            'foto_profil' => $user->foto_profil ? asset('storage/' . $user->foto_profil) : null
        ]);
    }


    /**
     * @OA\Delete(
     *     path="/users/{id_user}",
     *     tags={"Users"},
     *     summary="Delete user",
     *     description="Menghapus user",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         description="ID user",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User berhasil dihapus"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User tidak ditemukan"
     *     )
     * )
     */
    public function destroy($id_user): JsonResponse
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
