<?php class M_Buku extends CI_Model
{
    function __construct()
    {
        $this->load->helper('url');
    }
    function ifExist($id){
        $this->db->where('id', $id);
        $query = $this->db->get('buku');
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
    function fetch_all()
    {
        $this->db->order_by('id', 'ASC');
        $query = $this->db->get('buku');
        $result = $query->result_array();

        foreach ($result as &$row) {
            $row['gambar'] = base_url().'/upload/' . $row['gambar'];
        }
        return $result;
    }
    function get_stock_by_id($book_id)
    {
        $this->db->where('id', $book_id);
        $query = $this->db->get('buku');

        if ($query->num_rows() > 0) {
            return $query->row()->stock;
        }
    }
    function checkBukuExistOnPeminjam($buku){
        $this->db->where('id_buku', $buku);
        $query = $this->db->get("transaksi_peminjaman");
        if ($query->num_rows() > 0) {
            return true;
        }
        return false;
    }
    function get_isbn_by_id($book_id)
    {
        $this->db->where('id', $book_id);
        $query = $this->db->get('buku');

        if ($query->num_rows() > 0) {
            return $query->row()->isbn;
        }
    }
    function get_judul_by_id($book_id)
    {
        $this->db->where('id', $book_id);
        $query = $this->db->get('buku');

        if ($query->num_rows() > 0) {
            return $query->row()->judul;
        }
    }
    function fetch_single_data($id)
    {
        $this->db->where('id',$id);
        $query = $this->db->get('buku');
        $result = $query->row_array();
        $result['gambar'] = base_url().'/upload/' . $result['gambar'];
        return $result;
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