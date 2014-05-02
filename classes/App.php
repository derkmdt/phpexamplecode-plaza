<?php

class App {
    private $rooturl;
    public static $testClient = null;
	public static function run() {
	    self::$testClient = new TestClient(BOL_PLAZAAPI_PUBLIC_KEY, BOL_PLAZAAPI_PRIVATE_KEY);
        $servername = str_replace("www.", "", $_SERVER['SERVER_NAME']);
        $rooturl = 'http://'.$servername.$_SERVER['SCRIPT_NAME'];

        print('<html><body style="margin:20px;"><h4>PHP example code</h4>');
        print('<ul>');
        print('<li><a href="'.$rooturl.'?action=getorders">GET /services/rest/orders/v1/open/</a> (<a href="'.$rooturl.'?action=getordersraw">* raw xml</a>)</li>');
        print('<li><a href="'.$rooturl.'?action=getprocess">GET /services/rest/orders/v1/process/{id} (succes)</a> (<a href="'.$rooturl.'?action=getprocessraw">* raw xml</a>)</li>');
		print('<li><a href="'.$rooturl.'?action=setprocess">POST /services/rest/orders/v1/process/</a></li>');
        print('<li><a href="'.$rooturl.'?action=getpayments">GET /services/rest/payments/v1/payments/{monthyear}</a> (<a href="'.$rooturl.'?action=getpaymentsraw">* raw xml</a>)</li>');
		print('</ul>');
        //print('Download dit voorbeeld op <a href="https://github.com/devbolcom/phpexamplecodelibrary">https://github.com/devbolcom/phpexamplecodelibrary</a>.<br>');
		print('----');
        
        //convert html characters in $_REQUEST params for Cross-site scripting (XSS)
		foreach ($_REQUEST as $key => $value) {
			$params[$key] = htmlspecialchars($value);
		}
        //get action param for which data to get or post
	    $action = isset($params['action']) ? $params['action'] : "default";
	    switch($action) {
	        case 'default':
                self::getOrders(true,$params);
	            break;
	        case 'getorders':
	            self::getOrders(false,$params);
	            break;
            case 'getordersraw':
                self::getOrders(true,$params);
                break;
            case 'getprocess':
                self::getProcess(false,$params);
                break;
            case 'getprocessraw':
                self::getProcess(true,$params);
                break;
            case 'setprocess':
                self::setProcess(false,$params);
                break;
            case 'getpayments':
                self::getPayments(false,$params);
                break;
            case 'getpaymentsraw':
                self::getPayments(true,$params);
                break;
	    }
		
	}

	private static function getOrders($bRaw=0,$params='') {
	    //orders request /services/rest/orders/v1/open/ + queryParams
        $xmlResponse = self::$testClient->getOrders();
        if($bRaw) {
            self::printValue("<strong>Simple XML response</strong>");
            self::printValue('----');
            self::printValue($xmlResponse);
            //echo '<form id="rawform" method="POST" action="view.php" target="_blank"><input type="hidden" value="'.urlencode($xmlResponse).'" name="content"><input type="submit" value="Show raw XML output"></form>';
        } else {
            self::printValue("<strong>Example response</strong>");
            self::printValue("----");
			foreach($xmlResponse->OpenOrder as $child) {
                echo '<a href="'.$rooturl.'?action=getprocess&orderid='.$child->OrderId.'">'.$child->OrderId.'</a>';
				echo "<br>";
            }
        }
        self::printValue(" ");
	}

    private static function getProcess($bRaw=0,$params='') {
        //get order process /services/rest/orders/v1/process/{id} + queryParams
        if(!isset($params['orderid'])) $orderid = '123'; else $orderid = urldecode($params['orderid']);
        $xmlResponse = self::$testClient->getProcess($orderid);
	    if($xmlResponse) {
	        if($xmlResponse->errorCode) {
	            self::printValue("<strong>Error</strong>");
                    self::printValue('----');
                    self::printValue($xmlResponse->errorMessage);
	        } else {
    	        if($bRaw!=0) {
    	            self::printValue("<strong>Simple XML response</strong>");
                    self::printValue('----');
    	            self::printValue($xmlResponse);
    	        } else {
    	            self::printValue("<strong>Example response</strong>");
                    self::printValue('----');
    	        	self::printLine('<strong>ProcessOrderId: '.$xmlResponse->ProcessOrderId.'</strong>');
    				self::printLine('Process order status: '.$xmlResponse->Status);
    				foreach($xmlResponse->Order as $child) {
    	        		self::printLine('Order '.$child->OrderId.' - Status: '.$child->OrderItemList->OrderItemData->Process);
    				}
    	        }
            }
		} else {
			self::printLine('No data');
		}
        self::printValue(" ");
    }

    private static function setProcess($bRaw=0,$params='') {
        //get order process /services/rest/orders/v1/process/ + queryParams + content
		$field = '<?xml version="1.0" encoding="UTF-8"?>
		<ProcessOrders xmlns="http://plazaapi.bol.com/services/xsd/plazaapiservice-1.0.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://plazaapi.bol.com/services/xsd/plazaapiservice-1.0.xsd plazaapiservice-1.0.xsd ">
		<Shipments>
		<Shipment>
		<OrderId>123</OrderId>
		<DateTime>2011-01-01T12:00:00</DateTime>
		<Transporter>
		<Code>1234</Code>
		<TrackAndTraceCode>3SBOLDEVTEST</TrackAndTraceCode>
		</Transporter>
		<OrderItems>
		<Id>34567</Id>
		</OrderItems>
		</Shipment>
		</Shipments>
		</ProcessOrders>';
        $xmlResponse = self::$testClient->setProcess($orderid,'',$field);
	    if($xmlResponse) {
            self::printValue("<strong>Simple XML response</strong>");
            self::printValue($xmlResponse);
		} else {
            self::printValue("<strong>Example response</strong>");
		    self::printValue('----');
			self::printLine('No data');
		}
        self::printValue(" ");
    }


    private static function getPayments($bRaw=0,$params='') {
        //get order process /services/rest/payments/v1/payments/{yearmonth} + queryParams
        if(!isset($params['yearmonth'])) $yearmonth = '201301'; else $yearmonth = urldecode($params['yearmonth']);
        $xmlResponse = self::$testClient->getPayments($yearmonth);
        if($xmlResponse) {
            if($bRaw!=0) {
                self::printValue("<strong>Simple XML response</strong>");
                self::printValue('----');
                self::printValue($xmlResponse);
            } else {
                self::printValue("<strong>Example response</strong>");
                self::printValue('----');
                foreach($xmlResponse->Payment as $child) {
                    self::printLine('CreditInvoiceNumber '.$child->CreditInvoiceNumber.' - PaymentAmount: '.$child->PaymentAmount);
                }
            }
        } else {
            self::printLine('No data');
        }
        self::printValue(" ");
    }

	private static function printValue($value) {
		echo '<pre>' . print_r($value, 1) . '</pre>';
	}
	private static function printLine($value) {
		echo $value . '</br>';
	}
}

?>