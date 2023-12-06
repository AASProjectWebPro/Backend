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
            $this->load->library('form_validation');
        }
        function index_get(){
            ///get by id jwt payload
        }
        function index_post(){
            //add with id from jwt
            //decrese book stock
        }
    }
