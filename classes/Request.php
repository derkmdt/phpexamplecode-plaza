<?php

class Request {
    const SERVER = BOL_PLAZAAPI_SERVER;
    const PORT = BOL_PLAZAAPI_PORT;
    const DEBUG = BOL_PLAZAAPI_DEBUG_MODE;
  
    private $accessKey;
    private $secretAccessKey;
    private $httpResponseCode;
    private $httpFullHeader;
    
    public function __construct($accessKeyId, $secretAccessKey) {
        try {
            $this->accessKey = $accessKeyId;
            $this->secretAccessKey = $secretAccessKey;
        } catch(Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
        }
    }
    
    public function fetch($httpMethod, $uri, $parameters='', $content='') {
        
        switch($httpMethod) {
            default:
            case 'POST':
            case 'GET':
                $contentType =  'application/xml; charset=UTF-8';
                break;
            case 'PUT':
            case 'DELETE':
                $contentType =  'application/x-www-form-urlencoded';
                break;
        }

        $date = gmdate('D, d M Y H:i:s T'); 
        $signature_string = $httpMethod . "\n\n"; 
        $signature_string .= $contentType . "\n"; 
        $signature_string .= $date."\n"; 
        $signature_string .= "x-bol-date:" . $date . "\n"; 
        $signature_string .= $uri;
        $signature = $this->accessKey.':'.base64_encode(hash_hmac('SHA256', $signature_string, $this->secretAccessKey, true));
        /*
        echo '<pre>';
        echo $signature."<br>";
        echo $signature_string."<br><br>"; 
         */
         
        $httpheaderarray = array("Content-type: ".$contentType, "X-BOL-Date:".$date, "X-BOL-Authorization: ".$signature);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, $httpheaderarray);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_URL, self::SERVER.$uri);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT,'3');
        //curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        //curl_setopt($ch, CURLOPT_CAINFO, $_SERVER['DOCUMENT_ROOT'] . '/certs/cacert.pem');
        
		if($httpMethod == 'POST') {
			curl_setopt ($ch, CURLOPT_POST, true);
			curl_setopt ($ch, CURLOPT_POSTFIELDS, $content);
		}
        curl_setopt($ch, CURLOPT_PORT, self::PORT);
        if(curl_errno($ch)) {
            print_r(curl_errno($ch), true);
        }
        $result = curl_exec($ch);

        curl_close($ch);
        
        $result = str_replace('xmlns:bns="http://plazaapi.bol.com/services/xsd/plazaapiservice-1.0.xsd"', 'xmlns:bns="http://plazaapi.bol.com/services/xsd/plazaapiservice-v1.xsd"', $result);
        
        $this->httpResponseCode = intval(substr($result, 9, 3));
        $aResult = explode("<?xml", $result);

        if(count($aResult) > 1) {
            $this->httpFullHeader = $aResult[0];
            $response = "<?xml".$aResult[1];
            if (!count((array)$result)) {
                $result = $this->httpFullHeader;
            } else {
                $result = $response;
            }
        } else {
            $this->httpFullHeader = $result;
            $result=FALSE;
        }

        if(self::DEBUG) {
            echo '<pre>Debug info<br><br>-----<br><br><strong>http request:</strong><br>'.self::SERVER.$uri.$parameters.'<br><br>';
			echo '<strong>header request:</strong><br>'.print_r($httpheaderarray, 1).'<br><br>';
            if ($content) echo '<strong>content:</strong><br>'.htmlspecialchars($content).'<br><br>';
            echo '<strong>header response:</strong><br>'.self::getFullHeader();
            echo '----</pre>';
        }
        
        return $result;
    }
    
    public function getHttpResponseCode() {
        return $this->httpResponseCode;
    }

    public function getFullHeader() {
        return $this->httpFullHeader;
    }
    
}

?>