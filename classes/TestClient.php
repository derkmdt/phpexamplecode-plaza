<?php

class TestClient {
    private $requestHelper;
    private $fullResponse;
    
    public function __construct($accessKeyId=NULL, $secretAccessKey=NULL) {
        $this->requestHelper = new Request($accessKeyId, $secretAccessKey);
    }

    public function getOrders() {
        $response = $this->requestHelper->fetch('GET', '/services/rest/orders/v1/open');

        return $response;
    }

    public function getProcess($id,$queryParams='') {
        $response = $this->requestHelper->fetch('GET', '/services/rest/orders/v1/process/' . $id, $queryParams);

        return $response;
    }

    public function setProcess($id,$queryParams='',$content='') {
        $response = $this->requestHelper->fetch('POST', '/services/rest/orders/v1/process/' . $id, $queryParams, $content);

        return $response;
    }

    public function getFullHeader() {
        return $this->requestHelper->getFullHeader();
    }

}

?>