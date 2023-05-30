<?php
	require_once 'C:\laragon\www\otp\vendor\autoload.php';
	$ga = new PHPGangsta_GoogleAuthenticator();
?>

<form method="post" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
	<input type="text" name="secret" placeholder="secret">
	<br><br>
	<input type="text" name="otp" placeholder="otp">
	<input type="submit" value="go">
</form>

<?php
		$checkResult = $ga->verifyCode($_POST['secret'], $_POST['otp'],2);
	  if ($checkResult) {
				echo '<h1> Success !</h1>';
		} else {
			echo '<h1> Failed !</h1>';
		}
?>

