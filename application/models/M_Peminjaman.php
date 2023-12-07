<?php
    class M_Peminjaman extends CI_Model
    {
        function fetch_all()
        {
            $query = $this->db->get('transaksi_peminjaman');
            return $query->result_array();
        }
        function check_id_user($id)
        {
            $this->db->where("id", $id);
            $query = $this->db->get('user');

            if ($query->row()) {
                return true;
            } else {
                return false;
            }
        }

        function check_id_transaksi($id)
        {
            $this->db->where("id", $id);
            $query = $this->db->get('transaksi_peminjaman');

            if ($query->row()) {
                return true;
            } else {
                return false;
            }
        }
        function check_id_buku($id)
        {
            $this->db->where("id", $id);
            $query = $this->db->get('buku');

            if ($query->row()) {
                return true;
            } else {
                return false;
            }
        }
        function fetch_single_data($id)
        {
            $this->db->where('id',$id);
            $query = $this->db->get('transaksi_peminjaman');
            return $query->result_array();
        }
        function insert_api($data)
        {
            $this->db->insert('transaksi_peminjaman', $data);
            if ($this->db->affected_rows() > 0){
                return true;
            } else {
                return false;
            }
        }
        function update_data($id, $data)
        {
            $this->db->where("id", $id);
            $this->db->update("transaksi_peminjaman", $data);
        }
        function delete_data($id)
        {
            $this->db->where("id", $id);
            $this->db->delete("transaksi_peminjaman");
            if ($this->db->affected_rows() > 0){
                return true;
            } else {
                return false;
            }
        }
    }
?>