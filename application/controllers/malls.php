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
        foreach ($this->mall->nearest($x_coord,$y_coord,10) as $mall)
        {
            $this->mall->select($mall);
            $mall_object[]=array('mallid'=>$mall, 'name'=>$this->mall->name, 'x_coord'=>$this->mall->x_coord, 'y_coord'=>$this->mall->y_coord);
        }
        
        send_json(array('malls'=>$mall_object));
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
    
    /// Returns a list of shops at the mall
    /// POST: mallid
    public function shops()
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