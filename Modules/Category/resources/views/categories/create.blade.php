@extends('category::layouts.master')

@section('content')
    <h1>Create Category</h1>
    <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <label for="name">Category Name:</label>
        <input type="text" id="name" name="name" required>

        <label for="parent_id">Parent Category:</label>
        <select name="parent_id" id="parent_id">
            <option value="">No Parent</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </select>

        <label for="description">Description:</label>
        <textarea name="description" id="description"></textarea>

        <button type="submit">Create Category</button>
    </form>
@endsection
