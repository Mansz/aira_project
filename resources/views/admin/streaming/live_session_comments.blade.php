@extends('admin.layouts.stisla')

@section('content')
<div class="container mx-auto p-4">
    <h1 class="text-2xl font-bold mb-4">Komentar Live Streaming</h1>

    <form method="GET" action="{{ route('admin.streaming.comments') }}" class="mb-4 flex flex-wrap gap-4 items-center">
        <input type="text" name="search" placeholder="Cari nama user atau isi komentar" value="{{ request('search') }}" class="border rounded px-3 py-2 flex-grow" />
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2" />
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2" />
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Filter</button>
        <a href="{{ route('admin.streaming.exportComments', ['stream_id' => request('stream_id')]) }}" class="ml-auto bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Export ke CSV</a>
    </form>

    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded">
            <thead>
                <tr class="bg-gray-100 text-left">
                    <th class="py-2 px-4 border-b">Waktu</th>
                    <th class="py-2 px-4 border-b">Nama User</th>
                    <th class="py-2 px-4 border-b">Isi Komentar</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($comments as $comment)
                <tr class="hover:bg-gray-50">
                    <td class="py-2 px-4 border-b">{{ $comment->created_at->format('Y-m-d H:i:s') }}</td>
                    <td class="py-2 px-4 border-b">{{ $comment->user->name ?? 'Unknown' }}</td>
                    <td class="py-2 px-4 border-b">{{ $comment->content }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="3" class="py-4 px-4 text-center text-gray-500">Tidak ada komentar ditemukan.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $comments->appends(request()->query())->links() }}
    </div>
</div>
@endsection
