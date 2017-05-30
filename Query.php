<?php
/**
 * 簡単に使い回しが可能なQueryインスタンスを生成する
 * QueryManagerです！
 * 商用利用可能です。もしよければサイトなどに
 * 私の名前やこのライブラリの名前を載せていただけたら光栄です。
 *
 * @author tomotomo or tomocrafter
 * @version 1.0.0 [OpenSource]
 */

include "QueryException.php";

class Query{

	private $message = "";
	private $result = [];
	private $socket;

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

	/**
	 * [getConnection socketを返します。]
	 * @return [type] [description]
	 */
	public function getConnection(){
		return $this->socket;
	}

	/**
	 * [isConnecting description]
	 * @return boolean [接続されているかを返します]
	 */
	public function isConnecting() : bool{
		return $this->socket !== false;
	}

	/**
	 * [sendQuery Queryを送信します。]
	 * @return [type]       [description]
	 */
	public function sendQuery(){
		$this->socket = @fsockopen("udp://" . $this->getHost(), $this->getPort());
		if(!$this->socket){
			throw new QueryException("Host or Port is Invalid!!");
		}
		@socket_set_timeout($this->socket, 0, PHP_INT_MAX);
		if(!@fwrite($this->socket, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01")){
			throw new QueryException("fwrite error.");
			return "fwrite error.";
		}
		$challenge = fread($this->socket, 1400);
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
		if(!@fwrite($this->socket, $query)){
			throw new QueryException("fwrite error.");
		}
		$response = [];
		for($i = 0; $i < 2; $i++){
			$response[] = @fread($this->socket, 2048);
		}

		$response = explode("\0", substr(implode($response), 16));

		array_pop($response);
		array_pop($response);
		array_pop($response);
		array_pop($response);
		array_pop($response);

		$this->result = [];
		$type = 0;
		foreach($response as $key){
			if($type == 0){
				$val = $key;
			}

			if($type == 1){
				$this->result[$val] = $key;
			}
			$type == 0 ? $type = 1 : $type = 0;
		}
		$this->message = json_encode($this->getResult(), JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
	}
}
