<?php


class UserModel extends CI_Model
{
    protected $table = "user";
    function getPasswordUser($id) {
        $this->db->select('password');
        $this->db->where('id', $id);
        $query = $this->db->get("user");
        if ($query->row()) {
            return $query->row()->password;
        } else {
            return false;
        }
    }
    function ifExist($id){
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
    function get_id_by_email($email)
    {
        $this->db->where('email', $email);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            return $query->row()->id;
        }
    }
    function get_name_by_id($email)
    {
        $this->db->where('id', $email);
        $query = $this->db->get($this->table);

        if ($query->num_rows() > 0) {
            return $query->row()->username;
        }
    }
    function read($id='')
    {
        if($id){
            $this->db->where("id", $id);
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
    function checkUserExistOnPeminjam($user_id){
        $this->db->where('id_user', $user_id);
        $query = $this->db->get("transaksi_peminjaman");
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
    function update($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
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