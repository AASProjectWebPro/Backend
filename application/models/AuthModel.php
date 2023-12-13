<?php
class AuthModel extends CI_Model{
    function getPasswordAdmin($email) {
        $this->db->select('password');
        $this->db->where('email', $email);
        $query = $this->db->get("admin");
        if ($query->row()) {
            return $query->row()->password;
        } else {
            return false;
        }
    }
    function getPasswordUser($email) {
        $this->db->select('password');
        $this->db->where('email', $email);
        $query = $this->db->get("user");
        if ($query->row()) {
            return $query->row()->password;
        } else {
            return false;
        }
    }
}