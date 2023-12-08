<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class HistoryPeminjaman extends REST_Controller {
        function __construct($config = 'rest'){
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
        }
        public function options_get() {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
            header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
            exit();
        }
        function index_get() {
            $id = $this->get('id');
            if ($id) {
                $data = $this->PengembalianModel->read($id);
                if ($data) {
                    $this->response($data, 200);
                } else {
                    $this->response(['message' => 'Data not found'], 404);
                }
            } else {
                $dataAll = $this->PengembalianModel->read();
                $this->response($dataAll, 200);
            }
        }
        
    }
?>