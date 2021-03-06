<?php
/*
   :: REST API
   :: Free As in Beer 
   :: https://github.com/j0nix/j0nix-rest-api

   Requirements:
   1.	Enable mod_rewrite
   2.	Set  "AllowOverride All" in host/vhost conf
   3.	Create a .htaccess file with below content and place in folder where this file exists
   4.	j0nix-rest-api/rest.class.php

   RewriteEngine On

   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteCond %{REQUEST_FILENAME} !-s
   RewriteRule ^(.*)$ api.php?rquest=$1 [QSA,NC,L]

   RewriteCond %{REQUEST_FILENAME} -d
   RewriteRule ^(.*)$ api.php [QSA,NC,L]

   RewriteCond %{REQUEST_FILENAME} -s
   RewriteRule ^(.*)$ api.php [QSA,NC,L]	

 */
error_reporting(0);

require_once("rest.class.php"); // or use autoloader

class API extends REST {

	/*
	Your API KEY for simple security and have little overhead in 
	access validation. But some argue that api-key in url is very 
	weak security, if any at all. Is security important you should
	also include some authentication...
	*/

	private $apiKey = array(
			"apiKey" => array(
				"EXAMPLE" //Functions allowed by this key
			),
			"apiKey2" => array(
				"EXAMPLE",
				"EXAMPLE2",
			)
	); 

	public function __construct(){
		parent::__construct();
	}

	public function processRequest(){
		//PrettyPrint?	
		if(isset($this->_request['prettyprint'])) {
			$this->_prettyPrint = true;
		}
		/* 
			Request: http://your-url.com/API-KEY/FUNCTION/PARAM[/MOREPARAMS][?prettyprint] 
			BELOW will split and evaluate above request. If function exists it will be called using 
			your parameter.  IF sending more than one parameter in your request, 
			$request[2] will contain a none split parameter value like => PARAM/PARAM2 when
			making the call to your function. USE $this->_method to identify request method.
		 */
		if(!empty($this->_request['rquest'])) {

			// split into 0=>API-KEY, 1=>FUNCTION, 2=>PARAM
			$request = explode("/",$this->_request['rquest'],3); 

			//Verify number of parameters are 3 & that param 3 is not a empty param
			if(count($request) == 3 && !empty($request[2])) { 

				//Correct api-key?
				if(array_key_exists($request[0], $this->apiKey)) {

					//Does your called function exist?
					if((int)method_exists($this,$request[1]) > 0) {

						//Are you allowed to use function with this key
						if (in_array($request[1],$this->apiKey[$request[0]])) {

							//Make call to function
							$this->$request[1]($request[2]); 

						} else $this->response('FORBIDDEN: YOUR NOT ALLOWED TO ACCESS THIS FUNCTION',403);
					} else $this->response('NOT FOUND: API FUNCTION NOT FOUND',404);
				} else $this->response('FORBIDDEN: INVALID API KEY',403);
			} else $this->response('BAD REQUEST: NOT A VALID REQUEST',400);
		} else $this->response("EMPTY PAGE",200);
	}

	private function EXAMPLE($param) {
		//split parameter into an array
		$param = explode("/", $param); 

		switch($this->_method) {
			case "POST":
				if(count($param) < 2) {
					$this->response($this->json(array("ERROR" => "missing param"),200));
				} else {
					$this->response($this->json(
								array( "METADATA" => 
									array(	"REQUEST" => $this->_method, 
										"PARAM" => implode("/",$param), 
										"FUNCTION" => __FUNCTION__), 
									"DATA" => 
									array(	"PARAM" => $param)
								     )),200);
				}
				break;
			case "PUT":
				if(count($param) < 2) {
					$this->response($this->json(array("ERROR" => "missing param"),200));
				} else {
					$this->response($this->json(
								array( "METADATA" => 
									array(	"REQUEST" => $this->_method, 
										"PARAM" => implode("/",$param), 
										"FUNCTION" => __FUNCTION__), 
									"DATA" => 
									array(	"PARAM" => $param)
								     )),200);
				}
				break;
			case "DELETE":
				$this->response($this->json(
							array( "METADATA" => 
								array(	"REQUEST" => $this->_method, 
									"PARAM" => implode("/",$param), 
									"FUNCTION" => __FUNCTION__), 
								"DATA" => 
								array(	"PARAM" => $param)
							     )),200);
				break;
			default:
				$this->response($this->json(
						array( "METADATA" => 
							array(	"REQUEST" => $this->_method, 
								"PARAM" => implode("/",$param), 
								"FUNCTION" => __FUNCTION__), 
							"DATA" => 
							array(	"PARAM" => $param)
						     )),200);
				break;
		}
		//$this->response(JSON_DATA,HTTP_STATUS);
	}

	//Just as example for access restriction given in $apiKey
	private function EXAMPLE2($param) { 

		$this->response($this->json(
					array( "METADATA" => 
						array(	
							"REQUEST" => $this->_method, 
							"PARAM" => $param, 
							"FUNCTION" => __FUNCTION__
						     ), 
						"DATA" => "I HAVE ACCESS"
					     )),200);
	}
}

//Start processing your http REST request
$api = new API; 
$api->processRequest();			
?>
