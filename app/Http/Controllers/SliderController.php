<?php

namespace App\Http\Controllers;

use App\Models\Cerita;
use App\Models\Slider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SliderController extends Controller
{
    public function index(Request $request)
    {
        $query = Slider::with('cerita');
        $this->applyGridSort($query, $request, Slider::class, 'created_at', 'desc', [
            'url_gambar' => 'image_url',
            'lokasi_file' => 'image_path',
            'link_judul' => 'cerita_id',
        ]);

        $sliders = $query->paginate(10)->withQueryString();

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
            'image_file' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cerita_id' => 'nullable|exists:mst_cerita,id',
            'status' => 'required|boolean',
        ]);

        $imagePath = $this->storeImage($request);

        Slider::create([
            'image_url' => $this->publicImageUrl($imagePath),
            'image_path' => $imagePath,
            'cerita_id' => $request->cerita_id,
            'status' => $request->boolean('status'),
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
            'image_file' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
            'cerita_id' => 'nullable|exists:mst_cerita,id',
            'status' => 'required|boolean',
        ]);

        $data = [
            'cerita_id' => $request->cerita_id,
            'status' => $request->boolean('status'),
        ];

        if ($request->hasFile('image_file')) {
            if ($slider->image_path) {
                Storage::disk('public')->delete($slider->image_path);
            }

            $imagePath = $this->storeImage($request);
            $data['image_url'] = $this->publicImageUrl($imagePath);
            $data['image_path'] = $imagePath;
        }

        $slider->update($data);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil diperbarui.');
    }

    public function destroy(Slider $slider)
    {
        if ($slider->image_path) {
            Storage::disk('public')->delete($slider->image_path);
        }

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
        if (! $selectedId) {
            return null;
        }

        return Cerita::query()
            ->select('id', 'judul')
            ->find($selectedId);
    }

    private function storeImage(Request $request): string
    {
        $file = $request->file('image_file');
        $baseName = Str::slug(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME)) ?: 'slider';
        $filename = now()->format('YmdHis').'-'.Str::random(8).'-'.$baseName;

        return $file->storeAs('sliders', $filename.'.'.$file->getClientOriginalExtension(), 'public');
    }

    private function publicImageUrl(string $imagePath): string
    {
        return asset('storage/'.ltrim($imagePath, '/'));
    }
}
