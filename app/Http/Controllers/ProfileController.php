<?php

namespace App\Http\Controllers;

use App\Models\LevelModel;
use App\Models\UserModel;
use Illuminate\Http\Request;
use Storage;
use Validator;

class ProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $user = UserModel::find(auth()->user()->user_id);

        return view('profile.index', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit()
    {
        $user = UserModel::find(auth()->user()->user_id);
        // $level = LevelModel::select('level_id', 'level_nama')->get();

        return view('profile.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        // dd($request);
        if ($request->ajax() || $request->wantsJson()) {
            $rules = [
                'username' => 'required|max:20|unique:m_user,username,' . auth()->user()->user_id . ',user_id',
                'nama' => 'required|max:100',
                'password' => 'nullable|min:6|max:20',
                'profile_picture' => 'nullable|image|max:2048', // Pastikan hanya gambar yang diizinkan
            ];
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validasi gagal.',
                    'msgField' => $validator->errors()
                ]);
            }

            // $check = UserModel::find(auth()->user()->user_id);
            $user = auth()->user();

            if ($user) {
                if ($request->hasFile('profile_picture')) {
                    // return response()->json(['error' => 'No file uploaded'], 400);
                    $file = $request->file('profile_picture');

                    if (!$file->isValid()) {
                        return response()->json(['error' => 'Invalid file'], 400);
                    }

                    // Nama file unik
                    $filename = time() . '_' . $file->getClientOriginalName();

                    // Pastikan folder penyimpanan ada
                    $destinationPath = storage_path('app/public/profile-pictures');
                    if (!file_exists($destinationPath)) {
                        mkdir($destinationPath, 0775, true);
                    }

                    // Hapus file lama jika ada
                    $oldImage = $user->profile_picture ?? null; // Ambil path file lama dari database

                    if ($oldImage) {
                        Storage::disk('public')->delete($oldImage);
                    }

                    // Pindahkan file
                    $file->move($destinationPath, $filename);

                    $imagePath = "profile-pictures/$filename"; // Simpan path gambar
                } else {
                    $imagePath = null;
                    // return  . $imagePath;
                }

                // Update data lainnya
                $user->nama = $request->nama;
                $user->username = $request->username;

                if ($request->password) {
                    $user->password = $request->password;
                }

                if ($imagePath) {
                    $user->profile_picture = $imagePath;
                }

                if ($request->input('remove_picture') == "1") {
                    // Hapus gambar lama jika ada
                    if ($user->profile_picture) {
                        $oldImage = $user->profile_picture; // Ambil path file lama dari database
                        if ($oldImage) {
                            Storage::disk('public')->delete($oldImage);
                        }
                    }
                    $user->profile_picture = null; // Set kolom di database jadi null
                }
                $user->save();
                return response()->json(['status' => true, 'message' => 'Data berhasil diupdate']);
            } else {
                return response()->json(['status' => false, 'message' => 'Data tidak ditemukan']);
            }
        }
        return redirect('/');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UserModel $userModel)
    {
        //
    }
}
