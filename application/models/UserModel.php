<?php


class UserModel extends CI_Model
{
    protected $table = "user";
    function ifExist($id){
        $this->db->where('id', $id);
        $query = $this->db->get($this->table);
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
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