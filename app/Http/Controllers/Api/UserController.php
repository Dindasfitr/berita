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
     *                 @OA\Property(property="role", type="string", example="pembaca")
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
            'role' => $user->role
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
     *             @OA\Property(property="role", type="string", example="pembaca")
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
            'role' => $user->role
        ]);
    }

    /**
     * @OA\Put(
     *     path="/users/{id_user}",
     *     tags={"Users"},
     *     summary="Update user",
     *     description="Mengupdate username, name, email, dan password",
     *     @OA\Parameter(
     *         name="id_user",
     *         in="path",
     *         description="ID user",
     *         required=true,
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com"),
     *             @OA\Property(property="old_password", type="string", format="password", example="oldpass123"),
     *             @OA\Property(property="new_password", type="string", format="password", example="newpass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User berhasil diupdate",
     *         @OA\JsonContent(
     *             @OA\Property(property="id_user", type="integer", example=1),
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Password lama salah"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="User tidak ditemukan"
     *     )
     * )
     */
    public function update(Request $request, $id_user): JsonResponse
    {
        $user = User::find($id_user);
        if (!$user) {
            return response()->json(['error' => 'User tidak ditemukan'], 404);
        }

        $request->validate([
            'username' => 'sometimes|string|max:255|unique:user,username,' . $id_user . ',id_user',
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255',
            'old_password' => 'sometimes|required_with:new_password|string',
            'new_password' => 'sometimes|string|min:8'
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->old_password, $user->password)) {
                return response()->json(['error' => 'Password lama salah'], 400);
            }
            $user->password = Hash::make($request->new_password);
        }

        if ($request->filled('username')) $user->username = $request->username;
        if ($request->filled('name')) $user->name = $request->name;
        if ($request->filled('email')) $user->email = $request->email;
        $user->save();

        return response()->json([
            'id_user' => $user->id_user,
            'username' => $user->username,
            'name' => $user->name,
            'email' => $user->email
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
