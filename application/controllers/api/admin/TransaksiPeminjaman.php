<?php

defined('BASEPATH') or exit('No direct sript access allowed');

require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class TransaksiPeminjaman extends REST_Controller
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
        $this->load->database();
        $this->load->model('UserModel');
        $this->load->model('M_Buku');
        $this->load->model('M_Peminjaman');
        $this->load->model('PengembalianModel');
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
            if ($this->jwt->decodeAdmin($this->input->request_headers()['Authorization']) == false) {
                return false;
            } else {
                return true;
            }
        } else {
            return false;
        }
    }

    function mengakaliFormValidationYangHanyaMendeteksiPostRequest()
    {
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

    function get_stock_by_id($id)
    {
        if ($this->M_Buku->get_stock_by_id($id) >= 0) {
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

    function checkISBN($isbn)
    {
        if ($this->M_Peminjaman->checkISBN($isbn)) {
            return true;
        } else {
            $this->form_validation->set_message('checkISBN', 'The book isbn not found.');
            return false;
        }
    }

    function checkUSERNAME($username)
    {
        if ($this->M_Peminjaman->checkUSERNAME($username)) {
            return true;
        } else {
            $this->form_validation->set_message('checkUSERNAME', 'The username not found.');
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
        $id = $this->get('id');
        if ($id == '') {
            $data = $this->M_Peminjaman->fetch_all();
        } else {
            $data = $this->M_Peminjaman->fetch_single_data($id);
        }
        $this->response($data, 200);
    }

    function peminjaman_post()
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
        $this->form_validation->set_rules('username', 'ID User', 'required|callback_checkUSERNAME');
        $this->form_validation->set_rules('isbn', 'ID Buku', 'required|callback_checkISBN');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $_POST['id_user'] = $this->M_Peminjaman->checkUSERNAME($this->post('username'));
        $_POST['id_buku'] = $this->M_Peminjaman->checkISBN($this->post('isbn'));
        $this->form_validation->set_rules('id_user', 'ID User', 'numeric|callback_check_id_user|is_unique[transaksi_peminjaman.id_user]');
        $this->form_validation->set_rules('id_buku', 'ID Buku', 'numeric|callback_check_id_buku|callback_get_stock_by_id');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        //"The ID User field must contain a unique value." berarti "user masih meminjam buku"
        $data = array(
            'id_user' => $_POST['id_user'],
            'id_buku' => $_POST['id_buku'],
            'tanggal_peminjaman' => date('Y-m-d')
        );
        $stockBukuSaatIni = $this->M_Buku->get_stock_by_id($_POST['id_buku']);
        $kurangiSatuStockBuku = $this->M_Buku->update_data($_POST['id_buku'], array('stock' => $stockBukuSaatIni - 1));
        if ($this->M_Peminjaman->insert_api($data)) {
            $response = array(
                'status' => 201,
                'message' => 'Success'
            );
            return $this->response($response, 201);
        }
    }

    function pengembalian_delete()
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
        $this->mengakaliFormValidationYangHanyaMendeteksiPostRequest();
        $this->form_validation->set_rules('id', 'ID Transaksi', 'numeric|callback_check_id_transaksi');
        if ($this->form_validation->run() === FALSE) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        $dataTmp = $this->M_Peminjaman->fetch_single_data($this->delete('id')); //select * from peminjaman where id untuk ditambahkan ke histori
        $isbn = $this->M_Buku->get_isbn_by_id($dataTmp[0]['id_buku']);
        $judul = $this->M_Buku->get_judul_by_id($dataTmp[0]['id_buku']);
        $data = array(
            'id_user' => $dataTmp[0]['id_user'],
            'isbn_buku' => $isbn,
            'judul' => $judul,
            'tanggal_peminjaman' => $dataTmp[0]['tanggal_peminjaman'],
            'tanggal_pengembalian' => date('Y-m-d')
        );
        $delete = $this->M_Peminjaman->delete_data($this->delete('id'));
        $stockBukuSaatIni = $this->M_Buku->get_stock_by_id($dataTmp[0]['id_buku']);
        $TambahSatuStockBuku = $this->M_Buku->update_data($dataTmp[0]['id_buku'], array('stock' => $stockBukuSaatIni + 1));
        $PengembalianBuku = $this->PengembalianModel->insert($data);
        $response = array(
            'status' => 'success',
            'status_code' => 200,
        );
        return $this->response($response, 200);
    }
}

?>