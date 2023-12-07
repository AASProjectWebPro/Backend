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
    function index_get(){
        ///get all book
    }

}