<?php class M_Buku extends CI_Model
{
    function fetch_all()
    {
        $this->db->order_by('id','ASC');
        $query = $this->db->get('buku');
        return $query->result_array();
    }
    function get_stock_by_id($book_id)
    {
        $this->db->where('id', $book_id);
        $query = $this->db->get('buku');

        if ($query->num_rows() > 0) {
            return $query->row()->stock;
        }
    }
    function fetch_single_data($id)
    {
        $this->db->where('id',$id);
        $query = $this->db->get('buku');
        return $query->row();
    }
    function check_data($id)
    {
        $this->db->where('id',$id);
        $query = $this->db->get('buku');

        if($query->row())
        {
            return true;
        }else{
            return false;
        }
    }
    function insert_api($data)
    {
        $this->db->insert('buku', $data);
        if($this->db->affected_rows() > 0)
        {
            return true;
        }else {
            return false;
        }
    }
    function update_data($id,$data){
        $this->db->where('id',$id);
        $this->db->update('buku',$data);
        if($this->db->affected_rows()){
            return true;
        } else {
            return false;
        }
    }
    function delete_data($id){
        $this->db->where('id',$id);
        $this->db->delete('buku');
        if($this->db->affected_rows() > 0){
            return true;
        }else{
            return false;
        }
    }
}