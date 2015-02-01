<?php

require 'config.php';

require '../vendor/autoload.php';

use Jump24\CubeSensors\CubeSensorsDevice;

$cube_device = new CubeSensorsDevice(CONSUMER_KEY, CONSUMER_SECRET, $_SESSION['token'], $_SESSION['secret']);

$devices = $cube_device->getDevices();

$device = $cube_device->getDevice($example_device_id);

$reads = $cube_device->getDeviceReads($example_device_id);

