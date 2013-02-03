<?php
class Auth extends CI_Controller
{
    public function login()
    {
        if ($this->owner->login(false))
        {
            //already logged in
            if ($this->input->get('redir'))
                redirect($this->input->get('redir'));
            else
                redirect(base_url());
        }
        elseif (!$this->input->post('username'))
        {
            //form has not yet been submitted
            //just show it.
            $this->load->view('header',array('title'=>'Log in'));
            $this->load->view('login',array('username'=>'','redir'=>$this->input->get('redir')));
            $this->load->view('footer');
        } else
        {
            //form submitted. Let's see if it's valid
            if ($this->owner->check_psw($this->input->post('username'),$this->input->post('password')))
            {
                //successful login
                $this->owner->session_begin($this->input->post('username'));
                if ($this->input->get('redir'))
                    redirect($this->input->get('redir'));
                else
                    redirect(base_url());
            } else
            {
                //something fishy
                $this->load->view('header',array('title'=>'Log in'));
                $loginparms=array('username'=>$this->input->post('username'));
                if ($this->input->get('redir'))
                    $loginparms['redir']=$this->input->get('redir');
                    
                if (!$this->owner->uname_exists($this->input->post('username')))
                {
                    $loginparms['msg']='Username or password is incorrect';
                    $this->load->view('login',$loginparms);
                } else
                {
                    $this->owner->select($this->input->post('username'));
                    if ($this->owner->is_locked)
                        $this->load->view('locked',array('email'=>$this->config->item('email')));
                    else
                    {
                        //password is simply wrong
                        $loginparms['msg']='Username or password is incorrect';
                        $this->load->view('login',$loginparms);
                    }
                }
                $this->load->view('footer');
            }
        }
    }
    public function logout()
    {
        $this->owner->logout();
        redirect(base_url().'auth/login');
    }
}