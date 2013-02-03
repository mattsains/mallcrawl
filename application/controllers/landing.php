<?php
class Landing extends CI_Controller
{
    public function index()
    {
        $this->load->view('header',array('title'=>'Admin'));
        $this->load->view('footer');
    }
}