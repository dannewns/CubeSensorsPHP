<?php 

namespace Jump24\CubeSensors;

use Jump24\CubeSensors\Cube;
use Jump24\CubeSensors\Validation;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;

class CubeSensorsDevice  {

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
 	
 		$this->validation = new \Jump24\CubeSensors\CubeSensorsValidation;
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
 	 * returns a list of devices that the user has allowed access to
 	 * @return array an array of Cube objects
 	 */
 	public function getDevices()
 	{

 		$devices =  $this->get('devices');

 		if (!is_null($devices)) {

 			$formatted_devices = array();

 			foreach ($devices['devices'] as $device) {

 				$formatted_devices[] = $this->formatDevice($device);
 			}
 	
 			return $formatted_devices;

 		} else

 			return NULL;

 	}

 	/**
 	 * return a individual device by device UID from the API
 	 * @param  string $device_id the device UID belonging to the device to look up
 	 * @return array            the device found
 	 */
 	public function getDevice($device_id = NULL)
 	{
 		if (is_null($device_id)) return NULL;

 		$device = $this->get('devices/' . $device_id);

 		if (!is_null($device)) {

 			unset($device['ok']);

 			return $this->formatDevice($device['device']);

 		} else

 			return NULL;

 	}

 	public function getHumidityReadsForDevice($device_id, $start_date = NULL, $end_date = NULL, $resolution = 60)
 	{
 		$reads = $this->getDeviceReads($device_id, $start_date, $end_date, $resolution);

 		if (is_null($reads)) {

 			return NULL;

 		} else {


 		}

 	}

 	/**
 	 * returns a list of device reads from the API
 	 * @param  string $device_id  the device id to query against
 	 * @param  string $start_date the start date to pull data from
 	 * @param  string $end_date   the end date to pull data to
 	 * @return array             returns a array of objects the keys being the date of the read
 	 */
 	public function getDeviceReads($device_id = NULL, $start_date = NULL, $end_date = NULL, $resolution = 60)
 	{
 		if (is_null($device_id)) return NULL;

 		if ($device = $this->getDevice($device_id)) {

	 		if (is_null($start_date) && is_null($end_date)) {

	 			$reads = $this->getDeviceCurrentReads($device_id);

	 		} else {

	 			$start_date = $this->convertToCarbon($start_date);

	 			$end_date = $this->convertToCarbon($end_date);

	 			$reads = $this->getDeviceReadsBetweenDates($device_id, $start_date, $end_date, $resolution);
	 		
	 		}

	 		if ($this->isReturnValid($reads)) {

	 			return $this->formatReadResults($reads, $device);

	 		} else

	 			return NULL;

	 	}

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

 			return Carbon::createFromFormat('Y-m-d', $date)->setTimezone('UTC');

 	}

 	/**
 	 * returns reads for a device by a start and end date supplied by the user
 	 * @param  string $device_id  the device id to get data for
 	 * @param  [type] $start_date [description]
 	 * @param  [type] $end_date   [description]
 	 * @param  [type] $resolution [description]
 	 * @return [type]             [description]
 	 */
 	protected function getDeviceReadsBetweenDates($device_id = NULL, Carbon $start_date, Carbon $end_date, $resolution)
 	{	

 		if (!$this->validation->validateStartDateIsInPast($start_date)) {

 			$this->error = $this->validation->getErrorMessage(); 

 		 	return NULL;

 		}

 		if (!$this->validation->validateDateDifferenceIsInBetweenApiLimit($start_date, $end_date) ){

			$this->error = $this->validation->getErrorMessage();

			return NULL;

		} 

 		if (!is_null($this->end_date))  {


 			if (!$this->validateIfEndDateIsBeforeStartDate($start_date, $end_date)) {

 				$this->error = $this->validation->getErrorMessage();

 		 		return NULL;

 			}

 		}

		$end_date_internal = $end_date->toISO8601String();

		$start_date_internal = $start_date->toISO8601String();

		$end_date_internal = str_replace('+0000', 'Z', $end_date_internal);

		$start_date_internal = str_replace('+0000', 'Z', $start_date_internal); 
 	
 		return  $this->get('devices/' . $device_id . '/span', ['start' => $start_date_internal, 'end' => $end_date_internal, 'resolution' => $resolution ] );

 	}


 	/**
 	 * formats the returned list of devices from the device call api into a more friendly result set
 	 * @param  array $devices the array returned from the original api/devices call
 	 * @return array          the formatted list of devices returned
 	 */
 	protected function formatDevice($device)
 	{

 		$cube = new Cube;

 		$cube->uid = $device['uid'];

 		$cube->name = $device['extra']['name'];

 		$cube->roomtype = $device['extra']['roomtype'];

 		return $cube;

 	}

 	/**
 	 * formats the devices results
 	 * @param  array $results the results from the devices read
 	 * @return array          The formatted results for the device
 	 */
 	protected function formatReadResults($results, Cube $device)
 	{
 		$field_list = $results['field_list'];

 		$formatted_devices = array();

 		if(is_array($results['results'])) {

 			foreach ($results['results'] as $result) {

 				$cloned_device = clone $device;

 				foreach ($result as $id => $value){

 					if (isset($field_list[$id]) && $field_list[$id] == 'time') {

 						$time = Carbon::parse($value);

 						$date_in_loop = $time->format('Y-m-d');

 						$cloned_device->$field_list[$id] = $time;

 					} else 

 						$cloned_device->$field_list[$id] = $value;
 				
 				}

 				$formatted_devices[] = $cloned_device;

 				$cloned_device = NULL;

 			}

 		}

 		return $formatted_devices;

 	}

 	/**
 	 * returns the current reads for the device
 	 * @param  [type] $device_id [description]
 	 * @return [type]            [description]
 	 */
 	private function getDeviceCurrentReads($device_id)
 	{
 		return $this->get('devices/' . $device_id . '/current', array(), true);
 	}

 	/**
 	 * checks the returned value for the ok field to see if response was returned correctly
 	 * @param  array  $return the returned response from a API call
 	 * @return boolean         [description]
 	 */
 	private function isReturnValid($return)
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
 	private function get($url, $query_parameters = array(), $dump_data = false)
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

			//$this->setResponseValues($e->getResponse());
			
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