<?php

namespace App\Http\Controllers;

use App\Models\MstAction;
use Illuminate\Http\Request;

class MstActionController extends Controller
{
    public function index()
    {
        $actions = MstAction::latest()->paginate(10);
        return view('content.action.index', compact('actions'));
    }

    public function create()
    {
        return view('content.action.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'action_name' => 'required|string|max:255|unique:mst_action,action_name',
        ]);

        MstAction::create($request->only('action_name'));

        return redirect()->route('action.index')->with('success', 'Action berhasil ditambahkan.');
    }

    public function show(MstAction $action)
    {
        return view('content.action.show', compact('action'));
    }

    public function edit(MstAction $action)
    {
        return view('content.action.edit', compact('action'));
    }

    public function update(Request $request, MstAction $action)
    {
        $request->validate([
            'action_name' => 'required|string|max:255|unique:mst_action,action_name,' . $action->id,
        ]);

        $action->update($request->only('action_name'));

        return redirect()->route('action.index')->with('success', 'Action berhasil diperbarui.');
    }

    public function destroy(MstAction $action)
    {
        $action->delete();
        return redirect()->route('action.index')->with('success', 'Action berhasil dihapus.');
    }
}
