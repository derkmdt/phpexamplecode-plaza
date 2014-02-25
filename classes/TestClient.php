<?php

class TestClient {
    private $requestHelper;
    private $fullResponse;
    
    public function __construct($accessKeyId=NULL, $secretAccessKey=NULL) {
        $this->requestHelper = new Request($accessKeyId, $secretAccessKey);
    }

    public function getOrders() {
        $httpResponse = $this->requestHelper->fetch('GET', '/services/rest/orders/v1/open/');
        
        if($httpResponse) {
            $response = new SimpleXMLElement($httpResponse); 
            $ns = $response->getNamespaces(true);
            $result = $response->children($ns['bns']);
        } else $result = $this->requestHelper->getFullHeader();
        
        return $result;
    }

    public function getProcess($id,$queryParams='') {
        $httpResponse = $this->requestHelper->fetch('GET', '/services/rest/orders/v1/process/' . $id, $queryParams);

        if($httpResponse) {
            $response = new SimpleXMLElement($httpResponse); 
            $ns = $response->getNamespaces(true);
            $result = $response->children($ns['bns']);
        } else $result = $this->requestHelper->getFullHeader();
        
        return $result;
    }

    public function setProcess($id,$queryParams='',$content='') {
        $httpResponse = $this->requestHelper->fetch('POST', '/services/rest/orders/v1/process/' . $id, $queryParams, $content);
        
        if($httpResponse) {
            $response = new SimpleXMLElement($httpResponse); 
            $ns = $response->getNamespaces(true);
            $result = $response->children($ns['bns']);
        } else $result = $this->requestHelper->getFullHeader();
        
        return $result;
    }

    public function getPayments($yearmonth,$queryParams='') {
        //{YearMonth}
        $httpResponse = $this->requestHelper->fetch('GET', '/services/rest/payments/v1/payments/' . $yearmonth, $queryParams);
        
        if($httpResponse) {
            $response = new SimpleXMLElement($httpResponse); 
            $ns = $response->getNamespaces(true);
            $result = $response->children($ns['bns']);
        } else $result = $this->requestHelper->getFullHeader();
        
        return $result;
    } 

    public function getFullHeader() {
        return $this->requestHelper->getFullHeader();
    }

}

?>