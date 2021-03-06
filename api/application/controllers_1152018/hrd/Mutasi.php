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

class Mutasi extends REST_Controller
{
    /**
     * URL: http://localhost/CodeIgniter-JWT-Sample/auth/token
     * Method: GET
     */
	 var $table='abk_req_mutasi_jabatan';
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
					$this->db->like("user_id",$this->uri->segment(4)); 
				 }
				$total_rows = $this->db->count_all_results($this->table);
				$pagination = create_pagination_endless('/hrd/Mutasi/0/', $total_rows,$this->perpage,5);
				  
				if(!empty($this->input->get('id'))){
					$this->db->where('id',$this->input->get('id'));
				}
				if(!empty($this->uri->segment(4))){
					$this->db->like("user_id",$this->uri->segment(4)); 
				 }
				  $this->db->where('tampilkan','1');
				  $this->db->limit($pagination['limit'][0], $pagination['limit'][1]);
				  $res = $this->db->get($this->table)->result();
				  
			if(!empty($res)){
				 foreach($res as $dat){
					$arr['result'][]= array('id'=> $dat->id,'user_id'=> $dat->user_id,'direktorat_asal'=> $dat->direktorat_asal,'bagian_asal'=> $dat->bagian_asal,'sub_bagian_asal'=> $dat->sub_bagian_asal,'direktorat_tujuan'=> $dat->direktorat_tujuan,'bagian_tujuan'=> $dat->bagian_tujuan,'sub_bagian_tujuan'=> $dat->sub_bagian_tujuan,'tgl_mutasi'=> $dat->tgl_mutasi,'keterangan'=> $dat->keterangan,'tampilkan'=> $dat->tampilkan,'aktif'=> $dat->aktif,'no_sk'=> $dat->no_sk,'tgl_sk'=> $dat->tgl_sk,'id_satker'=> $dat->id_satker,'id_kelas'=> $dat->id_kelas,'jabatan_struktural'=> $dat->jabatan_struktural,'status'=> $dat->status,'grup'=> $dat->grup,'author'=> $dat->author,);
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

				if(!empty($this->input->post('id_hrd'))){
					//edit
					$id= $this->input->post('id_hrd');
					$arr=array('user_id'=> $this->input->post('user_id'),'direktorat_asal'=> $this->input->post('direktorat_asal'),'bagian_asal'=> $this->input->post('bagian_asal'),'sub_bagian_asal'=> $this->input->post('sub_bagian_asal'),'direktorat_tujuan'=> $this->input->post('direktorat_tujuan'),'bagian_tujuan'=> $this->input->post('bagian_tujuan'),'sub_bagian_tujuan'=> $this->input->post('sub_bagian_tujuan'),'tgl_mutasi'=> $this->input->post('tgl_mutasi'),'keterangan'=> $this->input->post('keterangan'),'tampilkan'=> $this->input->post('tampilkan'),'aktif'=> $this->input->post('aktif'),'no_sk'=> $this->input->post('no_sk'),'tgl_sk'=> $this->input->post('tgl_sk'),'id_satker'=> $this->input->post('id_satker'),'id_kelas'=> $this->input->post('id_kelas'),'jabatan_struktural'=> $this->input->post('jabatan_struktural'),'status'=> $this->input->post('status'),'grup'=> $this->input->post('grup'),'author'=> $this->input->post('author'),);;//array('nama'=>$this->input->post('nama'));
					$this->db->where('id',$id);
					$this->db->update($this->table,$arr);
				}else{
					//save
					$arr=array('user_id'=> $this->input->post('user_id'),'direktorat_asal'=> $this->input->post('direktorat_asal'),'bagian_asal'=> $this->input->post('bagian_asal'),'sub_bagian_asal'=> $this->input->post('sub_bagian_asal'),'direktorat_tujuan'=> $this->input->post('direktorat_tujuan'),'bagian_tujuan'=> $this->input->post('bagian_tujuan'),'sub_bagian_tujuan'=> $this->input->post('sub_bagian_tujuan'),'tgl_mutasi'=> $this->input->post('tgl_mutasi'),'keterangan'=> $this->input->post('keterangan'),'tampilkan'=> $this->input->post('tampilkan'),'aktif'=> $this->input->post('aktif'),'no_sk'=> $this->input->post('no_sk'),'tgl_sk'=> $this->input->post('tgl_sk'),'id_satker'=> $this->input->post('id_satker'),'id_kelas'=> $this->input->post('id_kelas'),'jabatan_struktural'=> $this->input->post('jabatan_struktural'),'status'=> $this->input->post('status'),'grup'=> $this->input->post('grup'),'author'=> $this->input->post('author'),);;//array('user_id'=>$this->input->post('nama'));
					 
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
	
	  
}