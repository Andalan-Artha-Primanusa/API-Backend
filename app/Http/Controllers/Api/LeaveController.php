<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Leave;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    // 🔥 Employee create leave
    public function store(Request $request)
    {
        $data = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'reason' => 'nullable|string',
        ]);

        $leave = Leave::create([
            'user_id' => $request->user()->id,
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'reason' => $data['reason'] ?? null,
        ]);

        return response()->json([
            'message' => 'Leave diajukan',
            'data' => $leave
        ], 201);
    }

    // 🔥 GET leave
    public function index(Request $request)
    {
        $user = $request->user();

        if ($user->hasRole('employee')) {
            return response()->json(
                Leave::where('user_id', $user->id)->get()
            );
        }

        if ($user->hasAnyRole(['manager', 'hr', 'admin', 'super_admin'])) {
            return response()->json(
                Leave::with('user')->get()
            );
        }

        return response()->json(['message' => 'Forbidden'], 403);
    }

    // 🔥 approve / reject
    public function update(Request $request, $id)
    {
        $leave = Leave::findOrFail($id);

        if (! $request->user()->hasAnyRole(['manager', 'hr'])) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected'
        ]);

        $leave->update([
            'status' => $request->status
        ]);

        return response()->json([
            'message' => 'Leave updated',
            'data' => $leave
        ]);
    }
}
