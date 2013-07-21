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
    
    /// Allow only A-Z, dot, hyphen and numbers
    private function clean_uname($uname)
    {
        return strtolower(preg_replace("[^a-zA-Z\.\-0-9]",'',$uname)); 
    }
    
    /// Returns true if the ownerid exists
    function exists($ownerid)
    {
        $this->db->select('ownerid');
        $this->db->where('ownerid',$ownerid);
        $query=$this->db->get('owners');
        return $query->num_rows()>0;
    }
    
    /// Returns true if the username is in use
    function uname_exists($uname)
    {
        $this->db->select('ownerid');
        $this->db->where('uname',$this->clean_uname($uname));
        $query=$this->db->get('owners');
        return $query->num_rows()>0;
    }
    
    function id_by_uname($uname)
    {
        $uname=$this->clean_uname($uname);
        $this->db->select('ownerid');
        $this->db->where('uname',$uname);
        $query=$this->db->get('owners');
        if ($query->num_rows()!=1)
            return false;
        $result=$query->result();
        $result=$result[0];
        return (int)$result->ownerid;
    }
    
    function check_psw($uname,$psw)
    {
        //allow only A-Z, dot, hyphen and numbers:
        $uname=$this->clean_uname($uname); 

        $this->db->where('uname',$uname);
        $this->db->where('is_locked',0);
        $query=$this->db->get('owners');
        if ($query->num_rows()!=1)
            return false;
        
        $dbpsw=$query->result();
        $dbpsw=$dbpsw[0];
        $this->load->helper('bytes');
        $providedhash=make_hash($dbpsw->salt,$psw);
        if ($providedhash===$dbpsw->hash)
            return true;
        
        return false;
    }
    
    function select($ownerid)
    {
        if (!is_int($ownerid)) //we probably have a username then
            $ownerid=$this->id_by_uname($ownerid);
        
        $ownerid=(int)$ownerid;
        if (!$ownerid)
            return false;
        
        $this->db->where('ownerid',$ownerid);
        $query=$this->db->get('owners');
        if ($query->num_rows()!=1)
            return false;
        
        $result=$query->result();
        $result=$result[0];
        
        $this->ownerid=$ownerid;
        $this->uname=$result->uname;
        $this->is_admin=!!$result->is_admin;
        
        return $ownerid;
    }
}