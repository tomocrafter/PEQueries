PEQueries
====

## Description
これは、簡単にPocketMine-MPのQueryを誰でも扱えるようにするライブラリです。  
商用利用可能ですが、ご使用いただく際はサイトなどに  
私の名前やこのライブラリの名前を掲載させていただければ光栄です。  
06/07 テスト完了しました！

## Usage
```php
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
```
詳しい使い方は、このライブラリに同梱されている
example.phpを読んでみてください！

## Author
[@tomocrafter](https://twitter.com/tomocrafter)
[tomocrafter](https://github.com/tomocrafter)
