<?php
class Owner extends CI_Model
{
    private $fields=array('ownerid','uname','is_admin','is_locked'); //auto-populable fields
    
    public $ownerid=false;
    public $uname=false;
    public $is_admin=false;
    public $is_locked=false;
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->library('session');
    }
    
    /// Allow only A-Z, dot, hyphen and numbers
    public function clean_uname($uname)
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
        
        foreach ($this->fields as $field)
            $this->$field=$result->$field?$result->$field:false;
        
        return $ownerid;
    }
    /// Starts a session.
    /// WARNING: no authentication done here!
    function session_begin($uid)
    {
        if (!is_int($uid))
            $uid=$this->id_by_uname($uid);
        $this->session->set_userdata(array('ownerid'=>$uid));
    }
    /// A nice function to call at the beginning of a script to restrict access to logged in users.
    /// If someone is logged in, that user will be select()ed
    function login($redir=true)
    {
        if ($this->session->userdata('ownerid') && $this->exists((int)$this->session->userdata('ownerid')))
        {
            $ownerid=(int)$this->session->userdata('ownerid');
            //check for locked-ness
            $this->select($ownerid);
            if ($this->is_locked)
            {
                //owner is locked
                $this->session->sess_destroy();
                if ($redir)
                {
                    $this->load->view('header',array('title'=>'Account locked'));
                    $this->load->view('locked',array('email'=>$this->config->item('email')));
                    $this->load->view('footer');
                    die();
                } else
                    return false;
            } else      //otherwise we are bona-fide logged in
                return true;
        } else
        {
            //not logged in, or login is invalid
            if ($redir)
            {
                var_dump(base_url());
                redirect(base_url().'auth/login?redir='.urlencode(current_url()));
                die();
            } else
                return false;
        }
    }
    function logout()
    {
        $this->session->sess_destroy();
    }
    /// Adds an owner to the database
    /// returns the new owner id
    /// NOTE: also selects new owner
    function add($uname, $psw)
    {
        $this->load->helper('bytes');
        // make usernames cleaner
        $uname=$this->clean_uname($uname);
        if ($this->uname_exists($uname)) return false;
        
        $sh=salted_hash($psw);
        $this->db->insert('owners', array( 
                                   'uname'=>$uname,
                                   'salt'=>$sh['salt'],
                                   'hash'=>$sh['hash']));
        $this->select($this->db->insert_id());
        return $this->ownerid;
    }
}