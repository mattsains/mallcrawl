<?php
class Store extends CI_Model
{
    // fields which can be automatically populated
    private fields=array('storeid','mallid','typeid','manager_name','name','email','bio','facebook','twitter','website','phone');
    
    public storeid=false;
    public mallid=false;
    public typeid=false;
    public manager_name=false;
    public name=false;
    public email=false;
    public bio=false;
    public facebook=false;
    public twitter=false;
    public website=false;
    public phone=false;
    
    //this needs special processing
    public typename=false;
    
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
        $store=(int)$store;
        if (!$this->exists($store)) return false;
        
        $this->db->where('storeid',$store);
        $query=$this->db->get('stores');
        
        $result=$query->result();
        $result=$result[0];//there should only be one store with a unique id       
        
        //auto-populate fields
        foreach ($fields as $field)
            $this->$field=$result->$field?
                            $result->$field : false;
        
        return true;
    }
    
    /// returns a list of categories that the store belongs to
    function categories()
    {
        if (!$this->storeid) return false;
        $this->storeid=(int)$this->storeid;
        
        $query=$this->db->query('SELECT `categories`.`categoryid` AS `categoryid`, `categories`.`text` AS `text` FROM `categories`,`category-members`'.
                                ' WHERE `category-members`.`storeid`='.$this->storeid.
                                ' AND `categories`.`categoryid`=`category-members`.`categoryid`');
        $categories=array();
        foreach ($query->result() as $row)
            $categories[]=array('categoryid'=>$row->categoryid, 'categoryname'=>$row->text);
        return $categories;
    }
}