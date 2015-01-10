<?php

use Jump24\CubeSensors\CubeSensorsDevice;
use \Mockery;

class CubeSensorsDeviceTest extends PHPUnit_Framework_TestCase {

    public function setUp() 
    {

    }
    
    public function tearDown() 
    {
    	Mockery::close();
    }
    
   	public function testGetDevices()
   	{


   		$devices_class = Mockery::mock('CubeSensorsDevice', array(1,2,3,4))->makePartial();

   		$devices_class->shouldReceive('get')->andReturn(array( 'ok' => 1, 
   							'devices' => array ( 0 => array 
   												( 	'type' => 'cube',
   												 	'uid' => '000D6F0004491253',
   												 	'extra' => array ( 'roomtype' => 'work', 'name' => 'Front Room' ) 
   												),
   												1 => array ( 'type' => 'cube', 'uid' => '000D6F0003E5385E',
   													'extra' => array ( 'roomtype' => 'sleep', 'name' => 'Bedroom' ) 
   												) 
   												) 
   						));
   		
   		$devices  = Mockery::mock('CubeSensorsDevice', array(1,2,3,4))->makePartial();

   		$devices_class->shouldReceive('get')->andReturn(array( 'ok' => 1, 
   							'devices' => array ( 0 => array 
   												( 	'type' => 'cube',
   												 	'uid' => '000D6F0004491253',
   												 	'extra' => array ( 'roomtype' => 'work', 'name' => 'Front Room' ) 
   												),
   												1 => array ( 'type' => 'cube', 'uid' => '000D6F0003E5385E',
   													'extra' => array ( 'roomtype' => 'sleep', 'name' => 'Bedroom' ) 
   												) 
   												) 
   						));

   		$this->assertNotCount(0, $devices->getDevices());

   	//	$this->assertSame($devices_class, $devices->getDevices());

   	}

}
