<x-layout pageTitle="Search results for _resource_name">

	<div>
		<ul>
			@foreach ($data as $item)

				<li> Item name: {{ $item->title }}</li>
			@endforeach
		</ul>
	</div>
</x-layout>