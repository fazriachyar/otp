<?php
	session_start();
	require_once 'C:\laragon\www\otp\vendor\autoload.php';
	use Predis\Client;

	$ga = new PHPGangsta_GoogleAuthenticator();
	$secret = $ga->createSecret();
	$otp = $ga->getCode($secret);
	$_SESSION['secret'] = $secret;

	echo "Secret Code : ".$secret." <br>";
	echo "OTP Code    : ".$otp;
	try {
			$redis = new Client();
			$redis->hset("user:123","secret", $secret);
			$redis->hset("user:123","otp", $otp);
			// $value = $redis->hgetall("user:123");
	}
	catch (Exception $e) {
			die ($e->getMessage());
	}
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
			elem.innerHTML = 'OTP Expired';
		} else {
			elem.innerHTML = timeLeft + ' seconds remaining';
			timeLeft--;
		}
	}
</script>