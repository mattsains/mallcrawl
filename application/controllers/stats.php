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
    public function stars()
    {
        $this->load->database();
        if (!$this->input->post('storeid'))
        {
            //No store has been specified, show a leaderboard
            $query=$this->db->query('SELECT storeid, count(*) AS stars FROM `store-lists` GROUP BY storeid ORDER BY stars DESC LIMIT 0,20');
            $output=array();
            foreach ($query->result() as $row)
            {
                $output[]=array('storeid'=>$row->storeid, 'stars'=>$row->stars);
            }
            send_json($output);
        } else
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