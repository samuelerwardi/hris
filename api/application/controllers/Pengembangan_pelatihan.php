<?php
// echo "<pre>";
// print_r($result);
// echo "<pre>";
// die;
error_reporting(0);
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: PUT, GET, POST");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

require APPPATH . '/libraries/REST_Controller.php';
require APPPATH . '/controllers/Monitoring.php';
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

class Pengembangan_pelatihan extends REST_Controller
{
/**
* URL: http://localhost/CodeIgniter-JWT-Sample/auth/token
* Method: GET
*/
public function __construct()
{
    parent::__construct();
//Do your magic here
    $this->load->model('Pengembangan_pelatihan_model');
    $this->load->model('Pengembangan_pelatihan_kegiatan_model');
    $this->load->model('Pengembangan_pelatihan_kegiatan_status_model');
    $this->load->model('System_auth_model');
}

public function preview_get()
{
    $jenis_surat = $this->input->get("surat");
    $id = $this->input->get("id");
    $kode = $this->input->get("kode");
    $results = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);
// print_r($jenis_surat);die;
    if (!empty($results)) {
        $result = $results[0];
        $createdby = $this->db->select("username")->where(array("id_user" => $result["createdby"]))->get("sys_user")->result_array();
        $updatedby = $this->db->select("username")->where(array("id_user" => $result["updatedby"]))->get("sys_user")->result_array();
        if (count($createdby) == 1) {
            $result["createdby"] = $createdby[0]["username"];
        }
        if (count($updatedby) == 1) {
            $result["updatedby"] = $updatedby[0]["username"];
        }
        $tanggal = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $result["id"]));
        $result["tanggal"] = $tanggal;
        $result["tanggal"]["tanggal_now"] = bulan(date("m")) ." ". date("Y");
        $result["tanggal"]["tanggal_to"] = date("d",strtotime($result["tanggal"][0]["tanggal_to"]))." ".bulan(date("m",strtotime($result["tanggal"][0]["tanggal_to"]))) ." ".date("Y",strtotime($result["tanggal"][0]["tanggal_to"]));
        $result["tanggal"]["tanggal_from"] = date("d",strtotime($result["tanggal"][0]["tanggal_from"]))." ".bulan(date("m",strtotime($result["tanggal"][0]["tanggal_from"]))) ." ".date("Y",strtotime($result["tanggal"][0]["tanggal_from"]));
        $result["created"]["date"] = date("d",strtotime($result["created"]))." ".bulan(date("m",strtotime($result["created"]))) ." ".date("Y",strtotime($result["created"]));
        $result["tanggal"]["day_to"] = hari_indo(date("D",strtotime($result["tanggal"][0]["tanggal_to"])));
        $result["tanggal"]["day_from"] = hari_indo(date("D",strtotime($result["tanggal"][0]["tanggal_from"])));
        $result["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($result["phl"]);

// print_r($result);die;
        $result["footer"]=true;
        $result["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($result["pengembangan_pelatihan_kegiatan"]);
        $result["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($result["pengembangan_pelatihan_kegiatan_status"]);
        if(!empty($kode)){
            $result["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
        }
//print_r($kode);die;
        foreach ($results as $key => $value) {
            $result["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
            $result["count"] = count($result["detail"]);
            $result["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));	
            $result["golongan"] = $this->Pengembangan_pelatihan_model->get_akomodasi("pengembangan_pelatihan_detail", array("golongan" => $result["detail"][$key]["golongan"], "pengembangan_pelatihan_id" => $value["id"]));	
            $result["count_golongan"] = count($result["golongan"]);
            $result["count_detail"] = count($result["detail_uraian"]);
//if (!empty($result["golongan"])) {
//foreach ($result["golongan"] as $key => $value){
//$count=count($result["golongan"]);
//$result["jumlah"] += $value["akomodasi"];

//}
//}
//if (!empty($result["detail_uraian"])) {
//foreach ($result["detail_uraian"] as $keya => $valuea){
//$jum=count($result["detail_uraian"]);
//if($jum==1){
//$result["tota"] = $value["total"];
//}else{
//$result["tota_1"] += $valuea["total"];
//}
//}
//}

        }

    }
//print_r($result);die;
//$this->load->library("pdf");
    $data = "test";
    if ($result['jenis_perjalanan'] == "Dalam Negeri") {
        if ($result['jenis_surat'] == "Surat Tugas") {
            if ($result["jenis"] == "Kelompok") {
                if ($jenis_surat == "RAK") {
                    $html = $this->load->view("surat", array("result" => $result), true);
                }else if ($jenis_surat == "Surat_verbal") {
                    $html = $this->load->view("view_pdf_15", array("result" => $result), true);
                }else if ($jenis_surat == "dft") {
                    $html = $this->load->view("dftar", array("result" => $result), true);
                }else{
                    $html = $this->load->view("view_pdf_0", array("result" => $result), true);
                }
            } else if ($result["jenis"] == "Individu"){
                if ($jenis_surat == "RAK") {
                    $html = $this->load->view("surat", array("result" => $result), true);
                }else if ($jenis_surat == "Surat_verbal") {
                    $html = $this->load->view("view_pdf_15", array("result" => $result), true);
                }else{
                    $html = $this->load->view("view_pdf_1", array("result" => $result), true);
                }
            }
        } else if ($result['jenis_surat'] == "Surat Izin") {
            if ($result["jenis_biaya"] == "Sponsor") {
                $html = $this->load->view("view_pdf_2", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_verbal") {
                $html = $this->load->view("view_pdf_15", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_5", array("result" => $result), true);
            }
        }
    }else{
        if ($result['jenis_surat'] == "Surat Tugas") {
            if ($jenis_surat == "RAK") {
                $html = $this->load->view("surat", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_verbal") {
                $html = $this->load->view("view_pdf_15", array("result" => $result), true);
            }else if ($jenis_surat == "dft") {
                $html = $this->load->view("dftar", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_pengantar") {
                $html = $this->load->view("view_pdf_11", array("result" => $result), true);
            }else if ($jenis_surat == "nota") {
                $html = $this->load->view("view_pdf_12", array("result" => $result), true);
            }else if ($jenis_surat == "ikatan") {
                $html = $this->load->view("view_pdf_13", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_9", array("result" => $result), true);
            }
        } else if ($result['jenis_surat'] == "Surat Izin") {
            if ($jenis_surat == "Surat_pengantar") {
                $html = $this->load->view("view_pdf_11", array("result" => $result), true);
            }else if ($jenis_surat == "nota") {
                $html = $this->load->view("view_pdf_12", array("result" => $result), true);
            }else if ($jenis_surat == "ikatan") {
                $html = $this->load->view("view_pdf_13", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_verbal") {
                $html = $this->load->view("view_pdf_15", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_10", array("result" => $result), true);
            }
        }
    }

    echo $html;
    die;
}

public function cetak_get()
{
    $id = $this->input->get("id");
    $jenis_surat = $this->input->get("surat");
    $kode = $this->input->get("kode");
    $results = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);
// print_r($results);die;
    if (!empty($results)) {
        $result = $results[0];
        $createdby = $this->db->select("username")->where(array("id_user" => $result["createdby"]))->get("sys_user")->result_array();
        $updatedby = $this->db->select("username")->where(array("id_user" => $result["updatedby"]))->get("sys_user")->result_array();
        if (count($createdby) == 1) {
            $result["createdby"] = $createdby[0]["username"];
        }
        if (count($updatedby) == 1) {
            $result["updatedby"] = $updatedby[0]["username"];
        }
        $tanggal = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $result["id"]));
        $result["tanggal"] = $tanggal;
        $result["tanggal"]["tanggal_now"] = bulan(date("m")) ." ". date("Y");
        $result["tanggal"]["tanggal_to"] = date("d",strtotime($result["tanggal"][0]["tanggal_to"]))." ".bulan(date("m",strtotime($result["tanggal"][0]["tanggal_to"]))) ." ".date("Y",strtotime($result["tanggal"][0]["tanggal_to"]));
        $result["tanggal"]["tanggal_from"] = date("d",strtotime($result["tanggal"][0]["tanggal_from"]))." ".bulan(date("m",strtotime($result["tanggal"][0]["tanggal_from"]))) ." ".date("Y",strtotime($result["tanggal"][0]["tanggal_from"]));
        $result["created"]["date"] = date("d",strtotime($result["created"]))." ".bulan(date("m",strtotime($result["created"]))) ." ".date("Y",strtotime($result["created"]));
        $result["tanggal"]["day_to"] = hari_indo(date("D",strtotime($result["tanggal"][0]["tanggal_to"])));
        $result["tanggal"]["day_from"] = hari_indo(date("D",strtotime($result["tanggal"][0]["tanggal_from"])));
// print_r($result);die;
        $result["footer"]=false;
        $result["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($result["phl"]);
        $result["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($result["pengembangan_pelatihan_kegiatan"]);
        $result["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($result["pengembangan_pelatihan_kegiatan_status"]);
        if(!empty($kode)){
            $result["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
        }
        foreach ($results as $key => $value) {
            $result["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
            $result["count"] = count($result["detail"]);
            $result["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));	
            $result["golongan"] = $this->Pengembangan_pelatihan_model->get_akomodasi("pengembangan_pelatihan_detail", array("golongan" => $result["detail"][$key]["golongan"], "pengembangan_pelatihan_id" => $value["id"]));	
            $result["count_golongan"] = count($result["golongan"]);
            $result["count_detail"] = count($result["detail_uraian"]);
            if (!empty($result["golongan"])) {
                foreach ($result["golongan"] as $key => $value){
                    $count=count($result["golongan"]);
                    $result["jumlah"] += $value["akomodasi"];
                }
            }

//if (!empty($result["detail"])) {
//foreach ($result["detail"] as $key_detail_biaya => $value_detail_biaya) {
//$result["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
//$result["count_detail"] = count($result["detail"][$key_detail_biaya]["detail_uraian"]);
//}
//}
        }
    }
//print_r($result);die;
    $this->load->library("pdf");
    $data = "test";
    $kertas="A4";
    if ($result['jenis_perjalanan'] == "Dalam Negeri") {
        if ($result['jenis_surat'] == "Surat Tugas") {
            if ($result["jenis"] == "Kelompok") {
                if ($jenis_surat == "RAK") {
                    $html = $this->load->view("surat", array("result" => $result), true);
                }else if ($jenis_surat == "Surat_verbal") {
                    $html = $this->load->view("view_pdf_15", array("result" => $result), true);
                }else if ($jenis_surat == "dft") {
                    $html = $this->load->view("dftar", array("result" => $result), true);
                }else{
                    $html = $this->load->view("view_pdf_0", array("result" => $result), true);
                }
            } else if ($result["jenis"] == "Individu"){
                if ($jenis_surat == "RAK") {
                    $html = $this->load->view("surat", array("result" => $result), true);
                } else if ($jenis_surat == "Surat_verbal") {
                    $html = $this->load->view("view_pdf_15", array("result" => $result), true);
                }else{
                    $html = $this->load->view("view_pdf_1", array("result" => $result), true);
                }
            }
        } else if ($result['jenis_surat'] == "Surat Izin") {
            if ($result["jenis_biaya"] == "Sponsor") {
                $html = $this->load->view("view_pdf_2", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_5", array("result" => $result), true);
            }
        }
    }else{
        if ($result['jenis_surat'] == "Surat Tugas") {
            if ($jenis_surat == "RAK") {
                $html = $this->load->view("surat", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_verbal") {
                $html = $this->load->view("view_pdf_15", array("result" => $result), true);
            }else if ($jenis_surat == "dft") {
                $html = $this->load->view("dftar", array("result" => $result), true);
            }else if ($jenis_surat == "Surat_pengantar") {
                $html = $this->load->view("view_pdf_11", array("result" => $result), true);
            }else if ($jenis_surat == "nota") {
                $html = $this->load->view("view_pdf_12", array("result" => $result), true);
            }else if ($jenis_surat == "ikatan") {
                $html = $this->load->view("view_pdf_13", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_9", array("result" => $result), true);
                $kertas="Legal";
            }
        } else if ($result['jenis_surat'] == "Surat Izin") {
            if ($jenis_surat == "Surat_pengantar") {
                $html = $this->load->view("view_pdf_11", array("result" => $result), true);
            }else if ($jenis_surat == "nota") {
                $html = $this->load->view("view_pdf_12", array("result" => $result), true);
            }else if ($jenis_surat == "ikatan") {
                $html = $this->load->view("view_pdf_13", array("result" => $result), true);
            }else{
                $html = $this->load->view("view_pdf_10", array("result" => $result), true);
                $kertas="Legal";
            }
        }
    }

//echo $kertas;
//die;

    $this->pdf->loadHtml($html);
    $this->pdf->setPaper($kertas, ($orientation = "P" ));
    $this->pdf->set_option("isPhpEnabled", true);
    $this->pdf->set_option("isHtml5ParserEnabled", true);
    $this->pdf->set_option("isRemoteEnabled", true);
    $this->pdf->render();
    $name = "download ".$jenis_surat;
    $this->pdf->stream($name, array("Attachment" => 1));
// return true;
}

public function preview_rekomendasi_get()
{
// echo date("d") ." ". bulan(date("m")) ." ". date("Y"); die();
    $id = $this->input->get("id");
    $kode = $this->input->get("kode");
    $results['result'] = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);
// echo $this->db->last_query();die;
// print_r($results);die;
    if (!empty($results['result'])) {
        foreach ($results["result"] as $key => $value) {
//print_r($results["result"][$key]["jenis_biaya"]);die;
            if ($results["result"][$key]["jenis_perjalanan"] != "Luar Negeri") {
                if ($results["result"][$key]["jenis_biaya"] == "Sponsor") {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
                    $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
                    $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
                    $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail"])) {
                        foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                            $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                        }
                    }
//print_r($results["result"][$key]);die;
                    $this->load->library("pdf");
                    $html = $this->load->view("view_pdf_3", array("result" => $results["result"][$key]), true);
                    echo $html;
                    die;
                }
            }else{
                $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                if (count($createdby) == 1) {
                    $results["result"][$key]["createdby"] = $createdby[0]["username"];
                }
                if (count($updatedby) == 1) {
                    $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                }
                $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
                $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
                $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
                $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
                $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
                $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                if (!empty($results["result"][$key]["detail"])) {
                    foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                        $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                    }
                }
//print_r($results["result"][$key]);die;
                $this->load->library("pdf");
                $html = $this->load->view("view_pdf_3", array("result" => $results["result"][$key]), true);
                echo $html;
                die;
            }
        }
    }



}

public function cetak_rekomendasi_get()
{
// echo date("d") ." ". bulan(date("m")) ." ". date("Y"); die();
    $id = $this->input->get("id");
    $kode = $this->input->get("kode");
    $results['result'] = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);
// echo $this->db->last_query();die;
// print_r($results);die;
    if (!empty($results['result'])) {
        foreach ($results["result"] as $key => $value) {
//print_r($results["result"][$key]["jenis_biaya"]);die;
            if ($results["result"][$key]["jenis_perjalanan"] != "Luar Negeri") {
                if ($results["result"][$key]["jenis_biaya"] == "Sponsor") {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
                    $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
                    $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
                    $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail"])) {
                        foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                            $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                        }
                    }
//print_r($results["result"][$key]);die;
                    $this->load->library("pdf");
                    $html = $this->load->view("view_pdf_3", array("result" => $results["result"][$key]), true);
//echo $html;
//die;
                    $customPaper = array(0,0,210,330);
                    $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
                    $this->pdf->setPaper("legal", ($orientation = "P" ));
                    $this->pdf->set_option("isPhpEnabled", true);
                    $this->pdf->set_option("isHtml5ParserEnabled", true);
                    $this->pdf->set_option("isRemoteEnabled", true);
                    $this->pdf->render();
                    $name = "download rekomendasi";
                    $this->pdf->stream($name, array("Attachment" => 1));

                }
            }else{
                $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                if (count($createdby) == 1) {
                    $results["result"][$key]["createdby"] = $createdby[0]["username"];
                }
                if (count($updatedby) == 1) {
                    $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                }
                $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
                $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
                $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
                $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
                $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
                $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                if (!empty($results["result"][$key]["detail"])) {
                    foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                        $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                    }
                }
//print_r($results["result"][$key]);die;
                $this->load->library("pdf");
                $html = $this->load->view("view_pdf_3", array("result" => $results["result"][$key]), true);
//echo $html;
//die;
                $customPaper = array(0,0,210,330);
                $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
                $this->pdf->setPaper("legal", ($orientation = "P" ));
                $this->pdf->set_option("isPhpEnabled", true);
                $this->pdf->set_option("isHtml5ParserEnabled", true);
                $this->pdf->set_option("isRemoteEnabled", true);
                $this->pdf->render();
                $name = "download rekomendasi";
                $this->pdf->stream($name, array("Attachment" => 1));

            }
        }
    }



}

public function preview_spd_get()
{
// echo date("d") ." ". bulan(date("m")) ." ". date("Y"); die();
    $id = $this->input->get("id");
    $kode = $this->input->get("kode");
    $results['result'] = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);

// $html = $this->load->view("view_pdf_4");
//                 echo $html;
//                 die;
// echo $this->db->last_query();die;
// print_r($results);die;
    if (!empty($results['result'])) {
        $results["result"][0]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail_spd("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $id));	
        $results["result"][0]["count_detail"] = count($results["result"][0]["detail_uraian"]);
        if (!empty($results["result"][0]["detail_uraian"])) {
            foreach ($results["result"][0]["detail_uraian"] as $key => $value){
                $jum=count($results["result"][0]["detail_uraian"]);
                if($jum==1){
                    $result["tota"] = $value["total"];
                }else{
                    $result["tota_1"] += $value["total"];
                }
            }
        }
//echo $result["jumlah"];die();
        if($jum==1){
            $results["result"][0]["total"] = $result["tota"]+($results["result"][0]["jumlah"]);
            $results["result"][0]["total_biaya"] = terbilang($results["result"][0]["total"]);
        }else{
            $results["result"][0]["total"] = ($result["tota_1"])+$results["result"][0]["jumlah"];
            $results["result"][0]["total_biaya"] = terbilang($results["result"][0]["total"]);
        }
        foreach ($results["result"] as $key => $value) {
//print_r($results["result"][$key]["jenis_biaya"]);die;
//if ($results["result"][$key]["jenis_biaya"] == "BLU") {
            $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
            $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
            if (count($createdby) == 1) {
                $results["result"][$key]["createdby"] = $createdby[0]["username"];
            }
            if (count($updatedby) == 1) {
                $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
            }
            $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
            $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
            $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
            $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
            $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
            $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
            $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
            $results["result"][$key]["count"] = count($results["result"][$key]["detail"]);
//if (!empty($result["detail"])) {
//if (!empty($results["result"][$key]["detail"])) {

//    foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
//        $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
//    }
//}

            $this->load->library("pdf");
            if ($results["result"][$key]['jenis_perjalanan'] == "Dalam Negeri") {
                $html = $this->load->view("view_pdf_4", array("result" => $results["result"][$key]), true);
            }else{
                $html = $this->load->view("view_pdf_14", array("result" => $results["result"][$key]), true);
            }
            echo $html;
            die;
//}
        }
    }



}

public function cetak_spd_get()
{
// echo date("d") ." ". bulan(date("m")) ." ". date("Y"); die();
    $id = $this->input->get("id");
    $kode = $this->input->get("kode");
    $results['result'] = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);

// $html = $this->load->view("view_pdf_4");
//                 echo $html;
//                 die;
// echo $this->db->last_query();die;
// print_r($results);die;
    if (!empty($results['result'])) {
        $results["result"][0]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail_spd("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $id));	
        $results["result"][0]["count_detail"] = count($results["result"][0]["detail_uraian"]);
        if (!empty($results["result"][0]["detail_uraian"])) {
            foreach ($results["result"][0]["detail_uraian"] as $key => $value){
                $jum=count($results["result"][0]["detail_uraian"]);
                if($jum==1){
                    $result["tota"] = $value["total"];
                }else{
                    $result["tota_1"] += $value["total"];
                }
            }
        }
//echo $result["jumlah"];die();
        if($jum==1){
            $results["result"][0]["total"] = $result["tota"]+($results["result"][0]["jumlah"]);
            $results["result"][0]["total_biaya"] = terbilang($results["result"][0]["total"]);
        }else{
            $results["result"][0]["total"] = ($result["tota_1"])+$results["result"][0]["jumlah"];
            $results["result"][0]["total_biaya"] = terbilang($results["result"][0]["total"]);
        }
        foreach ($results["result"] as $key => $value) {
//print_r($results["result"][$key]["jenis_biaya"]);die;
            if ($results["result"][$key]["jenis_biaya"] == "BLU") {
                $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                if (count($createdby) == 1) {
                    $results["result"][$key]["createdby"] = $createdby[0]["username"];
                }
                if (count($updatedby) == 1) {
                    $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                }
                $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($kode);
                $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                $results["result"][$key]["tanggal"]["now"] = bulan(date("m")) ." ". date("Y");
                $results["result"][$key]["tanggal"]["from"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_from"]));
                $results["result"][$key]["tanggal"]["to"] = date("d",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))." ".bulan(date("m",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]))) ." ".date("Y",strtotime($results["result"][$key]["tanggal"][$key]["tanggal_to"]));
                $results["result"][$key]["aprove_phl"] = $this->Pengembangan_pelatihan_model->get_phl($results["result"][$key]["phl"]);
                $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                $results["result"][$key]["count"] = count($results["result"][$key]["detail"]);
//print_r($results["result"][$key]);die;
                $this->load->library("pdf");
                if ($results["result"][$key]['jenis_perjalanan'] == "Dalam Negeri") {
                    $html = $this->load->view("view_pdf_4", array("result" => $results["result"][$key]), true);
                    $kertas="Legal";
                }else{
                    $html = $this->load->view("view_pdf_14", array("result" => $results["result"][$key]), true);
                    $kertas="Legal";
}// echo $html;
// die;
$customPaper = array(0,0,210,330);
$this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
$this->pdf->setPaper($kertas, ($orientation = "P" ));
$this->pdf->set_option("isPhpEnabled", true);
$this->pdf->set_option("isHtml5ParserEnabled", true);
$this->pdf->set_option("isRemoteEnabled", true);
$this->pdf->render();
$name = "download SPD";
$this->pdf->stream($name, array("Attachment" => 1));
}
}
}



}

public function preview_laporan_get()
{
    $offset = 0;
    $filt="pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan_status,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.institusi,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan_detail.id ,pengembangan_pelatihan.id, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan_pelaksanaan.tanggal_from, dm_term.nama, sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup,pengembangan_pelatihan_detail.nama_pegawai";
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jenis = $this->input->get("jenis");
    $jenis_perjalanan = $this->input->get("jenis_perjalanan");
    $kegiatan = $this->input->get("kegiatan");
    $jenis_surat = $this->input->get("surat");
    $direktorat = $this->input->get("direktorat");
    $txtbagian = $this->input->get("txtbagian");
    $unitkerja = $this->input->get("unitkerja");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_new(null, $nopeg, $offset, 500, $from, $to, null, null, $filt, $unit, $kegiatan, $jenis, $jenis_perjalanan, $direktorat, $txtbagian, $unitkerja);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $result["result"][$key]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
            if (!empty($result["result"][$key]["detail_uraian"])) {
                foreach ($result["result"][$key]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {


                    if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                        if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)!=$value_detail_biaya["uraian"]){
                            $result["result"][$key]["nom"] += $value_detail_biaya["pernominal"];
                        }
                    }
                    if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)==$value_detail_biaya["uraian"]){
                        $result["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
                    }
                    $result["result"][$key]["pernominal"]=$result["result"][$key]["nominal_gol"]+$result["result"][$key]["nom"];
                }
            }
        }
    }
//print_r($result["result"]);die();
    $data = "test";
    if($jenis_surat=="laporan1"){
        $html = $this->load->view("laporan/laporan_1", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan3"){
        $html = $this->load->view("laporan/laporan_3", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan5"){
        $html = $this->load->view("laporan/laporan_5", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan20"){
        $html = $this->load->view("laporan/laporan_20", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan7"){
        $html = $this->load->view("laporan/laporan_7", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan8"){
        $html = $this->load->view("laporan/laporan_8", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan16"){
        $html = $this->load->view("laporan/laporan_16", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan017"){
        $html = $this->load->view("laporan/laporan_017", array("result" => $result['result']), true);
    }
    echo $html;
    die;
}

public function laporan_view_get()
{
    $offset = 0;
    $jenis_surat = $this->input->get("surat");
    $direktorat = $this->input->get("direktorat");
    $txtbagian = $this->input->get("txtbagian");
    $unitkerja = $this->input->get("unitkerja");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_unit($offset, null, $direktorat, $txtbagian, $unitkerja);

//print_r($jenis_perjalanan);die();
    $data = "test";
    if($jenis_surat=="laporan20"){
        $html = $this->load->view("laporan/laporan_20", array("result" => $result['result']), true);
    }
    echo $html;
    die;
}

public function cetak_laporan_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }$nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    if (!empty($this->input->get("jenis"))) {
        $jenis = $this->input->get("jenis");
    }else{
        $jenis =null;
    }
    $jenis_perjalanan = $this->input->get("jenis_perjalanan");
    $kegiatan = $this->input->get("kegiatan");
    $filt="pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan_status,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.institusi,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan_detail.id ,pengembangan_pelatihan.id, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan_pelaksanaan.tanggal_from, dm_term.nama, sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup,pengembangan_pelatihan_detail.nama_pegawai";
    $jenis_surat = $this->input->get("surat");
//print_r($result['result']);die();

    $direktorat = $this->input->get("direktorat");
    $txtbagian = $this->input->get("txtbagian");
    $unitkerja = $this->input->get("unitkerja");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_new(null, $nopeg, $offset, 500, $from, $to, null, null, $filt, $unit, $kegiatan, $jenis, $jenis_perjalanan, $direktorat, $txtbagian, $unitkerja);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $result["result"][0]["awal"]=$from;
            $result["result"][0]["akhir"]=$to;
            $result["result"][$key]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
            if (!empty($result["result"][$key]["detail_uraian"])) {
                foreach ($result["result"][$key]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {


                    if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                        if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)!==$value_detail_biaya["uraian"]){

                            $result["result"][$key]["nom"] += $value_detail_biaya["pernominal"];
                        }
                    }
                    if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)==$value_detail_biaya["uraian"]){
                        $result["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
                    }
                    $result["result"][$key]["pernominal"]=$result["result"][$key]["nominal_gol"]+$result["result"][$key]["nom"];
                }
            }
        }
//print_r($result['result']);die();
//print_r($result["result"][0]["profesi"]);die;
        $data = "test";
        $kertas='landscape';
        $this->load->library("pdf");
        if($jenis_surat=="laporan1"){
            $html = $this->load->view("laporan/laporan_1", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan3"){
            $html = $this->load->view("laporan/laporan_3", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan5"){
            $html = $this->load->view("laporan/laporan_5", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan18"){
            $html = $this->load->view("laporan/laporan_18", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan19"){
            $html = $this->load->view("laporan/laporan_19", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan20"){
            $html = $this->load->view("laporan/laporan_20", array("result" => $result['result']), true);
            $kertas="P";
        }else if($jenis_surat=="laporan7"){
            $html = $this->load->view("laporan/laporan_7", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan8"){
            $html = $this->load->view("laporan/laporan_8", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan16"){
            $html = $this->load->view("laporan/laporan_16", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan017"){
            $html = $this->load->view("laporan/laporan_017", array("result" => $result['result']), true);
        }

//echo $html;
//die;
//$customPaper = array(0,0,210,330);
        $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
        $this->pdf->setPaper("A4",$kertas);
        $this->pdf->set_option("isPhpEnabled", true);
        $this->pdf->set_option("isHtml5ParserEnabled", true);
        $this->pdf->set_option("isRemoteEnabled", true);
        $this->pdf->render();
        $name = "download Laporan";
        $this->pdf->stream($name, array("Attachment" => 1));
    }            
}

public function preview_laporan_jpl_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }$nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jpl = (($this->input->get("jpl")*8)/11);
	//print_r($jpl);die();
    $jenis = $this->input->get("jenis");
    $total_pegawai = $this->input->get("total_pegawai");
    $kegiatan = $this->input->get("kegiatan");
    $jenis_surat = $this->input->get("surat");
    if($jenis_surat=="laporan18"){
        $id_grup=$this->Pengembangan_pelatihan_model->getjpl();       
    }
    $filtr = "pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup, pengembangan_pelatihan_detail.nama_pegawai, pengembangan_pelatihan_detail.nopeg";
    $result['result'] = $this->Pengembangan_pelatihan_model->get_jpl(null, $nopeg, $offset, 500, $from, $to, null, null, $filtr, $unit, $kegiatan, $jenis, $jpl, $id_grup);
	//print_r($get);die;
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $result['result'][0]['total_pegawai']=$total_pegawai;
        }
    }
    $data = "test";
    if($jenis_surat=="laporan18"){
        $html = $this->load->view("laporan/laporan_18", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan19"){
        $html = $this->load->view("laporan/laporan_19", array("result" => $result['result']), true);
    }
    echo $html;
    die;
}

public function cetak_laporan_jpl_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }$nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
	$jpl = (($this->input->get("jpl")*8)/11);
	$jenis = $this->input->get("jenis");
    $kegiatan = $this->input->get("kegiatan");
    $jenis_surat = $this->input->get("surat");
    $filtr = "pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup, pengembangan_pelatihan_detail.nama_pegawai, pengembangan_pelatihan_detail.nopeg";

	//print_r($jenis_surat);die();
    if($jenis_surat=="laporan18"){
        $id_grup=$this->Pengembangan_pelatihan_model->getjpl();       
    }
    $filtr = "pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup, pengembangan_pelatihan_detail.nama_pegawai, pengembangan_pelatihan_detail.nopeg";
    $result['result'] = $this->Pengembangan_pelatihan_model->get_jpl(null, $nopeg, $offset, 500, $from, $to, null, null, $filtr, $unit, $kegiatan, $jenis, $jpl, $id_grup);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $result['result'][0]['total_pegawai']=$total_pegawai;
            $result["result"][0]["awal"]=$from;
            $result["result"][0]["akhir"]=$to;
        }

//print_r($result["result"][0]["profesi"]);die;
        $data = "test";
        $this->load->library("pdf");
        if($jenis_surat=="laporan18"){
            $html = $this->load->view("laporan/laporan_18", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan19"){
            $html = $this->load->view("laporan/laporan_19", array("result" => $result['result']), true);
        }
//echo $html;
//die;
//$customPaper = array(0,0,210,330);
        $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
        $this->pdf->setPaper("A4",'P');
        $this->pdf->set_option("isPhpEnabled", true);
        $this->pdf->set_option("isHtml5ParserEnabled", true);
        $this->pdf->set_option("isRemoteEnabled", true);
        $this->pdf->render();
        $name = "download Laporan";
        $this->pdf->stream($name, array("Attachment" => 1));
    }            
}

public function preview_laporan_del_get()
{
    $offset = 0;
    $filt="pengembangan_pelatihan_detail.nopeg, pengembangan_pelatihan_detail.berkas,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan_status,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.institusi,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan_detail.id ,pengembangan_pelatihan.id, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan_pelaksanaan.tanggal_from, dm_term.nama, sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup,pengembangan_pelatihan_detail.nama_pegawai";
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }$nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jenis = $this->input->get("jenis");
    $kegiatan = $this->input->get("kegiatan");
    $jenis_surat = $this->input->get("surat");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_new_del(null, $nopeg, $offset, 500, $from, $to, null, null, $filt, $unit, $kegiatan, $jenis);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
        }
    }
//print_r($result["result"]);die;
    $data = "test";
    if($jenis_surat=="laporan14"){
        $html = $this->load->view("laporan/laporan_14", array("result" => $result['result']), true);
    }
    echo $html;
    die;
}

public function cetak_laporan_del_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jenis = $this->input->get("jenis");
    $kegiatan = $this->input->get("kegiatan");
    $filt="pengembangan_pelatihan_detail.nopeg, pengembangan_pelatihan_detail.berkas,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan_status,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.institusi,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan_detail.id ,pengembangan_pelatihan.id, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan_pelaksanaan.tanggal_from, dm_term.nama, sys_user_profile.gelar_depan, sys_user_profile.gelar_belakang, sys_grup_user.grup,pengembangan_pelatihan_detail.nama_pegawai";
    $jenis_surat = $this->input->get("surat");
//print_r($jenis_surat);die();

    $result['result'] = $this->Pengembangan_pelatihan_model->get_new(null, $nopeg, $offset, 500, $from, $to, null, null, $filt, $unit, $kegiatan, $jenis);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $result["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
            $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);                
            $result["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_kegiatan_model->by_id($value["id"]);
            $result["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
            $result["result"][0]["awal"]=$from;
            $result["result"][0]["akhir"]=$to;
        }

//print_r($result["result"][0]["profesi"]);die;
        $data = "test";
        $this->load->library("pdf");
        if($jenis_surat=="laporan14"){
            $html = $this->load->view("laporan/laporan_14", array("result" => $result['result']), true);
        }
//echo $html;
//die;
//$customPaper = array(0,0,210,330);
        $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
        $this->pdf->setPaper("A4",'landscape');
        $this->pdf->set_option("isPhpEnabled", true);
        $this->pdf->set_option("isHtml5ParserEnabled", true);
        $this->pdf->set_option("isRemoteEnabled", true);
        $this->pdf->render();
        $name = "download Laporan";
        $this->pdf->stream($name, array("Attachment" => 1));
    }            
}

public function preview_laporan2_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $unit = $this->input->get("unit_ker");
    $jenis1 = $this->input->get("jenis1");
    $kegiatan1 = $this->input->get("kegiatan1");
    $jenis_surat = $this->input->get("surat");
    if($jenis_surat=="laporan9"){
        $group_prof="m_kode_profesi_group.ds_group_jabatan";
        $as_prof="m_kode_profesi_group.ds_group_jabatan as profesi";
        $group="m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_kegiatan.nama";
        $filt="m_kode_profesi_group.ds_group_jabatan";
        $as="m_kode_profesi_group.ds_group_jabatan as profesi,pengembangan_pelatihan_kegiatan.nama as kegiatan";
        $pegawai="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
        $pegawai_filter="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,m_kode_profesi_group.ds_group_jabatan as profesi, pengembangan_pelatihan_kegiatan.nama as kegiatan, sum(pengembangan_pelatihan_detail.uraian_total) as nominal, sum(pengembangan_pelatihan.total_hari_kerja) as hari,count(m_kode_profesi_group.ds_group_jabatan) as jum";
        $id="profesi";
        $kegiatan="kegiatan";
        $filt_kegiatan="pengembangan_pelatihan_kegiatan.nama";
    }else if ($jenis_surat=="laporan10"){
        $group_prof="pengembangan_pelatihan_kegiatan.nama";
        $as_prof="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $group="pengembangan_pelatihan_kegiatan.nama,m_kode_profesi_group.ds_group_jabatan";
        $as="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan,m_kode_profesi_group.ds_group_jabatan as profesi";
        $filt="pengembangan_pelatihan_kegiatan.nama";
        $pegawai="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama, m_kode_profesi_group.ds_group_jabatan, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
        $pegawai_filter="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama as nama_kegiatan, m_kode_profesi_group.ds_group_jabatan as profesi,m_kode_profesi_group.ds_group_jabatan as profesi, pengembangan_pelatihan_kegiatan.nama as kegiatan, sum(pengembangan_pelatihan_detail.uraian_total) as nominal, sum(pengembangan_pelatihan.total_hari_kerja) as hari,count(m_kode_profesi_group.ds_group_jabatan) as jum";
        $id="nama_kegiatan";
        $kegiatan="profesi";
        $filt_kegiatan="m_kode_profesi_group.ds_group_jabatan";
    }else if ($jenis_surat=="laporan6"){
        $group_prof="sys_grup_user.grup,pengembangan_pelatihan_kegiatan.nama";
        $as_prof="sys_grup_user.grup,pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $group="pengembangan_pelatihan_kegiatan.nama";
        $filt="pengembangan_pelatihan_kegiatan.nama";
        $as="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $pegawai="pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan_kegiatan_status.nama,pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan.total_hari_kerja";
        $pegawai_filter="pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan_kegiatan_status.nama as status,pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan.total_hari_kerja";
        $id="nama_kegiatan";
    }

    $result['result'] = $this->Pengembangan_pelatihan_model->get2(null, $unit, $offset, 500, $from, $to, null, null, $group_prof, $as_prof, $kegiatan1, $jenis1);

    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $profesi=array($filt => $value[$id]);

            $result['result'][$key]["data"] = $this->Pengembangan_pelatihan_model->get2($profesi, $unit, $offset, 500, $from, $to, null, null, $group, $as, $kegiatan1, $jenis1);     
            if (!empty($result['result'][$key]["data"])) {
                foreach ($result['result'][$key]["data"] as $key_value => $val) {
                    if($jenis_surat=="laporan9"){
                        $params_array=array($filt => $val[$id],$filt_kegiatan=>$val[$kegiatan]);
                    }elseif($jenis_surat=="laporan10"){
                        $params_array=array($filt => $val[$id],$filt_kegiatan=>$val[$kegiatan]);
                    }else if($jenis_surat=="laporan6"){
                        $params_array=array($filt => $val[$id]);
}//print_r($result['result']);die();
$filter="$group,pengembangan_pelatihan.total_hari_kerja";
$result['result'][$key]["data"][$key_value]["kegiatan"] = $this->Pengembangan_pelatihan_model->get_kegiatan($params_array, $unit, $offset, 500, $from, $to, null, null, $filter, $as, $kegiatan1, $jenis1);
$result['result'][$key]["data"][$key_value]["pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, 500, $from, $to, null, null, $pegawai,$pegawai_filter, $unit, $kegiatan1, $jenis1);
//print_r($result['result'][$key]["data"][$key_value]["pegawai"]);die();
if (!empty($result['result'][$key]["data"][$key_value]["pegawai"])) {
    foreach ($result['result'][$key]["data"][$key_value]["pegawai"] as $key_pegawai => $value_pegawai) {
        $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_pegawai["id"]));
        if (!empty($result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"])) {
            foreach ($result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {


                if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                    if("Akomodasi Gol ".substr($value_pegawai["golongan"],0,-2)!==$value_detail_biaya["uraian"]){
                        $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nom"] += $value_detail_biaya["pernominal"];
                    }
                }
                if("Akomodasi Gol ".substr($value_pegawai["golongan"],0,-2)==$value_detail_biaya["uraian"]){
                    $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nominal_gol"] = $value_detail_biaya["pernominal"];
                }
                $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["pernominal"]=$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nominal_gol"]+$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nom"];
            }
        }
        $result['result'][$key]["data"][$key_value]["pernomin"]+=$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["pernominal"];
        $result['result'][$key]["data"][$key_value]["kegiatan_nama"]=$value_pegawai["kegiatan"];	
        $result['result'][$key]["data"][$key_value]["profesi_nama"]=$value_pegawai["profesi"];	
        $result['result'][$key]["data"][$key_value]["jum_pegawai"]+=$value_pegawai["jum"];	
        $result['result'][$key]["data"][$key_value]["jum_hari"]+=$value_pegawai["hari"];	
        $result['result'][$key]["data"][$key_value]["jum_jam"]+=$value_pegawai["total_jam"];	
    }
}
$result['result'][$key]["harga"]+=$result['result'][$key]["data"][$key_value]["pernomin"];

}
}
}
}
//print_r($result["result"]);die;
$data = "test";
if($jenis_surat=="laporan9"){
    $html = $this->load->view("laporan/laporan_9", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan6"){
    $html = $this->load->view("laporan/laporan_6", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan10"){
    $html = $this->load->view("laporan/laporan_10", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan14"){
    $html = $this->load->view("laporan/laporan_14", array("result" => $result['result']), true);
}
echo $html;
die;
}


public function cetak_laporan2_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $unit = $this->input->get("unit_ker");
    $jenis_surat = $this->input->get("surat");
    $jenis1 = $this->input->get("jenis1");
    $kegiatan1 = $this->input->get("kegiatan1");
    if($jenis_surat=="laporan9"){
        $group_prof="m_kode_profesi_group.ds_group_jabatan";
        $as_prof="m_kode_profesi_group.ds_group_jabatan as profesi";
        $group="m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_kegiatan.nama";
        $filt="m_kode_profesi_group.ds_group_jabatan";
        $as="m_kode_profesi_group.ds_group_jabatan as profesi,pengembangan_pelatihan_kegiatan.nama as kegiatan";
        $pegawai="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
        $pegawai_filter="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,m_kode_profesi_group.ds_group_jabatan as profesi, pengembangan_pelatihan_kegiatan.nama as kegiatan, sum(pengembangan_pelatihan_detail.uraian_total) as nominal, sum(pengembangan_pelatihan.total_hari_kerja) as hari,count(m_kode_profesi_group.ds_group_jabatan) as jum";
        $id="profesi";
        $kegiatan="kegiatan";
        $filt_kegiatan="pengembangan_pelatihan_kegiatan.nama";
    }else if ($jenis_surat=="laporan10"){
        $group_prof="pengembangan_pelatihan_kegiatan.nama";
        $as_prof="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $group="pengembangan_pelatihan_kegiatan.nama,m_kode_profesi_group.ds_group_jabatan";
        $as="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan,m_kode_profesi_group.ds_group_jabatan as profesi";
        $filt="pengembangan_pelatihan_kegiatan.nama";
        $pegawai="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama, m_kode_profesi_group.ds_group_jabatan, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
        $pegawai_filter="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama as nama_kegiatan, m_kode_profesi_group.ds_group_jabatan as profesi,m_kode_profesi_group.ds_group_jabatan as profesi, pengembangan_pelatihan_kegiatan.nama as kegiatan, sum(pengembangan_pelatihan_detail.uraian_total) as nominal, sum(pengembangan_pelatihan.total_hari_kerja) as hari,count(m_kode_profesi_group.ds_group_jabatan) as jum";
        $id="nama_kegiatan";
        $kegiatan="profesi";
        $filt_kegiatan="m_kode_profesi_group.ds_group_jabatan";
    }else if ($jenis_surat=="laporan6"){
        $group_prof="sys_grup_user.grup,pengembangan_pelatihan_kegiatan.nama";
        $as_prof="sys_grup_user.grup,pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $group="pengembangan_pelatihan_kegiatan.nama";
        $filt="pengembangan_pelatihan_kegiatan.nama";
        $as="pengembangan_pelatihan_kegiatan.nama as nama_kegiatan";
        $pegawai="pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan_kegiatan_status.nama,pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan.total_hari_kerja";
        $pegawai_filter="pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan_kegiatan_status.nama as status,pengembangan_pelatihan.id,pengembangan_pelatihan.total_hari_kerja,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan.pengembangan_pelatihan_kegiatan,m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan.total_hari_kerja";
        $id="nama_kegiatan";
    }

    $result['result'] = $this->Pengembangan_pelatihan_model->get2(null, $unit, $offset, null, $from, $to, null, null, $group_prof, $as_prof, $kegiatan1, $jenis1);
//print_r($result['result']);die();
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $profesi=array($filt => $value[$id]);
            $result['result'][$key]["data"] = $this->Pengembangan_pelatihan_model->get2($profesi, $unit, $offset, null, $from, $to, null, null, $group, $as, $kegiatan1, $jenis1);     
            if (!empty($result['result'][$key]["data"])) {
                foreach ($result['result'][$key]["data"] as $key_value => $val) {
                    if($jenis_surat=="laporan9"){
                        $params_array=array($filt => $val[$id],$filt_kegiatan=>$val[$kegiatan]);
                    }elseif($jenis_surat=="laporan10"){
                        $params_array=array($filt => $val[$id],$filt_kegiatan=>$val[$kegiatan]);
                    }else if($jenis_surat=="laporan6"){
                        $params_array=array($filt => $val[$id]);
}//print_r($result['result']);die();
$filter="$group,pengembangan_pelatihan.total_hari_kerja";
$result['result'][$key]["data"][$key_value]["kegiatan"] = $this->Pengembangan_pelatihan_model->get_kegiatan($params_array, $unit, $offset, null, $from, $to, null, null, $filter, $as, $kegiatan1, $jenis1);
$result['result'][$key]["data"][$key_value]["pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $pegawai,$pegawai_filter, $unit, $kegiatan1, $jenis1);
//print_r($result["result"][$key]["status"]);die();
if (!empty($result['result'][$key]["data"][$key_value]["pegawai"])) {
    foreach ($result['result'][$key]["data"][$key_value]["pegawai"] as $key_pegawai => $value_pegawai) {
        $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_pegawai["id"]));
        if (!empty($result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"])) {
            foreach ($result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {


                if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                    if("Akomodasi Gol ".substr($value_pegawai["golongan"],0,-2)!==$value_detail_biaya["uraian"]){
                        $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nom"] += $value_detail_biaya["pernominal"];
                    }
                }
                if("Akomodasi Gol ".substr($value_pegawai["golongan"],0,-2)==$value_detail_biaya["uraian"]){
                    $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nominal_gol"] = $value_detail_biaya["pernominal"];
                }
                $result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["pernominal"]=$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nominal_gol"]+$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["nom"];
            }
        }
        $result['result'][$key]["data"][$key_value]["pernomin"]+=$result['result'][$key]["data"][$key_value]["pegawai"][$key_pegawai]["pernominal"];
        $result['result'][$key]["data"][$key_value]["kegiatan_nama"]=$value_pegawai["kegiatan"];	
        $result['result'][$key]["data"][$key_value]["profesi_nama"]=$value_pegawai["profesi"];	
        $result['result'][$key]["data"][$key_value]["jum_pegawai"]+=$value_pegawai["jum"];	
        $result['result'][$key]["data"][$key_value]["jum_hari"]+=$value_pegawai["hari"];	
        $result['result'][$key]["data"][$key_value]["jum_jam"]+=$value_pegawai["total_jam"];	
    }
}
$result['result'][$key]["harga"]+=$result['result'][$key]["data"][$key_value]["pernomin"];

}
}

$result["result"][0]["awal"]=$from;
$result["result"][0]["akhir"]=$to;
}
//print_r($result["result"][0]["profesi"]);die;
$data = "test";
$this->load->library("pdf");
if($jenis_surat=="laporan9"){
    $html = $this->load->view("laporan/laporan_9", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan6"){
    $html = $this->load->view("laporan/laporan_6", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan10"){
    $html = $this->load->view("laporan/laporan_10", array("result" => $result['result']), true);
}else if($jenis_surat=="laporan14"){
    $html = $this->load->view("laporan/laporan_14", array("result" => $result['result']), true);
}
//echo $html;
//die;
$customPaper = array(0,0,210,330);
$this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
$this->pdf->setPaper("A4", 'landscape');
$this->pdf->set_option("isPhpEnabled", true);
$this->pdf->set_option("isHtml5ParserEnabled", true);
$this->pdf->set_option("isRemoteEnabled", true);
$this->pdf->render();
$name = "download Laporan";
$this->pdf->stream($name, array("Attachment" => 1));

}
}


public function preview_laporan3_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $jenis_surat = $this->input->get("surat");
    $filt="sys_grup_user.grup";
    $pegawai="pengembangan_pelatihan.nama_pelatihan, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,pengembangan_pelatihan_detail_biaya.uraian,pengembangan_pelatihan_detail_biaya.pernominal,pengembangan_pelatihan_detail_biaya.nominal,pengembangan_pelatihan.total_hari_kerja";
    $pelatihan="pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $as_4="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,sum(pengembangan_pelatihan_detail.uraian_total) as uraian_total,sum(pengembangan_pelatihan.total_hari_kerja) as total_hari_kerja";
    $detail_4="pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup";
    $detail="pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup";
    $jenis="m_kode_profesi_group.ds_group_jabatan, sys_grup_user.grup";
    $as="sys_grup_user.grup, pengembangan_pelatihan_detail.nama_pegawai,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan.nama_pelatihan";
    $id="grup";

    $result['result'] = $this->Pengembangan_pelatihan_model->get3(null, null, $offset, null, $from, $to, null, null, $filt, $filt);
//print_r($result['result']);die();
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $val) {
            $params_array=array($filt => $val[$id]);
            $result["result"][$key]["pelatihan"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $pelatihan, $pelatihan);
            $result["result"][$key]["detail_pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $detail, $detail);
            $result["result"][$key]["total_pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $detail_4, $as_4);
            $result["result"][$key]["jenis"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $jenis, $jenis);
            if (!empty($result["result"][$key]["total_pegawai"])) {
                foreach ($result["result"][$key]["total_pegawai"] as $key_id => $value_id) {			
                    $result["result"][$key]["total_pegawai"][$key_id]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_id["id"]));
                    if (!empty($result["result"][$key]["total_pegawai"][$key_id]["detail"])) {
                        foreach ($result["result"][$key]["total_pegawai"][$key_id]["detail"] as $key_detail_biaya => $value_detail_biaya) {			
                            if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                                if("Akomodasi Gol ".substr($value_id["golongan"],0,-2)!==$value_detail_biaya["uraian"]){

                                    $result["result"][$key]["total_pegawai"][$key_id]["nom"] += $value_detail_biaya["pernominal"];
                                }
                            }
                            if("Akomodasi Gol ".substr($value_id["golongan"],0,-2)==$value_detail_biaya["uraian"]){
                                $result["result"][$key]["total_pegawai"][$key_id]["nominal_gol"] = $value_detail_biaya["pernominal"];
                            }
                            $result["result"][$key]["total_pegawai"][$key_id]["pernominal"]=$result["result"][$key]["total_pegawai"][$key_id]["nominal_gol"]+$result["result"][$key]["total_pegawai"][$key_id]["nom"];
                        }
                    }
                }
            }
//print_r($result["result"]);die();
        }
    }
//print_r($result["result"]);die;
    $data = "test";
    if($jenis_surat=="lapor12"){
        $html = $this->load->view("laporan/laporan_11", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan12"){
        $html = $this->load->view("laporan/laporan_12", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan13"){
        $html = $this->load->view("laporan/laporan_13", array("result" => $result['result']), true);
    }
    echo $html;
    die;
}

public function cetak_laporan3_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $jenis_surat = $this->input->get("surat");
    $filt="sys_grup_user.grup";
    $pegawai="pengembangan_pelatihan.nama_pelatihan, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,pengembangan_pelatihan_detail_biaya.uraian,pengembangan_pelatihan_detail_biaya.pernominal,pengembangan_pelatihan_detail_biaya.nominal,pengembangan_pelatihan.total_hari_kerja";
    $pelatihan="pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan, m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $as_4="pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup,sum(pengembangan_pelatihan_detail.uraian_total) as uraian_total,sum(pengembangan_pelatihan.total_hari_kerja) as total_hari_kerja";
    $detail_4="pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan.nama_pelatihan,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup";
    $detail="pengembangan_pelatihan_detail.nama_pegawai, sys_grup_user.grup";
    $jenis="m_kode_profesi_group.ds_group_jabatan, sys_grup_user.grup";
    $as="sys_grup_user.grup, pengembangan_pelatihan_detail.nama_pegawai,m_kode_profesi_group.ds_group_jabatan,pengembangan_pelatihan.nama_pelatihan";
    $id="grup";

    $result['result'] = $this->Pengembangan_pelatihan_model->get3(null, null, $offset, null, $from, $to, null, null, $filt, $filt);
//print_r($result['result']);die();
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $val) {
            $params_array=array($filt => $val[$id]);
            $result["result"][$key]["pelatihan"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $pelatihan, $pelatihan);
            $result["result"][$key]["detail_pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $detail, $detail);
            $result["result"][$key]["total_pegawai"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $detail_4, $as_4);
            $result["result"][$key]["jenis"] = $this->Pengembangan_pelatihan_model->get5($params_array, null, $offset, null, $from, $to, null, null, $jenis, $jenis);
            $result["result"][$key]["pegawai"] = $this->Pengembangan_pelatihan_model->get6($params_array, null, $offset, null, $from, $to, null, null, $pegawai, $pegawai);

//print_r($result["result"][$key]["pelatihan"]);die();
            if (!empty($result["result"][$key]["total_pegawai"])) {
                foreach ($result["result"][$key]["total_pegawai"] as $key_id => $value_id) {			
                    $result["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value_id["kode"]);                
                }
            }
            if (!empty($result["result"][$key]["pegawai"])) {
                foreach ($result["result"][$key]["pegawai"] as $key_detail_biaya => $value_detail_biaya) {			
                    if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                        if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)!==$value_detail_biaya["uraian"]){
                            $result["result"][$key]["nom"] += $value_detail_biaya["pernominal"];
                        }
                    }
                    if("Akomodasi Gol ".substr($result["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)==$value_detail_biaya["uraian"]){
                        $result["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
                    }
                    $result["result"][$key]["pernominal"]=$result["result"][$key]["nominal_gol"]+$result["result"][$key]["nom"];
                }
            }
            $result["result"][0]["awal"]=$from;
            $result["result"][0]["akhir"]=$to;
        }
    }
//print_r($result["result"]);die;
    $data = "test";
    $this->load->library("pdf");
    if($jenis_surat=="lapor12"){
        $html = $this->load->view("laporan/laporan_11", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan12"){
        $html = $this->load->view("laporan/laporan_12", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan13"){
        $html = $this->load->view("laporan/laporan_13", array("result" => $result['result']), true);
    }
//echo $html;
//die;
    $customPaper = array(0,0,210,330);
    $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
    $this->pdf->setPaper("A4", 'landscape');
    $this->pdf->set_option("isPhpEnabled", true);
    $this->pdf->set_option("isHtml5ParserEnabled", true);
    $this->pdf->set_option("isRemoteEnabled", true);
    $this->pdf->render();
    $name = "download Laporan";
    $this->pdf->stream($name, array("Attachment" => 1));

}

public function preview_laporan4_get()
{
    $offset = 0;
    $mulai = $this->input->get("awal");
    $hingga = $this->input->get("akhir");
    if (!empty($mulai)) {
        $from = date("Y-m-d", strtotime($mulai));
    }if (!empty($hingga)) {
        $to = date("Y-m-d", strtotime($hingga));
    }
    $no_peg = $this->input->get("no_peg");
    $jenis_surat = $this->input->get("surat");
    $filter_prof="m_kode_profesi_group.ds_group_jabatan";
    $filter="m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
    $filt="m_kode_profesi_group.ds_group_jabatan";
    $jenis="m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
    $kegiatan="pengembangan_pelatihan_kegiatan.nama";
    $pelatihan="pengembangan_pelatihan_detail.nopeg,sys_grup_user.grup,pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan_kegiatan_status.nama,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan_detail.nama_pegawai, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $pelatihan_as="pengembangan_pelatihan_detail.nopeg,sys_grup_user.grup,pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan_kegiatan_status.nama as status,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan_detail.nama_pegawai, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $id="ds_group_jabatan";
    $nama="nama";
    $filt_kegiatan="pengembangan_pelatihan_kegiatan.nama";
    $pelatih="nama_pelatihan";
    $filt_pelatihan="pengembangan_pelatihan.nama_pelatihan";


    $result['result'] = $this->Pengembangan_pelatihan_model->get3(null, $no_peg, $offset, 500, $from, $to, null, null, $filter_prof, $filter_prof);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $val) {
            $profesi=array($filt => $val[$id]);
            $result['result'][$key]["prof"] = $this->Pengembangan_pelatihan_model->get3($profesi, $no_peg, $offset, 500, $from, $to, null, null, $filter, $filter);
//print_r($result['result']);die();
            if (!empty($result['result'][$key]["prof"])) {
                foreach ($result['result'][$key]["prof"] as $key_prof => $val_prof) {
                    $params=array($filt => $val_prof[$id],$filt_pelatihan => $val_prof[$pelatih]);
                    $params_array=array($filt => $val_prof[$id],$filt_kegiatan => $val_prof[$nama]);
                    $result["result"][$key]["prof"][$key_prof]["kegiatan"] = $this->Pengembangan_pelatihan_model->get3($params_array, $no_peg, $offset, 200, $from, $to, null, null, $pelatihan, $pelatihan_as);
                    if (!empty($result["result"][$key]["prof"][$key_prof]["kegiatan"])) {
                        foreach ($result["result"][$key]["prof"][$key_prof]["kegiatan"] as $key_pegawai => $value) {
                            $params=array('pengembangan_pelatihan_kegiatan.nama' => $value["nama"]);
                            $array=$val[$id];
                            $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
                            if (!empty($result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"])) {
                                foreach ($result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {
                                    if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                                        if("Akomodasi Gol ".substr($value["golongan"],0,-2)!==$value_detail_biaya["uraian"]){
                                            $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nom"] += $value_detail_biaya["pernominal"];
                                        }
                                    }
                                    if("Akomodasi Gol ".substr($value["golongan"],0,-2)==$value_detail_biaya["uraian"]){
                                        $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nominal_gol"] = $value_detail_biaya["pernominal"];
                                    }
                                    $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["pernominal"]=$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nominal_gol"]+$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nom"];
                                }
                            }
                            $result["result"][$key]["prof"][$key_prof]["pernominal"]+=$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["pernominal"];
                            $result["result"][$key]["prof"][$key_prof]["kegiatan_nama"]=$value_pegawai["kegiatan"];	
                            $result["result"][$key]["prof"][$key_prof]["profesi_nama"]=$value_pegawai["profesi"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_pegawai"]+=$value_pegawai["jum"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_hari"]+=$value_pegawai["hari"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_jam"]+=$value_pegawai["total_jam"];	
                        }
                    }
//print_r($result["result"][$key]["pelatihan"]);die();
                }
            }

        }
    }
//print_r($result["result"]);die;
    $data = "test";
    if($jenis_surat=="laporan2"){
        $html = $this->load->view("laporan/laporan_2", array("result" => $result['result']), true);
    }else if($jenis_surat=="laporan4"){
        $html = $this->load->view("laporan/laporan_4", array("result" => $result['result']), true);
    }

    echo $html;
    die;
}

public function cetak_laporan4_get()
{
    $offset = 0;
    $from = date("Y-m-d", strtotime($this->input->get("awal")));
    $to = date("Y-m-d", strtotime($this->input->get("akhir")));

    $no_peg = $this->input->get("no_peg");
    $jenis_surat = $this->input->get("surat");
    $filter_prof="m_kode_profesi_group.ds_group_jabatan";
    $filter="m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
    $filt="m_kode_profesi_group.ds_group_jabatan";
    $jenis="m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_kegiatan.nama";
    $kegiatan="pengembangan_pelatihan_kegiatan.nama";
    $pelatihan="pengembangan_pelatihan_detail.nopeg,sys_grup_user.grup,pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan_kegiatan_status.nama,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan_detail.nama_pegawai, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $pelatihan_as="pengembangan_pelatihan_detail.nopeg,sys_grup_user.grup,pengembangan_pelatihan.id,pengembangan_pelatihan_detail.golongan,pengembangan_pelatihan_detail.id as kode,pengembangan_pelatihan_pelaksanaan.total_jam,pengembangan_pelatihan_kegiatan.nama,pengembangan_pelatihan_kegiatan_status.nama as status,pengembangan_pelatihan_pelaksanaan.tanggal_to,pengembangan_pelatihan.tujuan,pengembangan_pelatihan.nama_pelatihan,pengembangan_pelatihan_detail.nama_pegawai, m_kode_profesi_group.ds_group_jabatan, pengembangan_pelatihan_detail.uraian_total,pengembangan_pelatihan.total_hari_kerja";
    $id="ds_group_jabatan";
    $nama="nama";
    $filt_kegiatan="pengembangan_pelatihan_kegiatan.nama";
    $pelatih="nama_pelatihan";
    $filt_pelatihan="pengembangan_pelatihan.nama_pelatihan";


    $result['result'] = $this->Pengembangan_pelatihan_model->get3(null, $no_peg, $offset, 500, $from, $to, null, null, $filter_prof, $filter_prof);
    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $val) {
            $profesi=array($filt => $val[$id]);
            $result['result'][$key]["prof"] = $this->Pengembangan_pelatihan_model->get3($profesi, $no_peg, $offset, 500, $from, $to, null, null, $filter, $filter);
//print_r($result['result']);die();
            if (!empty($result['result'][$key]["prof"])) {
                foreach ($result['result'][$key]["prof"] as $key_prof => $val_prof) {
                    $params=array($filt => $val_prof[$id],$filt_pelatihan => $val_prof[$pelatih]);
                    $params_array=array($filt => $val_prof[$id],$filt_kegiatan => $val_prof[$nama]);
                    $result["result"][$key]["prof"][$key_prof]["kegiatan"] = $this->Pengembangan_pelatihan_model->get3($params_array, $no_peg, $offset, null, $from, $to, null, null, $pelatihan, $pelatihan_as);
                    if (!empty($result["result"][$key]["prof"][$key_prof]["kegiatan"])) {
                        foreach ($result["result"][$key]["prof"][$key_prof]["kegiatan"] as $key_pegawai => $value) {
                            $params=array('pengembangan_pelatihan_kegiatan.nama' => $value["nama"]);
                            $array=$val[$id];
                            $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
                            if (!empty($result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"])) {
                                foreach ($result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {
                                    if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                                        if("Akomodasi Gol ".substr($value["golongan"],0,-2)!==$value_detail_biaya["uraian"]){
                                            $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nom"] += $value_detail_biaya["pernominal"];
                                        }
                                    }
                                    if("Akomodasi Gol ".substr($value["golongan"],0,-2)==$value_detail_biaya["uraian"]){
                                        $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nominal_gol"] = $value_detail_biaya["pernominal"];
                                    }
                                    $result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["pernominal"]=$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nominal_gol"]+$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["nom"];
                                }
                            }
                            $result["result"][$key]["prof"][$key_prof]["pernominal"]+=$result["result"][$key]["prof"][$key_prof]["kegiatan"][$key_pegawai]["pernominal"];
                            $result["result"][$key]["prof"][$key_prof]["kegiatan_nama"]=$value_pegawai["kegiatan"];	
                            $result["result"][$key]["prof"][$key_prof]["profesi_nama"]=$value_pegawai["profesi"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_pegawai"]+=$value_pegawai["jum"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_hari"]+=$value_pegawai["hari"];	
                            $result["result"][$key]["prof"][$key_prof]["jum_jam"]+=$value_pegawai["total_jam"];	
                        }
                    }
//print_r($result["result"][$key]["pelatihan"]);die();
                }
            }

        }

//print_r($result["result"][0]["profesi"]);die;
        $data = "test";
        $this->load->library("pdf");
        if($jenis_surat=="laporan2"){
            $html = $this->load->view("laporan/laporan_2", array("result" => $result['result']), true);
        }else if($jenis_surat=="laporan4"){
            $html = $this->load->view("laporan/laporan_4", array("result" => $result['result']), true);
        }

//echo $html;
//die;
        $customPaper = array(0,0,210,330);
        $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
        $this->pdf->setPaper("A4",'landscape');
        $this->pdf->set_option("isPhpEnabled", true);
        $this->pdf->set_option("isHtml5ParserEnabled", true);
        $this->pdf->set_option("isRemoteEnabled", true);
        $this->pdf->render();
        $name = "download Laporan";
        $this->pdf->stream($name, array("Attachment" => 1));

    }
}

public function preview_laporan5_get()
{
    $offset = 0;
    $bulan="pengembangan_pelatihan_pelaksanaan.tanggal_too";
    $bulan_as="DISTINCT EXTRACT(month from pengembangan_pelatihan_pelaksanaan.tanggal_too) as tanggal";
    $filt="pengembangan_pelatihan.jenis_perjalanan, pengembangan_pelatihan_pelaksanaan.tanggal_too, pengembangan_pelatihan_kegiatan.nama";
    $as="sum(pengembangan_pelatihan_detail.uraian_total) as nominal, count(pengembangan_pelatihan_kegiatan.nama) as jum, pengembangan_pelatihan.jenis_perjalanan, pengembangan_pelatihan_pelaksanaan.tanggal_too, pengembangan_pelatihan_kegiatan.nama, EXTRACT(month from pengembangan_pelatihan_pelaksanaan.tanggal_too) as tanggal";
    $fil_as="count(pengembangan_pelatihan_kegiatan.nama) as jum";
    $year = $this->input->get("year");
    $to = $this->input->get("akhir");
    $nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jenis = $this->input->get("jenis");
    $workshop = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'1', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $jenis_perjalanan = array("pengembangan_pelatihan.jenis_perjalanan" => 'Luar Negeri');
    $dalam = array("pengembangan_pelatihan.dalam_negeri"=>'Luar Kota');
    $kegiatan = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'7', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $managerial = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'2', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $tamu = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'9', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $inhouse = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'4', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $pendidikan = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'3', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $luar = array("pengembangan_pelatihan.jenis_perjalanan"=>'Luar Negeri');
    $jenis_surat = $this->input->get("surat");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_5(null, $nopeg, $offset, null, $from, $to, null, null, $bulan, $bulan_as, $unit, null, null, $year);

    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $unit=$value['tanggal'];
            $result['result'][$key]['dik'] = $this->Pengembangan_pelatihan_model->get_thn($kegiatan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['dik'])) {
                foreach ($result['result'][$key]['dik'] as $key_dik => $value_dik) {
                    $result['result'][$key]['diklat']['jum'] = $value_dik['jum'];
                    $result['result'][$key]['diklat']['nominal'] = $value_dik['nominal'];
                }
            }
            $result['result'][$key]['manag'] = $this->Pengembangan_pelatihan_model->get_thn($managerial, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['manag'])) {
                foreach ($result['result'][$key]['manag'] as $key_manag => $value_manag) {
                    $result['result'][$key]['managerial']['jum'] = $value_manag['jum'];
                    $result['result'][$key]['managerial']['nominal'] = $value_manag['nominal'];
                }
            }
            $result['result'][$key]['work'] = $this->Pengembangan_pelatihan_model->get_thn($workshop, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['work'])) {
                foreach ($result['result'][$key]['work'] as $key_work => $value_work) {
                    $result['result'][$key]['workshop']['jum'] = $value_work['jum'];
                    $result['result'][$key]['workshop']['nominal'] = $value_work['nominal'];
                }
            }
            $result['result'][$key]['in'] = $this->Pengembangan_pelatihan_model->get_thn($inhouse, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['in'])) {
                foreach ($result['result'][$key]['in'] as $key_in => $value_in) {
                    $result['result'][$key]['inhouse']['jum'] = $value_in['jum'];
                    $result['result'][$key]['inhouse']['nominal'] = $value_in['nominal'];
                }
            }
            $result['result'][$key]['pen'] = $this->Pengembangan_pelatihan_model->get_thn($pendidikan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['pen'])) {
                foreach ($result['result'][$key]['pen'] as $key_pen => $value_pen) {
                    $result['result'][$key]['pendidikan']['jum'] = $value_pen['jum'];
                    $result['result'][$key]['pendidikan']['nominal'] = $value_pen['nominal'];
                }
            }
            $result['result'][$key]['lu'] = $this->Pengembangan_pelatihan_model->get_thn($luar, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['lu'])) {
                foreach ($result['result'][$key]['lu'] as $key_lu => $value_lu) {
                    $result['result'][$key]['luar']['jum'] = $value_lu['jum'];
                    $result['result'][$key]['luar']['nominal'] = $value_lu['nominal'];
                }
            }
            $result['result'][$key]['tam'] = $this->Pengembangan_pelatihan_model->get_thn($tamu, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['tam'])) {
                foreach ($result['result'][$key]['tam'] as $key_tam => $value_tam) {
                    $result['result'][$key]['tamu']['jum'] = $value_tam['jum'];
                    $result['result'][$key]['tamu']['nominal'] = $value_tam['nominal'];
                }
            }
            $result['result'][$key]['tot'] = $this->Pengembangan_pelatihan_model->get_thn(null, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['tot'])) {
                foreach ($result['result'][$key]['tot'] as $key_tot => $value_tot) {
                    $result['result'][$key]['total']['jum'] = $value_tot['jum'];
                    $result['result'][$key]['total']['nominal'] = $value_tot['nominal'];
                }
            }
            $result['result'][$key]['jum_tot'] = $this->Pengembangan_pelatihan_model->get_thn($jenis_perjalanan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['jum_tot'])) {
                foreach ($result['result'][$key]['jum_tot'] as $key_jum_tot => $value_jum_tot) {
                    $result['result'][$key]['jum_total']['jum'] = $value_jum_tot['jum'];
                    $result['result'][$key]['jum_total']['nominal'] = $value_jum_tot['nominal'];
                }
            }
            $result['result'][$key]['dal'] = $this->Pengembangan_pelatihan_model->get_thn($dalam, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['dal'])) {
                foreach ($result['result'][$key]['dal'] as $key_dal => $value_dal) {
                    $result['result'][$key]['dalam']['jum'] = $value_dal['jum'];
                    $result['result'][$key]['dalam']['nominal'] = $value_dal['nominal'];
                }
            }
        }
    }
    $data = "test";
    $html = $this->load->view("laporan/laporan_17", array("result" => $result['result']), true);

    echo $html;
    die;
}

public function cetak_laporan5_get()
{
    $offset = 0;
    $bulan="pengembangan_pelatihan_pelaksanaan.tanggal_too";
    $bulan_as="DISTINCT EXTRACT(month from pengembangan_pelatihan_pelaksanaan.tanggal_too) as tanggal";
    $filt="pengembangan_pelatihan.jenis_perjalanan, pengembangan_pelatihan_pelaksanaan.tanggal_too, pengembangan_pelatihan_kegiatan.nama";
    $as="sum(pengembangan_pelatihan_detail.uraian_total) as nominal, count(pengembangan_pelatihan_kegiatan.nama) as jum, pengembangan_pelatihan.jenis_perjalanan, pengembangan_pelatihan_pelaksanaan.tanggal_too, pengembangan_pelatihan_kegiatan.nama, EXTRACT(month from pengembangan_pelatihan_pelaksanaan.tanggal_too) as tanggal";
    $fil_as="count(pengembangan_pelatihan_kegiatan.nama) as jum";
    $year = $this->input->get("year");
    $to = $this->input->get("akhir");
    $nopeg = $this->input->get("nopeg");
    $unit = $this->input->get("unit");
    $jenis = $this->input->get("jenis");
    $workshop = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'1', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $jenis_perjalanan = array("pengembangan_pelatihan.jenis_perjalanan" => 'Luar Negeri');
    $dalam = array("pengembangan_pelatihan.dalam_negeri"=>'Luar Kota');
    $kegiatan = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'7', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $managerial = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'2', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $tamu = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'9', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $inhouse = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'4', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $cb = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'8', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $pendidikan = array("pengembangan_pelatihan.pengembangan_pelatihan_kegiatan"=>'3', "pengembangan_pelatihan.jenis_perjalanan"=>'Dalam Negeri');
    $luar = array("pengembangan_pelatihan.jenis_perjalanan"=>'Luar Negeri');
    $jenis_surat = $this->input->get("surat");
    $result['result'] = $this->Pengembangan_pelatihan_model->get_5(null, $nopeg, $offset, null, $from, $to, null, null, $bulan, $bulan_as, $unit, null, null, $year);

    if (!empty($result['result'])) {
        foreach ($result["result"] as $key => $value) {
            $unit=$value['tanggal'];
            $result['result'][$key]['dik'] = $this->Pengembangan_pelatihan_model->get_thn($kegiatan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['dik'])) {
                foreach ($result['result'][$key]['dik'] as $key_dik => $value_dik) {
                    $result['result'][$key]['diklat']['jum'] = $value_dik['jum'];
                    $result['result'][$key]['diklat']['nominal'] = $value_dik['nominal'];
                }
            }
            $result['result'][$key]['manag'] = $this->Pengembangan_pelatihan_model->get_thn($managerial, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['manag'])) {
                foreach ($result['result'][$key]['manag'] as $key_manag => $value_manag) {
                    $result['result'][$key]['managerial']['jum'] = $value_manag['jum'];
                    $result['result'][$key]['managerial']['nominal'] = $value_manag['nominal'];
                }
            }
            $result['result'][$key]['work'] = $this->Pengembangan_pelatihan_model->get_thn($workshop, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['work'])) {
                foreach ($result['result'][$key]['work'] as $key_work => $value_work) {
                    $result['result'][$key]['workshop']['jum'] = $value_work['jum'];
                    $result['result'][$key]['workshop']['nominal'] = $value_work['nominal'];
                }
            }
            $result['result'][$key]['in'] = $this->Pengembangan_pelatihan_model->get_thn($inhouse, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['in'])) {
                foreach ($result['result'][$key]['in'] as $key_in => $value_in) {
                    $result['result'][$key]['inhouse']['jum'] = $value_in['jum'];
                    $result['result'][$key]['inhouse']['nominal'] = $value_in['nominal'];
                }
            }
            $result['result'][$key]['bilding'] = $this->Pengembangan_pelatihan_model->get_thn($cb, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['bilding'])) {
                foreach ($result['result'][$key]['bilding'] as $key_cb => $value_cb) {
                    $result['result'][$key]['cb']['jum'] = $value_cb['jum'];
                    $result['result'][$key]['cb']['nominal'] = $value_cb['nominal'];
                }
            }
            $result['result'][$key]['pen'] = $this->Pengembangan_pelatihan_model->get_thn($pendidikan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['pen'])) {
                foreach ($result['result'][$key]['pen'] as $key_pen => $value_pen) {
                    $result['result'][$key]['pendidikan']['jum'] = $value_pen['jum'];
                    $result['result'][$key]['pendidikan']['nominal'] = $value_pen['nominal'];
                }
            }
            $result['result'][$key]['lu'] = $this->Pengembangan_pelatihan_model->get_thn($luar, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['lu'])) {
                foreach ($result['result'][$key]['lu'] as $key_lu => $value_lu) {
                    $result['result'][$key]['luar']['jum'] = $value_lu['jum'];
                    $result['result'][$key]['luar']['nominal'] = $value_lu['nominal'];
                }
            }
            $result['result'][$key]['tam'] = $this->Pengembangan_pelatihan_model->get_thn($tamu, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['tam'])) {
                foreach ($result['result'][$key]['tam'] as $key_tam => $value_tam) {
                    $result['result'][$key]['tamu']['jum'] = $value_tam['jum'];
                    $result['result'][$key]['tamu']['nominal'] = $value_tam['nominal'];
                }
            }
            $result['result'][$key]['tot'] = $this->Pengembangan_pelatihan_model->get_thn(null, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['tot'])) {
                foreach ($result['result'][$key]['tot'] as $key_tot => $value_tot) {
                    $result['result'][$key]['total']['jum'] = $value_tot['jum'];
                    $result['result'][$key]['total']['nominal'] = $value_tot['nominal'];
                }
            }
            $result['result'][$key]['jum_tot'] = $this->Pengembangan_pelatihan_model->get_thn($jenis_perjalanan, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['jum_tot'])) {
                foreach ($result['result'][$key]['jum_tot'] as $key_jum_tot => $value_jum_tot) {
                    $result['result'][$key]['jum_total']['jum'] = $value_jum_tot['jum'];
                    $result['result'][$key]['jum_total']['nominal'] = $value_jum_tot['nominal'];
                }
            }
            $result['result'][$key]['dal'] = $this->Pengembangan_pelatihan_model->get_thn($dalam, $fil_as, $unit, null, $jenis);
            if (!empty($result['result'][$key]['dal'])) {
                foreach ($result['result'][$key]['dal'] as $key_dal => $value_dal) {
                    $result['result'][$key]['dalam']['jum'] = $value_dal['jum'];
                    $result['result'][$key]['dalam']['nominal'] = $value_dal['nominal'];
                }
            }
        }


//print_r($result["result"][0]["profesi"]);die;
        $data = "test";
        $this->load->library("pdf");
        $html = $this->load->view("laporan/laporan_17", array("result" => $result['result']), true);

//echo $html;
//die;
//$customPaper = array(0,0,210,330);
        $this->pdf->loadHtml($html);
// $this->pdf->setPaper($customPaper);
        $this->pdf->setPaper("Legal",'landscape');
        $this->pdf->set_option("isPhpEnabled", true);
        $this->pdf->set_option("isHtml5ParserEnabled", true);
        $this->pdf->set_option("isRemoteEnabled", true);
        $this->pdf->render();
        $name = "download Laporan";
        $this->pdf->stream($name, array("Attachment" => 1));
    }            
}

public function list_get($offset = 0, $param_search = "", $dari="", $sampai="")
{
    $search = null;
    $limit = 200;
    $order_by = "pengembangan_pelatihan.id";
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            if (!empty($param_search)) {
                $cari = urldecode($param_search);
            }if (!empty($dari)) {
                $mulai = date("Y-m-d", strtotime($dari));
            }if (!empty($sampai)) {
                $hingga = date("Y-m-d", strtotime($sampai));
            }

            $results['result'] = $this->Pengembangan_pelatihan_model->get_list(null, $search, $offset, $limit, $mulai, $hingga, null, $order_by, $cari);
//print_r($results['result']);die();
// echo $this->db->last_query();die;
            if (!empty($results['result'])) {
                foreach ($results["result"] as $key => $value) {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }

                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail_uraian"])) {
                        foreach ($results["result"][$key]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {

                            if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                                if("Akomodasi Gol ".substr($results["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)!=$value_detail_biaya["uraian"]){
                                    $results["result"][$key]["nominal"] += $value_detail_biaya["pernominal"];
}//else if("Registrasi + Akomodasi"!=$value_detail_biaya["uraian"]){
//$results["result"][$key]["nominal"] += $value_detail_biaya["pernominal"];
//}
}
if("Akomodasi Gol ".substr($results["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)==$value_detail_biaya["uraian"]){
    $results["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
}//else if("Registrasi + Akomodasi"==$value_detail_biaya["uraian"]){
//$results["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
//}
    $results["result"][$key]["pernominal"]=$results["result"][$key]["nominal_gol"]+$results["result"][$key]["nominal"];
}
}

if (!empty($results["result"][$key]["tanggal"])) {
    foreach ($results["result"][$key]["tanggal"] as $key_detail_tanggal => $value_detail_tanggal) {
        if ($value_detail_tanggal["tanggal_to"]!=$value_detail_tanggal["tanggal_from"]) {
            $results["result"][$key]["tanggal_from"] = $value_detail_tanggal["tanggal_from"].' s/d '.$value_detail_tanggal["tanggal_to"];
        }else{
            $results["result"][$key]["tanggal_from"] = $value_detail_tanggal["tanggal_from"];
        }
        $tanggal = date('d-m-Y');
        $besok = date('d-m-Y', strtotime("+5 day", strtotime($value_detail_tanggal["tanggal_to"])));
        $from = date('d-m-Y', strtotime("-1 day", strtotime($value_detail_tanggal["tanggal_from"])));
        $to = date('d-m-Y', strtotime("+1 day", strtotime($value_detail_tanggal["tanggal_to"])));
        if(strtotime($tanggal)>strtotime($value_detail_tanggal["tanggal_from"])){
            if (strtotime($tanggal)>strtotime($value_detail_tanggal["tanggal_to"])){
                if(strtotime($tanggal)<strtotime($besok)){
                    if($value["laporan_kegiatan"]==1){
                        $results["result"][$key]["laporan"] = "Menunggu Laporkan";
                    }else{
                        $results["result"][$key]["laporan"] = "Sudah Melaporkan";
                    }
                }else if (strtotime($tanggal)>strtotime($besok)){
                    if($value["laporan_kegiatan"]==1){
                        $results["result"][$key]["laporan"] = "Belum Melaporkan";
                    }else{
                        $results["result"][$key]["laporan"] = "Sudah Melaporkan";
                    }
                }
            }
            if($value["laporan_kegiatan"]==1){
                $results["result"][$key]["laporan"] = "Belum Melaporkan";
            }else{
                $results["result"][$key]["laporan"] = "Sudah Melaporkan";
            }
        }if(strtotime($from) <= strtotime($tanggal) && strtotime($to) >= strtotime($tanggal)){
            $results["result"][$key]["laporan"] = "Melakukan Kegiatan";							
        }else if(strtotime($value_detail_tanggal["tanggal_from"]) >= strtotime($tanggal) && strtotime($value_detail_tanggal["tanggal_to"]) >= strtotime($tanggal)){
            $results["result"][$key]["laporan"] = "Pengajuang Baru";
        }


    }
}
}
}
$results['cari'] = $this->Pengembangan_pelatihan_model->get_total($mulai, $hingga, null, $cari);
//print_r($results['cari']);die();
$results['total'] = $results['cari'][0]['count'];
$results["query"] = $this->db->last_query();
$results['limit'] = $limit;
$results["is_blocked"] = $this->Pengembangan_pelatihan_model->is_blocked($decodedToken->data->NIP);
$results["is_monev"] = $this->Pengembangan_pelatihan_model->is_monev($decodedToken->data->NIP);
// echo "<pre>";
//
// echo "</pre>";
// die;
$this->set_response($results, REST_Controller::HTTP_OK);
return;
}
}

$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}     

public function list_uk_get($offset = 0, $param_search = "", $dari="", $sampai="")
{
    $search = null;
    $limit = 200;
    $order_by = "pengembangan_pelatihan.id";
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            $grup = $decodedToken->data->_pnc_id_grup;
            if (!empty($param_search)) {
                $cari = urldecode($param_search);
            }if (!empty($dari)) {
                $mulai = date("Y-m-d", strtotime($dari));
            }if (!empty($sampai)) {
                $hingga = date("Y-m-d", strtotime($sampai));
            }

            $results['result'] = $this->Pengembangan_pelatihan_model->get_list(null, $search, $offset, $limit, $mulai, $hingga, null, $order_by, $cari, $grup);
//print_r($results['result']);die();
// echo $this->db->last_query();die;
            if (!empty($results['result'])) {
                foreach ($results["result"] as $key => $value) {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }

                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail_uraian"])) {
                        foreach ($results["result"][$key]["detail_uraian"] as $key_detail_biaya => $value_detail_biaya) {

                            if(substr($value_detail_biaya["uraian"],0,13)!="Akomodasi Gol"){
                                if("Akomodasi Gol ".substr($results["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)!=$value_detail_biaya["uraian"]){
                                    $results["result"][$key]["nominal"] += $value_detail_biaya["pernominal"];
}//else if("Registrasi + Akomodasi"!=$value_detail_biaya["uraian"]){
//$results["result"][$key]["nominal"] += $value_detail_biaya["pernominal"];
//}
}
if("Akomodasi Gol ".substr($results["result"][$key]["pengembangan_pelatihan_detail"]->golongan,0,-2)==$value_detail_biaya["uraian"]){
    $results["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
}//else if("Registrasi + Akomodasi"==$value_detail_biaya["uraian"]){
//$results["result"][$key]["nominal_gol"] = $value_detail_biaya["pernominal"];
//}
    $results["result"][$key]["pernominal"]=$results["result"][$key]["nominal_gol"]+$results["result"][$key]["nominal"];
}
}

if (!empty($results["result"][$key]["tanggal"])) {
    foreach ($results["result"][$key]["tanggal"] as $key_detail_tanggal => $value_detail_tanggal) {
        if ($value_detail_tanggal["tanggal_to"]!=$value_detail_tanggal["tanggal_from"]) {
            $results["result"][$key]["tanggal_from"] = $value_detail_tanggal["tanggal_from"].' s/d '.$value_detail_tanggal["tanggal_to"];
        }else{
            $results["result"][$key]["tanggal_from"] = $value_detail_tanggal["tanggal_from"];
        }
        $tanggal = date('d-m-Y');
        $besok = date('d-m-Y', strtotime("+5 day", strtotime($value_detail_tanggal["tanggal_to"])));
        $from = date('d-m-Y', strtotime("-1 day", strtotime($value_detail_tanggal["tanggal_from"])));
        $to = date('d-m-Y', strtotime("+1 day", strtotime($value_detail_tanggal["tanggal_to"])));
        if(strtotime($tanggal)>strtotime($value_detail_tanggal["tanggal_from"])){
            if (strtotime($tanggal)>strtotime($value_detail_tanggal["tanggal_to"])){
                if(strtotime($tanggal)<strtotime($besok)){
                    if($value["laporan_kegiatan"]==1){
                        $results["result"][$key]["laporan"] = "Menunggu Laporkan";
                    }else{
                        $results["result"][$key]["laporan"] = "Sudah Melaporkan";
                    }
                }else if (strtotime($tanggal)>strtotime($besok)){
                    if($value["laporan_kegiatan"]==1){
                        $results["result"][$key]["laporan"] = "Belum Melaporkan";
                    }else{
                        $results["result"][$key]["laporan"] = "Sudah Melaporkan";
                    }
                }
            }
            if($value["laporan_kegiatan"]==1){
                $results["result"][$key]["laporan"] = "Belum Melaporkan";
            }else{
                $results["result"][$key]["laporan"] = "Sudah Melaporkan";
            }
        }if(strtotime($from) <= strtotime($tanggal) && strtotime($to) >= strtotime($tanggal)){
            $results["result"][$key]["laporan"] = "Melakukan Kegiatan";							
        }else if(strtotime($value_detail_tanggal["tanggal_from"]) >= strtotime($tanggal) && strtotime($value_detail_tanggal["tanggal_to"]) >= strtotime($tanggal)){
            $results["result"][$key]["laporan"] = "Pengajuang Baru";
        }


    }
}
}
}
$results['cari'] = $this->Pengembangan_pelatihan_model->get_total($mulai, $hingga, null, $cari, $grup);
//print_r($results['cari']);die();
$results['total'] = $results['cari'][0]['count'];
$results["query"] = $this->db->last_query();
$results['limit'] = $limit;
$results["is_blocked"] = $this->Pengembangan_pelatihan_model->is_blocked($decodedToken->data->NIP);
$results["is_monev"] = $this->Pengembangan_pelatihan_model->is_monev($decodedToken->data->NIP);
// echo "<pre>";
//
// echo "</pre>";
// die;
$this->set_response($results, REST_Controller::HTTP_OK);
return;
}
}

$this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}    

public function listlap_get($offset = 0, $param_search = "", $awal = "", $akhir = "")
{
    $search = null;
    $limit = 500;
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            if (!empty($param_search)) {
                $search["field"] = array("jenis_perjalanan", "nama_pegawai", "jabatan");
                $search["search"] = $param_search;
            }
            if(!empty($awal)){
                $from= $awal;
            }
            if(!empty($akhir)){
                $to= $akhir;
            }
            $results['result'] = $this->Pengembangan_pelatihan_model->get_all(null, $search, $offset, $limit, $from, $to);
// echo $this->db->last_query();die;
//print_r($results['result']);die;
            if (!empty($results['result'])) {
                foreach ($results["result"] as $key => $value) {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }

                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail"])) {
                        foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                            $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                        }
                    }
                }
            }

            $results['cari'] = $this->Pengembangan_pelatihan_model->get_total($mulai, $hingga, null, $cari);

            $results['total'] = $results['cari'][0]['count'];
            $results["query"] = $this->db->last_query();
            $results['limit'] = $limit;
            $results["is_blocked"] = $this->Pengembangan_pelatihan_model->is_blocked($decodedToken->data->NIP);
            $results["is_monev"] = $this->Pengembangan_pelatihan_model->is_monev($decodedToken->data->NIP);
// echo "<pre>";
//print_r($results);
// echo "</pre>";
// die;
//print_r($results);die();
            $this->set_response($results, REST_Controller::HTTP_OK);
            return;
        }
    }

    $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function listlapdel_get($offset = 0, $param_search = "", $awal = "", $akhir = "")
{
    $search = null;
    $limit = 500;
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            if (!empty($param_search)) {
                $search["field"] = array("jenis_perjalanan", "nama_pegawai", "jabatan");
                $search["search"] = $param_search;
            }
            if(!empty($awal)){
                $from= $awal;
            }
            if(!empty($akhir)){
                $to= $akhir;
            }
            $results['result'] = $this->Pengembangan_pelatihan_model->getdel_all(null, $search, $offset, $limit, $from, $to);
// echo $this->db->last_query();die;
// print_r($results);die;
            if (!empty($results['result'])) {
                foreach ($results["result"] as $key => $value) {
                    $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
                    $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
                    if (count($createdby) == 1) {
                        $results["result"][$key]["createdby"] = $createdby[0]["username"];
                    }
                    if (count($updatedby) == 1) {
                        $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
                    }

                    $results["result"][$key]["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($value["pengembangan_pelatihan_kegiatan"]);
                    $results["result"][$key]["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($value["pengembangan_pelatihan_kegiatan_status"]);
                    $results["result"][$key]["pengembangan_pelatihan_detail"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by($value["kode"]);
                    $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
                    $results["result"][$key]["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $value["id"]));
                    if (!empty($results["result"][$key]["detail"])) {
                        foreach ($results["result"][$key]["detail"] as $key_detail_biaya => $value_detail_biaya) {
                            $results["result"][$key]["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
                        }
                    }
                }
            }

            $results['cari'] = $this->Pengembangan_pelatihan_model->get_total($mulai, $hingga, null, $cari);
//print_r($results['cari']);die();
            $results['total'] = $results['cari'][0]['count'];
            $results["query"] = $this->db->last_query();
            $results['limit'] = $limit;
            $results["is_blocked"] = $this->Pengembangan_pelatihan_model->is_blocked($decodedToken->data->NIP);
            $results["is_monev"] = $this->Pengembangan_pelatihan_model->is_monev($decodedToken->data->NIP);
// echo "<pre>";
//print_r($results);
// echo "</pre>";
// die;
            $this->set_response($results, REST_Controller::HTTP_OK);
            return;
        }
    }

    $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function save_post()
{
    $headers = $this->input->request_headers();
// echo "<pre>";
// print_r($headers);
// echo "</pre>";
// die;
    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {

            $nama_pelatihan = $this->input->post("nama_pelatihan");
            $tujuan = $this->input->post("tujuan");
            $membaca = $this->input->post("membaca");
            $yth = $this->input->post("yth");
            $institusi = $this->input->post("institusi");
            $alamat = $this->input->post("alamat");
            $no_disposisi = $this->input->post("no_disposisi");
            $laporan = $this->input->post("laporan");
            $monev = $this->input->post("monev");
            $jenis = $this->input->post("jenis");
            $jenis_biaya = $this->input->post("jenis_biaya");
            $jenis_perjalanan = $this->input->post("jenis_perjalanan");
            $dalam_negeri = $this->input->post("dalam_negeri");
            $jenis_surat = $this->input->post("jenis_surat");
            $jam_mulai = $this->input->post("jam_mulai");
            $jam_sampai = $this->input->post("jam_sampai");
// $surat_tugas_dalam_negeri_dalamkota = $this->input->post("surat_tugas_dalam_negeri_dalamkota");
// $surat_tugas_dalam_negeri_luarkota = $this->input->post("surat_tugas_dalam_negeri_luarkota");
// $surat_tugas_luar_negeri = $this->input->post("surat_tugas_luar_negeri");
            $total_hari_kerja = $this->input->post("total_hari_kerja");
            $pengembangan_pelatihan_kegiatan = $this->input->post("pengembangan_pelatihan_kegiatan");
            $pengembangan_pelatihan_kegiatan_status = $this->input->post("pengembangan_pelatihan_kegiatan_status");
            $phl = $this->input->post("phl");
            $jenis_plh = $this->input->post("jenis_plh");
            $target_kinerja = $this->input->post("target_kinerja");
            $surat_tugas_dalam_negeri_luarkota = $this->input->post("surat_tugas_dalam_negeri_luarkota");

            $save["nama_pelatihan"] = ($nama_pelatihan)?$nama_pelatihan:null;
            $save["tujuan"] = ($tujuan)?$tujuan:null;
            $save["membaca"] = ($membaca)?$membaca:null;
            $save["yth"] = ($yth)?$yth:null;
            $save["institusi"] = ($institusi)?$institusi:null;
            $save["alamat"] = ($alamat)?$alamat:null;
            $save["no_disposisi"] = ($no_disposisi)?$no_disposisi:null;
            $save["laporan"] = ($laporan)?$laporan:null;
            $save["monev"] = ($monev)?$monev:null;
            $save["jenis"] = ($jenis)?$jenis:null;
            $save["jenis_biaya"] = ($jenis_biaya)?$jenis_biaya:null;
            $save["jenis_perjalanan"] = ($jenis_perjalanan)?$jenis_perjalanan:null;
            $save["dalam_negeri"] = ($dalam_negeri)?$dalam_negeri:null;
            $save["jenis_surat"] = ($jenis_surat)?$jenis_surat:null;
            $save["jam_mulai"] = ($jam_mulai)?$jam_mulai:null;
            $save["jam_sampai"] = ($jam_sampai)?$jam_sampai:null;
// $save["surat_tugas_dalam_negeri_dalamkota"] = ($surat_tugas_dalam_negeri_dalamkota)?$surat_tugas_dalam_negeri_dalamkota:null;
// $save["surat_tugas_dalam_negeri_luarkota"] = ($surat_tugas_dalam_negeri_luarkota)?$surat_tugas_dalam_negeri_luarkota:null;
// $save["surat_tugas_luar_negeri"] = ($surat_tugas_luar_negeri)?$surat_tugas_luar_negeri:null;
            $save["total_hari_kerja"] = ($total_hari_kerja)?$total_hari_kerja:null;
            $save["pengembangan_pelatihan_kegiatan"] = ($pengembangan_pelatihan_kegiatan)?$pengembangan_pelatihan_kegiatan:null;
            $save["pengembangan_pelatihan_kegiatan_status"] = ($pengembangan_pelatihan_kegiatan_status)?$pengembangan_pelatihan_kegiatan_status:null;
            $save["phl"] = ($phl)?$phl:null;
            $save["jenis_plh"] = ($jenis_plh)?$jenis_plh:null;
            $save["target_kinerja"] = ($target_kinerja)?$target_kinerja:null;
            $save["alat_angkut"] = ($surat_tugas_dalam_negeri_luarkota)?$surat_tugas_dalam_negeri_luarkota:null;

// 195 = direktur SDM
            $id_parent = $this->System_auth_model->getparent($decodedToken->data->_pnc_id_grup, '1');
// echo "<pre>";
// echo "</pre>";
// die;
            $save["id_atasan"] = $id_parent;
            $save["id_uk"] = $decodedToken->data->_pnc_id_grup;
            $save["status"] = 102;


            $detail = ($this->input->post("detail"))?$this->input->post("detail"):null;
//print_r($detail["no_berkas"]);die();
            $tanggal = ($this->input->post("tanggal"))?$this->input->post("tanggal"):null;
            $tanggal_go = ($this->input->post("tanggal_go"))?$this->input->post("tanggal_go"):null;
            $hari_go = ($this->input->post("hari_go"))?$this->input->post("hari_go"):null;
            $tanggal_back = ($this->input->post("tanggal_back"))?$this->input->post("tanggal_back"):null;
            $hari_back = ($this->input->post("hari_back"))?$this->input->post("hari_back"):null;

            $biaya["biaya_uraian"] = ($this->input->post("biaya_uraian"))?$this->input->post("biaya_uraian"):null;
            $biaya["uraian_nominal"] = ($this->input->post("uraian_nominal"))?$this->input->post("uraian_nominal"):null;
            $biaya["biaya_nominal"] = ($this->input->post("biaya_nominal"))?$this->input->post("biaya_nominal"):null;
            $biaya["total_nominal"] = ($this->input->post("total_nominal"))?$this->input->post("total_nominal"):null;
            $biaya["biaya_pernominal"] = ($this->input->post("biaya_pernominal"))?$this->input->post("biaya_pernominal"):null;
            $biaya["qty_nominal"] = ($this->input->post("qty_nominal"))?$this->input->post("qty_nominal"):null;
            $biaya["orang"] = ($this->input->post("orang"))?$this->input->post("orang"):null;
            $biaya["total"] = ($this->input->post("total"))?$this->input->post("total"):null;
            $biaya["muncul"] = ($this->input->post("muncul"))?$this->input->post("muncul"):null;
            $jumlah=count($biaya["biaya_uraian"]);

//print_r($biaya);die();       
            $result = $this->Pengembangan_pelatihan_model->create($save);
// echo "<pre>";
// print_r($save);
// echo "</pre>";
// echo "<pre>";
// print_r($result);
// echo "</pre>";
// die;
            if ($result->id) {
                for ($i = 0; $i < $jumlah ; $i++) {
                    $pengembangan_pelatihan_detail_biaya["pengembangan_pelatihan_detail_id"] = $result->id;
                    $pengembangan_pelatihan_detail_biaya["uraian"] = $biaya["biaya_uraian"][$i]["value"]?$biaya["biaya_uraian"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["uraian_nominal"] = $biaya["uraian_nominal"][$i]["value"]?$biaya["uraian_nominal"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["nominal"] = preg_replace("/[^0-9]/", "", $biaya["total_nominal"][$i]["value"]);
                    $pengembangan_pelatihan_detail_biaya["pernominal"] = preg_replace("/[^0-9]/", "", $biaya["biaya_pernominal"][$i]["value"]?$biaya["biaya_pernominal"][$i]["value"]:0);
                    $pengembangan_pelatihan_detail_biaya["qty"] = $biaya["qty_nominal"][$i]["value"];
                    $pengembangan_pelatihan_detail_biaya["orang"] = $biaya["orang"][$i]["value"]?$biaya["orang"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["total"] = preg_replace("/[^0-9]/", "", $biaya["total"][$i]["value"]?$biaya["total"][$i]["value"]:0);
                    $pengembangan_pelatihan_detail_biaya["muncul"] = $biaya["muncul"][$i]["value"]?$biaya["muncul"][$i]["value"]:0;
                    $nominal += $pengembangan_pelatihan_detail_biaya["nominal"];
                    $total += $pengembangan_pelatihan_detail_biaya["total"];
// insert detail biaya
//print_r($pengembangan_pelatihan_detail_biaya);die();
                    $pengembangan_pelatihan_detail_biaya_id = $this->Pengembangan_pelatihan_model->create_detail_row("pengembangan_pelatihan_detail_biaya", $pengembangan_pelatihan_detail_biaya);
                }
            }

            $pengembangan_pelatihan_update = $this->Pengembangan_pelatihan_model->update($result->id, array("total" => $total));

            $date= date("y-m-d");
// NOMOR URUT ORDER
            $re = $this->Pengembangan_pelatihan_model->get_no_berks();
            $noberks = $re[0]["no_berkas"];
//print_r($result);die();
            $noUrut = (int) substr($noberks, 5, 2);
            $noUrut++;
            $tahun=substr($date, 0, 2);
            $bulan=substr($date, 3, 2);
            $no_berkas = $tahun .$bulan .'.'. sprintf("%02s", $noUrut);

            if ($result){
//print_r($no_berkas);die();
                $this->insert_detail($result->id, $detail, $nominal, $no_berkas);

                if (!empty($tanggal)) {
                    foreach ($tanggal as $key => $value) {
                        foreach ($tanggal_go as $key_go => $value_go) {
                            $tanggal_explode_go = explode(" - ", $tanggal_go_1);
                        }
                        foreach ($tanggal_back as $key_back => $value_back) {
                            $tanggal_explode_back = explode(" - ", $tanggal_back_1);
                        }
                        $tanggal_1 = @$value["value"];
                        $tanggal_go_1 = @$value_go["value"];
                        $tanggal_back_1 = @$value_back["value"];
                        $tanggal_explode = explode(" - ", $tanggal_1);
                        $tanggal_go = explode(" - ", $tanggal_go_1);
                        $tanggal_back = explode(" - ", $tanggal_back_1);

// dibagi 24jam x 8 jam

                        if(empty($jam_sampai)){
                            $tanggal_diff = $total_hari_kerja * 8;
                        }else{
                            $date_awal  = new DateTime($jam_mulai);
                            $date_akhir = new DateTime($jam_sampai);
                            $selisih = $date_akhir->diff($date_awal);

                            $jam = $selisih->format('%h');
                            $menit = $selisih->format('%i');

                            if($menit >= 0 && $menit <= 9){
                                $menit = "0".$menit;
                            }

                            $hasil = $jam.".".$menit;
                            $hasil = number_format($hasil,2);
                            if($hasil>=8){
                                $tanggal_diff = $total_hari_kerja * 8;
                            }else{
                                $tanggal_diff = $total_hari_kerja * $jam;
                            }
                        }
                        $pengembangan_pelatihan_pelaksanaan[$key]["pengembangan_pelatihan_id"] = $result->id;
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_from"] = @$tanggal_explode[0];
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_too"] = date('Y-m-d', strtotime(@$tanggal_explode[1]));
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_to"] = @$tanggal_explode[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_go"] = @$tanggal_go[0];
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_go1"] = @$tanggal_go[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["hari_go"] = $hari_go;
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_back"] = @$tanggal_back[0];
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_back1"] = @$tanggal_back[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["hari_back"] = $hari_back;
                        $pengembangan_pelatihan_pelaksanaan[$key]["total_jam"] = $tanggal_diff;
//print_r($pengembangan_pelatihan_pelaksanaan[$key]);die();
                    }
                    $this->Pengembangan_pelatihan_model->create_detail("pengembangan_pelatihan_pelaksanaan", $pengembangan_pelatihan_pelaksanaan);
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

public function update_post()
{
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            $id = ($this->input->post("id"))?$this->input->post("id"):null;
            $status = ($this->input->post("status"))?$this->input->post("status"):null;
            $result = $this->Pengembangan_pelatihan_model->update($id, array("status" => $status));
            if ($result) {
                $response['hasil'] = 'success';
                $response['message'] = 'Data berhasil diperbahurui!';
            }
            else{
                $response['hasil'] = 'failed';
                $response['message'] = 'Data gagal diperbahurui!';
            }
            $response["query"] = $this->db->last_query();
            $this->set_response($response, REST_Controller::HTTP_OK);
            return;
        }
    }
    $this->set_response($response, REST_Controller::HTTP_OK);
    return;
}


public function edit_post()
{
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {

            $id = $this->input->post("id");
            $nama_pelatihan = $this->input->post("nama_pelatihan");
            $tujuan = $this->input->post("tujuan");
            $membaca = $this->input->post("membaca");
            $yth = $this->input->post("yth");
            $institusi = $this->input->post("institusi");
            $alamat = $this->input->post("alamat");
            $no_disposisi = $this->input->post("no_disposisi");
            $laporan = $this->input->post("laporan");
            $monev = $this->input->post("monev");
            $jenis = $this->input->post("jenis");
            $jenis_biaya = $this->input->post("jenis_biaya");
            $jenis_perjalanan = $this->input->post("jenis_perjalanan");
            $dalam_negeri = $this->input->post("dalam_negeri");
            $jenis_surat = $this->input->post("jenis_surat");
            $jam_mulai = $this->input->post("jam_mulai");
            $jam_sampai = $this->input->post("jam_sampai");
// $surat_tugas_dalam_negeri_dalamkota = $this->input->post("surat_tugas_dalam_negeri_dalamkota");
// $surat_tugas_dalam_negeri_luarkota = $this->input->post("surat_tugas_dalam_negeri_luarkota");
// $surat_tugas_luar_negeri = $this->input->post("surat_tugas_luar_negeri");
            $total_hari_kerja = $this->input->post("total_hari_kerja");
            $pengembangan_pelatihan_kegiatan = $this->input->post("pengembangan_pelatihan_kegiatan");
            $pengembangan_pelatihan_kegiatan_status = $this->input->post("pengembangan_pelatihan_kegiatan_status");
            $phl = $this->input->post("phl");
            $jenis_plh = $this->input->post("jenis_plh");
            $target_kinerja = $this->input->post("target_kinerja");
            $surat_tugas_dalam_negeri_luarkota = $this->input->post("surat_tugas_dalam_negeri_luarkota");


            $save["id"] = ($id)?$id:null;
            $save["nama_pelatihan"] = ($nama_pelatihan)?$nama_pelatihan:null;
            $save["tujuan"] = ($tujuan)?$tujuan:null;
            $save["membaca"] = ($membaca)?$membaca:null;
            $save["yth"] = ($yth)?$yth:null;
            $save["institusi"] = ($institusi)?$institusi:null;
            $save["alamat"] = ($alamat)?$alamat:null;
            $save["no_disposisi"] = ($no_disposisi)?$no_disposisi:null;
            $save["laporan"] = ($laporan)?$laporan:null;
            $save["monev"] = ($monev)?$monev:null;
            $save["jenis"] = ($jenis)?$jenis:null;
            $save["jenis_biaya"] = ($jenis_biaya)?$jenis_biaya:null;
            $save["jenis_perjalanan"] = ($jenis_perjalanan)?$jenis_perjalanan:null;
            $save["dalam_negeri"] = ($dalam_negeri)?$dalam_negeri:null;
            $save["jenis_surat"] = ($jenis_surat)?$jenis_surat:null;
            $save["jam_mulai"] = ($jam_mulai)?$jam_mulai:null;
            $save["jam_sampai"] = ($jam_sampai)?$jam_sampai:null;
// $save["surat_tugas_dalam_negeri_dalamkota"] = ($surat_tugas_dalam_negeri_dalamkota)?$surat_tugas_dalam_negeri_dalamkota:null;
// $save["surat_tugas_dalam_negeri_luarkota"] = ($surat_tugas_dalam_negeri_luarkota)?$surat_tugas_dalam_negeri_luarkota:null;
// $save["surat_tugas_luar_negeri"] = ($surat_tugas_luar_negeri)?$surat_tugas_luar_negeri:null;
            $save["total_hari_kerja"] = ($total_hari_kerja)?$total_hari_kerja:null;
            $save["pengembangan_pelatihan_kegiatan"] = ($pengembangan_pelatihan_kegiatan)?$pengembangan_pelatihan_kegiatan:null;
            $save["pengembangan_pelatihan_kegiatan_status"] = ($pengembangan_pelatihan_kegiatan_status)?$pengembangan_pelatihan_kegiatan_status:null;
            $save["phl"] = ($phl)?$phl:null;
            $save["jenis_plh"] = ($jenis_plh)?$jenis_plh:null;
            $save["target_kinerja"] = ($target_kinerja)?$target_kinerja:null;
            $save["alat_angkut"] = ($surat_tugas_dalam_negeri_luarkota)?$surat_tugas_dalam_negeri_luarkota:null;

//print_r($save);die();
            $detail = ($this->input->post("detail"))?$this->input->post("detail"):null;
            $tanggal = ($this->input->post("tanggal"))?$this->input->post("tanggal"):null;
            $biaya_uraian = ($this->input->post("biaya_uraian"))?$this->input->post("biaya_uraian"):null;
            $tanggal_go = ($this->input->post("tanggal_go"))?$this->input->post("tanggal_go"):null;
            $hari_go = ($this->input->post("hari_go"))?$this->input->post("hari_go"):null;
            $tanggal_back = ($this->input->post("tanggal_back"))?$this->input->post("tanggal_back"):null;
            $hari_back = ($this->input->post("hari_back"))?$this->input->post("hari_back"):null;
//print_r($detail);die();
            $result = $this->Pengembangan_pelatihan_model->update_de($save["id"], $save);
//print_r($result);die();

            $biaya["biaya_uraian"] = ($this->input->post("biaya_uraian"))?$this->input->post("biaya_uraian"):null;
            $biaya["uraian_nominal"] = ($this->input->post("uraian_nominal"))?$this->input->post("uraian_nominal"):null;
            $biaya["biaya_nominal"] = ($this->input->post("biaya_nominal"))?$this->input->post("biaya_nominal"):null;
            $biaya["total_nominal"] = ($this->input->post("total_nominal"))?$this->input->post("total_nominal"):null;
            $biaya["biaya_pernominal"] = ($this->input->post("biaya_pernominal"))?$this->input->post("biaya_pernominal"):null;
            $biaya["qty_nominal"] = ($this->input->post("qty_nominal"))?$this->input->post("qty_nominal"):null;
            $biaya["orang"] = ($this->input->post("orang"))?$this->input->post("orang"):null;
            $biaya["total"] = ($this->input->post("total"))?$this->input->post("total"):null;
            $biaya["muncul"] = ($this->input->post("muncul"))?$this->input->post("muncul"):null;
            $jumlah=count($biaya["biaya_uraian"]);
// echo "<pre>";
// print_r($save);
// echo "</pre>";
// echo "<pre>";
//print_r($biaya["muncul"]);die();
// echo "</pre>";
// die;
            $this->Pengembangan_pelatihan_model->delete_detail_row("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $result->id));

            if ($result->id) {
                for ($i = 0; $i < $jumlah ; $i++) {
                    $pengembangan_pelatihan_detail_biaya["pengembangan_pelatihan_detail_id"] = $result->id;
                    $pengembangan_pelatihan_detail_biaya["uraian"] = $biaya["biaya_uraian"][$i]["value"]?$biaya["biaya_uraian"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["uraian_nominal"] = $biaya["uraian_nominal"][$i]["value"]?$biaya["uraian_nominal"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["nominal"] = preg_replace("/[^0-9]/", "", $biaya["total_nominal"][$i]["value"]);
                    $pengembangan_pelatihan_detail_biaya["pernominal"] = preg_replace("/[^0-9]/", "", $biaya["biaya_pernominal"][$i]["value"]?$biaya["biaya_pernominal"][$i]["value"]:0);
                    $pengembangan_pelatihan_detail_biaya["qty"] = $biaya["qty_nominal"][$i]["value"];
                    $pengembangan_pelatihan_detail_biaya["orang"] = $biaya["orang"][$i]["value"]?$biaya["orang"][$i]["value"]:null;
                    $pengembangan_pelatihan_detail_biaya["total"] = preg_replace("/[^0-9]/", "", $biaya["total"][$i]["value"]?$biaya["total"][$i]["value"]:0);
                    $pengembangan_pelatihan_detail_biaya["muncul"] = $biaya["muncul"][$i]["value"]?$biaya["muncul"][$i]["value"]:0;
                    $nominal += $pengembangan_pelatihan_detail_biaya["nominal"];
                    $total += $pengembangan_pelatihan_detail_biaya["total"];
//// insert detail biaya
//print_r($pengembangan_pelatihan_detail_biaya);die();
                    $pengembangan_pelatihan_detail_biaya_id = $this->Pengembangan_pelatihan_model->create_detail_row("pengembangan_pelatihan_detail_biaya", $pengembangan_pelatihan_detail_biaya);
                }
            }
            $date= date("y-m-d");
// NOMOR URUT ORDER
            $re = $this->Pengembangan_pelatihan_model->get_no_berks();
            $noberks = $re[0]["no_berkas"];
//print_r($result);die();
            $noUrut = (int) substr($noberks, 5, 2);
            $noUrut++;
            $tahun=substr($date, 0, 2);
            $bulan=substr($date, 3, 2);
            $no_berkas = $tahun .$bulan .'.'. sprintf("%02s", $noUrut);


            $pengembangan_pelatihan_update = $this->Pengembangan_pelatihan_model->update($result->id, array("total" => $total));
//print_r($result->id);die();

            if ($result){
// delete all pelatihan_detail
                $this->Pengembangan_pelatihan_model->delete_detail_row("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $result->id));
                $this->Pengembangan_pelatihan_model->delete_detail_row("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $result->id));

                $this->insert_detail($result->id, $detail, $nominal, $no_berkas);

                if (!empty($tanggal)) {
                    foreach ($tanggal as $key => $value) {
                        foreach ($tanggal_go as $key_go => $value_go) {
                            $tanggal_explode_go = explode(" - ", $tanggal_go_1);
                        }
                        foreach ($tanggal_back as $key_back => $value_back) {
                            $tanggal_explode_back = explode(" - ", $tanggal_back_1);
                        }
                        $tanggal_1 = @$value["value"];
                        $tanggal_go_1 = @$value_go["value"];
                        $tanggal_back_1 = @$value_back["value"];
                        $tanggal_explode = explode(" - ", $tanggal_1);
                        $tanggal_go = explode(" - ", $tanggal_go_1);
                        $tanggal_back = explode(" - ", $tanggal_back_1);

// dibagi 24jam x 8 jam
                        if(empty($jam_sampai)){
                            $tanggal_diff = $total_hari_kerja * 8;
                        }else{
                            $date_awal  = new DateTime($jam_mulai);
                            $date_akhir = new DateTime($jam_sampai);
                            $selisih = $date_akhir->diff($date_awal);

                            $jam = $selisih->format('%h');
                            $menit = $selisih->format('%i');

                            if($menit >= 0 && $menit <= 9){
                                $menit = "0".$menit;
                            }

                            $hasil = $jam.".".$menit;
                            $hasil = number_format($hasil,2);
                            if($hasil>=8){
                                $tanggal_diff = $total_hari_kerja * 8;
                            }else{
                                $tanggal_diff = $total_hari_kerja * $jam;
                            }
                        }
                        $pengembangan_pelatihan_pelaksanaan[$key]["pengembangan_pelatihan_id"] = $result->id;
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_from"] = @($tanggal_explode[0]?$tanggal_explode[0]:Null);
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_to"] = @$tanggal_explode[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_too"] = @($tanggal_explode[1]?$tanggal_explode[1]:Null);
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_go"] = @($tanggal_go[0]?$tanggal_go[0]:Null);
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_go1"] = @$tanggal_go[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["hari_go"] = $hari_go;
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_back"] = @($tanggal_back[0]?$tanggal_back[0]:Null);
                        $pengembangan_pelatihan_pelaksanaan[$key]["tanggal_back1"] = @$tanggal_back[1];
                        $pengembangan_pelatihan_pelaksanaan[$key]["hari_back"] = $hari_back;
                        $pengembangan_pelatihan_pelaksanaan[$key]["total_jam"] = $tanggal_diff;
//print_r($pengembangan_pelatihan_pelaksanaan[$key]);die();
                    }
                    $this->Pengembangan_pelatihan_model->create_detail("pengembangan_pelatihan_pelaksanaan", $pengembangan_pelatihan_pelaksanaan);
                }

                $response['hasil'] = 'success';
                $response['message'] = 'Data berhasil diperbahurui!';
            }
            else{
                $response['hasil'] = 'failed';
                $response['message'] = 'Data gagal diperbahurui!';
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
            $this->Pengembangan_pelatihan_model->delete($id);

            $response['hasil'] = 'success';
            $response['message'] = 'Data berhasil dihapus!';
            $this->set_response($response, REST_Controller::HTTP_OK);
            return;
        }
    }

    $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function upload_file_post()
{   print_r($_POST);die();
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {

            $config['upload_path'] = 'upload/data';
            $config['allowed_types'] = 'gif|jpg|png|jpeg|pdf|xls|doc|xlsx';
            $config['max_size'] = '50000000';
            $this->load->library('upload', $config);
            $arrdata = array(
                "statue" => 2,
            );
            if (!$this->upload->do_upload('inputfileupload')) {
                $error = array('error' => $this->upload->display_errors());
            } else {
                $upload = $this->upload->data();
                $arrdata["file"] = $upload['file_name'];
            }
//print_r($arrdata);die();

            $id = $this->input->get("id");

            $this->Pengembangan_pelatihan_model->update($id, $arrdata);

            $response['hasil'] = 'success';
            $response['message'] = 'Data berhasil diupdate!';
            $this->set_response($response, REST_Controller::HTTP_OK);
            return;
        }
    }
    $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function laporan_selesai_get()
{
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        date_default_timezone_set('Asia/Jakarta');
        if ($decodedToken != false) {
            $author = $decodedToken->data->_pnc_username;
            $id = $this->input->get("id");
            $laporan = $this->input->get("laporan");
            if($laporan!=0){
                $this->Pengembangan_pelatihan_model->update_detail($id, array("laporan_kegiatan" => 0, "laporan_by" => $author, "laporan_date" => date("Y-m-d H:i:s")));
            }else{
                $this->Pengembangan_pelatihan_model->update_detail($id, array("laporan_kegiatan" => 1, "laporan_by" => $author, "laporan_date" => date("Y-m-d H:i:s")));
            }
            $response['hasil'] = 'success';
            $response['message'] = 'Data berhasil diupdate!';
            $this->set_response($response, REST_Controller::HTTP_OK);
            return;
        }
    }
    $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
}

public function del_get()
{
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {
            $id = $this->input->get("id");
            $this->Pengembangan_pelatihan_model->update_pegawai($id, array("statue" => 0));

            $response['hasil'] = 'success';
            $response['message'] = 'Data berhasil diupdate!';
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
    $result = $this->Pengembangan_pelatihan_model->get_all(array("pengembangan_pelatihan.id" => $id), null, $offset, $limit);

    if (!empty($result)) {
        $results["success"] = true;
        $result = $result[0];
        $tanggal = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $result["id"]));
        $result["tanggal"] = $tanggal;
// print_r($result);die;
        $result["pengembangan_pelatihan_kegiatan"] = $this->Pengembangan_pelatihan_kegiatan_model->get_by_id($result["pengembangan_pelatihan_kegiatan"]);
        $result["pengembangan_pelatihan_kegiatan_status"] = $this->Pengembangan_pelatihan_kegiatan_status_model->get_by_id($result["pengembangan_pelatihan_kegiatan_status"]);
        $result["detail"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail", array("pengembangan_pelatihan_id" => $result["id"]));
        $result["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $result["id"]));

//if (!empty($result["detail"])) {
//    foreach ($result["detail"] as $key_detail_biaya => $value_detail_biaya) {
//        $result["detail"][$key_detail_biaya]["detail_uraian"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_detail_biaya", array("pengembangan_pelatihan_detail_id" => $value_detail_biaya["id"]));
//    }
//}
//print_r($result);die;
        $results["data"] = $result;



// if (!empty($results['result'])) {
//     foreach ($results["result"] as $key => $value) {
//         $createdby = $this->db->select("username")->where(array("id_user" => $value["createdby"]))->get("sys_user")->result_array();
//         $updatedby = $this->db->select("username")->where(array("id_user" => $value["updatedby"]))->get("sys_user")->result_array();
//         if (count($createdby) == 1) {
//             $results["result"][$key]["createdby"] = $createdby[0]["username"];
//         }
//         if (count($updatedby) == 1) {
//             $results["result"][$key]["updatedby"] = $updatedby[0]["username"];
//         }
//         $results["result"][$key]["tanggal"] = $this->Pengembangan_pelatihan_model->get_detail("pengembangan_pelatihan_pelaksanaan", array("pengembangan_pelatihan_id" => $value["id"]));
//     }
// }




    }

    $this->set_response($results, REST_Controller::HTTP_OK);
}

public function insert_detail($pengembangan_pelatihan_id, $detail, $nominal, $no_berkas)
{   
// echo "<pre>";
// print_r($pengembangan_pelatihan_id);
// echo "</pre>";
// echo "<pre>";
// print_r($detail);
// echo "</pre>";
// die();

    if (!empty($detail) && is_array($detail)) {
//echo $no_berkas;die();

        $pengembangan_pelatihan_detail["uraian_total"] = $nominal;
        foreach ($detail as $key => $value) {
            $pengembangan_pelatihan_detail["pengembangan_pelatihan_id"] = $pengembangan_pelatihan_id;
            if(!empty($value["berkas"])){
                $pengembangan_pelatihan_detail["berkas"] = $value["berkas"];
            }else{
                $pengembangan_pelatihan_detail["berkas"] = $no_berkas;
            }
            $pengembangan_pelatihan_detail["nopeg"] = $value["nopeg"];
            $pengembangan_pelatihan_detail["nip"] = $value["nip"];
            $pengembangan_pelatihan_detail["nik"] = $value["nik"];
            $pengembangan_pelatihan_detail["laporan_kegiatan"] = $value["laporan_kegiatan"];
            $pengembangan_pelatihan_detail["nama_pegawai"] = $value["nama_pegawai"];
            $pengembangan_pelatihan_detail["pangkat"] = $value["pangkat"];
            $pengembangan_pelatihan_detail["golongan"] = $value["golongan"];
//$pengembangan_pelatihan_detail["akomodasi"] = $value["akomodasi"]?$value["akomodasi"]:null;
            $pengembangan_pelatihan_detail["jabatan"] = $value["jabatan"];
//print_r($pengembangan_pelatihan_detail);
//print_r($pengembangan_pelatihan_detail);die();
            $detail_id = $this->Pengembangan_pelatihan_model->create_detail_row("pengembangan_pelatihan_detail", $pengembangan_pelatihan_detail);
// echo "<pre>";
// echo "</pre>";
// die();
        }
    }
}

function cek_post()
{
    $headers = $this->input->request_headers();

    if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
        $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
        if ($decodedToken != false) {

            $tanggal = $this->input->post("tanggal");

            if (!empty($tanggal)) {
                foreach ($tanggal as $key => $value) {
                    $tanggal_1 = @$value["value"];
                    $tanggal_explode = explode(" - ", $tanggal_1);

                }}			
                $nopeg = $this->input->post("nopeg");
                $from = @($tanggal_explode[0]?$tanggal_explode[0]:Null);
                $to = @$tanggal_explode[1];

                $this->db->where('his_cuti.id_user', $nopeg);
                $this->db->where('his_cuti.status', '103');
                $this->db->where("his_cuti.tgl_cuti <=", date_format(date_create($from), "Y-m-d"));
                $this->db->where("his_cuti.tgl_akhir_cuti >=", date_format(date_create($to), "Y-m-d"));
                $cek_cuti=$this->db->get('his_cuti')->row();

                $this->db->where('surat_detail.nopeg', $nopeg);
                $this->db->where("surat_pelaksanaan.tanggal_from <=", date_format(date_create($from), "Y-m-d"));
                $this->db->where("surat_pelaksanaan.tanggal_to >=", date_format(date_create($to), "Y-m-d"));
                $this->db->join("surat_pelaksanaan", "surat_detail.surat_id = surat_pelaksanaan.surat_id");
                $this->db->join("surat", "surat_detail.surat_id = surat.id");
                $cek_surat=$this->db->get('surat_detail')->row();


                $this->db->where('pengembangan_pelatihan_detail.nopeg', $nopeg);
                $this->db->where("pengembangan_pelatihan_pelaksanaan.tanggal_from <=", date_format(date_create($from), "Y-m-d"));
                $this->db->where("pengembangan_pelatihan_pelaksanaan.tanggal_to >=", date_format(date_create($to), "Y-m-d"));
                $this->db->join("pengembangan_pelatihan_pelaksanaan", "pengembangan_pelatihan_detail.pengembangan_pelatihan_id = pengembangan_pelatihan_pelaksanaan.pengembangan_pelatihan_id");
                $this->db->join("pengembangan_pelatihan", "pengembangan_pelatihan_detail.pengembangan_pelatihan_id = pengembangan_pelatihan.id");
                $cek=$this->db->get('pengembangan_pelatihan_detail')->row();
//print_r($from);die();
                if (empty($cek_cuti)) {
                    if (empty($cek_surat)) {
                        if (empty($cek)) {
                            $arr['hasil'] = 'success';
                        } else {
                            $arr['hasil'] = 'error';
                            $arr['message'] = 'Yang bersangkutan sedang menghadiri '.$cek->nama_pelatihan.' pada tanggal '.$cek->tanggal_from.' s/d '.$cek->tanggal_to.' yang diselenggarakan oleh '.$cek->institusi.' di '.$cek->tujuan;
                        }
                    }
                    else{
                        $arr['hasil'] = 'eror';
                        $arr['message'] = 'Yang bersangkutan telah dibuatkan surat tugas / surat izin pada tanggal '.$cek->nama_pelatihan.' pada tanggal '.$cek->tanggal_from.' s/d '.$cek->tanggal_to.' yang diselenggarakan oleh '.$cek->institusi.' di '.$cek->tujuan;
                    }
                }else {
                    $arr['hasil'] = 'eror';
                    $arr['message'] = 'Yang bersangkutan sedang cuti pada tanggal '.date_format(date_create($cek_cuti->tgl_cuti), "d-m-Y").' s/d '.date_format(date_create($cek_cuti->tgl_akhir_cuti), "d-m-Y");
                }
                $this->set_response($arr, REST_Controller::HTTP_OK);
                return;
            }
        }

        $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
	
}