<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Edit form</title>
</head>
<body>
	<div id="update-form">
		@include("hotwire/update-fragment") <!-- on failure, hotwire replaces this with a stream tag matching this ID -->
	</div>

	<!-- In real life, this won't be hard-coded but outputted as part of a loop -->
	<div id="employment_2"></div>
</body>
</html>