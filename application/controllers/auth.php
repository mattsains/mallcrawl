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
    public function register()
    {
        if ($this->owner->login(false))
        {
            //already has an account
            if ($this->input->get('redir'))
                redirect($this->input->get('redir'));
            else
                redirect(base_url());
        } 
        else if (!$this->input->post('username'))
        {
            //form hasn't been submitted
            //just show it.
            $this->load->view('header',array('title'=>'Register'));
            $this->load->view('register',array('username'=>'','redir'=>$this->input->get('redir')));
            $this->load->view('footer');
        } else
        {
            //validate registration
            $validate_failed=$this->validate_password($this->input->post('password'));
            $clean_uname=$this->owner->clean_uname($this->input->post('username'));
            
            if ($this->owner->uname_exists($this->input->post('username')))
            {
                $this->load->view('header',array('title'=>'Register'));
                $this->load->view('register',array('msg'=>"Username '$clean_uname' already exists",'username'=>$clean_uname,'redir'=>$this->input->get('redir')));
                $this->load->view('footer');
            } else if ($this->input->post('password')!=$this->input->post('password2'))
            {
                $this->load->view('header',array('title'=>'Register'));
                $this->load->view('register',array('msg'=>"Passwords do not match",'username'=>$clean_uname,'redir'=>$this->input->get('redir')));
                $this->load->view('footer');
            } else if ($validate_failed)
            {
                $this->load->view('header',array('title'=>'Register'));
                $this->load->view('register',array('msg'=>$validate_failed,'username'=>$clean_uname,'redir'=>$this->input->get('redir')));
                $this->load->view('footer');
            } else
            {
                //everything is validated
                $id=$this->owner->add($this->input->post('username'),$this->input->post('password'));
                var_dump($id);
                if ($id)
                    $this->owner->session_begin((int)$id);
                if ($this->input->get('redir'))
                    redirect($this->input->get('redir'));
                else
                    redirect(base_url());
            }
        }
    }
    public function logout()
    {
        $this->owner->logout();
        redirect(base_url().'auth/login');
    }
    
    private function validate_password($p)
    {
        if (strlen($p)<7)
        {
            return 'Your password is too short. It needs to be longer than six characters';
        }
        $badpsws=array('hello1','123456','000000','111111','222222','333333','444444','555555','666666','777777','888888','999999','121212','hellohello');
        foreach($badpsws as $bad)
            if ($bad==$p)
            {
                return "'$bad' is a terrible password!";
            }
        //other rules maybe
        
        return false;
    }
}