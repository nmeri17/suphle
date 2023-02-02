<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<title>Default Page</title>
</head>
<body>

	<div>
		<ul>
			@foreach($data as $name)
				<li>{{$name}}</li>
			@endforeach
		</ul>
	</div>
</body>
</html>