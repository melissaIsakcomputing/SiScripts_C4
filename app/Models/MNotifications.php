<?php
namespace App\Models;

use CodeIgniter\Model;

class MNotifications extends Model
{
    function  __construct()
    {
        parent::__construct();
        $this->load->model('RESTFM18');
        $this->fm = new RESTFM18();
    }

    function error($result)
    {
        if(isset($result["messages"][0]["code"]) && $result["messages"][0]["code"]!='0')
            return '('.$result["messages"][0]["code"].') '.$result["messages"][0]["message"];
        else
            return 0;
    }

    function Result($error_code=0, $error_msg=0, $result='')
    {
        if(isset($result["messages"][0]["code"]) && $result["messages"][0]["code"]!=='')
            $return['error_code']=$result["messages"][0]["code"];

        if(isset($result["messages"][0]["message"]) && $result["messages"][0]["message"]!=='')
            $return['error']=$error_msg;

        $return['data']=$result;

        return $return;
    }

    function newSMS($message)
    {

        $layout='Messages';
        $record['XML'] = json_encode($message);
        //$record['number'] = $message['number'];

        $data['fieldData'] = $record;//print json_encode($data);

        $result = $this->fm->createRecord($data, $layout);//var_dump($result);
        $return['error']=$this->error($result);

        if($return['error']===0)
        {

            //$return = $result;

        }

        return $return;
    }

    function addResponseMessage($message)
    {
        $layout='Messages';

        $record['MessageID']            = $message['MessageID'];
        $record['RelatesToMessageID']   = urlencode($message['RelatesToMessageID']);
        $record['MessageToID']          = urlencode($message['MessageToID']);
        $record['MessageToQ']           = urlencode($message['MessageToQ']);
        $record['MessageFromID']        = urlencode($message['MessageFromID']);
        $record['MessageFromQ']         = urlencode($message['MessageFromQ']);
        $record['SentTime']             = $message['SentTime'];
        $record['MessageStatusCode']    = $message['Code'];
        $record['MessageType']          = $message['MessageType'];

        $data['fieldData'] = $record;//print json_encode($data);

        $result = $this->fm->createRecord($data, $layout);//var_dump($result);
        $return['error']=$this->error($result);

        if($return['error']===0)
        {

            //$return = $result;

        }

        return $return;
    }



    function getMessage()
    {
        $layout='messages';

        $request1['id'] = "2020";
        $query = array ($request1);
        $criteria['query'] = $query;//print json_encode($criteria);

        $result = $this->fm->findRecords($criteria, $layout);//var_dump($result);
        $return['error']=$this->error($result);

        if($return['error']===0)
        {
            $cant=sizeof($result ["response"]["data"]);

            for($i=0;$i<$cant;$i++)
            {
                $record['id'] = trim($result["response"]["data"][$i]["fieldData"]["id"]);
                $records[$i] = $record;
            }

            $return['data'] = $records;
        }

        return $return;
    }


    function logout()
    {
        $this->fm->logout();
    }


}