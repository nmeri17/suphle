@extends('layouts.app')

@section('title', '{{ $resource_name }} List')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">{{ $resource_name }} List</h1>
        <a href="{{ route('{{ $resource_name_lower }}.create') }}" 
           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Create New {{ $resource_name }}
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    @foreach($columns as $column)
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            {{ ucfirst($column) }}
                        </th>
                    @endforeach
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Actions
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse(${{ $resource_name_lower }}_list as ${{ $resource_name_lower }})
                    <tr>
                        @foreach($columns as $column)
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ ${{ $resource_name_lower }}->{{ $column }} }}
                            </td>
                        @endforeach
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('{{ $resource_name_lower }}.show', ${{ $resource_name_lower }}->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">View</a>
                                <a href="{{ route('{{ $resource_name_lower }}.edit', ${{ $resource_name_lower }}->id) }}" 
                                   class="text-yellow-600 hover:text-yellow-900">Edit</a>
                                <form action="{{ route('{{ $resource_name_lower }}.destroy', ${{ $resource_name_lower }}->id) }}" 
                                      method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Are you sure you want to delete this {{ $resource_name_lower }}?')">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($columns) + 1 }}" class="px-6 py-4 text-center text-gray-500">
                            No {{ $resource_name_lower }} found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if(${{ $resource_name_lower }}_list->hasPages())
        <div class="mt-6">
            {{ ${{ $resource_name_lower }}_list->links() }}
        </div>
    @endif
</div>
@endsection 