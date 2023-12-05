<?php

defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class User extends REST_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('UserModel');
        $this->load->library('form_validation');
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
        $this->form_validation->set_rules('nomor_telepon', 'Phone Number', 'required|trim|numeric|is_unique[user.nomor_telepon]');
        $this->form_validation->set_rules('alamat', 'Adress', 'required|trim');
    }
    function index_get()
    {
        $id = $this->get('id');
        if ($id == '') {
            $data = $this->UserModel->fetchAll();
        } else {
            $data = $this->UserModel->fetchSingleData($id);
        }
        $this->response($data, 200);
    }
    function index_post()
    {
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
    function index_put()
    {
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->validate();
        $this->form_validation->set_rules('id', 'ID', 'required|numeric');
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
        if($this->UserModel->updateData($this->put('id'),$data)){
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response,201);
        }
    }
    function index_delete()
    {
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID', 'required|numeric');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        if($this->UserModel->deleteData($this->put('id'))){
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response,201);
        }else{
            $response = array(
                'status' => 502,
                'message' => 'ID tidak ditemukan'
            );
            return $this->response($response,502);
        }
    }

}