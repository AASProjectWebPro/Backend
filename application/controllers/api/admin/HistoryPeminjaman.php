<?php

defined('BASEPATH') or exit('No direct sript access allowed');

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
        $this->load->model('M_Buku');
        $this->load->model('M_Peminjaman');
        $this->load->model('PengembalianModel');
        $this->load->library('form_validation');
        $this->load->library('jwt');
    }

    public function options_get()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        exit();
    }

    function checkHistory($id)
    {
        if ($this->PengembalianModel->checkHistory($id)) {
            return true;
        } else {
            $this->form_validation->set_message('checkHistory', 'The id history not found');
            return false;
        }
    }

    function mengakaliFormValidationYangHanyaMendeteksiPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $putData = $this->input->input_stream();
        $_POST = $putData;
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
        $id = $this->get('id');
        if ($id) {
            $data = $this->PengembalianModel->read($id);
            if ($data) {
                $this->response($data, 200);
            } else {
                $this->response(['message' => 'Data not found'], 404);
            }
        } else {
            $dataAll = $this->PengembalianModel->fetch_all();
            $this->response($dataAll, 200);
        }
    }

    function index_delete()
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
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_checkHistory');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        if ($this->PengembalianModel->delete($this->delete('id'))) {
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response, 201);
        }
    }

}

?>