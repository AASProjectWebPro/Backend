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
        header("Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT,DELETE,PATCH");
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            die();
        }
        $this->load->database();
        $this->load->model('UserModel');
        $this->load->library('jwt');
        $this->load->library('form_validation');
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
            if ($this->jwt->decodeUser($this->input->request_headers()['Authorization']) == false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }
    function validasiUnikBuatanHilmiUpdateUsername()
    {
        $query1= $this->db
            ->select('id')
            ->from('user')
            ->where('id',$_POST['id'])
            ->where('username',$_POST['username'])
            ->count_all_results();
        $query2= $this->db
            ->select('id')
            ->from('user')
            ->where('username',$_POST['username'])
            ->count_all_results();
        if ($query1 > 0 and $query2 > 0 or $query2 <= 0) {
            return true;
        }
        else {
            $this->form_validation->set_message('validasiUnikBuatanHilmiUpdateUsername', 'Username tersebut sudah dipakai silahkan ganti yang lain.');
            return false;
        }
    }
    function validasiUnikBuatanHilmiUpdateEmail()
    {
        $query1= $this->db
            ->select('id')
            ->from('user')
            ->where('id',$_POST['id'])
            ->where('email',$_POST['email'])
            ->count_all_results();
        $query2= $this->db
            ->select('id')
            ->from('user')
            ->where('email',$_POST['email'])
            ->count_all_results();
        if ($query1 > 0 and $query2 > 0 or $query2 <= 0) {
            return true;
        }
        else {
            $this->form_validation->set_message('validasiUnikBuatanHilmiUpdateEmail', 'Email tersebut sudah dipakai silahkan ganti yang lain.');
            return false;
        }
    }
    function validasiUnikBuatanHilmiUpdateNomor()
    {
        $query1= $this->db
            ->select('id')
            ->from('user')
            ->where('id',$_POST['id'])
            ->where('nomor_telepon',$_POST['nomor_telepon'])
            ->count_all_results();
        $query2= $this->db
            ->select('id')
            ->from('user')
            ->where('nomor_telepon',$_POST['nomor_telepon'])
            ->count_all_results();
        if ($query1 > 0 and $query2 > 0 or $query2 <= 0) {
            return true;
        }
        else {
            $this->form_validation->set_message('validasiUnikBuatanHilmiUpdateNomor', 'Nomor Telepon tersebut sudah dipakai silahkan ganti yang lain.');
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
    public function ifExistForgot($id)
    {
        if ($this->UserModel->ifExist($id)) {
            return true;
        } else {
            $this->form_validation->set_message('ifExistForgot', 'The Email field not found.');
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
        $this->form_validation->set_rules('username', 'Username', 'required|trim|callback_validasiUnikBuatanHilmiUpdateUsername');
        $this->form_validation->set_rules('email', 'Email', 'required|trim|valid_email|callback_validasiUnikBuatanHilmiUpdateEmail');
        $this->form_validation->set_rules('nomor_telepon', 'Phone Number', 'required|trim|numeric|max_length[16]|callback_validasiUnikBuatanHilmiUpdateNomor');
        $this->form_validation->set_rules('alamat', 'Adress', 'required|trim');
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
    public function forgot_post(){
        $email = $this->input->post('email');
        $_POST['id']=$this->UserModel->get_id_by_email($email);
        $this->form_validation->set_rules('id', 'email', 'required|callback_ifExistForgot');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $data = array(
            "email" => $email,
            "id" => $_POST['id']
        );
        $token = $this->jwt->encode($data);
        $this->load->library('email');
        $config = Array(
            'protocol' => 'smtp',
            'smtp_host' => 'sandbox.smtp.mailtrap.io',
            'smtp_port' => 2525,
            'smtp_user' => 'eb042361a0cb09',
            'smtp_pass' => '41346920e56aaf',
            'crlf' => "\r\n",
            'newline' => "\r\n"
        );
        $potongDuluGaSih = explode('.', $token);
        $headerPayload = $potongDuluGaSih[0].'.'.$potongDuluGaSih[1];
        $signature = $potongDuluGaSih[2];
        $this->email->initialize($config)
        ->from('your@example.com', 'Your Name')
        ->to('someone@example.com')
        ->cc('another@another-example.com')
        ->bcc('them@their-example.com')
        ->set_mailtype("html")
        ->subject('Email Test')
        ->message('
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Password Reset Request - LibraLyra</title>
                <style>
                    body {
                        font-family: "Poppins", sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #ef823f;
            
                    }
                    .container {
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        background-color: #ffffff;
                        border-radius: 10px;
                        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #ef823f;
                    }
                    p {
                        margin-bottom: 20px;
                    }
                    a {
                        color: #ef823f;
                        text-decoration: none;
                        font-weight: bold;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #ef823f;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                </style>
            </head>
            <body>
            <div class="container">
                <h1>Password Reset Request</h1>
                <p>We received a request to reset your password for your LibraLyra account. If you did not make this request, you can safely ignore this email.</p>
                <p>To reset your password, click the button below:</p>
                <p>
                    <a class="button" href="http://localhost/aaSProjectWebpro/Frontend-User/forgotChange.html?token='.$signature.'">Reset Password</a>
                </p>
                <p>
                    If the button above doesnt work, you can also copy and paste the following link into your web browser:
                    <br>
                    <a href="http://localhost/aaSProjectWebpro/Frontend-User/forgotChange.html?token='.$signature.'">http://localhost/aaSProjectWebpro/Frontend-User/forgotChange.html?token='.$signature.'</a>
                </p>
                <p>This link will expire in 1 hour for security reasons.</p>
                <p>If you have any questions or need further assistance, please dont hesitate to contact us at
                    <a href="#">support@libralyra.com</a>.
                </p>
                <p>Best regards,<br>LibraLyra Team</p>
            </div>
            </body>
            </html>
        ');
        if ($this->email->send()) {
            $data = array(
                'status' => 200,
                'token' => $headerPayload
            );
            $this->response($data, 200);
        }
    }
    public function forgotGanti_patch(){
        if (isset($this->input->request_headers()['Authorization'])) {
            if ($this->jwt->decodeForgot($this->input->request_headers()['Authorization']) == false) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'Link ini sudah tidak berfungsi.',
                        'data' => []
                    ), 401
                );
            }
        } else {
            return $this->response(
                array(
                    'kode' => '401',
                    'pesan' => 'Link ini sudah tidak berfungsi.',
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