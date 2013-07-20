<?php
class Malls extends CI_Controller
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
        
        $this->load->library('upload');

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
                    $this->form_validation->set_rules('manager_name','Manager Name','required|trim|max_length[50]|callback_html_special');
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
                        $this->load->view('header',array('title'=>$this->mall->name,
                        'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                        $this->load->view('mall-details-edit', array_merge($_POST,array('submit_to'=>current_url().'?edit=1')));
                        $this->load->view('footer');
                    } else
                    {
                        //sort out some fields
                        $mappath=NULL;
                        $logopath=NULL;
                        if (isset($_FILES['map']['name']) && $_FILES['map']['name']!="")
                        {
                        
                            //we have a new map to upload
                            $upload=array();
                            $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/malls/".$mallid."/");
                            $upload['allowed_types']='jpg|jpeg|png';
                            $upload['max_size']='200';//kB
                            $upload['max_width']='1300';
                            $upload['max_height']='1000';//do I care?
                            $upload['encrypt_name']=true; //sacrificing good file names for safety
                            $this->upload->initialize($upload);
                            
                            if (!$this->upload->do_upload('map'))
                            {
                                //error with upload
                                $this->load->view('header',array('title'=>$this->mall->name,
                                'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                                $this->load->view('mall-details-edit', array_merge($_POST,array("map_err"=>$this->upload->display_errors('<span class="error">','</span>'), 'submit_to'=>current_url().'?edit=1')));
                                $this->load->view('footer');
                                return;
                            }
                            else
                            {
                                $data=$this->upload->data();
                                $mappath=$mallid.'/'.$data['file_name'];
                            }
                        }
                        if (isset($_FILES['logo']['name']) && $_FILES['logo']['name']!="")
                        {
                            //we have a new logo to upload
                            $upload=array();
                            $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/malls/".$mallid."/");
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
                                $this->load->view('mall-details-edit', array_merge($_POST,array("logo_err"=>$this->upload->display_errors('<span class="error">','</span>'),'submit_to'=>current_url().'?edit=1')));
                                $this->load->view('footer');
                                return;
                            }
                            else
                            {
                                $data=$this->upload->data();
                                $logopath=$mallid.'/'.$data['file_name'];
                            }
                        }
                        //TODO
                        //update database
                        $to_update=array('name'=>$this->input->post('name'),
                                        'manager_name'=>$this->input->post('manager_name'),
                                        'website'=>$this->input->post('website'),
                                        'twitter'=>$this->input->post('twitter'),
                                        'facebook'=>$this->input->post('facebook'),
                                        'phone'=>$this->input->post('phone'),
                                        'email'=>$this->input->post('email'),
                                        'bio'=>$this->input->post('bio'));
                        if (isset($mappath))
                          $to_update['map']=$mappath;
                        if (isset($logopath))
                          $to_update['logo']=$logopath;
                        $this->mall->update($to_update);
                        //we're done, time to redirect to the form
                        redirect(site_url('malls/'.$mallid));
                    }
                } else
                {
                    //just show the form
                    $this->load->view('header',array('title'=>$this->mall->name,'map'=>array('edit'=>'yes','x_coord'=>$this->mall->x_coord,'y_coord'=>$this->mall->y_coord)));
                    $this->load->view('mall-details-edit', array_merge($this->mall->as_array(),array('submit_to'=>current_url().'?edit=1')));
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
    public function _new()
    {
        $this->load->model('owner');
        $this->load->model('mall');
        $this->load->helper('bytes');
        //make sure there is an owner logged in
        $this->owner->login();
        $this->load->library('form_validation');
        $this->load->library('upload');
        if ($this->input->post('name'))//form data to check
        {
            $this->form_validation->set_rules('name','Name','required|trim|max_length[50]|callback_html_special');
            $this->form_validation->set_rules('manager_name','Manager Name','required|trim|max_length[50]|callback_html_special');
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
                $this->load->view('header',array('title'=>'New mall',
                'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                $this->load->view('mall-details-edit', array_merge($_POST,array('submit_to'=>current_url())));
                $this->load->view('footer');
            } else
            {
                
                //insert into database
                $to_insert=array('name'=>$this->input->post('name'),
                                'manager_name'=>$this->input->post('manager_name'),
                                'x_coord'=>$this->input->post('x_coord'),
                                'y_coord'=>$this->input->post('y_coord'),
                                'website'=>$this->input->post('website'),
                                'twitter'=>$this->input->post('twitter'),
                                'facebook'=>$this->input->post('facebook'),
                                'phone'=>$this->input->post('phone'),
                                'email'=>$this->input->post('email'),
                                'bio'=>$this->input->post('bio'),
                                'secret'=>rand_hex(8),
                                'ownerid'=>$this->owner->ownerid);
                $newmallid=$this->mall->create($to_insert);
                var_dump($newmallid);
                //add a directory to put mall assets into
                if (!is_dir($this->config->item('api-dir')."application/assets/malls/$newmallid/"))
                    mkdir($this->config->item('api-dir')."application/assets/malls/$newmallid/",0755,true);
                //sort out some fields
                $mappath=NULL;
                $logopath=NULL;
                if (isset($_FILES['map']['name']) && $_FILES['map']['name']!="")
                {
                
                    //we have a new map to upload
                    $upload=array();
                    $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/malls/".$newmallid."/");
                    $upload['allowed_types']='jpg|jpeg|png';
                    $upload['max_size']='200';//kB
                    $upload['max_width']='1300';
                    $upload['max_height']='1000';//do I care?
                    $upload['encrypt_name']=true; //sacrificing good file names for safety
                    $this->upload->initialize($upload);
                    
                    if (!$this->upload->do_upload('map'))
                    {
                        //error with upload
                        //here is a fancy trick:
                        // we have already added this mall to the database, so we can't just abort because the image is bad
                        // so instead we re-show the form, but have it submit to the edit handler
                        $this->load->view('header',array('title'=>$this->mall->name,
                        'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                        $this->load->view('mall-details-edit', array_merge($_POST,array('map_err'=>$this->upload->display_errors('<span class="error">','</span>'),
                                                                                        'submit_to'=>site_url('malls/'.$newmallid).'?edit=1')));
                        $this->load->view('footer');
                        return;
                    }
                    else
                    {
                        $data=$this->upload->data();
                        $mappath=$mallid.'/'.$data['file_name'];
                    }
                }
                if (isset($_FILES['logo']['name']) && $_FILES['logo']['name']!="")
                {
                    //we have a new logo to upload
                    $upload=array();
                    $upload['upload_path']=realpath($this->config->item('api-dir')."/application/assets/malls/".$newmallid."/");
                    $upload['allowed_types']='jpg|jpeg|png';
                    $upload['max_size']='200';//kB
                    $upload['max_width']='1300';
                    $upload['max_height']='1000';//do I care?
                    $upload['encrypt_name']=true; //sacrificing good file names for safety
                    $this->upload->initialize($upload);
                    
                    if (!$this->upload->do_upload('logo'))
                    {
                        //error with upload
                        //same fancy trick
                        $this->load->view('header',array('title'=>$this->mall->name,
                        'map'=>array('edit'=>'yes','x_coord'=>$this->input->post('x_coord'),'y_coord'=>$this->input->post('y_coord'))));
                        $this->load->view('mall-details-edit', array_merge($_POST,array('logo_err'=>$this->upload->display_errors('<span class="error">','</span>'),
                                                                                        'submit_to'=>site_url('malls/'.$newmallid).'?edit=1')));
                        $this->load->view('footer');
                        return;
                    }
                    else
                    {
                        $data=$this->upload->data();
                        $logopath=$mallid.'/'.$data['file_name'];
                    }
                }
                
                $to_update=array();
                if (isset($mappath))
                  $to_update['map']=$mappath;
                if (isset($logopath))
                  $to_update['logo']=$logopath;
                if (isset($mappath) || isset($logopath))
                    $this->mall->update($to_update);
                //we're done, time to redirect to the form
                redirect(site_url('malls/'.$newmallid));
            }
        } else
        {
            //just show the form
            $this->load->view('header',array('title'=>'New mall','map'=>array('x_coord'=>'-33.93472657551387','y_coord'=>'25.569795862731894')));
            $this->load->view('mall-details-edit', array('submit_to'=>current_url(),
                                                         'mallid'=>'', 'name'=>'', 'manager_name'=>'', 'x_coord'=>'-33.93472657551387', 'y_coord'=>'25.569795862731894', 'website'=>'', 'twitter'=>'', 'facebook'=>'', 'phone'=>'', 'email'=>'', 'bio'=>''));
            $this->load->view('footer');
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