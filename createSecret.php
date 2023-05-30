<?php
	require_once 'C:\laragon\www\otp\vendor\autoload.php';

	$ga = new PHPGangsta_GoogleAuthenticator();
	$secret = $ga->createSecret();
	$otp = $ga->getCode($secret);

	echo "Secret Code : ".$secret." <br>";
	echo "OTP Code    : ".$otp;
?>
<br>
<br>
Expierd in : <h4 id="timer"></h4>

<script>
	var timeLeft = 30;
	var elem = document.getElementById('timer');

	var timerId = setInterval(countdown, 1000);

	function countdown() {
		if (timeLeft == -1) {
			clearTimeout(timerId);
			updateOtp();
		} else {
			elem.innerHTML = timeLeft + ' seconds remaining';
			timeLeft--;
		}
	}
</script>