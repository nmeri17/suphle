<x-layout pageTitle="Create Flavor">

	<div>
		<form method="post" action="/Flavor/save">
			<label>Title:</label>
			<input type="text" name="title">

			<input type="hidden" name="_csrf_token" value="{{$_csrf_token}}">

			<input type="submit" value="save">
		</form>
	</div>
</x-layout>