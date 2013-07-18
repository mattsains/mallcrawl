<?php
class Stores extends CI_Controller
{
    function _remap($arg1,$arg2)
    {
        if ($arg1=="index")
            $arg1="page";
            
        if (is_numeric($arg1))
            $this->show($arg1);
        else if ($arg1=='new')
            $this->_new($arg2);
        else
            $this->$arg1($arg2);
    }
    /// Display a list of stores by that owner
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
        
        $result=$this->db->get('stores')->result();
        $result=$result[0];
        
        $config['base_url']=base_url().'stores/';
        $config['total_rows']=$result->count;
        $config['per_page']=20;
        
        $this->pagination->initialize($config);
        
        $sql='SELECT `stores`.`storeid`, `stores`.`name`,`stores`.`manager_name`, `stores`.`phone`,`stores`.`logo`,`types`.`text` AS type,`stores`.`mallid`,`malls`.`name` AS mall, `owners`.`uname` as uname, (SELECT count(*) FROM `store-lists` WHERE `store-lists`.`storeid`=`stores`.`storeid`) AS starred FROM `stores` LEFT JOIN `malls` ON `malls`.`mallid`=`stores`.`mallid` LEFT JOIN `types` ON `stores`.`typeid`=`types`.`typeid` LEFT JOIN `owners` ON `owners`.`ownerid`=`stores`.`ownerid`';
        if (!$this->owner->is_admin)
            $sql.=' WHERE `stores`.`ownerid`='.((int)$this->owner->ownerid);
            
        $sql.=' LIMIT '.$page.', '.($page+20);
        
        $query=$this->db->query($sql);
        
        $table=array();
        foreach($query->result_array() as $row)
        {
            $row['logo']=$row['logo']?$this->config->item('api-path').'assets/stores/'.$row['logo']:false;
            $table[]=$row;
        }
        $this->load->view('header',array('title'=>'Stores'));
        $this->load->view('store-list',array('is_admin'=>$this->owner->is_admin, 'stores'=>$table,'pagination'=>$this->pagination->create_links()));
        $this->load->view('footer');
    }
    /// Shows a single mall's information
    public function show($storeid)
    {  
        $storeid=(int)$storeid;
        $this->owner->login();
        $this->load->model('store');
        
        $this->load->model('owner');
        $this->load->model('mall');
        
        $this->load->library('upload');

        if ($this->store->select($storeid))
        {
            //make sure user is allowed to see this
            if ($this->store->ownerid!=$this->owner->ownerid && (!$this->owner->is_admin))
                show_404(current_url());
            
            if ($this->input->get('edit'))
            {
                //an edit
                $this->load->library('form_validation');
                if ($this->input->post('storeid'))
                {
                    $this->form_validation->set_rules('name','Name','required|trim|max_length[50]|callback_html_special');
                    $this->form_validation->set_rules('manager_name','Manager Name','required|trim|max_length[50]|callback_html_special');
                    $this->form_validation->set_rules('website','Website','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('twitter','Twitter','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('facebook','Facebook','trim|max_length[60]|callback_html_special');
                    $this->form_validation->set_rules('phone','Phone','callback_make_phone|required|max_length[11]');
                    $this->form_validation->set_rules('email','Email','valid_email|max_length[60]');
                    $this->form_validation->set_rules('bio','Bio','trim|callback_html_special');
                    
                    if (!$this->form_validation->run())
                    {
                        //validation failed
                        $this->load->view('header',array('title'=>$this->store->name));
                        $this->load->view('store-details-edit', array_merge($_POST,array('types'=>$this->store->all_types(),'submit_to'=>current_url().'?edit=1')));
                        $this->load->view('footer');
                    } else
                    {
                        //sort out some fields
                        $logopath=NULL;
                        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name']!="")
                        {
                            //we have a new logo to upload
                            $upload=array();
                            $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/stores/".$mallid."/");
                            $upload['allowed_types']='jpg|jpeg|png';
                            $upload['max_size']='100';//kB
                            $upload['max_width']='500';
                            $upload['max_height']='500';//do I care?
                            $upload['encrypt_name']=true; //sacrificing good file names for safety
                            $this->upload->initialize($upload);
                            
                            if (!$this->upload->do_upload('logo'))
                            {
                                //error with upload
                                $this->load->view('header',array('title'=>$this->mall->name,
                                'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                                $this->load->view('store-details-edit', array_merge($_POST,array('types'=>$this->store->all_types(),"logo_err"=>$this->upload->display_errors('<span class="error">','</span>'),'submit_to'=>current_url().'?edit=1')));
                                $this->load->view('footer');
                                return;
                            }
                            else
                            {
                                $data=$this->upload->data();
                                $logopath=$storeid.'/'.$data['file_name'];
                            }
                        }
                        //update database
                        $to_update=array('name'=>$this->input->post('name'),
                                        'manager_name'=>$this->input->post('manager_name'),
                                        'website'=>$this->input->post('website'),
                                        'twitter'=>$this->input->post('twitter'),
                                        'facebook'=>$this->input->post('facebook'),
                                        'phone'=>$this->input->post('phone'),
                                        'email'=>$this->input->post('email'),
                                        'typeid'=>$this->input->post('type'),
                                        'bio'=>$this->input->post('bio'));
                        if (isset($logopath))
                          $to_update['logo']=$logopath;
                        $this->store->update($to_update);
                        //we're done, time to redirect to the form
                        redirect(site_url('stores/'.$storeid));
                    }
                } else
                {
                    //just show the form
                    $this->load->view('header',array('title'=>$this->store->name));
                    $this->load->view('store-details-edit', array_merge($this->store->as_array(),array('types'=>$this->store->all_types(),'submit_to'=>current_url().'?edit=1')));
                    $this->load->view('footer');
                }
            } else
            {
                $store=$this->store->as_array();
                $store['categories']=$this->store->categories();
                if ($this->mall->select($store['mallid']))
                    //attached to a mall. I should hope this is true
                    $store['mall']=$this->mall->name;
                    
                $this->load->view('header',array('title'=>$this->store->name,));
                $this->load->view('store-details',$store);
                $this->load->view('footer');
            } 
        } else
        {
            //store does not exist
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