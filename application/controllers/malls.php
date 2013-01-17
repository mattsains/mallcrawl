<?php
class Malls extends CI_Controller
{
    /// Returns a list of up to ten malls closest to a location
    public function near()
    {
        if (!($this->input->post('x_coord') && $this->input->post('y_coord'))) 
            error('You did not provide coordinates');
        
        $x_coord=(double)$this->input->post('x_coord');
        $y_coord=(double)$this->input->post('y_coord');
        
        if (abs($x_coord)>180 || abs($y_coord)>180)
            error('Invalid coordinates');
        
        $this->load->model('mall');
        
        $mall_object=array();
        foreach ($this->mall->nearest($x_coord,$y_coord,10) as $mall)
        {
            $this->mall->select($mall);
            $mall_object[]=array('mallid'=>$mall, 'name'=>$this->mall->name, 'x_coord'=>$this->mall->x_coord, 'y_coord'=>$this->mall->y_coord);
        }
        
        send_json(array('malls'=>$mall_object));
    }
}