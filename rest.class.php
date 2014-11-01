<?php
/* 
:: Some basic REST
:: Free As in Beer 
:: https://github.com/j0nix/j0nix-rest-api
*/
class REST {
	
	private $_content_type = "application/json; charset=utf-8";
	protected $_prettyprint = false; //true = pretty JSON printing
	protected $_request = array();
	protected $_method = "";	
	private $_code = 200;

	//Constructor secure & clean input before further processing 
	protected function __construct(){
		$this->inputs();
	}
	//respond result
	protected function response($data,$status){
		$this->_code = ($status)?$status:200;
		$this->set_headers();
		echo $data;
		exit;
	}
	//translate respond header numbers to text
	private function get_status_message(){
		$status = array(
				100 => 'Continue',  
				101 => 'Switching Protocols',  
				200 => 'OK',
				201 => 'Created',  
				202 => 'Accepted',  
				203 => 'Non-Authoritative Information',  
				204 => 'No Content',  
				205 => 'Reset Content',  
				206 => 'Partial Content',  
				300 => 'Multiple Choices',  
				301 => 'Moved Permanently',  
				302 => 'Found',  
				303 => 'See Other',  
				304 => 'Not Modified',  
				305 => 'Use Proxy',  
				306 => '(Unused)',  
				307 => 'Temporary Redirect',  
				400 => 'Bad Request',  
				401 => 'Unauthorized',  
				402 => 'Payment Required',  
				403 => 'Forbidden',  
				404 => 'Not Found',  
				405 => 'Method Not Allowed',  
				406 => 'Not Acceptable',  
				407 => 'Proxy Authentication Required',  
				408 => 'Request Timeout',  
				409 => 'Conflict',  
				410 => 'Gone',  
				411 => 'Length Required',  
				412 => 'Precondition Failed',  
				413 => 'Request Entity Too Large',  
				414 => 'Request-URI Too Long',  
				415 => 'Unsupported Media Type',  
				416 => 'Requested Range Not Satisfiable',  
				417 => 'Expectation Failed',  
				500 => 'Internal Server Error',  
				501 => 'Not Implemented',  
				502 => 'Bad Gateway',  
				503 => 'Service Unavailable',  
				504 => 'Gateway Timeout',  
				505 => 'HTTP Version Not Supported');
		return ($status[$this->_code]) ? $status[$this->_code] : $status[500];
	}

	private function get_request_method(){
		return $_SERVER['REQUEST_METHOD']; 
		//Identify request method. POST,GET,PUT or DELETE
	}

	// Identify request method, Common for REST API are GET,POST,PUT,DELETE. 
	private function inputs(){
		switch($this->get_request_method()){ // Make switch case so we can add additional 
			case "GET":
				$this->_request = $this->cleanInputs($_REQUEST); //or $_GET
				$this->_method = "GET";
				break;
			case "POST":
				$this->_request = $this->cleanInputs($_REQUEST); //or $_GET
				$this->_method = "POST";
				//$this->logRequest();
				break;
			case "PUT":
				$this->_request = $this->cleanInputs($_REQUEST); //or $_GET
				$this->_method = "PUT";
				//$this->logRequest();
				break;
			case "DELETE":
				$this->_request = $this->cleanInputs($_REQUEST); //or $_GET
				$this->_method = "DELETE";
				//$this->logRequest();
				break;
			default:
				$this->response('Forbidden',403);
				break;
		}
	}
		
	//Clean shit from request
	private function cleanInputs($data){
		$clean_input = array();
		if(is_array($data)){
			foreach($data as $k => $v){
				$clean_input[$k] = $this->cleanInputs($v);
			}
		}else{
			if(get_magic_quotes_gpc()){ 
			// Returns 0 if magic_quotes_gpc is off, 
			// 1 otherwise. Always returns FALSE as of PHP 5.4.0.
				$data = trim(stripslashes($data));
			}
			$data = strip_tags($data);
			$clean_input = trim($data);
		}
		return $clean_input;
	}		

	private function set_headers() {
		header("HTTP/1.1 ".$this->_code." ".$this->get_status_message());
		header("Content-Type:".$this->_content_type);
	}
	
	// As of php 5.4+ you could use JSON_PRETTY_PRINT option with json_encode() insead of below code...
	private function json_pretty($json) { 

		$tokens = preg_split('|([\{\}\]\[,])|', $json, -1, PREG_SPLIT_DELIM_CAPTURE);
		$result = '';
		$indent = 0;
		$lineBreak = "\n";
		$ind = "    ";
		$inLiteral = false;
		foreach ($tokens as $token) {
			if ($token == '') {
				continue;
			}
			$prefix = str_repeat($ind, $indent);
			if (!$inLiteral && ($token == '{' || $token == '[')) {
				$indent++;
				if (($result != '') && ($result[(strlen($result) - 1)] == $lineBreak)) {
					$result .= $prefix;
				}
				$result .= $token . $lineBreak;
			} elseif (!$inLiteral && ($token == '}' || $token == ']')) {
				$indent--;
				$prefix = str_repeat($ind, $indent);
				$result .= $lineBreak . $prefix . $token;
			} elseif (!$inLiteral && $token == ',') {
				$result .= $token . $lineBreak;
			} else {
				$result .= ( $inLiteral ? '' : $prefix ) . $token;
				if ((substr_count($token, "\"") - substr_count($token, "\\\"")) % 2 != 0) {
					$inLiteral = !$inLiteral;
				}
			}
		}
		return $result;
	}

	protected function json($data){
		if(is_array($data)){
			if($this->_prettyprint === false) return json_encode($data);
			else return $this->json_pretty(json_encode($data));
		}
	}

	private function logRequest() { //Do some simple syslog for requests
		//Uncomment or set 'error_log = syslog' to log to system default syslog location
		openlog(basename(__FILE__), LOG_NDELAY, LOG_LOCAL0);
		syslog(LOG_NOTICE, get_class($this)." ".$_SERVER['REMOTE_ADDR']." did $this->_method with request: ".$this->_request['rquest']."\n");
		closelog;
	}
}
?>
