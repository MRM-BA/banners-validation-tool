<?php
namespace classes;

class ConfigManager {
    private $client;
    private $brand;
    private $clientDefault = 'mrm';
    private $brandDefault = null;
    private $jsonFile;


    public function __construct($jsonFile = false) {
        $this->setClient(null);
        $this->setBrand(null);
        $this->jsonFile = $jsonFile;
        $this->setConfiguration();
    }


    private function setConfiguration() {
        if ($this->jsonFile) {
            try {
                $jsonString = \file_get_contents($this->jsonFile);
                if ($jsonString) {
                    $jsonData = \json_decode($jsonString);
                    if(isset($jsonData->client)) {
                        $this->setClient($jsonData->client);
                    } else {
                        $this->setClient($this->clientDefault);
                    }
                    if (isset($jsonData->brand)) {
                        $this->setBrand($jsonData->brand);
                    } else {
                        $this->setBrand($this->brandDefault);
                    }
                }
            } catch (\Exception $e) {
                $this->setDefaults();
            }
        } else {
            $this->setDefaults();
        }
    }


    private function setDefaults() {
        $this->setClient($this->clientDefault);
        $this->setBrand($this->brandDefault);
    }


    public function getClient() {
        return $this->client;
    }


    public function getBrand() {
        return $this->brand;
    }


    public function setClient($client) {
        $this->client = $client;
    }


    public function setBrand($brand) {
        $this->brand = $brand;
    }
}
