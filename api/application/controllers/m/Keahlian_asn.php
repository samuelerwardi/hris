<?php
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require APPPATH . '/libraries/REST_Controller.php'; 
$rest_json = file_get_contents("php://input");
$_POST = json_decode($rest_json, true);




/*
* Changes:
* 1. This project contains .htaccess file for windows machine.
*    Please update as per your requirements.
*    Samples (Win/Linux): http://stackoverflow.com/questions/28525870/removing-index-php-from-url-in-codeigniter-on-mandriva
*
* 2. Change 'encryption_key' in application\config\config.php
*    Link for encryption_key: http://jeffreybarke.net/tools/codeigniter-encryption-key-generator/
* 
* 3. Change 'jwt_key' in application\config\jwt.php
*
*/
date_default_timezone_set('Asia/Jakarta');
class Keahlian_asn extends REST_Controller
{
/**
* URL: http://localhost/CodeIgniter-JWT-Sample/auth/token
* Method: GET
*/
var $table='m_keahlian_asn_detail';
var $perpage = 20;


public function listdata_get(){
	$headers = $this->input->request_headers();

	if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
		$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
		if ($decodedToken != false) {


			if(!empty($this->input->get('id'))){
				$this->db->where('id',$this->input->get('id'));
			}

			if(!empty($this->uri->segment(4))){
				$this->db->like("nama",$this->uri->segment(4)); 
			}
			$total_rows = $this->db->count_all_results($this->table);
			$pagination = create_pagination_endless('/m/Keahlian_asn/0/', $total_rows,$this->perpage,5);

			$this->db->select('m_keahlian_asn_detail.*,dm_term.nama as keahlian');
			$this->db->join('dm_term', 'dm_term.id = m_keahlian_asn_detail.kode_ahli', 'LEFT');

			if(!empty($this->input->get('id'))){
				$this->db->where('m_keahlian_asn_detail.id',$this->input->get('id'));
			}
			if(!empty($this->uri->segment(4))){
				$this->db->like("m_keahlian_asn_detail.nama",$this->uri->segment(4)); 
			}
			$this->db->where('m_keahlian_asn_detail.tampilkan','1');
			$this->db->limit($pagination['limit'][0], $pagination['limit'][1]);
			$res = $this->db->get($this->table)->result();

			if(!empty($res)){
				foreach($res as $dat){
					$arr['result'][]= array('id'=> $dat->id,'nama'=> $dat->nama,'keahlian'=> $dat->keahlian,'kode_ahli'=> $dat->kode_ahli,'tgl_update'=> $dat->tgl_update,'no_peg_update'=> $dat->no_peg_update,);
				}
				$arr['total']=$total_rows;
				$arr['paging'] = $pagination['limit'][1];
				$arr['perpage']=$this->perpage;
			}else{
				$arr['result'] ='empty';
			}
			$this->set_response($arr, REST_Controller::HTTP_OK);

			return;
		}
	}

	$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}


public function save_post(){
	$headers = $this->input->request_headers();

	if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
		$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
		if ($decodedToken != false) {
			$id_user=$decodedToken->data->id;

			if(!empty($this->input->post('id'))){
//edit
				$id= $this->input->post('id');
				$arr=array(
					'nama'=> ($this->input->post('nama')?$this->input->post('nama'):NULL),
					'kode_ahli'=> ($this->input->post('kode_ahli')?$this->input->post('kode_ahli'):NULL),
					'tgl_update'=> date('Y-m-d H:i:s'),
'no_peg_update'=> $id_user,);;//array('nama'=>$this->input->post('nama'));
				$this->db->where('id',$id);
				$this->db->update($this->table,$arr);
			}else{
//save
				$arr=array('nama'=> ($this->input->post('nama')?$this->input->post('nama'):NULL),
					'kode_ahli'=> ($this->input->post('kode_ahli')?$this->input->post('kode_ahli'):NULL),
					'tgl_update'=> date('Y-m-d H:i:s'),
'no_peg_update'=> $id_user,);;//array('nama'=>$this->input->post('nama'));

				$this->db->insert($this->table,$arr);
			}



			if($this->db->affected_rows() == '1'){
				$arr['hasil']='success';
				$arr['message']='Data berhasil ditambah!';
			}else{
				$arr['hasil']='error';
				$arr['message']='Data Gagal Ditambah!';
			}


			$this->set_response($arr, REST_Controller::HTTP_OK);

			return;
		}
	}

	$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}


public function delete_get(){
	$headers = $this->input->request_headers();

	if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
		$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
		if ($decodedToken != false) {

			if(!empty($this->input->get('id'))){
//edit
				$id= $this->input->get('id'); 
				$this->db->where('id',$id);
				$this->db->update($this->table,array('tampilkan'=>'0'));
			} 



			if($this->db->affected_rows() == '1'){
				$arr['hasil']='success';
				$arr['message']='Data berhasil dihapus!';
			}else{
				$arr['hasil']='error';
				$arr['message']='Data Gagal dihapus!';
			}


			$this->set_response($arr, REST_Controller::HTTP_OK);

			return;
		}
	}

	$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function getoption_get(){
	$headers = $this->input->request_headers();

	if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
		$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
		if ($decodedToken != false) {

			if(!empty($this->uri->segment('4'))){
				$this->db->where('kode_ahli',$this->uri->segment('4'));
			}
			$this->db->order_by('id','ASC');

			$res = $this->db->get($this->table)->result();

			if(!empty($res)){

				foreach($res as $d){
					$arr['result'][]=array('label'=>$d->nama,'value'=>$d->id);
				}
			}else{
				$arr['result'][]=array('label'=>'No Data','value'=>'');
			}

			$this->set_response($arr, REST_Controller::HTTP_OK);

			return;
		}
	}

	$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}


}