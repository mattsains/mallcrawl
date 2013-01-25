<?php
class Users extends CI_Controller
{
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
            $this->mall->select($mall);
            $malls[]=$this->mall->as_array();
        }
        $stores=array();
        foreach($this->user->stores as $store)
        {
            $this->store->select($store);
            $stores[]=$this->store->as_array();
        }
        $user=$this->user;
        //replace raw mallids and storeids with a mall and store array
        $user->malls=$malls;
        $user->stores=$stores;
        
        send_json($user);
    }
}