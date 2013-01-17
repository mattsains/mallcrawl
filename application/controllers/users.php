<?php
class Users extends CI_Controller
{
    public function login()
    {
        $this->load->model('mall');
        if (!$this->input->post('access_token')) 
            error('You did not provide a facebook access token for authentication');
        
        $this->load->model('user');
        $this->user->initialise_from_token($this->input->post('access_token'));
        $malls=array();
        foreach($this->user->malls as $mall)
        {
            $this->mall->select($mall);
            $malls[]=array('mallid'=>$mall, 'name'=>$this->mall->name, 'logo'=>$this->mall->logo);
        }
        //replace raw mallids with a mall array, then send
        $user=$this->user;
        $user->malls=$malls;
        send_json($user);
    }
}