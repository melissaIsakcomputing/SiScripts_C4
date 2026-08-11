<?php

namespace App\Controllers;

use App\Models\SureScriptAdapter;
use Exception;

class Directory extends BaseController
{

    public function __construct()
    {
        $this->adpater = new SureScriptAdapter();
    }

    public function index()
    {
        redirect('Welcome');
    }

    public function GetDirectory()
    {
        $data['AccountID'] = "360590";
        $data['PortalID'] = "444353";
        $data['From'] = "ISAK61";
        $data['MessageID'] = uniqid();
        $data['SentTime'] = date('c');
        $data['MessageType'] = "ProviderLocation";
        $data['DirectoryDate'] = date('c');

        $xml = view('messages/getdirectory_message', $data);
        $url = SureScriptDirectoryURL . '?id=' . $data['MessageID'];
        $result = $this->adpater->postMessage($url, $xml);
        return view('result_message', $result);
    }


    public function AddProviderLocation()
    {

    }

    public function UpdateProviderLocation()
    {

    }

    /*
    public function DownloadDirectory() {

        $FileName = $_GET['FileName'];

        $link = "https://admin.surescripts.net/Downloads/".$FileName;
        $this->load->helper('download');
        force_download($FileName,$link);

        // load download helder
        $this->load->helper('download');
        // read file contents
        $data = file_get_contents(base_url('/uploads/'.FileName));
        force_download($filename, $data);

    }
    */

}
