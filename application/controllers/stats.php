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
}