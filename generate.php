<?php

if(isset($_GET['pass']))
{
	$hash = password_hash($_GET['pass'], PASSWORD_DEFAULT);
	echo $hash;
}