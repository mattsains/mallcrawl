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