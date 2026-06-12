<?php

namespace App\Http\Controllers;

use App\Models\Slider;
use Illuminate\Http\Request;

class SliderController extends Controller
{
    public function index()
    {
        $sliders = Slider::latest()->paginate(10);
        return view('content.slider.index', compact('sliders'));
    }

    public function create()
    {
        return view('content.slider.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'image_url' => 'required|url|max:255',
            'status'    => 'required|boolean',
        ]);

        Slider::create([
            'image_url' => $request->image_url,
            'status'    => $request->boolean('status'),
        ]);

        return redirect()->route('slider.index')->with('success', 'Slider berhasil ditambahkan.');
    }

    public function show(Slider $slider)
    {
        return view('content.slider.show', compact('slider'));
    }

    public function edit(Slider $slider)
    {
        return view('content.slider.edit', compact('slider'));
    }

    public function update(Request $request, Slider $slider)
    {
        $request->validate([
            'image_url' => 'required|url|max:255',
            'status'    => 'required|boolean',
        ]);

        $slider->update([
            'image_url' => $request->image_url,
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
