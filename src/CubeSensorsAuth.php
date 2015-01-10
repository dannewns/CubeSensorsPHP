<?php 

namespace Jump24\CubeSensors;

use ohmy\Auth1;

class CubeSensorsAuth {

	protected $request_token_url 	= 'http://api.cubesensors.com/auth/request_token';
  
  	protected $authorize_url 		= 'http://api.cubesensors.com/auth/authorize';
  
  	protected $access_token_url 	= 'http://api.cubesensors.com/auth/access_token';

  	protected $auth;

 	public function __construct($consumer_key, $consumer_secret, $call_back_url)
 	{
 		
 		$this->auth = Auth1::init(3)
               ->set( 'consumer_key', $consumer_key)
               ->set( 'consumer_secret', $consumer_secret)
               ->set( 'callback', $call_back_url);
             
 	}

 	public function getAccessToken()
 	{
 		
 		return $this->auth->request($this->request_token_url)
               	->authorize($this->authorize_url)
               	->access($this->access_token_url);

 	}

}