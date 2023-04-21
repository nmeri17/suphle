<x-layout pageTitle="Viewing all _resource_name">

	<div>
		<ul>
			@foreach ($data as $item)

				<li> Item name: {{ $item->title }}</li>
			@endforeach
		</ul>
	</div>
</x-layout>