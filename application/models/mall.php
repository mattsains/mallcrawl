<?php
class Mall extends CI_Model
{
    // fields which can be automatically populated
    private $fields=array('mallid','ownerid','name','x_coord','y_coord','province','city','secret','manager_name','bio','website','twitter','facebook','phone','email');
    
    public $mallid=false;
    public $ownerid=false;
    public $name=false;
    public $x_coord=false;
    public $y_coord=false;
    public $province=false;
    public $city=false;
    public $secret=false; //lol, but it isn't too big a secret!
    public $manager_name=false;
    public $bio=false;
    public $website=false;
    public $twitter=false;
    public $facebook=false;
    public $phone=false;
    public $email=false;
    
    //fields needing some processing. Incidentally, these all happen to be paths
    public $logo=false;
    public $map=false;
    public $polygons=false;
        
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
    ///Coordinates
    function nearest($x_coord, $y_coord, $limit)
    {
        $x_coord=(double)$x_coord;
        $y_coord=(double)$y_coord;
        $limit=(int)$limit;
        //this line is only OK because I have casted the injected variables as doubles
        $query=$this->db->query("SELECT `mallid` FROM `malls` ORDER BY (POWER(`x_coord`-$x_coord,2)+POWER(`y_coord`-$y_coord,2))");
        
        $malls=array();
        foreach($query->result() as $row)
            $malls[]=$row->mallid;
        return $malls;
    }
    /// City/province
    function in($city, $province, $limit)
    {
        $limit=(int)$limit;
        
        $this->db->select('mallid');
        $this->db->where('city',$city);
        $this->db->where('province',$province);
        $query=$this->db->get('malls',$limit);
        
        $malls=array();
        foreach($query->result() as $row)
            $malls[]=$row->mallid;
        return $malls;
    }
    /// Returns true if the mallid actually exists
    function exists($mallid)
    {
        $mallid=(int)$mallid;
        
        $this->db->select('mallid');
        $this->db->where('mallid',$mallid);
        $query=$this->db->get('malls');
        return $query->num_rows()>0;
    }
    /// Creates a new mall
    ///  data is an associative array with the new mall's data
    function create($data)
    {
        //check for compulsory fields
        if (!($data['ownerid'] && $data['name'] && $data['x_coord'] && $data['y_coord'] && $data['manager_name'] && $data['phone']))
            return false;
            
        //some cleansing
        $data['manager_name']=strip_tags($data['manager_name']);
        $data['bio']=strip_tags($data['bio']);
        
        $this->load->model('owner');
        if (!$this->owner->exists($data['ownerid'])) return false;
        
        //time for a bit of preprocessing before database
        $secret=rand_hex(8);
        //there's probably a better way to do this
         $insert=array('ownerid'=>(int)$data['ownerid'], 'name'=>$data['name'], 'logo'=>isset($data['logo'])?$data['logo']:null, 'x_coord'=>(double)$data['x_coord'], 'y_coord'=>(double)$data['y_coord'],
                      'city'=>isset($data['city'])?$data['city']:null, 'province'=>isset($data['city'])?$data['city']:null, 'map'=>isset($data['map'])?$data['map']:null, 'secret'=>$secret, 
                      'manager_name'=>$data['manager_name'], 'bio'=>$data['bio'], 'website'=>isset($data['website'])?$data['website']:null, 'facebook'=>isset($data['facebook'])?$data['facebook']:null, 
                      'twitter'=>isset($data['twitter'])?$data['twitter']:null, 'phone'=>$data['phone'], 'email'=>isset($data['email'])?$data['email']:null,
                      'polygon_path'=>isset($data['polygon_path'])?$data['polygon_path']:null, 'active'=>isset($data['active'])?(!!$data['active']):0);
        //time to insert into the db
        $this->db->insert('malls',$insert);
        $mall=(int)$this->db->insert_id();
        
        $this->select($mall);
        return $mall;
    }
    /// sets the object to describe the mallid passed
    /// populates all the fields
    function select($mallid)
    {
        $mallid=(int)$mallid;
        if (!$this->exists($mallid)) return false;
        
        $this->db->where('mallid',$mallid);
        $query=$this->db->get('malls');
        
        $result=$query->result();
        $result=$result[0];//there should only be one mall with a unique id       
        
        //auto-populate fields
        foreach ($this->fields as $field)
        {
            $this->$field=(isset($result->$field) && $result->$field!="")?
                            $result->$field : false;
        }
        
        //some manual processing
        //we need to turn these into proper urls
        $logopath=$result->logo;
        $mappath=$result->map;
        $polygonpath=$result->polygon_path;
        $this->logo=$logopath?$this->config->item('api-path').'assets/malls/'.$logopath:false;
        $this->map=$mappath?$this->config->item('api-path').'assets/malls/'.$mappath:false;
        $this->polygons=$polygonpath?$this->config->item('api-path').'assets/malls/'.$polygonpath:false;
        return true;
    }
    function select_by_secret($secret)
    {
        $this->db->where('secret',$secret);
        $query=$this->db->get('malls');
        if ($query->num_rows()!=1) return false; //either the secret isn't in the database, 
                            //or there are more than one mall with the same secret, which is very bad
        
        $result=$query->result();
        $result=$result[0];//there should only be one mall with a unique id       
        
        //auto-populate fields
        foreach ($this->fields as $field)
        {
            $this->$field=$result->$field?
                            $result->$field : false;
        }
        
        //some manual processing
        //we need to turn these into proper urls
        $logopath=$result->logo;
        $mappath=$result->map;
        $polygonpath=$result->polygon_path;
        $this->logo=$logopath?$this->config->item('api-path').'assets/malls/'.$logopath:false;
        $this->map=$mappath?$this->config->item('api-path').'assets/malls/'.$mappath:false;
        $this->polygons=$polygonpath?$this->config->item('api-path').'assets/malls/'.$polygonpath:false;
        return $this->mallid;
    }
    /// Returns an array of the useful properties of this object
    public function as_array()
    {
        if (!$this->mallid)
            return false;
        
        $return_fields=array('mallid','name','secret','x_coord','y_coord','city','province','manager_name','bio','website','twitter','facebook','phone','email','logo','map','polygons');
        
        $output=array();
        
        foreach($return_fields as $field)
            $output[$field]=$this->$field; //whenever I do this I feel badass
        
        return $output;
    }
    
    ///Updates certain fields
    public function update($field_arr)
    {
        if (!$this->mallid)
            return false;
        
        
        $this->db->where('mallid',$this->mallid);
        $this->db->update('malls',$field_arr);
        //refresh
        $this->select($this->mallid);
    }
}