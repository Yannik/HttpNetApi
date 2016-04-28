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

    public function zoneUpdate($zoneConfig, $recordsToAdd, $recordsToDelete)
    {
        $updateRecords = new \stdClass();
        $updateRecords->authToken = $this->apikey;
        $updateRecords->zoneConfig = $zoneConfig;
        $updateRecords->recordsToAdd = $recordsToAdd;
        $updateRecords->recordsToDelete = $recordsToDelete;
var_dump($updateRecords);
        $response = $this->guzzle->request('POST', 'zoneUpdate', [
            'body' => json_encode($updateRecords)
        ]);

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

    public function recordsFindRaw($options)
    {
        $requestObject = new \stdClass();
        $requestObject->authToken = $this->apikey;
        $requestObject = $this->mergeObjects($requestObject, $options);
        $response = $this->guzzle->request('POST', 'recordsFind', [
            'body' => json_encode($requestObject)
        ]);

        return json_decode($response->getBody());

    }

    public function recordsFindByHostname($host)
    {
        $options = new \stdClass();
        $options->filter = [ 'field' => "RecordName", 'value' => $host ];
        $options->limit = PHP_INT_MAX;

        return $this->recordsFindRaw($options);
    }
}