<?php
class Malls extends CI_Controller
{

    function _remap($arg1,$arg2)
    {
        if ($arg1=="index")
            $arg1="page";
            
        if (is_numeric($arg1))
            $this->show($arg1);
        else
            $this->$arg1($arg2);
    }
    /// Display a list of malls by that owner
    /// If the user is an admin, shows all the malls
    public function page($page=0)
    {
        $page=(int)$page;
        $this->owner->login();
        //might as well paginate
        $this->load->library('pagination');
        
        //some naughty database work because we'll only use this once
        $this->load->database();
        
        $this->db->select('count(*) AS count',false);
        if (!$this->owner->is_admin)
            $this->db->where('ownerid',$this->owner->ownerid);
        
        $result=$this->db->get('malls')->result();
        $result=$result[0];
        
        $config['base_url']=base_url().'malls/';
        $config['total_rows']=$result->count;
        $config['per_page']=20;
        
        $this->pagination->initialize($config);
        
        $sql='SELECT `malls`.`mallid`, `malls`.`logo`, `malls`.`name`,`malls`.`ownerid`, `owners`.`uname`, `malls`.`manager_name`,`malls`.`phone`, (SELECT count(*) FROM `mall-lists` WHERE `mall-lists`.`mallid`=`malls`.`mallid`) AS starred FROM malls';
        $sql.=' LEFT JOIN `owners` ON (`malls`.`ownerid`=`owners`.`ownerid`)';
        if (!$this->owner->is_admin)
            $sql.=' WHERE `malls`.`ownerid`='.((int)$this->owner->ownerid);
            
        $sql.=' LIMIT '.$page.', '.($page+20);
        
        $query=$this->db->query($sql);
        
        $table=array();
        foreach($query->result_array() as $row)
        {
            $row['logo']=$row['logo']?$this->config->item('api-path').'assets/malls/'.$row['logo']:false;
            $table[]=$row;
        }
        $this->load->view('header',array('title'=>'Malls'));
        $this->load->view('mall-list',array('is_admin'=>$this->owner->is_admin, 'malls'=>$table,'pagination'=>$this->pagination->create_links()));
        $this->load->view('footer');
    }
    /// Shows a single mall's information
    public function show($mallid)
    {  
        $mallid=(int)$mallid;
        $this->owner->login();
        $this->load->model('mall');
        
        $this->load->model('owner');
        $this->load->model('store');
        
        if ($this->mall->select($mallid))
        {
            //make sure user is allowed to see this
            if ($this->mall->ownerid!=$this->owner->ownerid && (!$this->owner->is_admin))
                show_404(current_url());
            
            if ($this->input->get('edit'))
            {
                //an edit
                $this->load->library('form_validation');
                if ($this->input->post('mallid'))
                {
                    $this->form_validation->set_rules('name','Name','required|trim|max_length[50]|callback_html_special');
                    $this->form_validation->set_rules('manager','Manager Name','required|trim|max_length[50]|callback_html_special');
                    $this->form_validation->set_rules('website','Website','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('twitter','Twitter','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('facebook','Facebook','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('phone','Phone','callback_make_phone|required|max_length[11]');
                    $this->form_validation->set_rules('email','Email','valid_email|max_length[60]');
                    $this->form_validation->set_rules('bio','Bio','trim|callback_html_special');
                    
                    $this->form_validation->set_rules('x_coord','required|numeric');
                    $this->form_validation->set_rules('y_coord','required|numeric');

                    if (!$this->form_validation->run())
                    {
                        //validation failed
                        $this->load->view('header',array('title'=>$this->mall->name,'map'=>'edit','map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                        $this->load->view('mall-details-edit', $this->mall->as_array());
                        $this->load->view('footer');
                    } else
                    {
                        //update database
                    }
                } else
                {
                    //just show the form
                    $this->load->view('header',array('title'=>$this->mall->name,'map'=>'edit','map'=>array('edit'=>'yes','x_coord'=>$this->mall->x_coord,'y_coord'=>$this->mall->y_coord)));
                    $this->load->view('mall-details-edit', $this->mall->as_array());
                    $this->load->view('footer');
                }
            } else
            {
                $mall=$this->mall->as_array();
                $mall['stores']=$this->store->list_mall($mallid);
                $this->load->view('header',array('title'=>$this->mall->name,'map'=>array('x_coord'=>$this->mall->x_coord,'y_coord'=>$this->mall->y_coord)));
                $this->load->view('mall-details',$mall);
                $this->load->view('footer');
            } 
        } else
        {
            //mall does not exist
            show_404(current_url());
        }
    }
    public function html_special($str)
    {
        return htmlspecialchars($str,ENT_QUOTES);
    }
    public function make_phone($str)
    {
        return preg_replace("/[^0-9]/","",$str);//strips all but numbers
    }
}