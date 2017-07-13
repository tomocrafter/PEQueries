<?php

include_once 'Query.php';

$host = 'moin.dip.jp';
$port = 19132;

try{
	$query = new Query($host, $port);

	// $query->setHost('play.lbsg.net')->setPort(19132);
	// set関数の返り値はインスタンスなのでチェーン可能です。
	// ※バージョン 1.1.0 から、インスタンス生成やsetHost関数やsetPort関数はQueryExceptionをスローするようになりました。
	// そのためエラーハンドリングを適切にしてあげる必要があります。

	$result = $query->sendQuery();
	echo json_encode($result);
} catch (QueryException $e){
	echo $e->getMessage();
}
