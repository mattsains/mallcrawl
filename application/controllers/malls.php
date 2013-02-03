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
        if ($this->mall->select($mallid))
        {
            $this->load->model('owner');
            $this->load->model('store');
            
            $this->load->view('header',array('title'=>$this->mall->name,'map'=>array('x_coord'=>$this->mall->x_coord,'y_coord'=>$this->mall->y_coord)));
            $this->load->view('mall-details',array('name'=>$this->mall->name, 'manager_name'=>$this->mall->manager_name, 'stores'=>$this->store->list_mall($mallid)));
            $this->load->view('footer');
        }
    }
}