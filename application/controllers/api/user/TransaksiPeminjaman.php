<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class TransaksiPeminjaman extends REST_Controller
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
            $this->load->model('M_Peminjaman');
            //load book model
            $this->load->model('M_Buku');
            $this->load->model('M_bukuDipinjam');
            $this->load->library('form_validation');
            $this->load->library('jwt');
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

        function get_stock_by_id($id)
        {
            if ($this->M_Buku->get_stock_by_id($id) > 0) {
                return true;
            } else {
                $this->form_validation->set_message('get_stock_by_id', 'The buku is out of stock.');
                return false;
            }
        }

        public function ifExist($id)
        {
            if ($this->M_Buku->ifExist($id)) {
                return true;
            } else {
                $this->form_validation->set_message('ifExist', 'The ID field not found.');
                return false;
            }
        }


        function index_get()
        {
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
            $jwt = explode("Bearer ", $this->input->request_headers()['Authorization']);
            $_POST['id_user'] = json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
            $this->form_validation->set_rules('id_user', 'ID User', 'numeric');
            if ($this->form_validation->run() === false) {
                $error_array = $this->form_validation->error_array();
                $response = array(
                    'status' => 502,
                    'message' => 'ID tidak valid.'
                );
                return $this->response($response, 502);
            }
            $data = array(
                'status'=>$this->M_Peminjaman->getStatusPeminjamUser($_POST['id_user'])
            );
            return $this->response($data, 200);
        }
        function index_post()
        {
            if (!$this->authorization()) {
                return $this->response(
                    array(
                        'kode' => '401',
                        'pesan' => 'Unauthorized',
                        'data' => []
                    ), 401
                );
            }
            $jwt = explode("Bearer ", $this->input->request_headers()['Authorization']);
            $_POST['id_user'] = json_decode(base64_decode(explode('.', $jwt[1])[1]))->data->id;
            $this->form_validation->set_rules('id_user', 'ID User', 'numeric|is_unique[transaksi_peminjaman.id_user]');
            $this->form_validation->set_rules('id_buku', 'ID Buku', 'numeric|callback_ifExist|callback_get_stock_by_id');
            if ($this->form_validation->run() === false) {
                $error_array = $this->form_validation->error_array();
                $response = array(
                    'status' => 502,
                    'message' => $error_array
                );
                return $this->response($response, 502);
            }
            $data = array(
                'id_user' => $_POST['id_user'],
                'id_buku' => $_POST['id_buku'],
                'status' => 'pending',
                'tanggal_peminjaman' => date('Y-m-d')
            );
            if ($this->M_Peminjaman->insert_api($data)) {
//                $stockBukuSaatIni = $this->M_Buku->get_stock_by_id($_POST['id_buku']);
//                $kurangiSatuStockBuku = $this->M_Buku->update_data($_POST['id_buku'], array('stock' => $stockBukuSaatIni - 1));
                //ganti logic karena di challange pak resa user hanya bisa status pending
                $response = array(
                    'status' => 201,
                    'message' => 'Success'
                );
                return $this->response($response, 201);
            }
        }
    }

