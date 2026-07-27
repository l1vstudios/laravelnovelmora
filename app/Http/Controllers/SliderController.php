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
        $ceritas = Cerita::orderBy('judul')->get(['id', 'judul']);

        return view('content.slider.create', compact('ceritas'));
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
        $ceritas = Cerita::orderBy('judul')->get(['id', 'judul']);

        return view('content.slider.edit', compact('slider', 'ceritas'));
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
}
