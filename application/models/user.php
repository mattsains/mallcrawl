<?php
class User extends CI_Model
{
    public $userid=false;
    public $name=false;
    public $uname=false;
    public $photo=false;
    public $noupload=true;
    
    public $malls=false;
    
    public $stores=false; //for some reason, these are called stars
    
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
            $this->noupload=!!$query->result()[0]->no_upload;
        //favourite malls
        $this->db->select('mallid');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('mall-lists');
        
        $this->malls=array();
        
        foreach($query->result() as $row)
            $this->malls[]=(int)$row->mallid;
        //favourite stores
        $this->db->select('storeid');
        $this->db->where('userid',$this->userid);
        $query=$this->db->get('store-lists');
        
        $this->stores=array();
        
        foreach($query->result() as $row)
            $this->stores[]=(int)$row->storeid;
            
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
    
    /// Adds a store to a user's star list
    function add_store($storeid)
    {
        $storeid=(int)$storeid;
        $this->load->model('store');
        
        if (!$this->userid) return false;
        if (!$this->store->exists($storeid)) return false;
        
        if (in_array($storeid,$this->stores)) // already has this store in the list
            return true; //swallow silently.
        
        $this->db->insert('store-lists',array('userid'=>$this->userid, 'storeid'=>$storeid));
        $this->stores[]=$storeid;
        
        return true;
    }
    
    /// Removes a store from a user's list
    function remove_store($storeid)
    {
        $storeid=(int)$storeid;
        $this->load->model('store');
        
        if (!$this->userid) return false;
        if (!$this->store->exists($storeid)) return false;
        
        if (!in_array($storeid,$this->stores)) // does not have the store in the list
            return true; //swallow silently.
        
        $this->db->delete('store-lists',array('userid'=>$this->userid, 'storeid'=>$storeid));
        $this->stores[]=array_diff($this->stores, array($storeid));//remove from array
        
        return true;
    }
}