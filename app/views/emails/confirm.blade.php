<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Confirm your meeting room reservation.</h2>

		<div>
            You receive this email because you made a reservation: <br />
            <br />
			Meeting room: {{ $thing_name }} <br />
			Date: {{ $from }} => {{ $to }} <br />
			<br />
			<strong>Confirm</strong> booking / Confirmez reservation / Bevestigen boeking: <br />
			<a href="{{ URL::to($confirm_url) }}">{{ URL::to($confirm_url) }}</a><br />
 			<br />
			<strong>Cancel</strong> / Annulez / Annuleer: <br />
			<a href="{{ URL::to($cancel_url) }}">{{ URL::to($cancel_url) }}</a><br />
			<br />
			<br />
			<br />
			<p>-- <br />
			help@FlatTurtle.com <br />
			&copy; <a href="http://FlatTurtle.com/">FlatTurtle</a></p>
		</div>
	</body>
</html>
