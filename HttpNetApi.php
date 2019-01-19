<?php
namespace Yannik\HttpNetApi;

use \GuzzleHttp\Client;

class HttpNetApi
{

    protected $guzzle;

    protected $apikey;

    public function __construct($apikey)
    {
        $this->guzzle = new \GuzzleHttp\Client(['base_uri' => 'https://partner.http.net/api/dns/v1/json/']);

        $this->apikey = $apikey;
    }

    public function setApiKey($apikey)
    {
        $this->apikey = $apikey;
    }

    public function getApiKey()
    {
        return $this->apikey;
    }

    public function zoneUpdate($zoneConfig, $recordsToAdd, $recordsToDelete = [])
    {
        $updateRecords = new \stdClass();
        $updateRecords->zoneConfig = $zoneConfig;
        $updateRecords->recordsToAdd = $recordsToAdd;
        $updateRecords->recordsToDelete = $recordsToDelete;
        $response = $this->request($updateRecords, 'zoneUpdate');

        return $response;

    }

    /*
     * Merges the elements of $additions into $original, existing elements are overwritten
     */
    public function mergeObjects($original, $additions)
    {
        foreach ($additions as $additionKey => $additionValue) {
            $original->{$additionKey} = $additionValue;
        }

        return $original;
    }

    public function request($requestObject, $path)
    {
        $requestObject->authToken = $this->apikey;
        $response = $this->guzzle->request('POST', $path, [
            'body' => json_encode($requestObject)
        ]);
        $response = json_decode($response->getBody());
        $this->validateResponse($response);
        return $response;
    }

    protected function validateResponse($response)
    {
        if ($response->status == "error") {
            throw new ApiErrorException($response->errors[0]->text);
        }
    }

    public function recordsFindRaw($options)
    {
        $requestObject = new \stdClass();
        $requestObject = $this->mergeObjects($requestObject, $options);
        $response = $this->request($requestObject, 'recordsFind');
        return $response;

    }

    /*
     * Use like
     $options->filter = [ 'field' => "ZoneName", 'value' => 'sembritzki.me' ];
     $options->filter = [ 'subFilterConnective' => 'OR',
                     'subFilter' => [
                         [ 'field' => "ZoneName", 'value' => 'sembritzki.me' ],
                         [ 'field' => "ZoneName", 'value' => '*.sembritzki.me' ]
                     ]];

     */
    public function zonesFindRaw($options)
    {
        $requestObject = new \stdclass();
        $requestObject = $this->mergeObjects($requestObject, $options);
        return $this->request($requestObject, 'zonesFind');
    }

    public function zonesFindByName($name)
    {
        $options = new \stdClass();
        $options->filter = [ 'field' => "zoneName", 'value' => $name ];
        $options->limit = PHP_INT_MAX;

        return $this->zonesFindRaw($options);
    }

    public function recordsFindByHostname($host)
    {
        $options = new \stdClass();
        $options->filter = [ 'field' => "RecordName", 'value' => $host ];
        $options->limit = PHP_INT_MAX;

        return $this->recordsFindRaw($options);
    }
}