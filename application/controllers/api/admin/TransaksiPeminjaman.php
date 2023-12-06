<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class TransaksiPeminjaman extends REST_Controller {
        function __construct($config = 'rest'){
            parent::__construct($config);
            $this->load->database();
            $this->load->model('M_Peminjaman');
            $this->load->library('form_validation');
        }
        function mengakaliFormValidationYangHanyaMendeteksiPostRequest(){
            $_SERVER['REQUEST_METHOD'] = 'POST';
            $putData = $this->input->input_stream();
            $_POST = $putData;
        }

        function check_id_user($id)
        {
            if ($this->M_Peminjaman->check_id_user($id)) {
                return true;
            } else {
                $this->form_validation->set_message('check_id_user', 'The ID User field not found.');
                return false;
            }
        }
        function check_id_buku($id)
        {
            if ($this->M_Peminjaman->check_id_buku($id)) {
                return true;
            } else {
                $this->form_validation->set_message('check_id_buku', 'The ID Buku field not found.');
                return false;
            }
        }
        function check_id_transaksi($id)
        {
            if ($this->M_Peminjaman->check_id_transaksi($id)) {
                return true;
            } else {
                $this->form_validation->set_message('check_id_transaksi', 'The ID Transaksi field not found.');
                return false;
            }
        }
       function validatePengembalian(){
            if (isset($_POST["status"])) {
                if ($_POST["status"] == "Pengembalian") {
                    $this->form_validation->set_rules('tanggal_pengembalian', 'Tanggal Pengembalian', 'required|date');
                }
            }
        }
        function validate()
        {
            $this->form_validation->set_rules('id_user', 'ID User', 'numeric|callback_check_id_user');
            $this->form_validation->set_rules('id_buku', 'ID Buku', 'numeric|callback_check_id_buku');
            $this->form_validation->set_rules('tanggal_peminjaman', 'Tanggal Peminjaman', 'required|date');
            $this->form_validation->set_rules('status', 'Status', 'required|in_list[Peminjaman,Pengembalian]');
        }

        function index_get()
        {
            $id = $this->get('id');
            if ($id == ''){
                $data = $this->M_Peminjaman->fetch_all();
            } else {
                $data = $this->M_Peminjaman->fetch_single_data($id);
            }
            $this->response($data, 200);
        }
        function index_post()
        {
            $this->validatePengembalian();
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
            'id_user' => $this->post('id_user'),
            'id_buku' => $this->post('id_buku'),
            'tanggal_peminjaman' => $this->post('tanggal_peminjaman'),
            'status' => $this->post('status'),
            'tanggal_pengembalian'=>null
            );
            if ($_POST["status"] == "Pengembalian"){
                $data['tanggal_pengembalian'] = $this->post('tanggal_pengembalian');
            }
            if($this->M_Peminjaman->insert_api($data)){
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
            $this->form_validation->set_rules('id', 'ID Transaksi', 'numeric|callback_check_id_transaksi');
            $this->validate();
            $this->validatePengembalian();
            if ($this->form_validation->run() === FALSE) {
                $error_array = $this->form_validation->error_array();
                $response = array(
                    'status' => 502,
                    'message' => $error_array
                );
                return $this->response($response,502);
            }
            $data = array(
                'id_user' => $this->put('id_user'),
                'id_buku' => $this->put('id_buku'),
                'tanggal_peminjaman' => $this->put('tanggal_peminjaman'),
                'status' => $this->put('status'),
                'tanggal_pengembalian'=>null
            );
            if ($_POST["status"] == "Pengembalian"){
                $data['tanggal_pengembalian'] = $this->put('tanggal_pengembalian');
            }
            $id = $this->put('id');
            $this->M_Peminjaman->update_data($id,$data);
            $response = array(
                'status' => 'success',
                'status_code' => 201,
            );
            return $this->response($response,201);
        }
        function index_delete() {
            $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
            $this->form_validation->set_rules('id', 'ID Transaksi', 'numeric|callback_check_id_transaksi');
            if ($this->form_validation->run() === FALSE) {
                $error_array = $this->form_validation->error_array();
                $response = array(
                    'status' => 502,
                    'message' => $error_array
                );
                return $this->response($response,502);
            }
            $delete = $this->M_Peminjaman->delete_data($id = $this->delete('id'));
            $response = array(
                'status' => 'success',
                'status_code' => 200,
            );
            return $this->response($response,200);
        }   
    }
?>