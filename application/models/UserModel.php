<?php


class UserModel extends CI_Model
{
    protected $table = "user";

    function fetchAll()
    {
        $this->db->order_by('id', 'DESC');
        $query = $this->db->get($this->table);
        return $query->result_array();
    }

    function fetchSingleData($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get($this->table);
        return $query->row();
    }

    function checkId($id)
    {
        $this->db->where("id", $id);
        $query = $this->db->get($this->table);
        if ($query->row()) {
            return true;
        } else {
            return false;
        }
    }


    function getInsertData()
    {
        return $this->db->select('*')->order_by('id', 'desc')->limit(1)->get($this->table)->row();
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

    function updateData($id, $data)
    {
        $this->db->where('id', $id);
        $this->db->update($this->table, $data);
        if ($this->db->affected_rows()) {
            return true;
        } else {
            return false;
        }
    }

    function deleteData($id)
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