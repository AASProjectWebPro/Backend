<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class transaksi_peminjaman extends REST_Controller {
        function __construct($config = 'rest'){
            parent::__construct($config);
            header('Access-Control-Allow-Origin:*');
            header("Access-Control-Allow-Headers:X-API-KEY,Origin,X-Requested-With,Content-Type,Accept,Access-Control-Request-Method,Authorization");
            header("Access-Control-Allow-Methods:GET,POST,OPTIONS,PUT,DELETE");
            $method = $_SERVER['REQUEST_METHOD'];
            if ($method == "OPTIONS") {
                die();
            }
            $this->load->database();//optional
            $this->load->model('M_Peminjaman');
            $this->load->library('form_validation');
        }
        function fetch_all()
        {
            $this->db->order_by('id', 'DESC');

            $query = $this->db->get('transaksi_peminjaman');

            return $query->result_array();
        }
        function fetch_single_data($id)
        {
            $this->db->where("id", $id);
            $query = $this->db->get('transaksi_peminjaman');

            return $query->row();
        }
        public function options_get() {
            header("Access-Control-Allow-Origin: *");
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
           header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
            exit();
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
            if ($this->post('id_user') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'id_user',
                    'message' => 'Isian id_user tidak boleh kosong!',
                );

                return $this->response($response);
            }
            if ($this->post('id_buku') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'id_bukus',
                    'message' => 'Isian id_buku tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            if ($this->post('tanggal_peminjaman') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'tanggal_peminjaman',
                    'message' => 'Isian tanggal_peminjaman tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            if ($this->post('tanggal_pengembalian') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'tanggal_pengembalian',
                    'message' => 'Isian tanggal_pengembalian tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            if ($this->post('status') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'status',
                    'message' => 'Isian status tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }

     
            $data = array(
            'id_user' => $this->post('id_user'),
            'id_buku' => trim($this->post('id_buku')),
            'tanggal_peminjaman' => trim($this->post('tanggal_peminjaman')),
            'tanggal_pengembalian' => trim($this->post('tanggal_pengembalian')),
            'status' => trim($this->post('status')),
            );
            $this->M_Peminjaman->insert_api($data);
            $last_row = $this->db->select('*')->order_by('id',"desc")->limit(1)->get('transaksi_peminjaman')->row();
            $response = array(
                'status' => 'succes',
                'data'=> $last_row,
                'status_code' => 201,
            );
            
            return $this->response($response);
        }
        function index_put()
        {
            $id = $this->put('id');
            $check = $this->M_Peminjaman->check_data($id);
            if ($check == false){
                $error = array(
                    'status' => 'fail',
                    'field' => 'id',
                    'message' => 'Data tidak ditemukan!',
                    'status_code' => 502
                );

                return $this->response($error);
            }
            if ($this->put('id_user') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'id_user',
                    'message' => 'Isian id_user tidak boleh kosong YAA!',
                    'status_code' => 502
                );

                return $this->response($response);
            }

            if ($this->put('id_buku') == ''){
                $response = array(
                    'status' => 'fail',
                    'field' => 'id_user',
                    'message' => 'Isian id_user tidak boleh kosong YAA!',
                    'status_code' => 502
                );

                 return $this->response($response);
            }
            if ($this->put('tanggal_peminjaman') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'tanggal_peminjaman',
                    'message' => 'Isian tanggal_peminjaman tidak boleh kosong YAA!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            if ($this->put('tanggal_pengembalian') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'tanggal_pengembalian',
                    'message' => 'Isian tanggal_pengembalian tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            if ($this->put('status') == '') {
                $response = array(
                    'status' => 'fail',
                    'field' => 'status',
                    'message' => 'Isian status tidak boleh kosong!',
                    'status_code' => 502
                );

                return $this->response($response);
            }
            $data = array(
                'id_user' => $this->put('id_user'),
                'id_buku' => trim($this->put('id_buku')),
                'tanggal_peminjaman' => trim($this->put('tanggal_peminjaman')),
                'tanggal_pengembalian' => trim($this->put('tanggal_pengembalian')),
                'status' => trim($this->put('status'))
            );

            $this->M_Peminjaman->update_data($id,$data);
            $newData = $this->M_Peminjaman->fetch_single_data($id);
            $response = array(
                'status' => 'success',
                'data' => $newData,
                'status_code' => 200,
            );

            return $this->response($response);
        }
        function index_delete() {
            $id = $this->delete('id');
            $check = $this->M_Peminjaman->check_data($id);
            if ($check == false){
                $error = array(
                    'status' => 'fail',
                    'field' => 'id',
                    'message' => 'Data tidak ditemukan!',
                    'status_code' => 502
                );

                return $this->response($error);
            }
            $delete = $this->M_Peminjaman->delete_data($id);
            $response = array(
                'status' => 'success',
                'data' => null,
                'status_code' => 200,
            );

            return $this->response($response);
        }   
    }
?>