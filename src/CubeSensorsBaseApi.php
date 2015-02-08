<?php 

namespace Jump24\CubeSensors;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CubeSensorsBaseApi  {

  	protected $auth;

  	protected $route = 'http://api.cubesensors.com/';

  	protected $version = 'v1/';

  	protected $device_room_types = array('work', 'sleep', 'living');

  	protected $client;

  	protected $start_date;

  	protected $end_date;

  	private $mock = NULL;

  	private $error = NULL;

  	private $status_code = NULL;

  	private $called_url = NULL;

  	private $reason = NULL;

  	const API_DAY_DIFFERENCE_LIMIT = 2;

  	const START_DATE_IN_FUTURE_ERROR = 'The start date you provided is in the future';

  	const END_DATE_BEFORE_START_DATE_ERROR = 'The end date you supplied is before the start date you supplied';

  	private $api_range_limit_error = 'There was more than 2 days between the start and end date The API currently only supports a 2 day limit';

 	public function __construct($consumer_key, $consumer_secret, $access_token, $access_token_secrect)
 	{

 		$this->consumer_key = $consumer_key;

 		$this->consumer_secret = $consumer_secret;

 		$this->access_token = $access_token;

 		$this->access_token_secrect = $access_token_secrect;
 
 	}

 	/**
 	 * sets up the mock data for the get requests to allow for testing
 	 * @param  GuzzleHttp\Subscriber\Mock $mock_data the Mock object to pass into the system for testing
 	 * @return [type]            [description]
 	 */
 	public function setupMockDataForRequest(\GuzzleHttp\Subscriber\Mock $mock_data)
 	{
 		$this->mock = $mock_data;
 	}


 	/**
 	 * returns the error message on the system
 	 * @return [type] [description]
 	 */
 	public function getErrorMessage()
 	{
 		return $this->error;
 	}

 	/**
 	 * sets up the start and end date variables for the class to use and converts them to carbon format
 	 */
 	protected function convertToCarbon($date)
 	{

 		if (is_null($date)) {

 			return Carbon::now()->setTimezone('UTC');

 		} else 

 			return Carbon::createFromFormat('Y-m-d H:i:s', $date . ' 00:00:00')->addHour()->setTimezone('UTC');

 	}

 	/**
 	 * formats the returned list of devices from the device call api into a more friendly result set
 	 * @param  array $devices the array returned from the original api/devices call
 	 * @return array          the formatted list of devices returned
 	 */
 	protected function formatDevice($device)
 	{

 		$cude = array();

 		$cube['uid'] = $device['uid'];

 		$cube['name'] = $device['extra']['name'];

 		$cube['roomtype'] = $device['extra']['roomtype'];

 		return $cube;

 	}

 	/**
 	 * formats the devices results
 	 * @param  array $results the results from the devices read
 	 * @return array          The formatted results for the device
 	 */
 	protected function formatReadResults($results)
 	{

 		$field_list = $results['field_list'];

 		$formatted_devices = array();

 		if(is_array($results['results'])) {

 			foreach ($results['results'] as $result) {

 				$device_reads = array();

 				foreach ($result as $id => $value){

 					if (isset($field_list[$id]) && $field_list[$id] == 'time') {

 						$time = Carbon::parse($value);

 						$date_in_loop = $time->format('Y-m-d');

 						$device_reads[$field_list[$id]] = $time;

 					} else 

 						$device_reads[$field_list[$id]] = $value;
 				
 				}

 				$formatted_devices['reads'][] = $device_reads;

 			}

 		}

 		return $formatted_devices;

 	}

 	/**
 	 * checks the returned value for the ok field to see if response was returned correctly
 	 * @param  array  $return the returned response from a API call
 	 * @return boolean         [description]
 	 */
 	protected function isReturnValid($return)
 	{
 		if (is_null($return)) return FALSE;

 		if ($return['ok']) return TRUE;

 		else return FALSE;
 	}

 	/**
 	 * wrapper method to call the api by just suppling a api endpoint url 
 	 * @param  string 	$url the API endpoint to be called
 	 * @param  array 	$query_parameters the query string parameters to pass into the call
 	 * @return 			the result from the call be it the details for a cube or NULL when nothing found
 	 */
 	public function get($url, $query_parameters = array(), $dump_data = false)
 	{

 		try {	

 			$this->setupClient();

		    $request = $this->client->createRequest('GET', $url);

		    if (!empty($query_parameters)) {

		    	$query = $request->getQuery();

		    	foreach ($query_parameters as $field => $value) {

		    		$query->set($field, $value);

		    	}

		    }

		    $response = $this->client->send($request);

		    if ($response->getStatusCode() != 200)  {

		    	return NULL;
		    
		    }

		    if ($dump_data) {

		    	$body = $response->getBody();

		    	echo $body;

		    	die();
		    
		    }

		   	$body = $response->json();

		   	if ($this->isReturnValid($body)) {

		   		return $body;
		   	
		   	} else

		   		return NULL;
		
		} catch(ServerException $e) {

			$this->setResponseValues($e->getResponse());

			$this->error = $e->getMessage();

			return NULL;

		} catch(ClientException $e) {

			$this->error = $e->getMessage();

			$this->setResponseValues($e->getResponse());

			return NULL;

		} catch (RequestException $e) {

			$this->error = $e->getMessage();

			$this->setResponseValues($e->getResponse());
			
			return NULL;
		
		}

 	}

 	/**
 	 * sets up the error responses for the exceptions handled
 	 * @param GuzzleHttp\Message\Response $response [description]
 	 */
 	private function setResponseValues(\GuzzleHttp\Message\Response $response)
 	{

		$this->status_code = $response->getStatusCode();

		$this->reason = $response->getReasonPhrase();

		$this->called_url = $response->getEffectiveUrl();
 	
 	}

 	/**
 	 * sets up the guzzle client ready to use the api
 	 * @return [type] [description]
 	 */
 	private function setupClient()
 	{
 		
 		$oauth = new Oauth1([
			    'consumer_key'    => $this->consumer_key,
			    'consumer_secret' => $this->consumer_secret,
			    'token'           => $this->access_token,
			    'token_secret'    => $this->access_token_secrect
			]);

 		$this->client = new Client( array(	'base_url' => $this->route . $this->version, 
 											'defaults' => array('auth' => 'oauth') ) );


 		if (!is_null($this->mock)) {

 			$this->client->getEmitter()->attach($this->mock);
 			
 		} 

		$this->client->getEmitter()->attach($oauth);
 	}

}