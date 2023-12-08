<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
require_once FCPATH.'vendor/autoload.php';
use Restserver\Libraries\REST_Controller;

class Auth extends REST_Controller
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
        $this->load->library('jwt');
        $this->load->database();
        $this->load->model('AuthModel');
    }
    public function options_get() {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        exit();
    }
    public function login_post()
    {
        $password = $this->input->post('password');
        $email = $this->input->post('email');
        if (hash('sha256', $password) == $this->AuthModel->getPassword($email)) {
            $data = array(
                "email" => $email,
                "role" => "admin"
            );
            $token = $this->jwt->encode($data);
            $data = array(
                'status' => 200,
                'massage' => 'Berhasil login',
                'token' => $token
            );
            $this->response($data, 200);
        } else {
            $data = array(
                'status' => 401,
                'message' => 'Email atau password salah',
            );
            $this->response($data, 401);
        }
    }
}