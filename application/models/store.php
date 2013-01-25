<?php
class Store extends CI_Model
{
    // fields which can be automatically populated
    private $fields=array('storeid','mallid','typeid','ownerid','manager_name','name','email','bio','facebook','twitter','website','phone');
    
    public $storeid=false;
    public $mallid=false;
    public $typeid=false;
    public $ownerid=false;
    public $manager_name=false;
    public $name=false;
    public $email=false;
    public $bio=false;
    public $facebook=false;
    public $twitter=false;
    public $website=false;
    public $phone=false;
    
    //this needs special processing
    public $type_name=false;
    
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
        
        // now figure out the name of the type of mall this is
        $typeid=(int)$this->typeid;
        $this->db->where('typeid',$typeid);
        $query=$this->db->get('types');
        
        if ($query->num_rows()==1)
        {
            $result=$query->result()[0];
            $this->type_name=$result->text;
        } else $this->type_name=false;
        
        return true;
    }
    
    /// Returns an array of the useful properties of this object
    public function as_array()
    {
        if (!$this->mallid)
            return false;
        
        $return_fields=array('storeid','mallid','typeid','type_name','name','manager_name','bio','website','twitter','facebook','phone','email');
        
        $output=array();
        
        foreach($return_fields as $field)
            $output[$field]=$this->$field;
        
        return $output;
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
    /// This is a STATELESS function, it works alone.
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
    function images($token, $fail_count=0)
    {
        if (!$this->storeid)
            return false;
        if (!ctype_alnum($token))
            error('The access token is invalid');
        
        $this->storeid=(int)$this->storeid;
        $query=$this->db->query('SELECT `store-images`.`image`, `store-images`.`thumb`, `store-images`.`userid`, UNIX_TIMESTAMP(`store-images`.`timestamp`) AS `timestamp` FROM `store-images` ORDER BY `store-images`.`timestamp` DESC');
        
        $output=array();
        $userids=array();
        foreach($query->result() as $row)
        {
            $output[]=array('image'=>base_url().'assets/stores/'.$row->image,
                            'thumb'=>base_url().'assets/stores/'.$row->thumb,
                            'timestamp'=>$row->timestamp, 
                            'userid'=>$row->userid,
                            'username'=>false,//these are set to false so that, if we fail to get their username from facebook, the field still exists
                            'name'=>false);
            if (!in_array($row->userid,$userids))
                $userids[]=$row->userid;
        }
        //only fifty requests are allowed at once
        for ($i=0; $i<count($userids); $i+=40)
        {
            //get a batch of 20 people's names and unames from facebook at once
            $requests='[';//unfortunately we have to construct this JSON object manually. The next few lines of code are UGLY.
            foreach(array_slice($userids,$i,40) as $uid)
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
            
            if (isset($response->error))
            {
                //some facebook error. will either use recursion to try again,
                //or die if we've already tried four times
                facebook_error($response,$fail_count<4);
                //we're still here, so try again
                return $this->images($token,$fail_count+1);
            }
                    
            foreach ($response as $response_item)
            {
                if (!isset($response_item)) 
                    break; //facebook ran out of computation time. Can't do anything this side, so just try keep going
                
                if (isset($response_item->error))//just skip to the next one, I guess
                    continue;
                    

                if ($response_item->code==200) //success
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
        }
        return $output;
    }
    
    /// Adds an image to the database
    function add_image($userid,$image,$thumb)
    {
        $trimfolder='assets/stores/';
        $image=substr($image, strpos($image,$trimfolder)+strlen($trimfolder));
        $thumb=substr($thumb, strpos($thumb,$trimfolder)+strlen($trimfolder));
        
        $this->db->insert('store-images', array('image'=>$image, 'thumb'=>$thumb, 'userid'=>$userid));
        if ($this->db->affected_rows()==1)
            return $image;
        else
            return false;
    }
}