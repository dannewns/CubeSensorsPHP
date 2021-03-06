<?php

namespace Jump24\CubeSensors;

use Jump24\CubeSensors\Traits\ValidationTrait;

class CubeSensorsDevice extends CubeSensorsBaseApi
{
    use ValidationTrait;

    public function __construct(
        $consumer_key,
        $consumer_secret,
        $access_token,
        $access_token_secret
    ) {
        parent::__construct($consumer_key, $consumer_secret, $access_token, $access_token_secret);
    }

    /**
     * returns a list of devices that the user has allowed access to.
     *
     * @return array an array of Cube objects
     */
    public function getDevices()
    {
        $devices = $this->get('devices');

        if (!is_null($devices)) {
            $devices = collect($devices['devices']);

            $devices = $devices->map(function ($item) {
                return $this->formatDevice($item);
            });

            return $devices;
        } else {
            return;
        }
    }

    /**
     * return a individual device by device UID from the API.
     *
     * @param string $device_id the device UID belonging to the device to look up
     *
     * @return array the device found
     */
    public function getDevice($device_id = null)
    {
        if (is_null($device_id)) {
            return;
        }

        $device = $this->get('devices/'.$device_id);

        if (!is_null($device)) {
            unset($device['ok']);

            return $this->formatDevice($device['device']);
        } else {
            return;
        }
    }

    /**
     * returns the current reads for the device.
     *
     * @param string $device the device id to use
     *
     * @return [type] [description]
     */
    public function getDeviceCurrentReads($device_id)
    {
        if ($reads = $this->get('devices/'.$device_id.'/current', [])) {
            if ($this->isReturnValid($reads)) {
                return $this->formatReadResults($reads);
            }
        }

        return false;
    }

    /**
     * returns a list of device reads from the API.
     *
     * @param string $device_id the device id to query against
     * @param string $date      the start date to pull data from
     *
     * @return array returns a array of objects the keys being the date of the read
     */
    public function getDeviceReadsForDate($device_id, $date, $resolution = 60)
    {
        if (is_null($device_id)) {
            return;
        }

        if ($this->isDateInThePast($date) && $this->isDatePastApiHistoryLimit($date)) {
            $date = $this->convertDateToApiFormat($date);

            if ($reads = $this->get(
                'devices/'.$device_id.'/span',
                [
                    'start'      => $date,
                    'end'        => $date,
                    'resolution' => $resolution,
                ]
            )
            ) {
                if ($this->isReturnValid($reads)) {
                    return $this->formatReadResults($reads);
                }
            }

            return;
        }
    }

    /**
     * returns reads for a device by a start and end date supplied by the user.
     *
     * @param string $device_id  the device id to get data for
     * @param [type] $start_date [description]
     * @param [type] $end_date   [description]
     * @param [type] $resolution [description]
     *
     * @return [type] [description]
     */
    public function getDeviceReadsBetweenDates($device_id = null, $start_date = null, $end_date = null, $resolution = 60)
    {
        if (is_null($start_date) || is_null($end_date)) {
            return;
        }

        if ($this->isDateInThePast($start_date) && $this->isDateInThePast($end_date)) {
            if ($this->isEndDateAfterStartDate($start_date, $end_date)) {
                if ($this->validateDateDifferenceIsInBetweenApiLimit($start_date, $end_date)) {
                    $start_date = $this->convertToCarbon($start_date);

                    $end_date = $this->convertToCarbon($end_date);

                    $start_date_internal = $start_date->addHour()->toISO8601String();

                    $end_date_internal = $end_date->toISO8601String();

                    $end_date_internal = str_replace('+0000', 'Z', $end_date_internal);

                    $start_date_internal = str_replace('+0000', 'Z', $start_date_internal);

                    if ($reads = $this->get(
                        'devices/'.$device_id.'/span',
                        [
                            'start'      => $start_date_internal,
                            'end'        => $end_date_internal,
                            'resolution' => $resolution,
                        ]
                    )
                    ) {
                        if ($this->isReturnValid($reads)) {
                            return $this->formatReadResults($reads);
                        }
                    }
                }
            }
        }
    }
}
