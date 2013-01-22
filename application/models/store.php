<?php
class Store extends CI_Model
{
    // fields which can be automatically populated
    private $fields=array('storeid','mallid','typeid','manager_name','name','email','bio','facebook','twitter','website','phone');
    
    public $storeid=false;
    public $mallid=false;
    public $typeid=false;
    public $manager_name=false;
    public $name=false;
    public $email=false;
    public $bio=false;
    public $facebook=false;
    public $twitter=false;
    public $website=false;
    public $phone=false;
    
    //this needs special processing
    public $typename=false;
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    
    /// Returns true if the storeid actually exists
    function exists($store)
    {
        $store=(int)$store;
        
        $this->db->select('storeid');
        $this->db->where('storeid',$store);
        $query=$this->db->get('stores');
        return $query->num_rows()>0;
    }
    
    /// Returns true if the typeid exists
    function type_exists($typeid)
    {
        $typeid=(int)$typeid;
        $this->db->select('typeid');
        $this->db->where('typeid',$typeid);
        $query=$this->db->get('types');
        return $query->num_rows()>0;
    }
    
    /// Creates a new store
    ///  data is an associative array with the new store's data
    function create($data)
    {
        //check for compulsory fields
        if (!($data['mallid'] && $data['typeid'] && $data['manager_name'] && $data['ownerid'] && $data['name'] && $data['bio'] && $data['phone']))
            return false;
            
        //some cleansing
        $data['manager_name']=strip_tags($data['manager_name']);
        $data['bio']=strip_tags($data['bio']);
        
        $this->load->model('mall');
        $this->load->model('owner');
        if (!$this->mall->exists($data['mallid'])) return false;
        if (!$this->type_exists($data['typeid'])) return false;
        if (!$this->owner->exists($data['ownerid'])) return false;
        
        //there's probably a better way to do this
        $insert=array('mallid'=>(int)$data['mallid'], 'typeid'=>(int)$data['typeid'], 'manager_name'=>$data['manager_name'], 'ownerid'=>(int)$data['ownerid'],
                      'name'=>$data['name'], 'email'=>isset($data['email'])?$data['email']:null, 'bio'=>$data['bio'], 'facebook'=>isset($data['facebook'])?$data['facebook']:null,
                      'twitter'=>isset($data['twitter'])?$data['twitter']:null, 'website'=>isset($data['website'])?$data['website']:null,
                      'phone'=>$data['phone']);
        //time to insert into the db
        $this->db->insert('stores',$insert);
        $store=(int)$this->db->insert_id();
        
        $this->select($store);
        return $store;
    }
    
    /// sets the object to describe the storeid passed
    /// populates all the fields
    function select($storeid)
    {
        $storeid=(int)$storeid;
        if (!$this->exists($storeid)) return false;
        
        $this->db->where('storeid',$storeid);
        $query=$this->db->get('stores');
        
        $result=$query->result();
        $result=$result[0];//there should only be one store with a unique id       
        
        //auto-populate fields
        foreach ($this->fields as $field)
            $this->$field=$result->$field?
                            $result->$field : false;
        
        return true;
    }
    
    /// returns a list of categories that the store belongs to
    function categories($storeid=false)
    {
        if (!$storeid)
        {
            if (!$this->storeid)
                return false;
            else $storeid=$this->storeid;
        }
        $storeid=(int)$storeid;
        
        $query=$this->db->query('SELECT `categories`.`categoryid` AS `categoryid`, `categories`.`text` AS `text` FROM `categories`,`category-members`'.
                                ' WHERE `category-members`.`storeid`='.$storeid.
                                ' AND `categories`.`categoryid`=`category-members`.`categoryid`');
        $categories=array();
        foreach ($query->result() as $row)
            $categories[]=array('categoryid'=>$row->categoryid, 'categoryname'=>$row->text);
        return $categories;
    }
    
    /// Gets details about all the stores in the mall
    function list_mall($mallid)
    {
        $mallid=(int)$mallid;
        
        $this->load->model('mall');
        if (!$this->mall->exists($mallid)) return false;
        
        $query=$this->db->query('SELECT `stores`.`storeid`, `stores`.`typeid`, `types`.`text` AS `typename`, `stores`.`name`, `stores`.`manager_name`, `stores`.`email`, `stores`.`bio`, `stores`.`facebook`, `stores`.`twitter`, `stores`.`website`,`stores`.`phone`'.
                                ' FROM `stores` LEFT JOIN `types` ON `stores`.`typeid`=`types`.`typeid`'.
                                ' WHERE `stores`.`mallid`='.$mallid.
                                ' ORDER BY `stores`.`name`');
        $output=$query->result_array();
        foreach($output as $key=>$store)
            $output[$key]['categories']=$this->categories($output[$key]['storeid']);
        return $output;
    }
    
    /// Returns a list of image URLs, their author, and timestamp
    /// Access token seems needed for facebook batch jobs
    // TODO: This code is incredible hacky and must be changed. For example, there is almost no error checking
    function images($token)
    {
        if (!$this->storeid)
            return false;
        if (!ctype_alnum($token))
            error('The access token is invalid');
        
        $this->storeid=(int)$this->storeid;
        $query=$this->db->query('SELECT `store-images`.`path`,`store-images`.`userid`, UNIX_TIMESTAMP(`store-images`.`timestamp`) AS `timestamp` FROM `store-images` ORDER BY `store-images`.`timestamp` DESC');
        
        $output=array();
        $userids=array();
        foreach($query->result() as $row)
        {
            $output[]=array('url'=>base_url().'assets/stores/'.$row->path,
                            'timestamp'=>$row->timestamp, 'userid'=>$row->userid);
            if (!in_array($row->userid,$userids))
                $userids[]=$row->userid;
        }
        
        //get everyone's name and uname from facebook all at once
        $requests='[';//unfortunately we have to construct this JSON object manually. The next few lines of code are UGLY.
        foreach($userids as $uid)
            $requests.='{"method":"GET", "relative_url":"'.$uid.'?fields=name,username"}, ';
        $requests=substr($requests,0,-2).']';
        
        // make request
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://graph.facebook.com/"); 
        curl_setopt($ch, CURLOPT_POSTFIELDS, "access_token=$token&batch=$requests");
        curl_setopt($ch, CURLOPT_FAILONERROR, false);
        curl_setopt($ch, CURLOPT_HTTP200ALIASES, (array)400);
        curl_setopt($ch, CURLOPT_CAINFO, "c:/xampp/php/ext/cacert.crt");//DEBUG ONLY
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
        $response = json_decode(curl_exec($ch));
        curl_close($ch);  
        
        foreach ($response as $response_item)
        {
            if (ISSET($response_item) && $response_item->code==200) //success
            {
                $person=json_decode($response_item->body);
                
                foreach($output as $output_key=>$output_item) //for each photo by this user, add their username and name to the photo
                    if ($output_item['userid']==$person->id)
                    {
                        $output[$output_key]['username']=$person->username;
                        $output[$output_key]['name']=$person->name;
                    }
            }
        }
        return $output;
    }       
}