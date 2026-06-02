<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\WorkDay;
use App\Models\Presence;
use Illuminate\Http\Request;

class PresenceController extends Controller
{

    public function index()
    {
        $presences = Presence::all();
        return response()->json(['presences' => $presences]);
    }

    public function getByEmployeur($employeurId)
    {
        $presences = Presence::where('employeur_id', $employeurId)->get();
        return response()->json(['presences' => $presences]);
    }

    public function CheckIn($employeurId)
    {
        $now = Carbon::now();

        $presence = Presence::firstOrCreate(
            [
                'employeur_id' => $employeurId,
                'date' => $now->toDateString(),
            ],
            [
                'check_in' => $now->toTimeString(),
            ]
        );

        return response()->json([
            'message' => 'Check-in successful',
            'presence' => $presence
        ], 200);
    }
   

    public function CheckOut($employeurId)
    {
        $now = Carbon::now();

        $presence = Presence::where('employeur_id', $employeurId)
            ->where('date', $now->toDateString())
            ->first();

        if (!$presence) {
            return response()->json([
                'message' => 'No check-in found for today'
            ], 404);
        }

        if ($presence->check_out) {
            return response()->json([
                'message' => 'Already checked out today'
            ], 409);
        }

        $presence->check_out = $now->toTimeString();
        $presence->save();

        return response()->json([
            'message' => 'Check-out successful',
            'presence' => $presence
        ], 200);
    }

    public function getTodayWorkHours()
    {
        $todayName = Carbon::now()->format('l');
        $workDay = WorkDay::where('day_name', $todayName)->first();

        if (!$workDay) {
            return response()->json(['message' => 'No working hours defined for today'], 404);
        }

        return response()->json([
            'day' => $todayName,
            'start' => $workDay->start_time,
            'end' => $workDay->end_time,
        ]);
    }
}
