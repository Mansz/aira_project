<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\Product;
use App\Services\ZegoCloudService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class StreamingController extends Controller
{
    protected $zegoService;

    public function __construct(ZegoCloudService $zegoService)
    {
        $this->zegoService = $zegoService;
    }

    public function index()
    {
        $streams = LiveStream::with(['user', 'products'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.streaming.index', compact('streams'));
    }

    public function show(LiveStream $stream)
    {
        return view('admin.streaming.show', compact('stream'));
    }

    public function edit(LiveStream $stream)
    {
        return view('admin.streaming.edit', compact('stream'));
    }

    public function update(Request $request, LiveStream $stream)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:scheduled,live,ended',
            'scheduled_at' => 'nullable|date',
            'thumbnail' => 'nullable|image|max:2048',
            'settings' => 'nullable|array',
        ]);

        if ($request->hasFile('thumbnail')) {
            if ($stream->thumbnail_path) {
                Storage::disk('public')->delete($stream->thumbnail_path);
            }
            $validated['thumbnail_path'] = $request->file('thumbnail')->store('thumbnails', 'public');
        }

        $stream->update($validated);

        return redirect()
            ->route('admin.streaming.index')
            ->with('success', 'Live stream updated successfully.');
    }

    public function destroy(LiveStream $stream)
    {
        if ($stream->thumbnail_path) {
            Storage::disk('public')->delete($stream->thumbnail_path);
        }

        $stream->delete();

        return redirect()
            ->route('admin.streaming.index')
            ->with('success', 'Live stream deleted successfully.');
    }

    public function monitor()
    {
        $activeStreams = LiveStream::active()->with(['user', 'products'])->get();
        return view('admin.streaming.monitor', compact('activeStreams'));
    }

    public function endStream(LiveStream $stream)
    {
        $stream->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return redirect()
            ->route('admin.streaming.index')
            ->with('success', 'Live stream ended successfully.');
    }

    public function create()
{
    $products = Product::all(); // Fetch all products or apply any filters needed
    return view('admin.streaming.create', compact('products'));
}

    
}
