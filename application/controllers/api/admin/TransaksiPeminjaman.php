<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class TransaksiPeminjaman extends REST_Controller {
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
        function get_stock_by_id($id){
            if ($this->M_Buku->get_stock_by_id($id)>=0) {
                return true;
            } else {
                $this->form_validation->set_message('get_stock_by_id', 'The buku is out of stock.');
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
        function peminjaman_post()
        {
            $this->form_validation->set_rules('id_user', 'ID User', 'numeric|callback_check_id_user|is_unique[transaksi_peminjaman.id_user]');
            $this->form_validation->set_rules('id_buku', 'ID Buku', 'numeric|callback_check_id_buku|callback_get_stock_by_id');
            $this->form_validation->set_rules('tanggal_peminjaman', 'Tanggal Peminjaman', 'required|date');
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
            'tanggal_peminjaman' => $this->post('tanggal_peminjaman')
            );
            $stockBukuSaatIni=$this->M_Buku->get_stock_by_id($this->post('id_buku'));
            $kurangiSatuStockBuku=$this->M_Buku->update_data($this->post('id_buku'),array('stock' => $stockBukuSaatIni-1));
            if($this->M_Peminjaman->insert_api($data)){
                $response = array(
                    'status' => 201,
                    'message' => 'Success'
                );
                return $this->response($response,201);
            }
        }
        function pengembalian_delete() {
            $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
            $this->form_validation->set_rules('tanggal_pengembalian', 'Tanggal Pengembalian', 'required|date');
            $this->form_validation->set_rules('id', 'ID Transaksi', 'numeric|callback_check_id_transaksi');
            if ($this->form_validation->run() === FALSE) {
                $error_array = $this->form_validation->error_array();
                $response = array(
                    'status' => 502,
                    'message' => $error_array
                );
                return $this->response($response,502);
            }
            $dataTmp =$this->M_Peminjaman->fetch_single_data($this->delete('id'));
            $data = array(
                'id_user' => $dataTmp[0]['id_user'],
                'id_buku' => $dataTmp[0]['id_buku'],
                'tanggal_peminjaman' => $dataTmp[0]['tanggal_peminjaman'],
                'tanggal_pengembalian' => $this->delete('tanggal_pengembalian')
            );
            $delete = $this->M_Peminjaman->delete_data($this->delete('id'));
            $stockBukuSaatIni=$this->M_Buku->get_stock_by_id($dataTmp[0]['id_buku']);
            $TambahSatuStockBuku=$this->M_Buku->update_data($dataTmp[0]['id_buku'],array('stock' => $stockBukuSaatIni+1));
            $PengembalianBuku = $this->PengembalianModel->insert($data);
            $response = array(
                'status' => 'success',
                'status_code' => 200,
            );
            return $this->response($response,200);
        }   
    }
?>