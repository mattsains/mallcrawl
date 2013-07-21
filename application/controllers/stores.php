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
        if (count($page)==0) $page=0;
        $page=(int)$page[0];
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
        
        $config['base_url']=base_url().'stores/page';
        $config['total_rows']=$result->count;
        $config['per_page']=20;
        
        $this->pagination->initialize($config);
        
        $sql='SELECT `stores`.`storeid`, `stores`.`name`,`stores`.`manager_name`, `stores`.`phone`,`stores`.`logo`,`types`.`text` AS type,`stores`.`mallid`,`malls`.`name` AS mall, `owners`.`uname` as uname, (SELECT count(*) FROM `store-lists` WHERE `store-lists`.`storeid`=`stores`.`storeid`) AS starred FROM `stores` LEFT JOIN `malls` ON `malls`.`mallid`=`stores`.`mallid` LEFT JOIN `types` ON `stores`.`typeid`=`types`.`typeid` LEFT JOIN `owners` ON `owners`.`ownerid`=`stores`.`ownerid`';
        if (!$this->owner->is_admin)
            $sql.=' WHERE `stores`.`ownerid`='.((int)$this->owner->ownerid);
            
        $sql.=' LIMIT '.$page.', 20';
        
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
                
                //get list of all categories
                $allcats=$this->store->all_categories();
                
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
                        $this->load->view('store-details-edit', array_merge($_POST,array('typeid'=>$this->store->typeid,'categorydata'=>$allcats,'types'=>$this->store->all_types(),'submit_to'=>current_url().'?edit=1')));
                        $this->load->view('footer');
                    } else
                    {
                        //sort out some fields
                        $logopath=NULL;
                        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name']!="")
                        {
                            //we have a new logo to upload
                            $upload=array();
                            $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/stores/".$storeid."/");
                            $upload['allowed_types']='jpg|jpeg|png';
                            $upload['max_size']='100';//kB
                            $upload['max_width']='500';
                            $upload['max_height']='500';//do I care?
                            $upload['encrypt_name']=true; //sacrificing good file names for safety
                            $this->upload->initialize($upload);
                            
                            if (!$this->upload->do_upload('logo'))
                            {
                                //error with upload
                                $this->load->view('header',array('title'=>$this->mall->name));
                                $this->load->view('store-details-edit', array_merge($_POST,array('typeid'=>$this->store->typeid,'types'=>$this->store->all_types(),"logo_err"=>$this->upload->display_errors('<span class="error">','</span>'),'submit_to'=>current_url().'?edit=1')));
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
                        
                        //start a transaction, I don't want this to go half way
                        $this->db->trans_start();
                            //nuke existing categories
                            $this->db->where('storeid',$storeid);
                            $this->db->delete('category-members');
                            //put in new categories
                            $categories=array();
                            foreach ($this->input->post('categories') as $catid=>$junk)
                                $categories[]=array('storeid'=>$storeid, 'categoryid'=>$catid);
                            $this->db->insert_batch('category-members',$categories);
                        $this->db->trans_complete();
                        
                        //we're done, time to redirect to the form
                        redirect(site_url('stores/'.$storeid));
                    }
                } else
                {
                    //just show the form
                    $cats=$this->store->categories();
                    $categories=array();//needs a different structured array, this is it
                    foreach ($cats as $category)
                    {
                        $categories[$category['categoryid']]="on";
                    }
                    $this->load->view('header',array('title'=>$this->store->name));
                    $this->load->view('store-details-edit', array_merge($this->store->as_array(),array('categories'=>$categories,'categorydata'=>$allcats,'types'=>$this->store->all_types(),
                                                                                                       'submit_to'=>current_url().'?edit=1')));
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
    public function _new()
    {
        $this->load->model('owner');
        $this->owner->login();//only logged in users can do this.
        if ($this->input->post('name'))
        {
            $this->load->model(array('store','mall'));
            
            //new store data coming through now
            $this->load->library('form_validation');
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
                $this->load->view('store-details-edit', array_merge($_POST,array('categorydata'=>$this->store->all_categories(),'typeid'=>$this->store->typeid,'types'=>$this->store->all_types(),'submit_to'=>current_url().'?edit=1')));
                $this->load->view('footer');
            } else
            {
                //make sure secret is legit
                $mallid=$this->mall->select_by_secret($this->input->post('secret'));
                if (!$mallid)
                {
                    //we have to boot this request back to the very beginning
                    redirect(current_url());
                    return;
                }
                //now we have a legit mallid
                
                //insert new store
                $to_insert=array('name'=>$this->input->post('name'),
                                 'mallid'=>$mallid,
                                 'manager_name'=>$this->input->post('manager_name'),
                                 'website'=>$this->input->post('website'),
                                 'twitter'=>$this->input->post('twitter'),
                                 'facebook'=>$this->input->post('facebook'),
                                 'phone'=>$this->input->post('phone'),
                                 'email'=>$this->input->post('email'),
                                 'typeid'=>$this->input->post('type'),
                                 'bio'=>$this->input->post('bio'),
                                 'ownerid'=>$this->owner->ownerid);
                $storeid=$this->store->create($to_insert);
                                
                if (!$storeid)
                {
                    show_error('Failed to create the new store :(');
                    return;//dunno if I need this?
                }
                
                //I'm going to make a directory for this store now so we don't have to worry about it later
                if (!is_dir($this->config->item('api-dir')."application/assets/stores/$storeid/"))
                    mkdir($this->config->item('api-dir')."application/assets/stores/$storeid/",0755,true);
                
                //this is a new store so no need to nuke existing categories
                $categories=array();
                foreach ($this->input->post('categories') as $catid=>$junk)
                    $categories[]=array('storeid'=>$storeid, 'categoryid'=>$catid);
                $this->db->insert_batch('category-members',$categories);
                
                //ok now we might have the ugly problem of file uploads
                if (isset($_FILES['logo']['name']) && $_FILES['logo']['name']!="")
                {
                    //ok uploading things are weird because we need a storeid first, which we won't have until we insert into the database
                    //anyway we have a storeid now so it is easier
                    $this->load->library('upload');
                    $upload=array();
                    $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/stores/".$storeid."/");
                    $upload['allowed_types']='jpg|jpeg|png';
                    $upload['max_size']='100';//kB
                    $upload['max_width']='500';
                    $upload['max_height']='500';//do I care?
                    $upload['encrypt_name']=true; //sacrificing good file names for safety
                    $this->upload->initialize($upload);
                    
                    if (!$this->upload->do_upload('logo'))
                    {
                        //error with upload
                        //this is some magic over here: transparently transition to editing the mall
                        $this->load->view('header',array('title'=>$this->mall->name));
                        $this->load->view('store-details-edit', array_merge($_POST,array('typeid'=>$this->store->typeid,'categorydata'=>$this->store->all_categories(),'storeid'=>$storeid,'types'=>$this->store->all_types(),"logo_err"=>$this->upload->display_errors('<span class="error">','</span>'),'submit_to'=>base_url().'stores/'.$storeid.'?edit=1')));
                        $this->load->view('footer');
                        return;
                    }
                    else
                    {
                        $data=$this->upload->data();
                        $logopath=$storeid.'/'.$data['file_name'];
                        $to_update=array('logo'=>$logopath);
                        $this->store->update($to_update);
                    }
                }
                
                //we're done, time to redirect to the form
                redirect(site_url('stores/'.$storeid));
            }
        }
        else if ($this->input->post('verified'))
        {
            //the owner has confirmed the mall, show the form
            
            //well even if the secret is wrong, it'll be checked when this form gets submitted
            $this->load->model('store');
            
            $this->load->view('header',array('title'=>'Add a store'));
            $this->load->view('store-details-edit',array('typeid'=>-1, 'categorydata'=>$this->store->all_categories(),'secret'=>$this->input->post('secret'),'submit_to'=>base_url().'stores/new','types'=>$this->store->all_types()));
            $this->load->view('footer');
        }
        else if ($this->input->post('secret'))
        {
            //make sure the owner wants to add to this mall
            if (!$this->check_secret($this->input->post('secret')))
            {
                $this->load->view('header',array('title'=>'Add a store'));
                $this->load->view('secret-verify',array('msg'=>'That secret is invalid. Secrets contain only numbers and the letters A-F'));
                $this->load->view('footer');
            } else 
            {
                //secret looks right, but does it correspond to a mall?
                $this->load->model('mall');
                $secret=$this->check_secret($this->input->post('secret'));
                
                $mallid=$this->mall->select_by_secret($secret);
                
                if (!$mallid)
                {
                    //no such secret
                    $this->load->view('header',array('title'=>'Add a store'));
                    $this->load->view('secret-verify',array('msg'=>'There is no mall with such a secret','secret'=>$secret));
                    $this->load->view('footer');
                }
                else
                {
                    //this secret is bona fide
                    //show the next form
                    $this->load->view('header',array('title'=>'Add a store'));
                    $this->load->view('secret-verify',array('mall'=>$this->mall->as_array()));
                    $this->load->view('footer');
                }
            }
        } else
        {
            //ask for a secret
            $this->load->view('header',array('title'=>'Add a store'));
            $this->load->view('secret-verify');
            $this->load->view('footer');
        }
    }
    /// Makes sure we have a hexadecimal, and treats it nicely
    public function check_secret($secret)
    {
        $secret=strtolower(trim($secret));
        
        for ($i=0; $i<strlen($secret); $i++)
            if (!in_array($secret[$i],array('0','1','2','3','4','5','6','7','8','9','a','b','c','d','e','f')))
                return false;
        return $secret;
    }
    public function html_special($str)
    {
        return htmlspecialchars($str,ENT_QUOTES);
    }
    public function make_phone($str)
    {
        return preg_replace("/[^0-9]/","",$str);//strips all but numbers
    }
    public function debug()
    {
        var_dump($_POST);
    }
}