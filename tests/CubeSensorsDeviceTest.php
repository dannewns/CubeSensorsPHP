<?php

use Jump24\CubeSensors\CubeSensorsDevice;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use Carbon\Carbon;

class CubeSensorsDeviceTest extends PHPUnit_Framework_TestCase {

    public function setUp() 
    {

    }
    
    public function tearDown() 
    {

    }

    public function testGetInvalidDevice()
    {
    	$cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

    	$mock_response = new Response(404);

    	$mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
    	$mock_response->setBody($mockResponseBody);
    
   		$mock = new Mock([
		    $mock_response
		  ]);

   		$cube_device->setupMockDataForRequest($mock);

   		$device = $cube_device->getDevice('000D6F0004491253');

   		$this->assertNull($device);
    
    }

    public function testValidDeviceIsReturned()
    {
      $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

      $mock_response = new Response(200);

      $mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
      $mock_response->setBody($mockResponseBody);
    
      $mock = new Mock([
        $mock_response
      ]);

      $cube_device->setupMockDataForRequest($mock);

      $device = $cube_device->getDevice('000D6F0004491253');

      $this->assertInstanceOf('Jump24\CubeSensors\Cube', $device);
    
    }

    /**
     * test the results of a valid device to make sure the function returns the correct class type
     * @return [type] [description]
     */
    public function testCorrectClassIsReturnedWithValidDevice()
    {
    	
      $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

    	$mock_response = new Response(200);

    	$mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
    	$mock_response->setBody($mockResponseBody);
    
   		$mock = new Mock([
		    $mock_response
		]);

   		$cube_device->setupMockDataForRequest($mock);

   		$device = $cube_device->getDevice('000D6F000449125334');

   		$this->assertInstanceOf('Jump24\CubeSensors\Cube', $device);
    
    }

    /**
     * check to make sure that the contained device variables are returned correctly
     * @return [type] [description]
     */
    public function testValidDeviceContainsUIDValue()
    {

    	$cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

    	$mock_response = new Response(200);

    	$mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
    	$mock_response->setBody($mockResponseBody);
    
   		$mock = new Mock([
		    $mock_response
		]);

   		$cube_device->setupMockDataForRequest($mock);

   		$device = $cube_device->getDevice('000D6F0004491253');

    	$this->assertSame('000D6F0004491253', $device->uid);
   
    }

    /**	
     * checks to see if a roomtype attribute is present when getDevice is called
     * @return [type] [description]
     */
    public function testValidDeviceContainsRoomTypeValue()
    {

    	$cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

    	$mock_response = new Response(200);

    	$mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
    	$mock_response->setBody($mockResponseBody);
    
   		$mock = new Mock([
  		    $mock_response
  		]);

   		$cube_device->setupMockDataForRequest($mock);

   		$device = $cube_device->getDevice('000D6F0004491253');

    	$this->assertSame('work', $device->roomtype);
   
    }

    /**
     * checks to make sure that the name attribute is present when a correct device is returned
     * @return [type] [description]
     */
    public function testValidDeviceContainsNameValue()
    {

    	$cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

    	$mock_response = new Response(200);

    	$mockResponseBody = Stream::factory('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');
    
    	$mock_response->setBody($mockResponseBody);
    
   		$mock = new Mock([
		    $mock_response
		  ]);

   		$cube_device->setupMockDataForRequest($mock);

   		$device = $cube_device->getDevice('000D6F0004491253');

    	$this->assertSame('Front Room', $device->name);
   
    }

  
    /**
     * tests that the class handles a start date thats in the future and that it returns a NULL and that a error message is set letting the user know whats happened
     * @return [type] [description]
     */
    public function testStartDateInTheFutureForDeviceReadsReturnsNullAndError()
    {
     
      // $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

      // $mock_response = $mock_response_second = $mock_response_third =  new Response(200);

      // $mockResponseBody = Stream::factory(fopen(__DIR__ . '/files/single_device_returned.json', 'r+'));

      // $mock_response->setBody($mockResponseBody);

      // $mock_response_second->setBody($mockResponseBody);

      // $mock_response_third->setBody($mockResponseBody);
    
      // $mock = new Mock([
      //   $mock_response,
      //   $mock_response_second,
      //   $mock_response_third
      // ]);

      // $cube_device->setupMockDataForRequest($mock);

      // $tomorrow = Carbon::now()->addDay(1);

      // $device = $cube_device->getDeviceReads('000D6F0004491253', $tomorrow->format('Y-m-d'));

      // $this->assertNull($device);

      // $this->assertSame('The start date you provided is in the future', $cube_device->getErrorMessage());
   
    }



    
 
}
