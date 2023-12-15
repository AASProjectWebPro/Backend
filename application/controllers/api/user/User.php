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
        $this->load->library('jwt');
        $this->load->library('form_validation');
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
    public function ifExist($id)
    {
        if ($this->UserModel->ifExist($id)) {
            return true;
        } else {
            $this->form_validation->set_message('ifExist', 'The ID field not found.');
            return false;
        }
    }
    function validate()
    {
        $this->form_validation->set_rules('username', 'Username', 'required|trim|is_unique[user.username]');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|is_unique[user.email]');
        $this->form_validation->set_rules('nomor_telepon', 'Phone Number', 'required|trim|numeric|is_unique[user.nomor_telepon]|max_length[16]');
        $this->form_validation->set_rules('alamat', 'Adress', 'required|trim');
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
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $id=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
        $data = $this->UserModel->read($id);
        $this->response(array($data), 200);
    }
    function name_get(){
        if (!$this->authorization()) {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $id=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
        $data = $this->UserModel->get_name_by_id($id);
        $this->response(array('username'=>$data), 200);
    }
    function index_put(){
        //user by id jwt payload
    }
    function register_post(){
        $this->form_validation->set_rules('password', 'Password', 'required|min_length[6]');
        $this->validate();
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $data = array(
            'username' => $this->post('username'),
            'email' => $this->post('email'),
            'password' => hash('sha256', $this->post('password')),
            'nomor_telepon' => $this->post('nomor_telepon'),
            'alamat' => $this->post('alamat')
        );
        if ($this->UserModel->insert($data)) {
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response, 201);
        }
    }
    function index_patch(){
        if (!$this->authorization()) {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $putData = $this->input->input_stream();
        $_POST = $putData;
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $_POST['id']=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        $this->validate();
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $data = array(
            'username' => $this->patch('username'),
            'email' => $this->patch('email'),
            'nomor_telepon' => $this->patch('nomor_telepon'),
            'alamat' => $this->patch('alamat')
        );
        if ($this->UserModel->update($_POST['id'], $data)) {
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response, 201);
        }
    }
    function new_pw(){
        if ($_POST['new_pw']==$_POST['repeat_new_pw']) {
            return true;
        } else {
            $this->form_validation->set_message('new_pw', 'New Password and Retype Password not match');
            return false;
        }
    }
    function current_pw($new_pw){
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $_POST['id']=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
        if (hash('sha256', $new_pw) == $this->UserModel->getPasswordUser($_POST['id'])) {
            return true;
        }
        else {
            $this->form_validation->set_message('current_pw', 'Current password wrong.');
            return false;
        }
    }
    function password_patch(){
        if (!$this->authorization()) {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Unauthorized',
                    'data' => []
                ), 401
            );
        }
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $putData = $this->input->input_stream();
        $_POST = $putData;
        $jwt=explode("Bearer ",$this->input->request_headers()['Authorization']);
        $_POST['id']=json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;

        $this->form_validation->set_rules('repeat_new_pw', 'repeat_new_pw', 'required|callback_new_pw');
        $this->form_validation->set_rules('new_pw', 'new_pw', 'required|callback_new_pw');
        $this->form_validation->set_rules('current_pw', 'current_pw', 'required|callback_current_pw');
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $data = array(
            'password' => hash('sha256', $_POST['new_pw']),
        );
        if ($this->UserModel->update($_POST['id'], $data)) {
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response, 201);
        }
    }
}