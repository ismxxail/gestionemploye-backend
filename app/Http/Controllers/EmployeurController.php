<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\WorkDay;
use Illuminate\Http\Request;

class EmployeurController extends Controller
{
    public function index()
    {
        $employeurs = User::where('role', 'employeur')->get();
        return response()->json(['employeurs' => $employeurs]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        $employeur = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'employeur',
        ]);

        return response()->json(['employeur' => $employeur], 201);
    }

    public function show($id)
    {
        $employeur = User::where('role', 'employeur')->find($id);
        if (!$employeur) {
            return response()->json(['message' => 'Employeur not found'], 404);
        }
        return response()->json(['employeur' => $employeur]);
    }

    public function update(Request $request, $id)
    {
        $employeur = User::where('role', 'employeur')->find($id);
        if (!$employeur) {
            return response()->json(['message' => 'Employeur not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|email|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        if (isset($validated['name'])) {
            $employeur->name = $validated['name'];
        }
        if (isset($validated['email'])) {
            $employeur->email = $validated['email'];
        }
        if (isset($validated['password'])) {
            $employeur->password = bcrypt($validated['password']);
        }

        $employeur->save();

        return response()->json(['employeur' => $employeur]);
    }

    public function destroy($id)
    {
        $employeur = User::where('role', 'employeur')->find($id);
        if (!$employeur) {
            return response()->json(['message' => 'Employeur not found'], 404);
        }

        $employeur->delete();

        return response()->json(['message' => 'Employeur deleted successfully']);
    }    

    public function indexWorkDay()
    {
        $workDays = WorkDay::all();
        return response()->json(['work_days' => $workDays]);
    }
}
