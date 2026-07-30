<?php

namespace App\Http\Controllers;

use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status === '1');
        }

        $this->applyGridSort($query, $request, Faq::class, 'created_at', 'desc', [
            'pertanyaan' => 'question',
            'jawaban' => 'answer',
        ]);

        $faqs = $query->paginate(10)->withQueryString();

        return view('content.faqs.index', compact('faqs'));
    }

    public function create()
    {
        return view('content.faqs.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');
        Faq::create($data);

        return redirect()->route('faqs.index')->with('success', 'FAQ berhasil ditambahkan.');
    }

    public function show(Faq $faq)
    {
        return view('content.faqs.show', compact('faq'));
    }

    public function edit(Faq $faq)
    {
        return view('content.faqs.edit', compact('faq'));
    }

    public function update(Request $request, Faq $faq)
    {
        $data = $request->validate([
            'question' => 'required|string|max:255',
            'answer' => 'required|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        $data['status'] = $request->boolean('status');
        $faq->update($data);

        return redirect()->route('faqs.index')->with('success', 'FAQ berhasil diperbarui.');
    }

    public function destroy(Faq $faq)
    {
        $faq->delete();

        return redirect()->route('faqs.index')->with('success', 'FAQ berhasil dihapus.');
    }
}
