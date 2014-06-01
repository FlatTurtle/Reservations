<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
	</head>
	<body>
		<h2>Confirm your reservation</h2>

		<div>
            You receive this email because you made a reservation for "{{ $thing_name }}" from {{ $from }} to {{ $to }}.
            <br>
			Please confirm your reservation by clicking this link:
			<br><br>
			<a href="{{ URL::to($confirm_url) }}">{{ URL::to($confirm_url) }}</a>

			<br><br>
			You can cancel your reservation by clicking this link:
			<br><br>
			<a href="{{ URL::to($cancel_url) }}">{{ URL::to($cancel_url) }}</a>
		</div>
	</body>
</html>
