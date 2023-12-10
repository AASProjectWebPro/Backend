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
        $this->load->library('jwt');
        $this->load->model('M_Buku');
        $this->load->library('form_validation');
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
        $this->form_validation->set_rules('isbn', 'Isbn' ,'required|trim|is_unique[buku.isbn]');
        $this->form_validation->set_rules('judul', 'Judul', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('pengarang', 'Pengarang', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('penerbit', 'Penerbit', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('tahun_terbit', 'Tahun Terbit', 'required|trim|min_length[4]|numeric');
        $this->form_validation->set_rules('jenis', 'Jenis', 'required|trim|min_length[4]');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('stock', 'Stock', 'required|trim|numeric');
    }

    function index_get()
    {
        if(isset($this->input->request_headers()['Authorization'])){
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
        if ($id == '')
        {
            $data = $this->M_Buku->fetch_all();
        }else{
            $data = $this->M_Buku->fetch_single_data($id);
        }
        $this->response($data, 200);
    }

    function index_post()
    {
        if(isset($this->input->request_headers()['Authorization'])){
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
        if($this->form_validation->run() === false){
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        $data = array(
            'isbn' => $this->post('isbn'),
            'judul' => $this->post('judul'),
            'pengarang' => $this->post('pengarang'),
            'penerbit' => $this->post('penerbit'),
            'tahun_terbit' => trim($this->post('tahun_terbit')),
            'jenis' => $this->post('jenis'),
            'deskripsi' => $this->post('deskripsi'),
            'stock' => $this->post('stock')
        );

        if($this->M_Buku->insert_api($data)){
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response,201);
        }
    }
    public function ifExist($id)
    {
        if ($this->M_Buku->check_data($id)) {
            return true;
        } else {
            $this->form_validation->set_message('ifExist', 'The ID field not found.');
            return false;
        }
    }
    
    function index_put(){
        if(isset($this->input->request_headers()['Authorization'])){
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
        $this->validate();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        if($this->form_validation->run() === false){
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        $data = array(
            'isbn' => $this->put('isbn'),
            'judul' => $this->put('judul'),
            'pengarang' => $this->put('pengarang'),
            'penerbit' => $this->put('penerbit'),
            'tahun_terbit' => $this->put('tahun_terbit'),
            'jenis' => $this->put('jenis'),
            'deskripsi' =>$this->put('deskripsi'),
            'stock' => $this->put('stock')
        );

        if($this->M_Buku->update_data($this->put('id'),$data)){
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response,201);
        }
    }
    function checkBukuExistOnPeminjam($id){
        if(!($this->M_Buku->checkBukuExistOnPeminjam($id))){
            return true;
        } else {
            $this->form_validation->set_message('checkBukuExistOnPeminjam', 'This book is still on loan.');
            return false;
        }
    }
    function index_delete(){
        if(isset($this->input->request_headers()['Authorization'])){
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
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist|callback_checkBukuExistOnPeminjam');
        if($this->form_validation->run() === false){
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response,502);
        }
        if($this->M_Buku->delete_data($this->delete('id'))){
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response,201);
        }
    }
}