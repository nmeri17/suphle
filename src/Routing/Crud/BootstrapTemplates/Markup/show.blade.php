@extends('layouts.app')

@section('title', '{{ $resource_name }} Details')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $resource_name }} Details</h1>
        <div class="flex space-x-2">
            <a href="{{ route('{{ $resource_name_lower }}.edit', ${{ $resource_name_lower }}->id) }}" 
               class="bg-yellow-500 hover:bg-yellow-700 text-white font-bold py-2 px-4 rounded">
                Edit {{ $resource_name }}
            </a>
            <a href="{{ route('{{ $resource_name_lower }}.index') }}" 
               class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to List
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <div class="px-6 py-4">
            @foreach($columns as $column)
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        {{ ucfirst($column) }}
                    </label>
                    <div class="text-sm text-gray-900">
                        {{ ${{ $resource_name_lower }}->{{ $column }} }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <div class="mt-6">
        <form action="{{ route('{{ $resource_name_lower }}.destroy', ${{ $resource_name_lower }}->id) }}" 
              method="POST" class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                    onclick="return confirm('Are you sure you want to delete this {{ $resource_name_lower }}?')">
                Delete {{ $resource_name }}
            </button>
        </form>
    </div>
</div>
@endsection 