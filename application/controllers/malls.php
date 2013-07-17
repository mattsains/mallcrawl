<?php
class Malls extends CI_Controller
{
    /// Returns a list of up to ten malls closest to a location
    /// POST: x_coord, y_coord
    public function near()
    {
        if (!($this->input->post('x_coord') && $this->input->post('y_coord'))) 
            error('You did not provide coordinates');
        
        $x_coord=(double)$this->input->post('x_coord');
        $y_coord=(double)$this->input->post('y_coord');
        
        if (abs($x_coord)>180 || abs($y_coord)>180)
            error('Invalid coordinates');
        
        $this->load->model('mall');
        
        $mall_object=array();
        foreach ($this->mall->nearest($x_coord,$y_coord,10) as $mallid)
        {
            $this->mall->select($mallid);//don't check the mallid because we have just gotten it from the database
            $mall_object[]=$this->mall->as_array();
        }
        send_json($mall_object);
    }
    ///Returns malls in the city/province combination
    public function in()
    {
        if (!$this->input->post('city'))
            error('You did not provide a city');
        if (!$this->input->post('province'))
            error('You did not provide a province');
        
        $this->load->model('mall');
        
        $mall_object=array();
        foreach ($this->mall->in($this->input->post('city'),$this->input->post('province'),10) as $mallid)
        {
            $this->mall->select($mallid);//don't check the mallid because we have just gotten it from the database
            $mall_object[]=$this->mall->as_array();
        }
        send_json($mall_object);
    }
    ///Returns a list of provinces
    public function provinces()
    {
        $this->load->database();
        
        $query=$this->db->query("SELECT DISTINCT(province) FROM malls WHERE active=1");
        
        $provinces=array();
        foreach ($query->result() as $row)
            $provinces[]=$row->province;
        send_json($provinces);
    }
    ///Returns a list of cities in a province
    public function cities()
    {
        if (!$this->input->post('province'))
            error('You did not provice a province');

        $this->load->database();
        
        $this->db->select('city');
        $this->db->where('province',$this->input->post('province'));
        $this->db->group_by('city');
        $query=$this->db->get('malls');
        
        $cities=array();
        foreach ($query->result() as $row)
            $cities[]=$row->city;
        send_json($cities);
    }
    ///Returns information about a specific mall
    public function index()
    {
        if (!$this->input->post('mallid'))
            error('You did not provide a mallid');
        $mallid=(int)$this->input->post('mallid');
        
        $this->load->model('mall');
        $this->mall->select($mallid) or error('mallid is invalid');
        send_json(array('mallid'=>$mallid, 'name'=>$this->mall->name, 'x_coord'=>$this->mall->x_coord, 'y_coord'=>$this->mall->y_coord, 
                        'city'=>$this->mall->city, 'province'=>$this->mall->province,
                        'manager_name'=>$this->mall->manager_name, 'bio'=>$this->mall->bio, 'website'=>$this->mall->website, 'twitter'=>$this->mall->twitter, 
                        'facebook'=>$this->mall->facebook, 'phone'=>$this->mall->phone, 'email'=>$this->mall->email, 'logo'=>$this->mall->logo, 'map'=>$this->mall->map, 'polygons'=>$this->mall->polygons));
    }
    /// Adds a mall to a user's list
    /// POST: mallid, access_token
    public function add()
    {
        if (!$this->input->post('access_token'))
            error('You did not provide a facebook access token');
        if (!$this->input->post('mallid'))
            error('You did not provide a mallid to add');
        $mallid=(int)$this->input->post('mallid');
        
        $this->load->model('mall');
        $this->load->model('user');
        
        $this->user->initialise_from_token($this->input->post('access_token'));
        
        $this->user->add_mall($mallid) or error('mallid is invalid');
        send_json($mallid);
    }
    /// Removes a mall from the user's list
    /// POST: mallid, access_token
    public function remove()
    {
        if (!$this->input->post('access_token'))
            error('You did not provide a facebook access token');
        if (!$this->input->post('mallid'))
            error('You did not provide a mallid to add');
        $mallid=(int)$this->input->post('mallid');
        
        $this->load->model('mall');
        $this->load->model('user');
        
        $this->user->initialise_from_token($this->input->post('access_token'));
        
        $this->user->remove_mall($mallid) or error('mallid is invalid');
        send_json($mallid);
    }
    
    /// Returns a list of stores at the mall
    /// POST: mallid
    public function stores()
    {
        if (!$this->input->post('mallid'))
            error('You did not provide a mallid');
        $mallid=(int)$this->input->post('mallid');
        
        $this->load->model('store');
        
        $output=$this->store->list_mall($mallid);
        if ($output===false)
            error('mallid is invalid');

       send_json(array('mallid'=>$mallid,'stores'=>$output));
    }
}