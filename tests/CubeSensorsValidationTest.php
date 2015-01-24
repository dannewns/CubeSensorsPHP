<?php

use Jump24\CubeSensors\CubeSensorsValidation;
use Carbon\Carbon;

class CubeSensorsValidationTest extends PHPUnit_Framework_TestCase {

    public function setUp() 
    {

    }
    
    public function tearDown() 
    {
   
    }

    /**
     * test that the start date in past validation works correctly and errors out
     * @return [type] [description]
     */
    public function testValidateStartDateIsInThePastMethodWithFutureDate()
    {
    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now()->addDay(1);

    	$result = $validation->validateStartDateIsInPast($start_date);

      	$this->assertFalse($result);

      	$this->assertSame('The start date you provided is in the future', $validation->getErrorMessage());
   
    }

    public function testValidateStartDateIsInThePastMethodWithPastDate()
    {
    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now()->subHours(3);

    	$result = $validation->validateStartDateIsInPast($start_date);

      	$this->assertTrue($result);

      	$this->assertNull($validation->getErrorMessage());

    }

   
     /**
     * tests to see that when a date range that is out of the API limit is passed into the system that the class handles and returns null and sets a error message correctly
     * @return [type] [description]
     */
    public function testInvalidAPIDateRangeValidationHandlingAndErrorSet()
    {

    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->subDays(3);

    	$result = $validation->validateDateDifferenceIsInBetweenApiLimit($start_date, $end_date);

      	$this->assertFalse($result);

      	$this->assertSame('There was more than 2 days between the start and end date The API currently only supports a 2 day limit', $validation->getErrorMessage());
   
    }

    /**
     * should return true when a valid date range is passed into the validation method with no error message set
     * @return [type] [description]
     */
    public function textValidAPIDateRangeValidation()
    {
    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->subDays(2);

    	$result = $validation->validateDateDifferenceIsInBetweenApiLimit($start_date, $end_date);

      	$this->assertTrue($result);

      	$this->asserNull($validation->getErrorMessage());
   
    }

    /**
     * test the validation system for a end data before the start date
     * @return [type] [description]
     */
    public function testValidateIfEndDateIsBeforeStartDateWithEndDateBeforeStart()
    {
    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->subDay();

    	$result = $validation->validateIfEndDateIsBeforeStartDate($start_date, $end_date);

      	$this->assertFalse($result);

      	$this->assertSame('The end date you supplied is before the start date you supplied', $validation->getErrorMessage());
   
    }

    public function textValidateIfEndDateIsBeforeStartDateWithValidDates()
    {
    	$validation = new CubeSensorsValidation;

    	$start_date = Carbon::now();

    	$end_date = Carbon::now()->addDay();

    	$result = $validation->validateIfEndDateIsBeforeStartDate($start_date, $end_date);

      	$this->assertTrue($result);

      	$this->assertNull( $validation->getErrorMessage());
    
    }


    /**
     * tests that the class can handle when a user puts a end date thats before a start date and returns NULL and sets a error message
     * @return [type] [description]
     */
    public function testEndDateBeforeStartDateError()
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

        // $today = Carbon::now();

        // $yesterday = Carbon::now()->subDays(3);

        // $device = $cube_device->getDeviceReads('000D6F0004491253', $today->format('Y-m-d'), $yesterday->format('Y-m-d'));

        // $this->assertNull($device);

        // $this->assertSame('The end date you supplied is before the start date you supplied', $cube_device->getErrorMessage());
    
    }


    public function testStartDateInIncorrectFormat()
    {
       // $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

       //  $mock_response = $mock_response_second = $mock_response_third =  new Response(200);

       //  $mockResponseBody = Stream::factory(fopen(__DIR__ . '/files/single_device_returned.json', 'r+'));

       //  $mock_response->setBody($mockResponseBody);

       //  $mock_response_second->setBody($mockResponseBody);

       //  $mock_response_third->setBody($mockResponseBody);
      
       //  $mock = new Mock([
       //    $mock_response,
       //    $mock_response_second,
       //    $mock_response_third
       //  ]);

       //  $cube_device->setupMockDataForRequest($mock);

       //  $today = Carbon::now();

       //  $device = $cube_device->getDeviceReads('000D6F0004491253', $today->format('Y.m.d'));

       //  $this->assertNull($device);

       //  $this->assertSame('The end date you supplied is before the start date you supplied', $cube_device->getErrorMessage());
    
    }
    
 
}
