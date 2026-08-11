<?php

namespace App\Controllers;

use App\Models\MNotifications;

class Notifications extends BaseController
{
    function __construct()
    {
        $this->mNotifications = new MNotifications();
    }

    public function index()
    {
        $this->load->helper('General_Helper');
        if (!isset($_POST['From'])) {
            echo "Invalid Request";
            return;
        }
        $post['number'] = $_POST['From'];
        if (isset($_POST['Body'])) {
            $post['body'] = $_POST['Body'];
        }

        $result = $this->mNotifications->newSMS($post);//var_dump($result);

        if ($result['error'] === 0) {
            if ($post['body'] === "Yes") {
                $data['body'] = "We received your request! We will prepare your order and when ready we will contact you to complete the process! Thanks for choosing Infuserve America!";
                $data['redirect'] = "";
                return view('twilio_response', $data);
            } elseif ($post['body'] === "No`") {
                $data['body'] = "We received your request! if you have any questions about your medication, call us at at (800) 886-9222! Thanks for choosing Infuserve America!";
                $data['redirect'] = "";
                return view('twilio_response', $data);
            }
        }
    }
}
