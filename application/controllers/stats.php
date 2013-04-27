<?php
class Stats extends CI_Controller
{
    public function view($table)
    {
        $tables=array('store','mall');
        if (!in_array($table,$tables))
            show_404(current_url());
        
        if (!$this->input->post($table.'id'))
            error('You did not provide a '.$table.'id');
        $this->load->model($table);
        $id=(int)$this->input->post($table.'id');
        
        if (!$this->$table->exists($id))
            error($table.'id is invalid');
        
        $this->load->database();
        
        $insert=array('table'=>$table, 'id'=>$id);
        
        $this->db->insert('stats',$insert);
        
        send_json($id);
    }
    public function viewcount($table)
    {
        $tables=array('store','mall');
        if (!in_array($table,$tables))
            show_404(current_url());
        
        if (!$this->input->post($table.'id'))
            error('You did not provide a '.$table.'id');
        
        $id=(int)$this->input->post($table.'id');
        
        $this->load->database();
        
        //check if the [table]id exists
        $query=$this->db->query('SELECT `'.$table.'id` FROM `'.$table.'s` WHERE `'.$table.'id`='.$id);
        if ($query->num_rows()==0)
            error($table.'id is invalid');
        
        $query=$this->db->query('SELECT count(*) AS count FROM `stats` WHERE `table`="'.$table.'" AND `id`='.$id);
        $result=$query->result();
        $result=$result[0];
        
        send_json((int)$result->count);
    }
    public function stars()
    {
        $this->load->database();
        if (!($this->input->post('storeid') || $this->input->post('mallid')))
        {
            //No store has been specified, show a leaderboard
            $query=$this->db->query('SELECT storeid, count(*) AS stars FROM `store-lists` GROUP BY storeid ORDER BY stars DESC LIMIT 0,20');
            $output=array();
            foreach ($query->result() as $row)
            {
                $output[]=array('storeid'=>$row->storeid, 'stars'=>$row->stars);
            }
            send_json($output);
        } else if ($this->input->post('storeid'))
        {
            //A single store, we should return how many stars that store has
            $storeid=(int)$this->input->post('storeid');
            //check for existance
            $query=$this->db->query('SELECT storeid from `stores` where `storeid`='.$storeid);
            if ($query->num_rows==0)
                error('storeid is invalid');
            //get number of stars
            $query=$this->db->query('SELECT count(*) as count from `store-lists` where `storeid`='.$storeid);
            $result=$query->result();
            $result=$result[0];
            send_json((int)$result->count);
        }
        else if ($this->input->post('mallid'))
        {
            //A mall, we should return a list of store's stars
            $mallid=(int)$this->input->post('mallid');
            //check for existance
            $query=$this->db->query('SELECT mallid from `malls` where `mallid`='.$mallid);
            if ($query->num_rows==0)
                error('mallid is invalid');
            //get list of number of stars
            $output=array();
            $this->db->select('storeid');
            $this->db->where('mallid',$mallid);
            $query=$this->db->get('stores');
            foreach($query->result() as $row)
            {
                $storequery=$this->db->query('SELECT count(*) as count from `store-lists` where `storeid`='.$row->storeid);
                $result=$storequery->result();
                $result=$result[0];
                $output[]=array('storeid'=>(int)$row->storeid, 'stars'=>(int)$result->count);
            }
            send_json($output);
        }
    }
    /// Checks the health of the API
    /// ASSUMES THERE IS AT LEAST ONE MALL
    public function status()
    {
        try
        {
            $this->load->model('mall');
            $mallid=$this->mall->nearest(1,1,10);
            $mallid=$mallid[0];
            $this->mall->select($mallid);
            send_json('API is healthy');
        } catch (Exception $e)
        {
            server_error('API is unhealthy!');
        }
    }
}