<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;
class HistoryPeminjaman extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Headers:X-API-KEY,Origin,X-Requested-With,Content-Type,Accept,Access-Control-Request-Method,Authorization");
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT,DELETE");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
        $this->load->database();
        $this->load->model('PengembalianModel');
        $this->load->library('form_validation');
        $this->load->library('jwt');
    }
    function index_get()
    {
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $id=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
        $data = $this->PengembalianModel->readUser($id);
        $this->response($data, 200);
    }
//

}