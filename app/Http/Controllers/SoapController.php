<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hello;


class SoapController extends Controller
{
    public function index(){
        $serverUrl = "http://bkdana-va.localhost/api/server";
        $options = [
            'uri' => $serverUrl,
        ];
        $server = new \Zend\Soap\Server(null, $options);

        if (isset($_GET['wsdl'])) {
            $soapAutoDiscover = new \Zend\Soap\AutoDiscover(new \Zend\Soap\Wsdl\ComplexTypeStrategy\ArrayOfTypeSequence());
            $soapAutoDiscover->setBindingStyle(array('style' => 'document'));
            $soapAutoDiscover->setOperationBodyStyle(array('use' => 'literal'));
            $soapAutoDiscover->setClass(Hello::class);
            $soapAutoDiscover->setUri($serverUrl);

            header("Content-type: text/xml");
            echo $soapAutoDiscover->generate()->toXML();
            exit();
        } else {
            $soap = new \Zend\Soap\Server($serverUrl . '?wsdl');
            $soap->setObject(new \Zend\Soap\Server\DocumentLiteralWrapper(new Hello));
            $soap->handle();
        }
    }

    public function client(){
        $client = new \Zend\Soap\Client('http://bkdana-va.localhost/api/server?wsdl');
        $result = $client->call('sayHello', [['firstName' => 'World']]);

        echo $result->sayHelloResult;
    }
}
