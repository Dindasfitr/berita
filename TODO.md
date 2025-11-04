# TODO: Enhance Authentication and Profile Management

## 1. Update Models and Migrations
- [x] Add 'foto_profil' to User model fillable array
- [x] Ensure migration for foto_profil is run (add_foto_profil_to_user_table)

## 2. Enhance AuthController
- [x] Update login method to return membership in response
- [x] Fix register validation if needed (ensure username unique, etc.)

## 3. Update UserController
- [x] Modify update method to handle foto_profil upload (image file)
- [x] Add validation for foto_profil (image, max size, etc.)
- [x] Update Swagger docs for profile update endpoint

## 4. Add Membership-Based Access in BeritaController
- [x] Modify index method: guest sees limited berita, free sees non-premium, premium all
- [x] Modify show method: check membership for accessing specific berita
- [x] Add middleware for auth:sanctum and membership checks

## 5. Update Routes
- [x] Ensure profile update route is protected with auth:sanctum
- [x] Add any new routes if needed (e.g., for profile photo)
- [x] Update middleware for berita routes based on membership

## 6. Test Endpoints in Swagger
- [x] Register user (fix 422 if occurs)
- [x] Login and get token
- [x] Update profile with foto_profil
- [x] Access berita based on membership
- [x] Transaction and upgrade to premium
- [x] Ensure no errors, all work properly

## 7. Additional Fixes
- [x] Ensure TransactionController and UpgradeController work
- [x] Update Swagger docs for all modified endpoints
- [x] Check role-based access for berita management (penulis own, admin all)
