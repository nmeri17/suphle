<x-layout>

	<x-slot name="page-title">Create _resource_name</x-slot>

	<div>
		<form action="post" target="/_resource_name/save">
			<input type="text" name="title">

			<input type="submit" value="save">
		</form>
	</div>
</x-layout>