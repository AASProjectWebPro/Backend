<?php 
defined('BASEPATH') OR exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class buku extends REST_Controller
{
    function __construct($config = 'rest')
    {
        parent::__construct($config);
        $this->load->database('');
        $this->load->model('M_Buku');
        $this->load->library('form_validation');
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
        $this->form_validation->set_rules('pengarang', 'Pengarang', 'trim|min_length[5]');
        $this->form_validation->set_rules('penerbit', 'Penerbit', 'trim|min_length[5]');
        $this->form_validation->set_rules('tahun_terbit', 'Tahun Terbit', 'trim|min_length[4]|numeric');
        $this->form_validation->set_rules('jenis', 'Jenis', 'required|trim|min_length[4]');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'trim|min_length[5]');
        $this->form_validation->set_rules('stock', 'Stock', 'trim|numeric');
    }

    function index_get()
    {
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
    function index_delete(){
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        if($this->M_Buku->delete_data($this->delete('id'))){
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response,201);
        }
    }
}