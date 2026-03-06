@extends('category::layouts.master')

@section('content')
    <h1>Edit Category</h1>
    <form action="{{ route('categories.update', $category->id) }}" method="POST">
        @csrf
        @method('PUT')

        <label for="name">Category Name:</label>
        <input type="text" id="name" name="name" value="{{ $category->name }}" required>

        <label for="parent_id">Parent Category:</label>
        <select name="parent_id" id="parent_id">
            <option value="">No Parent</option>
            @foreach ($categories as $parentCategory)
                <option value="{{ $parentCategory->id }}" {{ $category->parent_id == $parentCategory->id ? 'selected' : '' }}>
                    {{ $parentCategory->name }}
                </option>
            @endforeach
        </select>

        <label for="description">Description:</label>
        <textarea name="description" id="description">{{ $category->description }}</textarea>

        <button type="submit">Update Category</button>
    </form>
@endsection
