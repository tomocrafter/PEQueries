PEQueries
====

Overview

## Description
これは、簡単にPocketMine-MPのQueryを誰でも扱えるようにするライブラリです。

商用利用可能ですが、ご使用いただく際はサイトなどに私の名前やこのライブラリの名前を掲載させていただければ

光栄です。

## Usage
```php
<?php
include_once "Query.php";

$host = "ctf.nekocafe.pw";
$port = 19132;

$query = new Query($host, $port);
$query->sendQuery();
echo "<pre>".$query->getMessage()."</pre>";
```
詳しい使い方は、このライブラリに同梱されている

example.phpを読んでみてください！

## Author
[@tomocrafter](https://twitter.com/tomocrafter)
[tomocrafter](https://github.com/tomocrafter)