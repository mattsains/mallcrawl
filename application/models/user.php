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
    /// fail count is in case of facebook failure
    function initialise_from_token($token,$fail_count=0)
    {
        if (!ctype_alnum($token)) //token is not alphanumeric
            error('The access token is invalid');
        
        // make request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/me?fields=id,name,username&access_token=$token"); 
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTP200ALIASES, (array)400);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_CAINFO, "/etc/apache2/ssl-cert/verisign-fb.crt");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $response = json_decode(curl_exec($ch));
        curl_close($ch);  

        if (isset($response->error))
        {
            //some facebook error. will either use recursion to try again,
            //or die if we've already tried four times
            facebook_error($response,$fail_count<4);
            //we're still here, so try again
            return $this->initialise_from_token($token,$fail_count+1);
        }
        
        $this->userid=$response->id;
        $this->name=$response->name;
        $this->uname=isset($response->username)?$response->username:$response->id; //some people don't have usernames
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
        {
            $result=$query->result();
            $result=$result[0];
            $this->noupload=!!$result->no_upload;
        }
        //favourite malls
        $this->db->select('mallid');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('mall-lists');
        
        $this->malls=array();
        
        foreach($query->result() as $row)
            $this->malls[]=(int)$row->mallid;
                    
        return $this->userid;
    }
    
    /// Adds a mall to a user's list
    function add_mall($mallid)
    {
        $mallid=(int)$mallid;
        $this->load->model('mall');
        
        if (!$this->userid) return false;
        if (!$this->mall->exists($mallid)) return false;
        
        if (in_array($mallid,$this->malls)) // already has this mall in the list
            return true; //swallow silently.
        
        $this->db->insert('mall-lists',array('userid'=>$this->userid, 'mallid'=>$mallid));
        $this->malls[]=$mallid;
        
        return true;
    }
    
    /// Removes a mall from a user's list
    function remove_mall($mallid)
    {
        $mallid=(int)$mallid;
        $this->load->model('mall');
        
        if (!$this->userid) return false;
        if (!$this->mall->exists($mallid)) return false;
        
        if (!in_array($mallid,$this->malls)) // does not have the mall in the list
            return true; //swallow silently.
        
        $this->db->delete('mall-lists',array('userid'=>$this->userid, 'mallid'=>$mallid));
        $this->malls[]=array_diff($this->malls, array($mallid));//remove from array
        
        return true;
    }
    /// Returns a list of the user's favourite stores
    function list_stores()
    {
        if (!$this->userid) return false;
        
        $this->db->select('storeid');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('store-lists');
        
        $output=array();
        
        foreach($query->result() as $row)
            $output[]=(int)$row->storeid;
        
        return $output;
    }
    /// Adds a store to a user's star list
    function add_store($storeid)
    {
        $storeid=(int)$storeid;
        $this->load->model('store');
        
        if (!$this->userid) return false;
        if (!$this->store->exists($storeid)) return false;
        
        if (in_array($storeid,$this->list_stores())) // already has this store in the list
            return true; //swallow silently.
        
        $this->db->insert('store-lists',array('userid'=>$this->userid, 'storeid'=>$storeid));
        return true;
    }
    
    /// Removes a store from a user's list
    function remove_store($storeid)
    {
        $storeid=(int)$storeid;
        $this->load->model('store');
        
        if (!$this->userid) return false;
        if (!$this->store->exists($storeid)) return false;
        
        $this->db->delete('store-lists',array('userid'=>$this->userid, 'storeid'=>$storeid));
        return true;
    }
}