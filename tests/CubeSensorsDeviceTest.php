<?php

use Jump24\CubeSensors\CubeSensorsDevice;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use Carbon\Carbon;

/**
 * Class CubeSensorsDeviceTest
 * @coversDefaultClass Jump24\CubeSensors\CubeSensorsDevice
 * @group CubeSensorsDevice
 */
class CubeSensorsDeviceTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }


    /**
     * tests the getDevice method with a invalid device id to make sure the result returned is correct
     * @test
     * @covers ::getDevice
     * @group getDevice
     */
    public function testGetInvalidDevice()
    {
        $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

        $mock_response = new Response(404);

        $mockResponseBody = Psr7\stream_for('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": false}');

        $mock_response = $mock_response->withBody($mockResponseBody);

        $mock = new MockHandler([ $mock_response ]);

        $cube_device->setupMockDataForRequest($mock);

        $device = $cube_device->getDevice('000D6F0004491253');

        $this->assertNull($device);

    }

    /**
     * test that a valid device is returned with the correct data
     * @test
     * @covers ::getDevice
     * @group getDevice
     */
    public function testValidDeviceIsReturned()
    {
        $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

        $mock_response = new Response(200);

        $mockResponseBody = Psr7\stream_for('{"device": {"type": "cube", "uid": "000D6F0004491253", "extra": {"roomtype": "work", "name": "Front Room"}}, "ok": true}');

        $mock_response = $mock_response->withBody($mockResponseBody);

        $mock = new MockHandler([
            $mock_response
        ]);

        $cube_device->setupMockDataForRequest($mock);

        $device = $cube_device->getDevice('000D6F0004491253');

        $this->assertArrayHasKey('uid', $device);

        $this->assertArrayHasKey('roomtype', $device);

        $this->assertArrayHasKey('name', $device);

    }

    /**
     * tests that the class handles a start date thats in the future and that it returns a NULL
     * and that a error message is set letting
     * the user know whats happened
     * @test
     * @covers ::getDeviceReadsForDate
     * @group getDeviceReadsForDate
     *
     *
     */
    public function testGetDeviceReadsForDateUsingDateInTheFuture()
    {
        $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

        $mock_response = $mock_response_second = $mock_response_third =  new Response(200);

        $mockResponseBody = Psr7\stream_for(fopen(__DIR__ . '/files/single_device_read.json', 'r+'));

        $mock_response->withBody($mockResponseBody);

        $mock_response_second->withBody($mockResponseBody);

        $mock_response_third->withBody($mockResponseBody);

        $mock = new MockHandler([
            $mock_response,
            $mock_response_second,
            $mock_response_third
        ]);

        $cube_device->setupMockDataForRequest($mock);

        $tomorrow = Carbon::now()->addDay(1);

        $device = $cube_device->getDeviceReadsForDate('000D6F0004491253', $tomorrow->format('Y-m-d'));

        $this->assertNull($device);

        $this->assertSame('The date you provided is in the future', $cube_device->getErrorMessage());

    }

    /**
     * tests that the correct error is returned when a date is passed in thats
     * older than 24 hours from the current date as thats all the api
     * can currently handle
     * @test
     * @covers ::getDeviceReadsForDate
     * @group getDeviceReadsForDate
     */
    public function testGetDeviceReadsForDateOutsideOfApiHistoryLimit()
    {
        $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

        $mock_response = $mock_response_second = $mock_response_third =  new Response(200);

        $mockResponseBody = Psr7\stream_for(fopen(__DIR__ . '/files/single_device_read.json', 'r+'));

        $mock_response = $mock_response->withBody($mockResponseBody);

        $mock_response_second = $mock_response_second->withBody($mockResponseBody);

        $mock_response_third = $mock_response_third->withBody($mockResponseBody);

        $mock = new MockHandler([
            $mock_response,
            $mock_response_second,
            $mock_response_third
        ]);

        $cube_device->setupMockDataForRequest($mock);

        $tomorrow = Carbon::now()->subDays(3);

        $device = $cube_device->getDeviceReadsForDate('000D6F0004491253', $tomorrow->format('Y-m-d'));

        $this->assertNull($device);

        $this->assertSame('The date you are trying to use is past the 48 hour API History limit', $cube_device->getErrorMessage());

    }

    /**
     * tests getDeviceRead method with a valid device and a valid read
     * @test
     * @covers ::getDeviceReadsForDate
     * @group getDeviceReadsForDate
     * @group testTest
     */
    public function testGetDeviceReadsForValidDate()
    {
        $cube_device = new CubeSensorsDevice('tester1', 'tester2', 'token_tester', 'secret_tester');

        $mock_response_second = new Response(200);

        $mockResponseBodyTwo = Psr7\stream_for(file_get_contents(__DIR__ . '/files/single_device_read.json'));

        $mock_response = $mock_response_second->withBody($mockResponseBodyTwo);

        $mock = new MockHandler([
            $mock_response
        ]);

        $cube_device->setupMockDataForRequest($mock);

        $yesterday = Carbon::now()->subDays(1);

        $reads = $cube_device->getDeviceReadsForDate('000D6F0004491253', $yesterday->format('Y-m-d'));

        $this->assertArrayHasKey('reads', $reads);
    }
}
