<x-layout pageTitle="Create _resource_name">

	<div>
		<form method="post" action="/_resource_name/save">
			<label>Title:</label>
			
			<input type="text" name="title" value="@isset($payload_storage){{$payload_storage['title']}}@endisset">

			<input type="submit" value="save">
		</form>
	</div>

@isset($validation_errors)
	<div id="validation-errors">
		<h3>Validation errors</h3>

		<ul>
			@foreach($validation_errors as $key => $error)

				<li class="error">

					{{$key . ":". implode("\n", $error)}}
				</li>
			@endforeach
		</ul>
	</div>
@endisset
</x-layout>