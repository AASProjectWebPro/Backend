<?php

defined('BASEPATH') or exit('No direct sript access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Dashboard extends REST_Controller
{

    function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers:X-API-KEY,Origin,X-Requested-With,Content-Type,Accept,Access-Control-Request-Method,Authorization");
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT,DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
        $this->load->database();
        $this->load->model('DashboardModel');
        $this->load->library('jwt');
    }

    public function options_get()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        exit();
    }

    function authorization()
    {
        if (isset($this->input->request_headers()['Authorization'])) {
            if ($this->jwt->decodeAdmin($this->input->request_headers()['Authorization']) == false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function index_get()
    {
        if (!$this->authorization()) {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $data = array(
            'user' => $this->DashboardModel->getCountuser(),
            'buku' => $this->DashboardModel->getCountbuku(),
            'transaksi' => $data = $this->DashboardModel->getCountTransaksi(),
            'history' => $this->DashboardModel->getCounthistory()
        );
        return $this->response($data, 200);
    }
}
