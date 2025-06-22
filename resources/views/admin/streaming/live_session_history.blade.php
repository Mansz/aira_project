@extends('admin.layouts.stisla')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Riwayat Live Streaming</h1>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="py-2 px-4 border-b">Judul Live</th>
                    <th class="py-2 px-4 border-b">Waktu Mulai</th>
                    <th class="py-2 px-4 border-b">Waktu Selesai</th>
                    <th class="py-2 px-4 border-b">Jumlah Komentar</th>
                    <th class="py-2 px-4 border-b">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($sessions as $session)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $session->title }}</td>
                    <td class="py-2 px-4 border-b">{{ $session->started_at ? $session->started_at->format('Y-m-d H:i:s') : '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $session->ended_at ? $session->ended_at->format('Y-m-d H:i:s') : '-' }}</td>
                    <td class="py-2 px-4 border-b">{{ $session->comments_count }}</td>
                    <td class="py-2 px-4 border-b">
                        <a href="{{ route('admin.streaming.comments', ['stream_id' => $session->id]) }}" class="text-blue-600 hover:underline">Lihat Komentar</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-4 px-4 text-center text-gray-500">Tidak ada riwayat live streaming ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $sessions->links() }}
    </div>
</div>
@endsection
