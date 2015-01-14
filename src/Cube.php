<?php 

namespace Jump24\CubeSensors;

class Cube  {

	/**
	 * the universial unique identifier
	 * @var string
	 */
	public $uid;

	/**
	 * the name given to the cube
	 * @var string
	 */
	public $name;

	/**
	 * the type of cube at the momement there is only a single type
	 * @var string
	 */
	public $type;

	/**
	 * the type of room the cube has been assigned
	 * @var [type]
	 */
	public $roomtype;

	/**	
	 * the time of the reading
	 * @var [type]
	 */
	public $time;

	/**
	 * Temperature in °C * 100 (example: 2130 means 21.30°C)
	 * @var [type]
	 */
 	public $temp; 

 	/**
 	 * barometric pressure in mbar
 	 * @var [type]
 	 */
 	public $pressure;

 	/**
 	 * relative humidity in % 
 	 * @var [type]
 	 */
 	public $humidity;

 	/**
 	 * Amount of VOC gases in the air ppm
 	 * @var [type]
 	 */
 	public $voc;

 	/**
 	 * Measured in Lux
 	 * @var [type]
 	 */
 	public $light;

 	/**
 	 * Measured in RMS
 	 * @var [type]
 	 */
 	public $noise;

 	/**
 	 * Measured in RMS
 	 * @var [type]
 	 */
 	public $noisedb;

 	/**
 	 * Fullness of the battery in %
 	 * @var [type]
 	 */
 	public $battery;

 	/**
 	 * Indicates if the cube has recently been shaken or not
 	 * @var boolean
 	 */
 	public $shake;

 	/**
 	 * Indicates if the cube is currently charging
 	 * @var boolean
 	 */
 	public $cable;

 	/**
 	 * Raw resistance value from the VOC sensor
 	 * @var [type]
 	 */
 	public $voc_resistance;

 	/**
 	 * Wireless signal strength the higher the number(closer to 0) the stronger the signal
 	 * @var [type]
 	 */
 	public $rssi;

}