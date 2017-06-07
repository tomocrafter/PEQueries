<?php

include_once "Query.php";

$host = "ctf.nekocafe.pw";
$port = 19132;

$query = new Query($host, $port);

// $query->setHost("play.lbsg.net")->setPort(19132);
// set関数の返り値はインスタンスなのでチェーン可能です。

try{
	$result = $query->sendQuery();
	echo "<pre>".print_r($result, true)."</pre>";
} catch (QueryException $e){
	echo $e->getMessage();
}
