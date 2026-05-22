<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChartOfAccountsController extends Controller
{
    // Get all chart of accounts with full details (joins with stations and parent accounts)
    public function index()
    {
        $accounts = DB::select(
            'SELECT coa.id, coa.account_code, coa.name AS account_name, coa.type, 
                    coa.parent_id, parent.name AS parent_account_name, 
                    coa.station_id, st.name AS station_name, st.location AS station_location, 
                    coa.created_at, coa.updated_at
             FROM chart_of_accounts coa
             LEFT JOIN chart_of_accounts parent ON coa.parent_id = parent.id
             LEFT JOIN stations st ON coa.station_id = st.id
             ORDER BY coa.account_code ASC'
        );

        return response()->json($accounts);
    }

    // Get a single chart of account by ID with full details
    public function show($id)
    {
        $account = DB::select(
            'SELECT coa.id, coa.account_code, coa.name AS account_name, coa.type, 
                    coa.parent_id, parent.name AS parent_account_name, 
                    coa.station_id, st.name AS station_name, st.location AS station_location, 
                    coa.created_at, coa.updated_at
             FROM chart_of_accounts coa
             LEFT JOIN chart_of_accounts parent ON coa.parent_id = parent.id
             LEFT JOIN stations st ON coa.station_id = st.id
             WHERE coa.id = ?',
            [$id]
        );

        if (empty($account)) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        return response()->json($account[0]);
    }

    // Create a new chart of account
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'station_id' => 'required|integer|exists:stations,id',
            'account_code' => 'required|string|max:20|unique:chart_of_accounts,account_code',
            'name' => 'required|string|max:100',
            'type' => 'required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        DB::insert(
            'INSERT INTO chart_of_accounts (station_id, account_code, name, type, parent_id, created_at, updated_at)
             VALUES (?, ?, ?, ?, ?, NOW(), NOW())',
            [
                $validatedData['station_id'],
                $validatedData['account_code'],
                $validatedData['name'],
                $validatedData['type'],
                $validatedData['parent_id'],
            ]
        );

        return response()->json(['message' => 'Chart of account created successfully'], 201);
    }

    // Update an existing chart of account
    public function update(Request $request, $id)
    {
        $account = DB::select('SELECT * FROM chart_of_accounts WHERE id = ?', [$id]);

        if (empty($account)) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        $validatedData = $request->validate([
            'station_id' => 'sometimes|required|integer|exists:stations,id',
            'account_code' => 'sometimes|required|string|max:20|unique:chart_of_accounts,account_code,' . $id,
            'name' => 'sometimes|required|string|max:100',
            'type' => 'sometimes|required|in:asset,liability,equity,income,expense',
            'parent_id' => 'nullable|integer|exists:chart_of_accounts,id',
        ]);

        $updateFields = [];
        $updateValues = [];

        foreach ($validatedData as $key => $value) {
            $updateFields[] = "$key = ?";
            $updateValues[] = $value;
        }

        $updateValues[] = $id;

        DB::update(
            'UPDATE chart_of_accounts SET ' . implode(', ', $updateFields) . ', updated_at = NOW() WHERE id = ?',
            $updateValues
        );

        return response()->json(['message' => 'Chart of account updated successfully']);
    }

    // Delete a chart of account
    public function destroy($id)
    {
        $account = DB::select('SELECT * FROM chart_of_accounts WHERE id = ?', [$id]);

        if (empty($account)) {
            return response()->json(['message' => 'Account not found'], 404);
        }

        DB::delete('DELETE FROM chart_of_accounts WHERE id = ?', [$id]);

        return response()->json(['message' => 'Chart of account deleted successfully']);
    }
}
