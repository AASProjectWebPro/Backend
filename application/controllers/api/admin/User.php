<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class User extends REST_Controller
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
        $this->load->model('UserModel');
        $this->load->library('form_validation');
        $this->load->library('jwt');
    }
    public function options_get() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        exit();
    }

    function mengakaliFormValidationYangHanyaMendeteksiPostRequest(){
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $putData = $this->input->input_stream();
        $_POST = $putData;
    }
    function validate()
    {
        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[user.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->form_validation->set_rules('nomor_telepon', 'Phone Number', 'required|trim|numeric|is_unique[user.nomor_telepon]|max_length[16]');
        $this->form_validation->set_rules('alamat', 'Adress', 'required|trim');
    }
    function checkUserExistOnPeminjam($id){
        if(!($this->UserModel->checkUserExistOnPeminjam($id))){
            return true;
        } else {
            $this->form_validation->set_message('checkUserExistOnPeminjam', 'The user still borrow the book');
            return false;
        }
    }
    function index_get()
    {
        if (isset($this->input->request_headers()['Authorization'])){
            if ($this->jwt->decode($this->input->request_headers()['Authorization'])==false) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'signature tidak sesuai',
                        'data' => []
                    ), 401
                );
            }
        } else{
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
            $data = $this->UserModel->read();
        } else {
            $data = $this->UserModel->read($id);
        }
        $this->response($data, 200);
    }
    function index_post()
    {
        if (isset($this->input->request_headers()['Authorization'])){
            if ($this->jwt->decode($this->input->request_headers()['Authorization'])==false) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'signature tidak sesuai',
                        'data' => []
                    ), 401
                );
            }
        } else{
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $this->validate();
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        $data = array(
            'username' => $this->post('username'),
            'email' => $this->post('email'),
            'password' => hash('sha256', $this->post('password')),
            'nomor_telepon' => $this->post('nomor_telepon'),
            'alamat' => $this->post('alamat')
        );
        if($this->UserModel->insert($data)){
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response,201);
        }
    }
    public function ifExist($id)
    {
        if ($this->UserModel->ifExist($id)) {
            return true;
        } else {
            $this->form_validation->set_message('ifExist', 'The ID field not found.');
            return false;
        }
    }
    function index_put()
    {
        if (isset($this->input->request_headers()['Authorization'])){
            if ($this->jwt->decode($this->input->request_headers()['Authorization'])==false) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'signature tidak sesuai',
                        'data' => []
                    ), 401
                );
            }
        } else{
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        $this->validate();
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        $data = array(
            'username' => $this->put('username'),
            'email' => $this->put('email'),
            'password' => hash('sha256', $this->put('password')),
            'nomor_telepon' => $this->put('nomor_telepon'),
            'alamat' => $this->put('alamat')
        );
        if($this->UserModel->update($this->put('id'),$data)){
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response,201);
        }
    }
    function index_delete()
    {
        if (isset($this->input->request_headers()['Authorization'])){
            if ($this->jwt->decode($this->input->request_headers()['Authorization'])==false) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'signature tidak sesuai',
                        'data' => []
                    ), 401
                );
            }
        } else{
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist|callback_checkUserExistOnPeminjam');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        if($this->UserModel->delete($this->delete('id'))){
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response,201);
        }
    }

}