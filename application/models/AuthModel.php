<?php
class AuthModel extends CI_Model{
    protected $table = "admin";
    function getPassword($email) {
        $this->db->select('password');
        $this->db->where('email', $email);
        $query = $this->db->get($this->table);
        if ($query->row()) {
            return $query->row()->password;
        } else {
            return false;
        }
    }
}