<?php

class QueryException extends Exception {
	public function __construct($mes){
		parent::__construct($mes);
	}
}