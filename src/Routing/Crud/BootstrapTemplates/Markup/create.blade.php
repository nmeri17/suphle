@extends('layouts.app')

@section('title', 'Create {{ $resource_name }}')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-900">Create New {{ $resource_name }}</h1>
        <a href="{{ route('{{ $resource_name_lower }}.index') }}" 
           class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
            Back to List
        </a>
    </div>

    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white shadow-md rounded-lg overflow-hidden">
        <form action="{{ route('{{ $resource_name_lower }}.store') }}" method="POST" class="p-6">
            @csrf
            
            @foreach($fields as $field => $type)
                <div class="mb-4">
                    <label for="{{ $field }}" class="block text-sm font-medium text-gray-700 mb-1">
                        {{ ucfirst($field) }}
                    </label>
                    
                    @switch($type)
                        @case('textarea')
                            <textarea 
                                id="{{ $field }}" 
                                name="{{ $field }}" 
                                rows="4"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field) border-red-500 @enderror"
                                placeholder="Enter {{ $field }}"
                            >{{ old($field) }}</textarea>
                            @break
                            
                        @case('select')
                            <select 
                                id="{{ $field }}" 
                                name="{{ $field }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field) border-red-500 @enderror"
                            >
                                <option value="">Select {{ $field }}</option>
                                @foreach(${{ $field }}_options ?? [] as $option)
                                    <option value="{{ $option['value'] }}" {{ old($field) == $option['value'] ? 'selected' : '' }}>
                                        {{ $option['label'] }}
                                    </option>
                                @endforeach
                            </select>
                            @break
                            
                        @case('checkbox')
                            <div class="flex items-center">
                                <input 
                                    type="checkbox" 
                                    id="{{ $field }}" 
                                    name="{{ $field }}" 
                                    value="1"
                                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded @error($field) border-red-500 @enderror"
                                    {{ old($field) ? 'checked' : '' }}
                                >
                                <label for="{{ $field }}" class="ml-2 block text-sm text-gray-900">
                                    {{ ucfirst($field) }}
                                </label>
                            </div>
                            @break
                            
                        @case('file')
                            <input 
                                type="file" 
                                id="{{ $field }}" 
                                name="{{ $field }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field) border-red-500 @enderror"
                            >
                            @break
                            
                        @default
                            <input 
                                type="{{ $type }}" 
                                id="{{ $field }}" 
                                name="{{ $field }}" 
                                value="{{ old($field) }}"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error($field) border-red-500 @enderror"
                                placeholder="Enter {{ $field }}"
                            >
                    @endswitch
                    
                    @error($field)
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            @endforeach
            
            <div class="flex justify-end space-x-2">
                <a href="{{ route('{{ $resource_name_lower }}.index') }}" 
                   class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    Cancel
                </a>
                <button type="submit" 
                        class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Create {{ $resource_name }}
                </button>
            </div>
        </form>
    </div>
</div>
@endsection 