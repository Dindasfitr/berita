<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * @OA\Tag(
 *     name="Authentication",
 *     description="API Endpoints untuk autentikasi"
 * )
 */
class AuthController extends Controller
{
    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"Authentication"},
     *     summary="Register new user",
     *     description="Mendaftarkan user baru dan menyimpan data ke tabel user",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"username","name","email","password","password_confirmation","role","membership"},
     *             @OA\Property(property="username", type="string", example="johndoe"),
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="P@ssW0rd3"),
     *             @OA\Property(property="password_confirmation", type="string", format="password", example="P@ssW0rd3"),
     *             @OA\Property(property="role", type="string", enum={"penulis", "pembaca"}, example="pembaca"),
     *             @OA\Property(property="membership", type="string", enum={"guest","free","premium"}, example="free")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User berhasil didaftarkan"),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 @OA\Property(property="id_user", type="integer", example=1),
     *                 @OA\Property(property="username", type="string", example="johndoe"),
     *                 @OA\Property(property="name", type="string", example="John Doe"),
     *                 @OA\Property(property="email", type="string", example="john@example.com"),
     *                 @OA\Property(property="role", type="string", example="pembaca"),
     *                 @OA\Property(property="membership", type="string", example="free")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Terjadi kesalahan saat registrasi")
     *         )
     *     )
     * )
     */
    public function register(Request $request): JsonResponse
    {
        try {
            $passwordRules = [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/'
            ];

            $validated = $request->validate([
                'username' => 'required|string|max:255|unique:user,username',
                'name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:user,email',
                'password' => $passwordRules,
                'role' => 'required|string|in:penulis,pembaca',
                'membership' => 'required|string|in:guest,free,premium'
            ]);

            DB::beginTransaction();

            $user = User::create([
                'username'   => $validated['username'],
                'name'       => $validated['name'],
                'email'      => $validated['email'],
                'password'   => Hash::make($validated['password']),
                'role'       => $validated['role'],
                'membership' => $validated['membership']
            ]);

            DB::commit();

            // Response (✅ tampilkan membership)
            return response()->json([
                'success' => true,
                'message' => 'User berhasil didaftarkan',
                'data' => [
                    'id_user'    => $user->id_user,
                    'username'   => $user->username,
                    'name'       => $user->name,
                    'email'      => $user->email,
                    'role'       => $user->role,
                    'membership' => $user->membership
                ]
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors'  => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat registrasi',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ============================================================
    // Register sederhana (tidak menghapus versi di atas)
    // ============================================================
    public function simpleRegister(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:user,email',
            'password'    => 'required|string|min:8|confirmed',
            'role'        => 'required|in:penulis,pembaca', // user tidak bisa register admin
            'membership'  => 'required|in:guest,free,premium'
        ]);

        $user = User::create([
            'name'        => $request->name,
            'email'       => $request->email,
            'password'    => Hash::make($request->password),
            'role'        => $request->role,
            'membership'  => $request->membership
        ]);

        return response()->json([
            'message' => 'Registrasi berhasil',
            'user'    => $user
        ], 201);
    }

    // ============================================================
    // Login
    // ============================================================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
            'role' => 'required|in:admin,penulis,pembaca' // semua role bisa login
        ]);

        $user = User::where('email', $request->email)
            ->where('role', $request->role)
            ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Email, password, atau role salah'], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message'      => 'Login berhasil',
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'role'         => $user->role,
            'membership'   => $user->membership // ✅ tampilkan membership saat login
        ]);
    }
}
