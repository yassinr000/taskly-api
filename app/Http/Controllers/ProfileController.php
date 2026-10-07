<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function update(Request $request)
    {
        $data = $request->validate(['name' => 'required|string|max:255']);
        $request->user()->update($data);
        return $request->user();
    }

    public function password(Request $request)
    {
        $data = $request->validate([
            'current_password' => 'required',
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);
        $user = $request->user();
        if (!Hash::check($data['current_password'], $user->password)) {
            return response()->json(['message' => 'Mot de passe actuel incorrect'], 422);
        }
        $user->update(['password' => Hash::make($data['password'])]);
        // 🔒 déconnecte les autres appareils
        $user->tokens()->where('id', '!=', $user->currentAccessToken()->id)->delete();
        return response()->json(['message' => 'Mot de passe modifié']);
    }
}
