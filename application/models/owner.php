<?php
class Owner extends CI_Model
{
    public $ownerid=false;
    public $uname=false;
    public $is_admin=false;
    
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
    function check_psw($uname,$psw)
    {
        //allow only A-Z, dot, hyphen and numbers:
        $uname=strtolower(preg_replace("/[^a-zA-Z\.\-0-9]",'',$uname)); 

        $this->db->where('uname',$uname);
        $query=$this->db->get('owners');
        if ($query->num_rows()!=1)
            return false;
        
        $dbpsw=$query->result()[0];
        $this->load->helper('bytes');
        $providedhash=make_hash($dbpsw->salt,$psw);
        if ($providedhash===$dbpsw->hash)
            return true;
        
        return false;
    }
}