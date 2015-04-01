<?php
namespace classes;

class ConfigManager {
    private $partner;
    private $client;
    private $partnerDefault = 'mrm';
    private $clientDefault = null;
    private $jsonFile;


    public function __construct($jsonFile = false) {
        $this->setPartner(null);
        $this->setClient(null);
        $this->jsonFile = $jsonFile;
        $this->setConfiguration();
    }


    private function setConfiguration() {
        if ($this->jsonFile) {
            try {
                $jsonString = \file_get_contents($this->jsonFile);
                if ($jsonString) {
                    $jsonData = \json_decode($jsonString);
                    if(isset($jsonData->partner)) {
                        $this->setPartner($jsonData->partner);
                    } else {
                        $this->setPartner($this->partnerDefault);
                    }
                    if(isset($jsonData->client)) {
                        $this->setClient($jsonData->client);
                    } else {
                        $this->setClient($this->clientDefault);
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
        $this->setPartner($this->partnerDefault);
        $this->setClient($this->clientDefault);
    }


    public function getPartner() {
        return $this->partner;
    }


    public function getClient() {
        return $this->client;
    }


    public function setPartner($partner) {
        $this->partner = $partner;
    }


    public function setClient($client) {
        $this->client = $client;
    }
}
