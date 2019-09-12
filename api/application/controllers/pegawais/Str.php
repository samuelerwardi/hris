<?php 
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");


class Str extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->load->model("His_str_model");
    }

    public function index($id = null)
    {
        $response['success'] = true; 
        if($id <> null){
            $response['data'] = $this->view($id);
        }
        $this->set_response($response);
    }
    private function view($id)
    {
        $datas["result"] = $this->His_str_model->get_all(array("id_user" => $id));
        $view = $this->load->view('pegawai/view_str', $datas, true);
        return $view;
    }

    public function add()
    {
// print_r($_POST);die();
        $config['upload_path'] = 'upload/data';
        $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|xls|doc|xlsx';
        $config['max_size'] = '50000000';
        $this->load->library('upload', $config);
        $filename = 'logo.png';
        if (!$this->upload->do_upload('inputfileupload')) {
            $error = array('error' => $this->upload->display_errors());
        } else {
            $data = array('inputfileupload' => $this->upload->data());
            $filename = $data['inputfileupload']['file_name'];
        }
        $date_start = date_format(date_create($this->input->post('date_start')), "Y-m-d");
        $date_end = date_format(date_create($this->input->post('date_end')), "Y-m-d");

        $datas["id_user"] = ($this->input->post('id_userfile')?$this->input->post('id_userfile'):NULL);
        $datas["str"] = ($this->input->post('str')?$this->input->post('str'):NULL);
        $datas["date_start"] = ($date_start?$date_start:NULL);
        $datas["date_end"] = ($date_end?$date_end:NULL);
        $datas["url"] = ($filename?$filename:NULL);

        $create = $this->His_str_model->create($datas);

        if ($create) {
            $response['success'] = true;
            $response['message'] = 'Data berhasil ditambah!';
            $response['data'] = $this->view($datas["id_user"]);
        } 
        else {
            $response['success'] = false;
            $response['message'] = 'Data Gagal Ditambah!';
        }

        $this->set_response($response);
    }

    public function delete($id)
    {
        $check = $this->His_str_model->delete($id);
        $id_user = $this->input->get("id_userfile");
        if ($check) {
            $response['success'] = true;
            $response['message'] = 'Berhasil!';
        } 
        else {
            $response['success'] = false;
            $response['message'] = 'Gagal!';
        }
        $response['data'] = $this->view($id_user);

        $this->set_response($response);
    }

    public function _remap(){
        $this->indexfixer->remap();
    }
}
?>