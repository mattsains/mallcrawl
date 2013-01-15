<?php
class User extends CI_Model
{
    public $userid=false;
    
    function __construct()
    {
        parent::__construct();
        $this->load->database();
    }
}