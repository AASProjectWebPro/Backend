<?php

    defined('BASEPATH') OR exit('No direct sript access allowed');

    require APPPATH . '/libraries/REST_Controller.php';
    use Restserver\Libraries\REST_Controller;

    class Dashboard extends REST_Controller
    {
        function __construct()
        {
            parent::__construct();
            $this->load->database();
            // load dashboard model
            $this->load->model('DashboardModel');
            $this->load->library('form_validation');
        }

        function history_get(){
            $data = $this->DashboardModel->getCounthistory();
            return $this->response($data, 200);
        }
        function user_get(){
            $data = $this->DashboardModel->getCountuser();
            return $this->response($data,200);
        }
        function buku_get(){
            $data = $this->DashboardModel->getCountbuku();
            return $this->response($data,200);
        }
        function transaksi_get(){
            $data = $this->DashboardModel->getCountTransaksi();
            return $this->response($data,200);
        }
    }

?>