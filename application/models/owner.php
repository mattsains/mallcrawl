<?php
class Owner extends CI_Model
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    /// Returns true if the username is in use
    function uname_exists($uname)
    {
        $this->db->select('ownerid');
        $this->db->where('uname',$uname);
        $query=$this->db->get('owners');
        return $query->num_rows()>0;
    }
    function exists($ownerid)
    {
        $this->db->select('ownerid');
        $this->db->where('ownerid',$ownerid);
        $query=$this->db->get('owners');
        return $query->num_rows()>0;
    }
}