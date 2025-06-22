@extends('admin.layouts.stisla')

@section('content')
<div class="content-wrapper">
    <section class="content">
        <div class="container-fluid">
            <h1>Edit Stream</h1>
            <form action="{{ route('admin.streaming.update', $stream->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" value="{{ old('title', $stream->title) }}" class="form-control" required>
                </div>

                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" class="form-control">{{ old('description', $stream->description) }}</textarea>
                </div>

                <div class="form-group">
                    <label>Thumbnail (optional)</label>
                    <input type="file" name="thumbnail" class="form-control-file">
                    @if($stream->thumbnail)
                        <p><img src="{{ asset('storage/'.$stream->thumbnail) }}" height="100"></p>
                    @endif
                </div>

                <div class="form-group">
                    <label>Products</label>
                    <select name="products[]" class="form-control select2" multiple required>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}" {{ in_array($product->id, $stream->products->pluck('id')->toArray()) ? 'selected' : '' }}>
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Update Stream</button>
                <a href="{{ route('admin.live-streams.index') }}" class="btn btn-secondary">Back</a>
            </form>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2();
    });
</script>
@endpush
