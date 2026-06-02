<?php

namespace App\Http\Controllers;

use App\Models\Conge;
use Illuminate\Http\Request;

class CongeController extends Controller
{
    public function index()
    {
        $conges = Conge::all();
        return response()->json(['conges' => $conges]);
    }

    public function DemandeConge(Request $request)
    {
        $validated = $request->validate([
            'employeur_id' => 'required|exists:users,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'nullable|string',
        ]);

        $conge = Conge::create([
            'employeur_id' => $validated['employeur_id'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'reason' => $validated['reason'] ?? null,
            'status' => 'En attente',
        ]);

        return response()->json(['conge' => $conge], 201);
    }

    public function updateStatus(Request $request, Conge $conge)
    {
        $validated = $request->validate([
            'status' => 'required|in:En attente,Accepter,Refuser',
            'admin_comment' => 'nullable|string',
        ]);

        $conge->update($validated);

        return response()->json(['conge' => $conge]);
    }

    public function indexByEmployeur($employeurId)
    {
        $conges = Conge::where('employeur_id', $employeurId)->get();
        return response()->json(['conges' => $conges]);
    }
}
