<?php

namespace App\Http\Controllers;

use App\Models\Cerita;
use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::with('cerita')->latest()->paginate(10);
        return view('content.slider.index', compact('sliders'));
    }

    public function create()
    {
        $selectedCerita = $this->findSelectedCerita(request()->old('cerita_id'));
        $ceritas = $this->ceritaSelectOptions($selectedCerita?->id);

        return view('content.slider.create', compact('ceritas', 'selectedCerita'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url|max:255',
            'cerita_id' => 'nullable|exists:mst_cerita,id',
            'status'    => 'required|boolean',
        ]);

        Slider::create([
            'image_url' => $request->image_url,
            'cerita_id' => $request->cerita_id,
            'status'    => $request->boolean('status'),
        ]);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil ditambahkan.');
    }

    public function show(Slider $slider)
    {
        $slider->load('cerita');

        return view('content.slider.show', compact('slider'));
    }

    public function edit(Slider $slider)
    {
        $slider->load('cerita');
        $selectedCerita = $this->findSelectedCerita(request()->old('cerita_id', $slider->cerita_id));
        $ceritas = $this->ceritaSelectOptions($selectedCerita?->id);

        return view('content.slider.edit', compact('slider', 'ceritas', 'selectedCerita'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'image_url' => 'required|url|max:255',
            'cerita_id' => 'nullable|exists:mst_cerita,id',
            'status'    => 'required|boolean',
        ]);

        $slider->update([
            'image_url' => $request->image_url,
            'cerita_id' => $request->cerita_id,
            'status'    => $request->boolean('status'),
        ]);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy(Slider $slider)
    {
        $slider->delete();
        return redirect()->route('slider.index')->with('success', 'Slider berhasil dihapus.');
    }

    public function ceritaOptions(Request $request)
    {
        $request->validate([
            'q' => 'nullable|string|max:255',
        ]);

        $query = Cerita::query()
            ->select('id', 'judul')
            ->orderBy('judul');

        if ($request->filled('q')) {
            $keyword = mb_strtolower(trim($request->q));
            $query->whereRaw('LOWER(judul) LIKE ?', ["%{$keyword}%"]);
        }

        return response()->json([
            'data' => $query->limit(5)->get(),
        ]);
    }

    private function ceritaSelectOptions(?int $selectedId = null)
    {
        $query = Cerita::query()
            ->select('id', 'judul')
            ->orderBy('judul');

        if ($selectedId) {
            $query->where('id', '!=', $selectedId);
        }

        $ceritas = $query->limit($selectedId ? 4 : 5)->get();

        if ($selectedId && $selectedCerita = $this->findSelectedCerita($selectedId)) {
            $ceritas->prepend($selectedCerita);
        }

        return $ceritas;
    }

    private function findSelectedCerita($selectedId): ?Cerita
    {
        if (!$selectedId) {
            return null;
        }

        return Cerita::query()
            ->select('id', 'judul')
            ->find($selectedId);
    }
}
