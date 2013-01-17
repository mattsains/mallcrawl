<?php
class User extends CI_Model
{
    public $userid=false;
    public $name=false;
    public $uname=false;
    public $photo=false;
    public $noupload=true;
    
    public $malls=false;
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    function initialise_from_token($token)
    {
        if (!ctype_alnum($token)) //token is not alphanumeric
        error('The access token is invalid');
        
        // make request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?fields=id,name,username&access_token=$token"); 
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTP200ALIASES, (array)400);
        curl_setopt($ch, CURLOPT_CAINFO, "c:/xampp/php/ext/cacert.crt");//DEBUG ONLY
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $response = json_decode(curl_exec($ch));
        curl_close($ch);  

        if (isset($response->error))
            error($response->error->message);
        
        $this->userid=(int)$response->id;
        $this->name=$response->name;
        $this->uname=$response->username;
        $this->photo='https://graph.facebook.com/'.$this->uname.'/picture';
        
        $this->db->select('no_upload');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('users');
        if ($query->num_rows()==0)
        {   
            //the user does not exist in the database. Let's make him an entry
            $this->db->insert('users',array('userid'=>$this->userid));
            $this->noupload=false;
        } else //otherwise make sure he is allowed to upload images
            $this->noupload=!!$query->result()[0]->no_upload;
        
        $this->db->select('mallid');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('mall-lists');
        
        $this->malls=array();
        
        foreach($query->result() as $row)
            $this->malls[]=(int)$row->mallid;
            
        return $this->userid;
    }
}