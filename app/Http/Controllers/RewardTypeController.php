<?php

namespace App\Http\Controllers;

use App\Models\RewardType;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RewardTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = RewardType::withCount('dailyRewards')->latest();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $rewardTypes = $query->paginate(10)->withQueryString();

        return view('content.reward-types.index', compact('rewardTypes'));
    }

    public function create()
    {
        return view('content.reward-types.create');
    }

    public function store(Request $request)
    {
        $request->merge(['name' => Str::slug($request->input('name', ''), '_')]);

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:mst_reward_types,name',
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        RewardType::create($data);

        return redirect()->route('reward-types.index')->with('success', 'Reward type berhasil ditambahkan.');
    }

    public function show(RewardType $rewardType)
    {
        $rewardType->loadCount('dailyRewards');

        return view('content.reward-types.show', compact('rewardType'));
    }

    public function edit(RewardType $rewardType)
    {
        return view('content.reward-types.edit', compact('rewardType'));
    }

    public function update(Request $request, RewardType $rewardType)
    {
        $request->merge(['name' => Str::slug($request->input('name', ''), '_')]);

        $data = $request->validate([
            'name'        => 'required|string|max:255|unique:mst_reward_types,name,' . $rewardType->id,
            'label'       => 'required|string|max:255',
            'description' => 'nullable|string',
            'status'      => 'required|boolean',
        ]);

        $data['status'] = $request->boolean('status');

        $rewardType->update($data);

        return redirect()->route('reward-types.index')->with('success', 'Reward type berhasil diperbarui.');
    }

    public function destroy(RewardType $rewardType)
    {
        $rewardType->delete();

        return redirect()->route('reward-types.index')->with('success', 'Reward type berhasil dihapus.');
    }
}
