<x-layout>

	<x-slot name="page-title">Search results for _resource_name</x-slot>

	<div>
		<ul>
			@foreach ($data as $item)

				<li> Item name: {{ $item->title }}</li>
			@endforeach
		</ul>
	</div>
</x-layout>