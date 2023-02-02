<!-- These fragments/partials are expected to be included in the preceding GET page with a matching hotwire frame tag -->
<h3>Replace form</h3>

<form id="">
	<input type="text" name="id" value="@isset($payload_storage){{$payload_storage['id']}}@endisset">
	
	<input type="text" name="id2" value="@isset($payload_storage){{@$payload_storage['id2']}}@endisset">
</form>

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

<!-- In the real world, this section will reside in the before/after partials, served with IDs matching a target on the edit-form page. Just not using it here since hotwire front end is not available to do replacement for us -->
@isset($data)

	<div id="from-handler">{{$data->id}}</div>
@endisset