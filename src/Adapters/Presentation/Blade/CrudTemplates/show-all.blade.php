<x-layout>

	<x-slot name="page-title">Viewing all _resource_name</x-slot>

	<div>
		<ul>
			@foreach ($data as $item)

				<li> Item name: {{ $item->title }}</li>
			@endforeach
		</ul>
	</div>
</x-layout>