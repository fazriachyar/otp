<?php
	require_once 'C:\laragon\www\otp\vendor\autoload.php';
	use Predis\Client;
	$ga = new PHPGangsta_GoogleAuthenticator();

	try {
		$redis = new Client();
		// $redis->hset("user:123","secret", $secret);
		// $redis->hset("user:123","otp", $otp);
		$value = $redis->hgetall("user:123");
		var_dump($value);
	}
	catch (Exception $e) {
			die ($e->getMessage());
	}
?>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<input type="text" name="secret" placeholder="secret">
	<br><br>
	<input type="text" name="otp" placeholder="otp">
	<input type="submit" value="go">
</form>

<?php
		// $checkResult = $ga->verifyCode($_POST['secret'], $_POST['otp'],2);
	  // if ($checkResult) {
		// 		echo '<h1> Success !</h1>';
		// } else {
		// 	echo '<h1> Failed !</h1>';
		// }
		$checkResult = $ga->verifyCode($value['secret'], $value['otp'],1);
	  if ($checkResult) {
				echo '<h1> Success !</h1>';
				$redis->del("user:123");
		} else {
			echo '<h1> Failed !</h1>';
		}
?>

