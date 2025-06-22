<?php

namespace App\Http\Controllers\Admin;

use App\Exports\CommentsExport;
use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Models\Product;
use App\Models\LiveAnalytics;
use App\Services\ZegoCloudService;
use App\Services\FCMService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class LiveStreamingController extends Controller
{
    protected $zegoCloudService;
    protected $fcmService;

    public function __construct(ZegoCloudService $zegoCloudService, FCMService $fcmService)
    {
        $this->zegoCloudService = $zegoCloudService;
        $this->fcmService = $fcmService;
    }

    // Pin product to live stream
    public function pinProduct(Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id',
            'product_id' => 'required|exists:products,id',
        ]);

        try {
            $stream = LiveStream::findOrFail($request->live_stream_id);
            $stream->pinned_product_id = $request->product_id;
            $stream->save();

            $product = Product::findOrFail($request->product_id);

            // Send push notification
            $title = 'Produk Baru di Live!';
            $body = 'Produk "' . $product->name . '" sedang ditampilkan di live streaming.';
            $data = [
                'type' => 'pinned_product',
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_image' => $product->image_url ?? '',
                'product_price' => $product->price,
                'live_stream_id' => $stream->id,
            ];

            $this->fcmService->sendTopicNotification('live_streams', $title, $body, $data);

            return response()->json(['message' => 'Produk berhasil dipasang di live stream.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal memasang produk: ' . $e->getMessage()], 500);
        }
    }

    // Save live analytics data
    public function saveAnalytics(Request $request)
    {
        $request->validate([
            'live_stream_id' => 'required|exists:live_streams,id',
            'total_comments' => 'required|integer|min:0',
            'active_users' => 'required|integer|min:0',
        ]);

        try {
            
            LiveAnalytics::create([
                'live_stream_id' => $request->live_stream_id,
                'total_comments' => $request->total_comments,
                'active_users' => $request->active_users,
                'recorded_at' => Carbon::now(),
            ]);

            return response()->json(['message' => 'Analytics data saved successfully.']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to save analytics: ' . $e->getMessage()], 500);
        }
    }

    // List order-type comments
    public function orderComments(Request $request)
    {
        $request->validate([
            'stream_id' => 'required|exists:live_streams,id',
        ]);

        $stream = LiveStream::findOrFail($request->stream_id);

        $orderComments = $stream->comments()
            ->where('type', 'ORDER')
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('admin.streaming.order_comments', compact('orderComments', 'stream'));
    }

    // Export order comments to Excel
    public function exportOrderComments(Request $request)
    {
        $request->validate([
            'stream_id' => 'required|exists:live_streams,id',
        ]);

        $stream = LiveStream::findOrFail($request->stream_id);

        try {
            $fileName = 'order_comments_' . $stream->id . '_' . date('Y-m-d_His') . '.xlsx';

            Excel::create($fileName, function($excel) use ($stream) {
                $excel->sheet('Order Comments', function($sheet) use ($stream) {
                    $comments = $stream->comments()
                        ->where('type', 'ORDER')
                        ->with('user')
                        ->orderBy('created_at', 'asc')
                        ->get()
                        ->map(function($comment) {
                            return [
                                'Time' => $comment->created_at->format('Y-m-d H:i:s'),
                                'User' => $comment->user->name,
                                'Comment' => $comment->content,
                            ];
                        });

                    $sheet->fromArray($comments);
                });
            })->store('xlsx', storage_path('app/public/exports'));

            return response()->download(storage_path('app/public/exports/' . $fileName))
                ->deleteFileAfterSend();
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to export order comments: ' . $e->getMessage());
        }
    }
    public function index()
{
    // Mengambil semua live streams dengan paginasi
    $streams = LiveStream::with('products', 'comments')->paginate(10); // Paginasi
    $products = Product::all(); // Ambil semua produk

    // Mengembalikan view dengan data streams dan products
    return view('admin.streaming.index', compact('streams', 'products'));
}

public function dashboard()
{
    // Logika untuk menampilkan dashboard live streaming
    $liveStreams = LiveStream::count(); // Menghitung jumlah live streams
    return view('admin.streaming.dashboard', compact('liveStreams'));
}

public function startStream(Request $request)
{
    $request->validate([
        'stream_id' => 'required|exists:live_streams,id',
    ]);

    // Logika untuk memulai streaming
    $stream = LiveStream::findOrFail($request->stream_id);
    $stream->status = 'live'; // Ubah status menjadi 'live'
    $stream->save();

    return response()->json(['message' => 'Streaming berhasil dimulai.']);
}

public function endStream(Request $request)
{
    $request->validate([
        'stream_id' => 'required|exists:live_streams,id',
    ]);

    // Logika untuk mengakhiri streaming
    $stream = LiveStream::findOrFail($request->stream_id);
    $stream->status = 'ended'; // Ubah status menjadi 'ended'
    $stream->save();

    return response()->json(['message' => 'Streaming berhasil diakhiri.']);
}

public function exportComments(Request $request)
{
    $request->validate([
        'stream_id' => 'required|exists:live_streams,id',
    ]);

    $stream = LiveStream::findOrFail($request->stream_id);
    $comments = $stream->comments()->with('user')->get(); // Ambil semua komentar dengan user

    // Menggunakan kelas ekspor
    return Excel::download(new CommentsExport($comments), 'comments_' . $stream->id . '.xlsx');
}

public function addProduct(Request $request)
{
    $request->validate([
        'stream_id' => 'required|exists:live_streams,id',
        'product_id' => 'required|exists:products,id',
    ]);

    // Logika untuk menambahkan produk ke streaming
    $stream = LiveStream::findOrFail($request->stream_id);
    $stream->products()->attach($request->product_id); // Mengaitkan produk dengan streaming

    return response()->json(['message' => 'Produk berhasil ditambahkan ke live stream.']);
}

public function removeProduct(Request $request)
{
    $request->validate([
        'stream_id' => 'required|exists:live_streams,id',
        'product_id' => 'required|exists:products,id',
    ]);

    // Logika untuk menghapus produk dari streaming
    $stream = LiveStream::findOrFail($request->stream_id);
    $stream->products()->detach($request->product_id); // Menghapus kaitan produk dengan streaming

    return response()->json(['message' => 'Produk berhasil dihapus dari live stream.']);
}

public function create()
{
    $products = Product::all(); // Fetch all products or apply any filters needed
    return view('admin.streaming.create', compact('products'));
}

public function store(Request $request)
{
    $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'required|string'
    ]);

    try {
        DB::beginTransaction();

        // Generate ZEGO room and stream IDs
        $zegoData = $this->zegoCloudService->createRoom($request->title, auth()->id());
        
        // Create the live stream record
        $stream = LiveStream::create([
            'title' => $request->title,
            'description' => $request->description,
            'user_id' => auth()->id(),
            'room_id' => $zegoData['room_id'],
            'stream_id' => $zegoData['stream_id'],
            'status' => 'scheduled',
            'viewer_count' => 0
        ]);

        // Generate stream token
        $token = $this->zegoCloudService->generateToken(
            auth()->id(),
            auth()->user()->name,
            $zegoData['room_id'],
            1 // role 1 for host
        );

        $stream->update(['stream_token' => $token]);

        DB::commit();

        return redirect()
            ->route('admin.streaming.dashboard')
            ->with('success', 'Live stream created successfully. You can now start the stream.');

    } catch (\Exception $e) {
        DB::rollBack();
        return back()
            ->withInput()
            ->with('error', 'Failed to create live stream: ' . $e->getMessage());
    }
}
}