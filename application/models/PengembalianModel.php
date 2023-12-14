<?php
class PengembalianModel extends CI_Model
{
    protected $table = "history_pengembalian";
    function fetch_all()
    {
        $this->db->select('history_pengembalian.id, user.username,user.email, history_pengembalian.isbn_buku,history_pengembalian.judul, history_pengembalian.tanggal_peminjaman,history_pengembalian.tanggal_pengembalian');
        $this->db->from('history_pengembalian');
        $this->db->join('user', 'history_pengembalian.id_user = user.id');

        $query = $this->db->get();
        return $query->result_array();
    }
    function checkHistory($id){
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
    function read($id = '')
    {
        if ($id) {
            $this->db->where("id", $id);
        }
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    function readUser($id = '')
    {
        if ($id) {
            $this->db->where("id_user", $id);
        }
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }
    function insert($data)
    {
        $this->db->insert($this->table, $data);
        if ($this->db->affected_rows()) {
            return true;
        } else {
            return false;
        }
    }
    function delete($id)
    {
        $this->db->where("id", $id);
        $this->db->delete($this->table);
        if ($this->db->affected_rows() > 0) {
            return true;
        } else {
            return false;
        }
    }
}