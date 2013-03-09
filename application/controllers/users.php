<?php
class Users extends CI_Controller
{
    /// A way to verify the legitimacy of a user, also returns user's mall list
    /// POST: access_token
    public function login()
    {
        $this->load->model('mall');
        $this->load->model('store');
        if (!$this->input->post('access_token')) 
            error('You did not provide a facebook access token for authentication');
        
        $this->load->model('user');
        $this->user->initialise_from_token($this->input->post('access_token'));
	
        $malls=array();
        foreach($this->user->malls as $mall)
        {
            if (!$this->mall->select($mall))
                continue;
            $malls[]=$this->mall->as_array();
        }
        $user=$this->user;
        //replace raw mallids and storeids with a mall and store array
        $user->malls=$malls;
        
        send_json($user);
    }
    /// Returns starred stores
    /// POST: access_token
    public function stores()
    {
        $this->load->model('user');
        $this->load->model('store');
        if (!$this->input->post('access_token'))
            error('You did not provide a facebook access token for authentication');
        
        $this->user->initialise_from_token($this->input->post('access_token'));
        
        $stores=array();
        foreach($this->user->list_stores() as $store)
        {
            if (!$this->store->select($store))
                continue;
            $stores[]=$this->store->as_array();
        }
        send_json($stores);
    }
}
