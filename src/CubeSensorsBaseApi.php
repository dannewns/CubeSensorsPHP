<?php

namespace Jump24\CubeSensors;

use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Subscriber\Oauth\Oauth1;
use GuzzleHttp\TransferStats;

class CubeSensorsBaseApi
{
    protected $auth;

    protected $route = 'http://api.cubesensors.com/';

    protected $version = 'v1/';

    protected $device_room_types = ['work', 'sleep', 'living'];

    protected $client;

    protected $start_date;

    protected $end_date;

    private $mock = null;

    private $error = null;

    private $status_code = null;

    protected $called_url = null;

    private $reason = null;

    /**
     * @var stack handler required for guzzle
     */
    protected $stack_handler;

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
     * sets up the mock data for the get requests to allow for testing.
     *
     * @param GuzzleHttp\Subscriber\Mock $mock_data the Mock object to pass into the system for testing
     */
    public function setupMockDataForRequest(MockHandler $mock_data)
    {
        $this->mock = $mock_data;
    }

    /**
     * returns the error message on the system.
     *
     * @return [type] [description]
     */
    public function getErrorMessage()
    {
        return $this->error;
    }

    /**
     * sets up the start and end date variables for the class to use and converts them to carbon format.
     */
    protected function convertToCarbon($date)
    {
        if (is_null($date)) {
            return Carbon::now()->setTimezone('UTC');
        } else {
            return Carbon::createFromFormat('Y-m-d H:i:s', $date.' 00:00:00')->addHour()->setTimezone('UTC');
        }
    }

    /**
     * formats the returned list of devices from the device call api into a more friendly result set.
     *
     * @param array $devices the array returned from the original api/devices call
     *
     * @return array the formatted list of devices returned
     */
    protected function formatDevice($device)
    {
        $cube = [];

        $cube['uid'] = $device['uid'];

        $cube['name'] = $device['extra']['name'];

        $cube['roomtype'] = $device['extra']['roomtype'];

        return $cube;
    }

    /**
     * formats the devices results.
     *
     * @param array $results the results from the devices read
     *
     * @return array The formatted results for the device
     */
    protected function formatReadResults($results)
    {
        $field_list = $results['field_list'];

        $formatted_devices = [];

        if (is_array($results['results'])) {
            foreach ($results['results'] as $result) {
                $device_reads = [];

                foreach ($result as $id => $value) {
                    if (isset($field_list[$id]) && $field_list[$id] == 'time') {
                        $time = Carbon::parse($value);

                        $device_reads[$field_list[$id]] = $time;
                    } else {
                        $device_reads[$field_list[$id]] = $value;
                    }
                }

                $formatted_devices['reads'][] = $device_reads;
            }
        }

        return $formatted_devices;
    }

    /**
     * checks the returned value for the ok field to see if response was returned correctly.
     *
     * @param array $return the returned response from a API call
     *
     * @return bool [description]
     */
    protected function isReturnValid($return)
    {
        if (is_null($return)) {
            return false;
        }

        if ($return['ok']) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * wrapper method to call the api by just suppling a api endpoint url.
     *
     * @param string $url              the API endpoint to be called
     * @param array  $query_parameters the query string parameters to pass into the call
     *
     * @return the result from the call be it the details for a cube or NULL when nothing found
     */
    public function get($url, $query_parameters = [], $dump_data = false)
    {
        try {
            $this->setupClient();

            if (!empty($query_parameters)) {
                $url = $url.'?'.http_build_query($query_parameters);
            }
            $request = new Request('GET', $url);

            $response = $this->client->send($request);

            if ($response->getStatusCode() != 200) {
                return;
            }

            if ($dump_data) {
                $body = $response->getBody();

                echo $body;

                die();
            }

            $body = json_decode($response->getBody(), true);

            if ($this->isReturnValid($body)) {
                return $body;
            } else {
                return;
            }
        } catch (ServerException $e) {
            $this->setResponseValues($e->getResponse());

            $this->error = $e->getMessage();

            return;
        } catch (ClientException $e) {
            $this->error = $e->getMessage();

            $this->setResponseValues($e->getResponse());

            return;
        } catch (RequestException $e) {
            $this->error = $e->getMessage();

            if (!is_null($e->getResponse())) {
                $this->setResponseValues($e->getResponse());
            }

            return;
        }
    }

    public function getCalledUrl()
    {
        return $this->called_url;
    }

    /**
     * sets up the error responses for the exceptions handled.
     *
     * @param GuzzleHttp\Psr7\Response $response the response from the API Call
     */
    private function setResponseValues(Response $response)
    {
        $this->status_code = $response->getStatusCode();

        $this->reason = $response->getReasonPhrase();
    }

    /**
     * sets up the guzzle client ready to use the api.
     *
     * @return [type] [description]
     */
    private function setupClient()
    {
        $this->createStackHandler();

        $this->addOauthToStack();

        $this->client = new Client(
            [
                'base_uri'  => $this->route.$this->version,
                'handler'   => $this->stack_handler,
                'auth'      => 'oauth',
                'on_stats'  => function (TransferStats $stats) use (&$url) {
                    $this->called_url = $stats->getEffectiveUri();
                },
            ]
        );
    }

    protected function createStackHandler()
    {
        $this->stack_handler = HandlerStack::create($this->mock);
    }

    protected function addOauthToStack()
    {
        $middleware = new Oauth1([
            'consumer_key'    => $this->consumer_key,
            'consumer_secret' => $this->consumer_secret,
            'token'           => $this->access_token,
            'token_secret'    => $this->access_token_secrect,
        ]);

        $this->stack_handler->push($middleware);
    }

    protected function convertDateToApiFormat($date)
    {
        $date = $this->convertToCarbon($date);

        $date = $date->toISO8601String();

        $date = str_replace('+0000', 'Z', $date);

        return $date;
    }
}
