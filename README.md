#PEQueries

`<?php    include_once "Query.php";        $host = "ctf.nekocafe.pw";    $port = 19132;        $query = new Query($host, $port);        // $query->setHost("play.lbsg.net");    // $query->setPort(19132);        $query->sendQuery();        echo "<pre".$query->getMesage()."</pre>";`