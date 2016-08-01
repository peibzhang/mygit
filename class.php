<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
</head>
<body>
	<?php
	sleep(3);
	$a="apple";
	$b=&$a;
	$b="5$b";
	echo "$a"."<br/>"."$b";
	unset($b);
	var_dump(isset($b));
	echo "$a";
	?> 
</body>
</html>
