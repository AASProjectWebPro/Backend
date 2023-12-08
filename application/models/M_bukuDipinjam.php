<?php 
class M_bukuDipinjam extends CI_Model
{
    function fetch_all()
    {
        // untuk menampilkan buku yang sedang dipinjam.
        $this->db->select('id_buku');
        $this->db->order_by('id_buku','asc');
        $query = $this->db->get('transaksi_peminjaman');
        return $query->result_array();
    }
}
?>