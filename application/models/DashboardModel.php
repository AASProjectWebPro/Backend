<?php

    class DashboardModel extends CI_Model
    {
        protected $table_history = 'history_pengembalian';
        protected $table_user = 'user';
        protected $table_buku = 'buku';
        protected $table_transaksi = 'transaksi_peminjaman';

        function getCounthistory(){
            return $this->db->count_all($this->table_history);
        }
        function getCountuser(){
            return $this->db->count_all($this->table_user);
        }
        function getCountbuku(){
            return $this->db->count_all($this->table_buku);
        }
        function getCountTransaksi(){
            return $this->db->count_all($this->table_transaksi);
        }
    }
?>