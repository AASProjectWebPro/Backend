<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class TransaksiPeminjaman extends REST_Controller
    {
        function __construct()
        {
            parent::__construct();
            $this->load->database();
            $this->load->model('M_Peminjaman');
            //load book model
            $this->load->model('M_Buku');
            $this->load->model('M_bukuDipinjam');
            $this->load->library('form_validation');
        }
        function index_get(){
            ///get by id jwt payload
            $id = $this->get('id_buku');
            if ($id == ''){
                $data = $this->M_bukuDipinjam->fetch_all();
            }
            $this->response($data,200);
        }
        function index_post(){
            //validate user not in status "peminjam"
            //add with id from jwt
            //decrese book stock
            
        }
    }
