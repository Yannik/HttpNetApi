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

    public function zoneUpdate($zoneConfig, $recordsToAdd, $recordsToDelete = [])
    {
        $updateRecords = new \stdClass();
        $updateRecords->zoneConfig = $zoneConfig;
        $updateRecords->recordsToAdd = $recordsToAdd;
        $updateRecords->recordsToDelete = $recordsToDelete;
        $response = $this->request($updateRecords, 'zoneUpdate');

        return json_decode($response->getBody());

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
        return $this->guzzle->request('POST', $path, [
            'body' => json_encode($requestObject)
        ]);
    }

    public function recordsFindRaw($options)
    {
        $requestObject = new \stdClass();
        $requestObject = $this->mergeObjects($requestObject, $options);
        $response = $this->request($requestObject, 'recordsFind');
        return json_decode($response->getBody());

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
        return json_decode($this->request($requestObject, 'zonesFind')->getBody());
    }

    public function recordsFindByHostname($host)
    {
        $options = new \stdClass();
        $options->filter = [ 'field' => "RecordName", 'value' => $host ];
        $options->limit = PHP_INT_MAX;

        return $this->recordsFindRaw($options);
    }
}