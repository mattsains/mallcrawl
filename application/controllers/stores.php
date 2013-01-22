<?phpclass Stores extends CI_Controller{    /// Adds a store to a user's list    /// POST: storeid, access_token    public function add()    {        if (!$this->input->post('access_token'))            error('You did not provide a facebook access token');        if (!$this->input->post('storeid'))            error('You did not provide a storeid to add');        $storeid=(int)$this->input->post('storeid');                $this->load->model('store');        $this->load->model('user');                $this->user->initialise_from_token($this->input->post('access_token'));                $this->user->add_store($storeid)) or error('storeid is invalid');        send_json($storeid);    }    /// Removes a store from the user's list    /// POST: storeid, access_token    public function remove()    {        if (!$this->input->post('access_token'))            error('You did not provide a facebook access token');        if (!$this->input->post('storeid'))            error('You did not provide a storeid to add');        $storeid=(int)$this->input->post('storeid');                $this->load->model('store');        $this->load->model('user');                $this->user->initialise_from_token($this->input->post('access_token'));                $this->user->remove_store($storeid) or error('storeid is invalid');        send_json($storeid);    }}