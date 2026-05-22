<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntriesController extends Controller
{
    // Get all journal entries
    public function index()
    {
        $entries = DB::select(
            'SELECT je.id, je.entry_date, je.description, je.station_id, st.name AS station_name, je.created_at
             FROM journal_entries je
             LEFT JOIN stations st ON je.station_id = st.id
             ORDER BY je.entry_date DESC'
        );

        return response()->json($entries);
    }

    // Get a single journal entry by ID
    public function show($id)
    {
        $entry = DB::select(
            'SELECT je.id, je.entry_date, je.description, je.station_id, st.name AS station_name, je.created_at
             FROM journal_entries je
             LEFT JOIN stations st ON je.station_id = st.id
             WHERE je.id = ?',
            [$id]
        );

        if (empty($entry)) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        return response()->json($entry[0]);
    }

    // Create a new journal entry
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
            'entry_date' => 'required|date',
            'description' => 'nullable|string|max:255',
        ]);

        DB::insert(
            'INSERT INTO journal_entries (station_id, entry_date, description, created_at)
             VALUES (?, ?, ?, NOW())',
            [
                $validatedData['station_id'],
                $validatedData['entry_date'],
                $validatedData['description'],
            ]
        );

        return response()->json(['message' => 'Journal entry created successfully'], 201);
    }

    // Update an existing journal entry
    public function update(Request $request, $id)
    {
        $entry = DB::select('SELECT * FROM journal_entries WHERE id = ?', [$id]);

        if (empty($entry)) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        $validatedData = $request->validate([
            'station_id' => 'sometimes|required|integer|exists:stations,id',
            'entry_date' => 'sometimes|required|date',
            'description' => 'nullable|string|max:255',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE journal_entries SET ' . implode(', ', $updateFields) . ' WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Journal entry updated successfully']);
    }

    // Delete a journal entry
    public function destroy($id)
    {
        $entry = DB::select('SELECT * FROM journal_entries WHERE id = ?', [$id]);

        if (empty($entry)) {
            return response()->json(['message' => 'Journal entry not found'], 404);
        }

        DB::delete('DELETE FROM journal_entries WHERE id = ?', [$id]);

        return response()->json(['message' => 'Journal entry deleted successfully']);
    }
}
