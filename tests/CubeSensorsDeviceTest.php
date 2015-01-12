<?php

use Jump24\CubeSensors\CubeSensorsDevice;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

class CubeSensorsDeviceTest extends PHPUnit_Framework_TestCase {

    public function setUp() 
    {

    }
    
    public function tearDown() 
    {
    	Mockery::close();
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

   		$device = $cube_device->getDevice('000D6F000449125334');

    	$this->assertClassHasAttribute('uid', get_class($device));
   
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

   		$device = $cube_device->getDevice('000D6F000449125334');

    	$this->assertClassHasAttribute('roomtype', get_class($device));
   
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

   		$device = $cube_device->getDevice('000D6F000449125334');

    	$this->assertClassHasAttribute('name', get_class($device));
   
    }
    
    
 
}
