<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/REST_Controller.php';

use Restserver\Libraries\REST_Controller;

class Buku extends REST_Controller
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

    public function options_get()
    {
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
        header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");
        exit();
    }

    function mengakaliFormValidationYangHanyaMendeteksiPostRequest()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $putData = $this->input->input_stream();
        $_POST = $putData;
    }

    function validasiUnikBuatanHilmiUpdateBuku()
    {
        $query1= $this->db
            ->select('id')
            ->from('buku')
            ->where('id',$_POST['id'])
            ->where('isbn',$_POST['isbn'])
            ->count_all_results();
        $query2= $this->db
            ->select('id')
            ->from('buku')
            ->where('isbn',$_POST['isbn'])
            ->count_all_results();
        if ($query1 > 0 and $query2 > 0 or $query2 <= 0) {
            return true;
        }
        else {
            $this->form_validation->set_message('validasiUnikBuatanHilmiUpdateBuku', 'ISBN tersebut sudah dipakai silahkan ganti yang lain.');
            return false;
        }
    }

    function validate()
    {

        $this->form_validation->set_rules('judul', 'Judul', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('pengarang', 'Pengarang', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('penerbit', 'Penerbit', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('tahun_terbit', 'Tahun Terbit', 'required|trim|min_length[4]|numeric');
        $this->form_validation->set_rules('jenis', 'Jenis', 'required|trim|min_length[4]');
        $this->form_validation->set_rules('deskripsi', 'Deskripsi', 'required|trim|min_length[5]');
        $this->form_validation->set_rules('stock', 'Stock', 'required|trim|numeric');
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
            $data = $this->M_Buku->fetch_all();
        } else {
            $data = $this->M_Buku->fetch_single_data($id);
        }
        $this->response($data, 200);
    }

    public function checkFileExtension($str)
    {
        if ($str === "jpg" or $str === "png") {
            return true;
        } else {
            $this->form_validation->set_message('checkFileExtension', 'Invalid file extension. Allowed extensions: jpg or png');
            return false;
        }
    }

    public function checkFileSize($str)
    {
        $max_file_size = 6 * 1024 * 1024;
        if ($str > $max_file_size) {
            $this->form_validation->set_message('checkFileSize', 'File size exceeds the allowed limit (6 MB).');
            return false;
        } else {
            return true;
        }
    }

    //file inputnya post ini menggunakan format content formdata-webkit jadi php bisa mendeteksi lgsg byte file request
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
        $this->form_validation->set_rules('isbn', 'Isbn', 'required|trim|is_unique[buku.isbn]');
        $this->validate();

        //validasi file:v
        if ($_FILES["file"]["name"] != '') {
            $_POST['file'] = $_FILES["file"]["name"];
            $_POST['file_extension'] = pathinfo($_POST['file'], PATHINFO_EXTENSION);
            $_POST['file_size'] = $_FILES["file"]["size"];
            $this->form_validation->set_rules('file_extension', 'File Extension', 'callback_checkFileExtension');
            $this->form_validation->set_rules('file_size', 'File Extension', 'callback_checkFileSize');
        } else {
            $this->form_validation->set_rules('file', 'File', 'required');
        }
        if ($this->form_validation->run() === false) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        //save file :v
        $filename = time() . '_' . uniqid() . "." . pathinfo($_FILES["file"]["name"], PATHINFO_EXTENSION);
        move_uploaded_file($_FILES["file"]["tmp_name"], FCPATH . '/upload/' . $filename);

        $data = array(
            'isbn' => trim($this->post('isbn')),
            'judul' => $this->post('judul'),
            'pengarang' => $this->post('pengarang'),
            'penerbit' => $this->post('penerbit'),
            'tahun_terbit' => trim($this->post('tahun_terbit')),
            'jenis' => $this->post('jenis'),
            'deskripsi' => $this->post('deskripsi'),
            'stock' => $this->post('stock'),
            'gambar' => $filename
        );

        if ($this->M_Buku->insert_api($data)) {
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response, 201);
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

    //file inputnya put ini menggunakan format content x-www jadi harus bs64 karena php tidak otomatis response $_FILE kalau methodnya put
    function index_put()
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
        if (isset($_POST["file"])) {
            //proses validasi file versi bs64
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['file']));
            $_POST["file_size"] = strlen($data);
            $this->form_validation->set_rules('file_size', 'File Extension', 'callback_checkFileSize');
            $_POST['file_extension'] = pathinfo($_POST['file_name'], PATHINFO_EXTENSION);
            $this->form_validation->set_rules('file_extension', 'File Extension', 'callback_checkFileExtension');
        }

        $this->form_validation->set_rules('isbn', 'Isbn', 'required|trim|callback_validasiUnikBuatanHilmiUpdateBuku');
        $this->validate();
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist');
        if ($this->form_validation->run() === false) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }

        //proses simpan file versi bs64
        if (isset($_POST["file"])) {
            $data = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $_POST['file']));
            $filename = time() . '_' . uniqid() . "." . pathinfo($_POST['file_name'], PATHINFO_EXTENSION);
            file_put_contents(FCPATH . '/upload/' . $filename, $data);
        }

        $data = array(
            'isbn' => trim($this->put('isbn')),
            'judul' => $this->put('judul'),
            'pengarang' => $this->put('pengarang'),
            'penerbit' => $this->put('penerbit'),
            'tahun_terbit' => $this->put('tahun_terbit'),
            'jenis' => $this->put('jenis'),
            'deskripsi' => $this->put('deskripsi'),
            'stock' => $this->put('stock'),
        );
        if (isset($_POST["file"])){
            $data['gambar']=$filename;
        }

        if ($this->M_Buku->update_data($this->put('id'), $data)) {
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response, 201);
        }
    }

    function checkBukuExistOnPeminjam($id)
    {
        if (!($this->M_Buku->checkBukuExistOnPeminjam($id))) {
            return true;
        } else {
            $this->form_validation->set_message('checkBukuExistOnPeminjam', 'This book is still on loan.');
            return false;
        }
    }

    function index_delete()
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
        $this->form_validation->set_rules('id', 'ID', 'required|callback_ifExist|callback_checkBukuExistOnPeminjam');
        if ($this->form_validation->run() === false) {
            $error_array = $this->form_validation->error_array();
            $response = array(
                'status' => 502,
                'message' => $error_array
            );
            return $this->response($response, 502);
        }
        if ($this->M_Buku->delete_data($this->delete('id'))) {
            $response = array(
                'status' => 201,
                'message' => 'Succes'
            );
            return $this->response($response, 201);
        }
    }
}