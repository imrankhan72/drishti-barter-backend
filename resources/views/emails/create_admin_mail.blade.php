<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<p>
	     Hi {{$user->first_name}} {{$user->last_name}}.<br>
	    Email: {{$user->email }}<br>
	    Password: {{$password}}
		<br>
		Regards,<br>
		Team Drishtee
	</p>
</body>
</html>