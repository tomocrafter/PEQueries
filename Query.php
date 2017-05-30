<?php
/**
 * 簡単に使い回しが可能なQueryインスタンスを生成する
 * QueryManagerです！
 * 商用利用可能です。もしよければサイトなどに
 * 私の名前やこのライブラリの名前を載せていただけたら光栄です。
 *
 * @author tomotomo or tomocrafter <tomoppeko@gmail.com>
 * @version 1.0.0 [OpenSource]
 */

include "QueryException.php";

class Query{

	private $message = "";
	private $result = [];

	/**
	 * [__construct description]
	 * @param string $host [description]
	 * @param int    $port [description]
	 */
	public function __construct(string $host, int $port){
		$this->host = $host;
		$this->port = $port;
	}

	/**
	 * [getMessage 現在のメッセージを返します？]
	 * @return string [現在のメッセージ]
	 */
	public function getMessage() : string{
		return $this->message;
	}

	/**
	 * [getResult Queryの結果を返却します。]
	 * @return array [Queryの結果]
	 */
	public function getResult() : array{
		if(empty($this->result)) throw new QueryException("You have not submitted a query yet.");
		return $this->result;
	}

	/**
	 * [setHost ホストを変更します。]
	 * @param string $host [変更するホスト]
	 * @return this [このインスタンスを返します]
	 */
	public function setHost(string $host){
		$this->host = $host;
		return $this;
	}

	/**
	 * [setPort ポートを変更します。]
	 * @param int $port [変更するポート]
	 * @return this [このインスタンスを返します]
	 */
	public function setPort(int $port){
		$this->port = $port;
		return $this;
	}

	/**
	 * [getHost 現在のホストを返します。]
	 * @return string [現在のホスト]
	 */
	public function getHost() : string{
		return $this->host;
	}

	/**
	 * [getPort 現在のポートを返します。]
	 * @return int [現在のポート]
	 */
	public function getPort() : int{
		return $this->port;
	}

	public function UT3QueryServer(string $host, int $port) : array{
		$sock = @fsockopen("udp://" . $host, $port);
		if(!$sock){
			throw new QueryException("Host or Port is Invalid!!");
		}
		@socket_set_timeout($sock, 0, PHP_INT_MAX);
		if(!@fwrite($sock, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01")){
			throw new QueryException("fwrite error.");
			return "fwrite error.";
		}
		$challenge = fread($sock, 1400);
		if(!$challenge){
			throw new QueryException("fread error.");
		}

		$challenge = substr(preg_replace("/[^0-9\-]/si",  "",  $challenge), 1);
		$query = sprintf("\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01",
			($challenge >> 24),
			($challenge >> 16),
			($challenge >> 8),
			($challenge >> 0)
		);
		if(!@fwrite($sock, $query)){
			throw new QueryException("fwrite error.");
		}
		$response = [];
		for($i = 0; $i < 2; $i++){
			$response[] = @fread($sock, 2048);
		}

		$response = explode("\0", substr(implode($response), 16));
		array_pop($response);
		array_pop($response);
		array_pop($response);
		array_pop($response);
		array_pop($response);

		$return = [];
		$type = 0;
		foreach($response as $key){
			if($type == 0){
				$val = $key;
			}

			if($type == 1){
				$return[$val] = $key;
			}
			$type == 0 ? $type = 1 : $type = 0;
		}

		return $return;
	}

	/**
	 * [sendQuery Queryを送信します。]
	 * @param  [type] $host [description]
	 * @param  [type] $port [description]
	 * @return [type]       [description]
	 */
	public function sendQuery(string $host, int $port){
		$this->result = $this->UT3QueryServer($host, $port);
		$this->message = json_encode($result, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}
}
