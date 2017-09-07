<?php
class Response {
	public $Request;
	public $Status;
	public $Data;

	public function __construct($request) {
		$this->Request = $request;
	}

	public function Success($data) {
		$this->Status = 'ok';
		$this->Data = $data;
		
		header ('HTTP/1.0 200 OK');
		die( json_encode($this) );
	}
	
	public function Error($message, $code = 500) {
		$this->Status = 'err';
		$this->Data = $message;
		
		http_response_code($code);
		die( json_encode($this) );
	}
}