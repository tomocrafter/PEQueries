<?php
/**
 * 簡単に使い回しが可能なQueryインスタンスを生成する
 * QueryManagerです！
 * 商用利用可能です。もしよければサイトなどに
 * 私の名前やこのライブラリの名前を載せていただけたら光栄です。
 *
 * @author tomotomo or tomocrafter <tomoppeko@gmail.com>
 * @version 1.1.0 [OpenSource]
 */

class Query{

	private $socket;
	private $host;
	private $port;

	/**
	 * [__construct description]
	 * @param string $host [description]
	 * @param int    $port [description]
	 */
	public function __construct(string $host, int $port){
		$this->host = $host;
		$this->port = $port;

		$this->socket = @fsockopen('udp://' . $this->host, $this->port);
		if(!$this->socket){
			throw new QueryException('Host or Port is Invalid!!');
			$this->closeConnection();
		}
	}

	public function connect(){
		if($this->isConnecting()){
			$this->closeConnection();
		}
		$this->socket = @fsockopen('udp://' . $this->host, $this->port);
		if(!$this->socket){
			throw new QueryException('Host or Port is Invalid!!');
			$this->closeConnection();
		}
	}

	/**
	 * ホストを変更します。
	 * @param string $host [変更するホスト]
	 * @return this [このインスタンス]
	 */
	public function setHost(string $host){
		$this->host = $host;
		$this->connect();
		return $this;
	}

	/**
	 * ポートを変更します。
	 * @param int $port [変更するポート]
	 * @return this [このインスタンス]
	 */
	public function setPort(int $port){
		$this->port = $port;
		$this->connect();
		return $this;
	}

	/**
	 * 現在のホストを返します。
	 * @return string [現在のホスト]
	 */
	public function getHost() : string{
		return $this->host;
	}

	/**
	 * 現在のポートを返します。
	 * @return int [現在のポート]
	 */
	public function getPort() : int{
		return $this->port;
	}

	/**
	 * socketを返します。
	 * @return [type] [description]
	 */
	public function getConnection(){
		return $this->socket;//接続されていなかったらnull
	}

	/**
	 * Alias of closeConnection
	 */
	public function stopConnect(){ $this->closeConnection(); }

	/**
	 * socketを閉じます。
	 */
	public function closeConnection(){
		fclose($this->socket);
		$this->socket = null;
	}

	/**
	 * 接続されているかを返します。
	 * @return boolean [接続されているか]
	 */
	public function isConnecting(): bool{
		return $this->socket !== false && is_resource($this->socket);
	}

	/**
	 * Queryを送信します。
	 * @return [type]       [description]
	 */
	public function sendQuery(): Array{
		@socket_set_timeout($this->socket, 5);//5秒
		if(!@fwrite($this->socket, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01")){
			throw new QueryException('Failed to write a command.');
		}
		$challenge = fread($this->socket, 1400);
		if(!$challenge){
			throw new QueryException('Could not read a response.');
		}

		$challenge = substr(preg_replace('/[^0-9\-]/si',  '',  $challenge), 1);
		$query = sprintf("\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01",
			($challenge >> 24),
			($challenge >> 16),
			($challenge >> 8),
			($challenge >> 0)
		);
		if(!@fwrite($this->socket, $query)){
			throw new QueryException('Failed to write a command.');
		}
		$response = [];
		for($i = 0; $i < 2; $i++){
			$response[] = @fread($this->socket, 2048);
		}

		$response = explode("\0", substr(implode($response), 16));

		array_pop($response);
		array_pop($response);

		$result = [];
		$flag = false;
		$playerflag = false;
		foreach($response as $val){
			if($val === "") continue;
			if($val === json_decode('"\u0001"').'player_'){
				$playerflag = true;
				continue;
			}elseif($playerflag && $val !== ""){
				$result['players'][] = $val;
				continue;
			}

			if($flag === false){
				$key = $val;
			}
			if($flag === true){
				if($key === 'version'){
					$array = explode(',', $val);
					if(count($array) === 1){
						$result['version'] = ltrim($val, 'v');
					}else{
						foreach ($array as $key => $value) {
							$result['versions'][] = ltrim($value, 'v');
						}
					}
				}elseif($key === 'whitelist'){
					if($val === 'off'){
						$result['whitelist'] = false;
					}else{
						$result['whitelist'] = true;
					}
				}elseif($key === 'plugins'){
					$engine = $result['server_engine'] ?? '';
					$trimed = ltrim($val, $engine.': ');
					$plugins = explode('; ', $trimed);
					foreach ($plugins as $value) {
						$array = explode(' ', $value);
						$result['plugins'][$array[0]] = $array[1];
					}
				}else{
					$result[$key] = $val;
				}
			}
			$flag = !$flag;
		}
		return $result;
	}
}

class QueryException extends Exception {}
