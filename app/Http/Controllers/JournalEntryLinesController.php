<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JournalEntryLinesController extends Controller
{
    // Get all journal entry lines
    public function index()
    {
        $lines = DB::select(
            'SELECT jel.id, jel.journal_entry_id, je.entry_date, jel.account_id, coa.name AS account_name, 
                    jel.party_id, a.name AS party_name, jel.debit, jel.credit
             FROM journal_entry_lines jel
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             LEFT JOIN chart_of_accounts coa ON jel.account_id = coa.id
             LEFT JOIN accounts a ON jel.party_id = a.id
             ORDER BY jel.id ASC'
        );

        return response()->json($lines);
    }

    // Get a single journal entry line by ID
    public function show($id)
    {
        $line = DB::select(
            'SELECT jel.id, jel.journal_entry_id, je.entry_date, jel.account_id, coa.name AS account_name, 
                    jel.party_id, a.name AS party_name, jel.debit, jel.credit
             FROM journal_entry_lines jel
             LEFT JOIN journal_entries je ON jel.journal_entry_id = je.id
             LEFT JOIN chart_of_accounts coa ON jel.account_id = coa.id
             LEFT JOIN accounts a ON jel.party_id = a.id
             WHERE jel.id = ?',
            [$id]
        );

        if (empty($line)) {
            return response()->json(['message' => 'Journal entry line not found'], 404);
        }

        return response()->json($line[0]);
    }

    // Create a new journal entry line
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'journal_entry_id' => 'required|integer|exists:journal_entries,id',
            'account_id' => 'required|integer|exists:chart_of_accounts,id',
            'party_id' => 'nullable|integer|exists:accounts,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        DB::insert(
            'INSERT INTO journal_entry_lines (journal_entry_id, account_id, party_id, debit, credit)
             VALUES (?, ?, ?, ?, ?)',
            [
                $validatedData['journal_entry_id'],
                $validatedData['account_id'],
                $validatedData['party_id'],
                $validatedData['debit'] ?? 0,
                $validatedData['credit'] ?? 0,
            ]
        );

        return response()->json(['message' => 'Journal entry line created successfully'], 201);
    }

    // Update an existing journal entry line
    public function update(Request $request, $id)
    {
        $line = DB::select('SELECT * FROM journal_entry_lines WHERE id = ?', [$id]);

        if (empty($line)) {
            return response()->json(['message' => 'Journal entry line not found'], 404);
        }

        $validatedData = $request->validate([
            'journal_entry_id' => 'sometimes|required|integer|exists:journal_entries,id',
            'account_id' => 'sometimes|required|integer|exists:chart_of_accounts,id',
            'party_id' => 'nullable|integer|exists:accounts,id',
            'debit' => 'nullable|numeric|min:0',
            'credit' => 'nullable|numeric|min:0',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE journal_entry_lines SET ' . implode(', ', $updateFields) . ' WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Journal entry line updated successfully']);
    }

    // Delete a journal entry line
    public function destroy($id)
    {
        $line = DB::select('SELECT * FROM journal_entry_lines WHERE id = ?', [$id]);

        if (empty($line)) {
            return response()->json(['message' => 'Journal entry line not found'], 404);
        }

        DB::delete('DELETE FROM journal_entry_lines WHERE id = ?', [$id]);

        return response()->json(['message' => 'Journal entry line deleted successfully']);
    }
}
