<?php

include_once "Query.php";

$host = "ctf.nekocafe.pw";
$port = 19132;

$query = new Query($host, $port);

// $query->setHost("play.lbsg.net");
// $query->setPort(19132);

try{
	$result = $query->sendQuery();
	echo "<pre>".print_r($result, true)."</pre>";
} catch (QueryException $e){
	echo $e->getMessage();
}
