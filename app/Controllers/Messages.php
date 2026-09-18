<?php

namespace App\Controllers;

/*
 * @change Isak - 2/14/2024 - Return Error Code 600 for Communication problem - try again later
 * @change Isak - 2/14/2024 - Return Status Code 000 for Not able to reach final destination (pharmacy)
 */


use App\Models\MMessages;
use App\Models\RestFM;
use App\Models\SureScriptAdapter;
use DOMDocument;
use Exception;
use SimpleXMLElement;

class Messages extends BaseController
{
    public function __construct()
    {
        $this->adapter = new SureScriptAdapter();
        $this->mMessages = new MMessages();
    }

    public function index()
    {
        $this->load->helper('General_Helper');
        $xml = file_get_contents('php://input');
        if ($xml === "") {
            echo "<pre>Waiting for that message!</pre>";
            return;
        }

        $doc = new DOMDocument();
        $doc->loadXML($xml);
        $xml = $doc->saveXML();

        /* Get Message as XML Object */
        $message = new SimpleXMLElement($xml);
        $data['MessageID'] = $message->Header[0]->MessageID;
        $data['MessageToID'] = $message->Header[0]->To;
        $data['MessageToQ'] = $message->Header[0]->To['Qualifier'];
        $data['MessageFromID'] = $message->Header[0]->From;
        $data['MessageFromQ'] = $message->Header[0]->From['Qualifier'];
        //$pharmacy = $this->MOrganizations->getOrganization($data['MessageToID']);
        $xml_encode = base64_encode($xml);

        /* Log Message in Isak Computing Hub */
        $logResult = $this->mMessages->logMessage($xml, $data);

        $data['MessageID'] = uniqid();
        $data['RelatesToMessageID'] = $message->Header[0]->MessageID;
        $data['SentTime'] = date('c');
        if ($logResult['error'] !== 0) {
            /* Create Error Response Message */
            $data['MessageType'] = "Error";
            $data['Code'] = "600";
            $result = $this->mMessages->logResponseMessage($data);
            return view('status_message', $data);
        }

        /* Send Message To Pharmacy */
        $sendResult = $this->mMessages->sendMessage($xml, $data);
        $data['MessageType'] = "Status";
        if ($sendResult['error'] !== 0) {
            /* Create Error Response Message */
            $data['Code'] = "000";
        } else {
            /* Create Success Response Message */
            $data['Code'] = "010";
            $logResponseResult = $this->mMessages->logResponseMessage($data);
            $sendResponseResult = $this->mMessages->sendResponseMessage($data);
        }
        return view('status_message', $data);
    }


    function testPharmacy()
    {
        $this->load->model('RestFM');
        $this->fm = new RestFM();

        $messageTo = "5712876";
        $host = "";
        $this->fm->user = "icapi";
        $this->fm->pass = "1c4p1";
        $this->fm->db = "SiScripts.fmp12";

        if ($messageTo == "1071492") {
            // Infuserve America
            $host = "fmserver.infuserveamerica.com";
        } elseif ($messageTo == "5750662") {
            //Magnum Compounding
            $host = "magnum.sicompound.cloud";
        } elseif ($messageTo == "4237435") {
            // Bioclinical Infusion
            $host = "bioclinical.sicompound.cloud";
        } elseif ($messageTo == "3159541") {
            // RPC - RPC2B
            $host = "rpc2b.sicompound.cloud";
        } elseif ($messageTo == "1617169") {
            // Olive Tree
            $host = "olivetree.sicompounding.cloud";
        } elseif ($messageTo == "5712876") {
            // PharmaLabs
            $host = "pharmalabs.sicompounding.cloud";
        } elseif ($messageTo == "RPCSB") {
            // RPC - RPC2B
            $host = "rpc.sicompound.cloud";
        }
        $this->fm->host = $host;

        $layout = 'Messages';
        $record['XML_Encode'] = "";
        $record['MessageID'] = "12345";
        $record['RelatesToMessageID'] = "12345";
        $record['MessageToID'] = $messageTo;
        $record['MessageToQ'] = "P";
        $record['MessageFromID'] = "123456";
        $record['MessageFromQ'] = "D";
        $record['SentTime'] = "111";
        $record['MessageStatusCode'] = "1112";
        $record['MessageType'] = "TEST";

        $data['fieldData'] = $record;//print json_encode($data);
        $result = $this->fm->createRecord($data, $layout);

        var_dump($result);
    }

    public function getHealthStatus()
    {
        $return["status"] = "UP";
        echo json_encode($return);
    }

}
