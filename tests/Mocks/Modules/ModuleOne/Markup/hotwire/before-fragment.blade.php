<!-- These are fresh fragments to insert if form submission succeeds-->

<div class="outer-container">
	<h3>Before form</h3>

	@isset ($data) <!-- this may be absent and the form will still render if the renderer has no replace node on validation failure -->
		<span class="id-holder">{{$data->id}}</span>
		<span class="title">{{$data->title}}</span>
	@endisset
</div>