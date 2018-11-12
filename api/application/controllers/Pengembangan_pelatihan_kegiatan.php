<?php
error_reporting(0);
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

class Pengembangan_pelatihan_kegiatan extends REST_Controller
{
    /**
     * URL: http://localhost/CodeIgniter-JWT-Sample/auth/token
     * Method: GET
     */
    public function __construct()
    {
        parent::__construct();
        //Do your magic here
        $this->load->model('Pengembangan_pelatihan_kegiatan_model');
    }

    public function list_get($offset = 0, $param_search = "")
    {
        $search = null;
        $limit = 1000;
        $headers = $this->input->request_headers();
        
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization']) || true) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            if ($decodedToken != false || true) {
                if (!empty($param_search)) {
                    $search["field"] = array("nama");
                    $search["search"] = $param_search;
                }
                $results['result'] = $this->Pengembangan_pelatihan_kegiatan_model->get_all(null, $search, $offset, $limit);
                // echo $this->db->last_query();die;
                $results['total'] = count($this->Pengembangan_pelatihan_kegiatan_model->get_all());
                $results['limit'] = $limit;
                $this->set_response($results, REST_Controller::HTTP_OK);
                return;
            }
        }

        $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function save_post()
    {
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            if ($decodedToken != false) {
                $save["no_disposisi"] = $this->input->post("no_disposisi");
                $save["laporan"] = $this->input->post("laporan");
                $save["monev"] = $this->input->post("monev");
                $save["jenis"] = $this->input->post("jenis");
                $save["jenis_biaya"] = $this->input->post("jenis_biaya");
                $save["jenis_perjalanan"] = $this->input->post("jenis_perjalanan");
                $save["dalam_negeri"] = $this->input->post("dalam_negeri");
                $save["surat_tugas_dalam_negeri"] = $this->input->post("surat_tugas_dalam_negeri");
                $save["surat_tugas_luar_negeri"] = $this->input->post("surat_tugas_luar_negeri");
                $save["nopeg"] = $this->input->post("nopeg");
                $save["nama_pegawai"] = $this->input->post("nama_pegawai");
                $save["jabatan"] = $this->input->post("jabatan");

                $tanggal = $this->input->post("tanggal");

                $biaya_uraian = $this->input->post("biaya_uraian");
                $biaya_nominal = $this->input->post("biaya_nominal");

                // echo "<pre>";
                // print_r($save);
                // echo "</pre>";
                // die;
                $result = $this->Pengembangan_pelatihan_kegiatan_model->create($save);
                if ($result){
                    if (!empty($biaya_uraian)) {
                        foreach ($biaya_uraian as $key => $value) {
                            $pengembangan_pelatihan_uraian_biaya[$key]["pengembangan_pelatihan_id"] = $result->id;
                            $pengembangan_pelatihan_uraian_biaya[$key]["biaya_uraian"] = @$value["value"];
                            $pengembangan_pelatihan_uraian_biaya[$key]["biaya_nominal"] = @$biaya_nominal[$key]["value"];
                        }
                        $this->Pengembangan_pelatihan_kegiatan_model->create_detail("pengembangan_pelatihan_uraian_biaya", $pengembangan_pelatihan_uraian_biaya);
                    }
                    if (!empty($tanggal)) {
                        foreach ($tanggal as $key => $value) {
                            $tanggal_1 = @$value["value"];
                            $tanggal_explode = explode(" - ", $tanggal_1);
                            // dibagi 24jam x 8 jam
                            $tanggal_diff = (strtotime($tanggal_explode[1]) - strtotime($tanggal_explode[0])) / 86400 * 8;
                            $pengembangan_pelatihan_pelaksanaan[$key]["pengembangan_pelatihan_id"] = $result->id;
                            $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_from"] = @$tanggal_explode[0];
                            $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_to"] = @$tanggal_explode[1];
                            $pengembangan_pelatihan_pelaksanaan[$key]["total_jam"] = $tanggal_diff;                            
                        }
                        $this->Pengembangan_pelatihan_kegiatan_model->create_detail("pengembangan_pelatihan_pelaksanaan", $pengembangan_pelatihan_pelaksanaan);
                    }


                    $response['hasil'] = 'success';
                    $response['message'] = 'Data berhasil ditambah!';
                }
                else{
                    $response['hasil'] = 'failed';
                    $response['message'] = 'Data gagal ditambah!';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                }
                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
            }
        }
        $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }


    public function edit_post()
    {
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            if ($decodedToken != false) {
                $save["id"] = $this->input->post("id");
                $save["no_disposisi"] = $this->input->post("no_disposisi");
                $save["laporan"] = $this->input->post("laporan");
                $save["monev"] = $this->input->post("monev");
                $save["jenis"] = $this->input->post("jenis");
                $save["jenis_biaya"] = $this->input->post("jenis_biaya");
                $save["jenis_perjalanan"] = $this->input->post("jenis_perjalanan");
                $save["dalam_negeri"] = $this->input->post("dalam_negeri");
                $save["surat_tugas_dalam_negeri"] = $this->input->post("surat_tugas_dalam_negeri");
                $save["surat_tugas_luar_negeri"] = $this->input->post("surat_tugas_luar_negeri");
                $save["nopeg"] = $this->input->post("nopeg");
                $save["nama_pegawai"] = $this->input->post("nama_pegawai");
                $save["jabatan"] = $this->input->post("jabatan");

                $tanggal = $this->input->post("tanggal");
                $biaya_uraian = $this->input->post("biaya_uraian");
                $biaya_nominal = $this->input->post("biaya_nominal");

                $result = $this->Pengembangan_pelatihan_kegiatan_model->update($save["id"], $save);
                if ($result){
                    $this->Pengembangan_pelatihan_kegiatan_model->delete_detail("pengembangan_pelatihan_uraian_biaya", $save["id"]);
                    $this->Pengembangan_pelatihan_kegiatan_model->delete_detail("pengembangan_pelatihan_pelaksanaan", $save["id"]);

                    if (!empty($biaya_uraian)) {
                        foreach ($biaya_uraian as $key => $value) {
                            $pengembangan_pelatihan_uraian_biaya[$key]["pengembangan_pelatihan_id"] = $result->id;
                            $pengembangan_pelatihan_uraian_biaya[$key]["biaya_uraian"] = @$value["value"];
                            $pengembangan_pelatihan_uraian_biaya[$key]["biaya_nominal"] = @$biaya_nominal[$key]["value"];
                        }
                        $this->Pengembangan_pelatihan_kegiatan_model->create_detail("pengembangan_pelatihan_uraian_biaya", $pengembangan_pelatihan_uraian_biaya);
                    }
                    if (!empty($tanggal)) {
                        foreach ($tanggal as $key => $value) {
                            $tanggal_1 = @$value["value"];
                            $tanggal_explode = explode(" - ", $tanggal_1);
                            // dibagi 24jam x 8 jam
                            $tanggal_diff = (strtotime($tanggal_explode[1]) - strtotime($tanggal_explode[0])) / 86400 * 8;
                            $pengembangan_pelatihan_pelaksanaan[$key]["pengembangan_pelatihan_id"] = $result->id;
                            $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_from"] = @$tanggal_explode[0];
                            $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_to"] = @$tanggal_explode[1];
                            $pengembangan_pelatihan_pelaksanaan[$key]["total_jam"] = $tanggal_diff;
                        }
                        $this->Pengembangan_pelatihan_kegiatan_model->create_detail("pengembangan_pelatihan_pelaksanaan", $pengembangan_pelatihan_pelaksanaan);
                    }


                    $response['hasil'] = 'success';
                    $response['message'] = 'Data berhasil ditambah!';
                }
                else{
                    $response['hasil'] = 'failed';
                    $response['message'] = 'Data gagal ditambah!';
                    $this->set_response($response, REST_Controller::HTTP_OK);
                }
                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
            }
        }

        $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function delete_get()
    {
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            if ($decodedToken != false) {
                $id = $this->input->get("id");
                $this->Pengembangan_pelatihan_kegiatan_model->delete($id);

                $response['hasil'] = 'success';
                $response['message'] = 'Data berhasil dihapus!';
                $this->set_response($response, REST_Controller::HTTP_OK);
                return;
            }
        }

        $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function get_get()
    {
        $results["success"] = false;
        $id = $this->input->get('id');
        $result = $this->Pengembangan_pelatihan_kegiatan_model->get_all(array("id" => $id), null, $offset, $limit);

       if (count($result) == 1) {
            $result = $result[0];
            $results["success"] = true;
            $tanggal = $this->Pengembangan_pelatihan_kegiatan_model->get_detail("pengembangan_pelatihan_pelaksanaan", $result["id"]);
            $biaya_uraian = $this->Pengembangan_pelatihan_kegiatan_model->get_detail("pengembangan_pelatihan_uraian_biaya", $result["id"]);
            $result["tanggal"] = $tanggal;
            $result["biaya_uraian"] = $biaya_uraian;
            $results["data"] = $result;
       }

       $this->set_response($results, REST_Controller::HTTP_OK);
    }
}