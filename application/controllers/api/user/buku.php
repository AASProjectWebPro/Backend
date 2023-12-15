<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class buku extends REST_Controller
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
        $this->load->database('');
        $this->load->model('M_Buku');
        $this->load->library('form_validation');
        $this->load->library('jwt');
    }
    function authorization()
    {
        if (isset($this->input->request_headers()['Authorization'])) {
            if ($this->jwt->decodeUser($this->input->request_headers()['Authorization']) == false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    function index_get(){
        if (!$this->authorization()) {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $id = $this->get('id');
        if ($id == '') {
            $data = $this->M_Buku->fetch_all();
        } else {
            $data = $this->M_Buku->fetch_single_data($id);
        }
        $this->response($data, 200);
    }

}