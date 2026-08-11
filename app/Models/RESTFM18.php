<?php

namespace App\Models;

use CodeIgniter\Model;

class RESTFM18 extends Model
{
    public $host = '';
    public $db = '';
    public $user = '';
    public $pass = '';

    public $version = 'vLatest';
    public $fmversion = 18;
    public $layout = '';
    public $secure = true;
    public $token_name = 'fmtoken';
    public $show_debug = false;

    public $debug_array = array();

    function productInfo()
    {
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        $url = "https://" . $this->host . "/fmi/data/" . $this->version . "/productInfo";
        $result = $this->callCURL($url, 'GET');
        $this->updateDebug('productInfo result', $result);
        return $result;
    }

    function databaseNames()
    { //doesn't work when you're logged in (because both basic & bearer headers are sent)
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        $url = "https://" . $this->host . "/fmi/data/" . $this->version . "/databases";
        $header = "Authorization: Basic " . base64_encode($this->user . ":" . $this->pass);
        $result = $this->callCURL($url, 'GET', array(), array($header));
        $this->updateDebug('databaseNames result', $result);
        return $result;
    }

    function layoutNames()
    {
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/";
        $result = $this->callCURL($url, 'GET');
        $this->updateDebug('layoutNames result pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET');
            $this->updateDebug('layoutNames result pass 2', $result);
        }

        return $result;
    }

    function scriptNames()
    {
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/scripts/";
        $result = $this->callCURL($url, 'GET');
        $this->updateDebug('scriptNames result pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET');
            $this->updateDebug('scriptNames result pass 2', $result);
        }
        return $result;
    }

    function layoutMetadata($layout = NULL, $recordId = NULL)
    {
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        if (empty ($layout)) $layout = $this->layout;

        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout);

        if (!empty($recordId))
            $url = $url . '?recordId=' . $recordId;

        $result = $this->callCURL($url, 'GET');
        $this->updateDebug('layoutMetadata result pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET');
            $this->updateDebug('layoutMetadata result pass 2', $result);
        }
        return $result;
    }

    function createRecord($data, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . "/records";
        $result = $this->callCURL($url, 'POST', $data);
        $this->updateDebug('create record data : ', $data);
        $this->updateDebug('createRecord pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'POST', $data);
            $this->updateDebug('createRecord pass 2', $result);
        }

        return $result; //error, foundcount, json and array
    }

    function duplicateRecord($id, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . "/records/" . $id;
        $result = $this->callCURL($url, 'POST');
        $this->updateDebug('duplicate recordID : ', $id);
        $this->updateDebug('createRecord pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'POST');
            $this->updateDebug('createRecord pass 2', $result);
        }

        return $result; //error, foundcount, json and array
    }

    function deleteRecord($id, $scripts, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . '/records/' . $id;
        $result = $this->callCURL($url, 'DELETE', $scripts);
        $this->updateDebug('deleteRecord ' . $id . ' pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'DELETE', $scripts);
            $this->updateDebug('deleteRecord ' . $id . ' pass 2', $result);
        }
        return $result; //error
    }

    function editRecord($id, $record, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . '/records/' . $id;
        $result = $this->callCURL($url, 'PATCH', $record);

        $this->updateDebug('update record data ' . $id . ': ', $record);
        $this->updateDebug('editRecord ' . $id . ' pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'PATCH', $record);
            $this->updateDebug('editRecord ' . $id . ' pass 2', $result);
        }

        return $result; //error, foundcount, json and array
    }

    function getRecord($id, $parameters = array(), $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . '/records/' . $id;
        $result = $this->callCURL($url, 'GET', $parameters);
        $this->updateDebug('getRecord ' . $id . ' pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET', $parameters);
            $this->updateDebug('getRecord ' . $id . ' pass 2', $result);
        }
        return $result; //error, foundcount, json and array
    }

    function executeScript($scriptName, $scriptParameter, $layout = NULL)
    {
        if ($this->fmversion < 18) return $this->throwRestError(-1, "This function is not supported in FileMaker 17");
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . '/script/' . rawurlencode($scriptName);
        $parameters['script.param'] = $scriptParameter;
        $result = $this->callCURL($url, 'GET', $parameters);
        $this->updateDebug('executeScript ' . $scriptName . ' pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET', $parameters);
            $this->updateDebug('executeScript ' . $scriptName . ' pass 2', $result);
        }
        return $result; //error, foundcount, json and array
    }

    function getRecords($parameters = array(), $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . "/records";
        $result = $this->callCURL($url, 'GET', $parameters);
        $this->updateDebug('getRecords pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'GET', $parameters);
            $this->updateDebug('getRecords pass 2', $result);
        }

        return $result; //error, foundcount, json and array
    }

    function uploadContainer($id, $fieldName, $file, $repetition = 1, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . '/records/' . $id . '/containers/' . rawurlencode($fieldName) . '/' . $repetition;
        $cfile = curl_file_create($file['tmp_name'], $file['type'], $file['name']);
        $file = array('upload' => $cfile);
        $result = $this->callCURL($url, 'POSTFILE', $file);
        $this->updateDebug('file ', $file);
        $this->updateDebug('uploadContainer ' . $id . ' pass 1', $result);

        $result = $this->checkValidResult($result);
        if (!$result) {
            $result = $this->callCURL($url, 'POSTFILE', '', $file);
            $this->updateDebug('uploadContainer ' . $id . ' pass 2', $result);
        }
        return $result; //error
    }

    function findRecords($data, $layout = NULL)
    {
        if (empty ($layout)) $layout = $this->layout;
        $login = $this->login();//echo json_encode(!$this->checkValidLogin($login));

        if (!$this->checkValidLogin($login)) return $login;

        $url = "/layouts/" . rawurlencode($layout) . "/_find";
        $result = $this->callCURL($url, 'POST', $data);
        $this->updateDebug('findRecords pass 1', $result);

        $result = $this->checkValidResult($result);//echo json_encode($result);echo 'ok';die();
        if (!$result) {
            $result = $this->callCURL($url, 'POST', $data);
            $this->updateDebug('findRecords pass 2', $result);
        }

        return $result;
    }

    function setGlobalFields($fields)
    {
        $login = $this->login();
        if (!$this->checkValidLogin($login)) return $login;

        $url = "/globals";
        $result = $this->callCURL($url, 'PATCH', $fields);
        $this->updateDebug('setGlobalFields pass 1', $result);

        $result = $this->checkValidResult($result);

        if (!$result) {
            $result = $this->callCURL($url, 'PATCH', $fields);
            $this->updateDebug('setGlobalFields pass 1', $result);
        }

        return $result; //error, foundcount, json and array
    }

    function login()
    {
        $this->updateDebug('login start cookie', $_COOKIE);
        $session_data = $this->session->userdata('logged_user_swa_token');
        //echo $session_data['token'];
//        if($newToken === '')
//        {
        if (!empty ($session_data['token'])) {
            $this->updateDebug('login existing token', $session_data['token']);
            return (array('response' => array('token' => $session_data['token']), 'messages' => array('code' => 0, 'message' => 'Already have a token.')));
        }
        //}


        $url = "/sessions";
        $header = "Authorization: Basic " . base64_encode($this->user . ":" . $this->pass);
        $result = $this->callCURL($url, 'POST', array(), array($header));
        $this->updateDebug('login result', $result);

        if (isset ($result['response']['token'])) {
            $token = $result['response']['token'];
            setcookie('fmToken', $token, time() + (14 * 60), '', '', true, true);
            $_COOKIE['fmToken'] = $token;


            $sess_array = array('token' => $token);
            $this->session->set_userdata('logged_user_swa_token', $sess_array);

        }
        $session_data = $this->session->userdata('logged_user_swa_token');
        //echo $session_data['token'];
        $this->updateDebug('login end cookie', $_COOKIE);

        return $result;
    }

    function logout($token = NULL)
    {
        if (empty ($token)) {
//            $token = $_COOKIE['fmToken'];
            $session_data = $this->session->userdata('logged_user_swa_token');
            $token = $session_data['token'];
        }

        if (empty ($token)) {
            $this->updateDebug('logout no token');
            return ($this->throwRestError(0, 'No Token'));
        }

        $url = "/sessions/" . $token;
        $result = $this->callCURL($url, 'DELETE');
        $this->updateDebug('logout result', $result);
        /*if ($token == $_COOKIE['fmToken'])
        {
            setcookie('fmToken', '');
            $_COOKIE ['fmToken']='';
        }*/
        if ($this->session->userdata('logged_user_swa_token')) {
            $this->session->unset_userdata('logged_user_swa_token');
        }

        return $result;
    }

    function callCURL($url, $method, $payload = '', $header = array())
    {
        $session_data = $this->session->userdata('logged_user_swa_token');

        if (substr($url, 0, 4) != 'http') $url = "https://" . $this->host . "/fmi/data/" . $this->version . "/databases/" . rawurlencode($this->db) . $url;
        $this->updateDebug("pre-payload: ", $payload);

        if ($method == 'POSTFILE') $contentType = 'multipart/form-data';
        else $contentType = 'application/json';

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);         //follow redirects
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);         //return the transfer as a string
        if ($this->secure) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);         //verify SSL CERT
            //curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);         //verify SSL CERT
        } else {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);         //don't verify SSL CERT
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);         //don't verify SSL CERT
        }
        curl_setopt($ch, CURLOPT_VERBOSE, 1);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1); //Don'T use cache

        $h = preg_grep('/^Authorization/i', $header);

        if (!empty ($session_data['token']) && empty ($h)) {
            $this->updateDebug('not empty token on call', $session_data['token']);
            $header = array_merge($header, array('Authorization:Bearer ' . $session_data['token'], 'Content-Type: ' . $contentType));//echo json_encode($header);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        } else {
            $header = array_merge($header, array('Content-Type: ' . $contentType));
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        }

        $this->updateDebug("payload: ", $payload);
        if (isset ($payload) && is_array($payload)) {
            if ($method == 'GET' || $method == 'DELETE') {
                $url = $url . '?' . http_build_query($payload);
                unset ($payload);
            } elseif ($method != 'POSTFILE') {
                if (empty($payload)) $payload = json_encode($payload, JSON_FORCE_OBJECT);
                else $payload = json_encode($payload);
            }

        }
        if (isset ($payload)) curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        if ($method == 'POSTFILE') $method = 'POST';
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);//echo json_encode($result);
        $error = curl_error($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $this->updateDebug('header', $header);
        $this->updateDebug('url', $url);
        $this->updateDebug("call error: ", $error);
        $this->updateDebug("call result: ", $result);
        $this->updateDebug("call info: ", $info);

        if (!empty ($result)) {
            $result = json_decode($result, true);
            return $result;
        } elseif (!empty ($info['http_code'])) $this->throwRestError($info['http_code'], 'HTTP Error ' . $info['http_code']);
        elseif (!empty ($error)) return $this->throwRestError(-1, $error);
        else return $this->throwRestError(-1, 'Empty Result');
    }

    function throwRestError($num, $message)
    {
        //$message = array("code" => $num , "message" => $message);
        $message['code'] = $num;
        $message['message'] = $message;

        return (array('response' => array(), 'messages' => $message));
    }

    function checkValidResult($result)
    {
        //echo json_encode($result);
        if (isset($result['messages'][0]['code']) && $result['messages'][0]['code'] != 0) {
            $_COOKIE ['fmToken'] = '';

            if ($this->session->userdata('logged_user_swa_token')) {
                $this->session->unset_userdata('logged_user_swa_token');
            }

            $login = $this->login();//echo json_encode($login);
            if ((isset($login['messages']['code']) && $login['messages']['code'] != 0) || isset($login['messages'][0]['code']) && $login['messages'][0]['code'] != 0) {
                $this->updateDebug('checkValidResult', '2nd login failed');
                return $login;
            }
            $this->updateDebug('checkValidResult', '2nd login succeeded');
            return false;
        }

        $this->updateDebug('checkValidResult', 'valid result');
        return $result;
    }

    function checkValidLogin($result)
    {
        //echo var_dump($result['messages']['code']);//die();

        if (isset($result['messages']['code']) && $result['messages']['code'] > 0) { //any error in result
            $this->updateDebug('Failed initial login', $result);
            return false;
        }

        $this->updateDebug('Succeeded initial login', $result);
        return true;
    }

    function __construct($host = '', $db = '', $user = '', $pass = '', $layout = '')
    {
        if (empty($host)) $host = HOST;
        if (empty($db)) $db = DB;
        if (empty($user)) $user = USER;
        if (empty($pass)) $pass = PASS;

        if (empty($layout) && LAYOUT != '') $layout = LAYOUT;

        $this->host = $host;
        $this->db = $db;
        $this->user = $user;
        $this->pass = $pass;
        $this->layout = $layout;

        return true;
    }

    function __destruct()
    {
        if (strtoupper($this->show_debug) == "HTML") {
            echo "<br><strong>DEBUGGING ON: </strong><br>";
            echo "<pre>";
            print_r($this->debug_array);
            echo "</pre>";
        } elseif ($this->show_debug) {
            echo "\nDEBUGGING ON: \n";
            print_r($this->debug_array);
        }
    }

    function updateDebug($label, $value = '')
    {
        $this->debug_array [$label] = $value;
    }
}

?>