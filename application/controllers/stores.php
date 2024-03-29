<?php
class Stores extends CI_Controller
{
    /// Returns all sorts of info about a store
    public function index()
    {
        if (!$this->input->post('storeid'))
            error('You did not provide a storeid');
        $storeid=(int)$this->input->post('storeid');
        
        $this->load->model('store');
        $this->store->select($storeid) or error('storeid is invalid');
        
        $output=$this->store->as_array();
        $output['categories']=$this->store->categories();
        send_json($output);
    }
    
    /// Adds a store to a user's list
    /// POST: storeid, access_token
    public function add()
    {
        if (!$this->input->post('access_token'))
            error('You did not provide a facebook access token');
        if (!$this->input->post('storeid'))
            error('You did not provide a storeid to add');
        $storeid=(int)$this->input->post('storeid');
        
        $this->load->model('store');
        $this->load->model('user');
        
        $this->user->initialise_from_token($this->input->post('access_token'));
        
        $this->user->add_store($storeid) or error('storeid is invalid');
        send_json($storeid);
    }

    /// Removes a store from the user's list
    /// POST: storeid, access_token
    public function remove()
    {
        if (!$this->input->post('access_token'))
            error('You did not provide a facebook access token');
        if (!$this->input->post('storeid'))
            error('You did not provide a storeid to add');
        $storeid=(int)$this->input->post('storeid');
        
        $this->load->model('store');
        $this->load->model('user');
        
        $this->user->initialise_from_token($this->input->post('access_token'));
        
        $this->user->remove_store($storeid) or error('storeid is invalid');
        send_json($storeid);
    }
    
    /// Returns a list of image urls for a store
    /// POST: storeid, access token
    public function images()
    {
        if (!$this->input->post('storeid'))
            error('You did not provide a storeid');
        if (!$this->input->post('access_token'))
            error('You did not provide an access_token');
        $storeid=(int)$this->input->post('storeid');
        
        $this->load->model('store');
        $this->store->select($storeid) or error('storeid is invalid');
        
        $output=$this->store->images($this->input->post('access_token'));
        send_json($output);
    }
    /// This function responds to stores/images/add!
    /// POST: storeid, image, access_token
    public function images_add()
    {
        if (!$this->input->post('storeid'))
            error('You did not provide a storeid');
        if (!(isset($_FILES['image']['name'])) || ($_FILES['image']['name']==""))
            error('You did not upload an image');
        if (!$this->input->post('access_token'))
            error('You did not provide an access_token');
        $storeid=(int)$this->input->post('storeid');
        
        $this->load->model('user');
        $userid=$this->user->initialise_from_token($this->input->post('access_token'));//check that the token is valid before uploading
        
        /*if($this->user->no_upload)
            error('user is disallowed from uploading.');*/
        
        $this->load->library('upload');
        $this->load->model('store');
        
        if (!$this->store->exists($storeid))
            error('invalid storeid');
        
        //upload validation
        if (!is_dir("./application/assets/stores/$storeid/"))
              mkdir("./application/assets/stores/$storeid/",0755,true);
              
        if (!is_dir("./application/assets/stores/$storeid/thumbs"))
              mkdir("./application/assets/stores/$storeid/thumbs",0755,true);
              
        $upload['upload_path']="./application/assets/stores/$storeid/";
        $upload['allowed_types']='jpg|jpeg|png';
        $upload['max_size']='500';//kB
        $upload['max_width']='2000';//might need to change this some time
        $upload['max_height']='2000';
        $upload['encrypt_name']=true; //sacrificing good file names for safety
        $this->upload->initialize($upload);

        if (!$this->upload->do_upload('image')) // error with upload
            error(strip_tags($this->upload->display_errors()));
            
        //everything is OK with the image
        // make a thumbnail
        $orig_image=$this->upload->data();
        
        $config['image_library'] = 'gd2';
        $config['source_image'] = "./application/assets/stores/$storeid/".$orig_image['file_name'];
        $config['new_image'] = "./application/assets/stores/$storeid/thumbs/".$orig_image['file_name'];
        $config['maintain_ratio'] = true;
        $config['width'] = 32;
        $config['height'] = 32;
        $this->load->library('image_lib',$config);

        if (!$this->image_lib->resize())
            server_error(strip_tags($this->image_lib->display_errors()));
        
        //image resize went OK, now save to the database.
        $this->store->select($storeid);
        $url=$this->store->add_image($userid,$orig_image['full_path'],$config['new_image']);
        if (!$url)
            server_error("couldn't add the image to the database",true);
        else send_json(base_url()."assets/stores/$url");
    }
}