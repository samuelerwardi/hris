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

class Abk extends REST_Controller
{

    public function listshift_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;

                //manual setting:
                $kaur = 5;
                $sta = 6;
                $pa = 7;
                $id_shift=50;
                $g=$user_froup = $decodedToken->data->_pnc_id_grup;
                $id_skk = 53;
                $id_ski = 52;

                //cari jumlah shift 
                $this->db->where('id_shift', $id_shift);
                if(empty($this->input->get('year'))){
                    $this->db->where('tahun',date('Y'));
                   }else{
                    $this->db->where('tahun',$this->input->get('year'));
                   }
                   $this->db->order_by('waktu_kerja','DESC');
                 $shift = $this->db->get('abk_shift_pegawai')->row();
                  
                //beban kerja
                 $waktu = $shift->waktu_kerja;

                 //SKK
                 $this->db->select('sum(frekuensi*waktu) as totska');
                 $this->db->where('id_shift', $id_shift);
                 $this->db->where('id_faktor', $id_skk);
                 $this->db->where('tampilkan', '1');
                 $this->db->where('id_uk', $g);
                 if(empty($this->input->get('year'))){
                     $this->db->where('tahun',date('Y'));
                    }else{
                     $this->db->where('tahun',$this->input->get('year'));
                    } 

                  $skk_res = $this->db->get('abk_faktor_kelonggaran')->row();
                  $total_SKK = $skk_res->totska;
                  $FKK = $total_SKK/$waktu;
                  $SKK = 1/(1-$FKK);

                
                  //SKI KEPALA URUSAN
                  $this->db->select('sum(frekuensi*waktu) as totska');
                  $this->db->where('id_shift', $id_shift);
                  $this->db->where('id_faktor', $id_ski);
                  $this->db->where('kategori_sdm', $kaur);
                  $this->db->where('tampilkan', '1');
                  $this->db->where('id_uk', $g);
                  if(empty($this->input->get('year'))){
                      $this->db->where('tahun',date('Y'));
                     }else{
                      $this->db->where('tahun',$this->input->get('year'));
                     } 
 
                   $ski_kaur = $this->db->get('abk_faktor_kelonggaran')->row();
                   $total_SKI_KAUR = $ski_kaur->totska;
                   $SKI_KAUR =  $total_SKI_KAUR/$waktu;

                   //SKI ALL NOT KAUR
                  $this->db->select('sum(frekuensi*waktu) as totska');
                  $this->db->where('id_shift', $id_shift);
                  $this->db->where('id_faktor', $id_ski);
                  $this->db->where('kategori_sdm <>', $kaur);
                  $this->db->where('tampilkan', '1');
                  $this->db->where('id_uk', $g);
                  if(empty($this->input->get('year'))){
                      $this->db->where('tahun',date('Y'));
                     }else{
                      $this->db->where('tahun',$this->input->get('year'));
                     } 
 
                   $ski_non_kaur = $this->db->get('abk_faktor_kelonggaran')->row();
                   $total_SKI_NONKAUR =  $ski_non_kaur->totska;
                   $SKI_NONKAUR =  $total_SKI_NONKAUR/$waktu;
                  
                 
                $this->db->select('sum(kaur) as jkaur,sum(staff_admin) as jsa,sum(pekarya)as jpa');
                $this->db->where('abk_langkah_kerja.tampilkan','1');
                $this->db->join('abk_langkah_kerja','abk_langkah_kerja.id_beban_kerja = abk_beban_kerja.id');
                $this->db->where('abk_beban_kerja.tampilkan','1');
                
                if( ($user_froup =='1') OR  ($user_froup=='6')){
                    if(!empty($this->input->get('uk'))){
                   $this->db->where('abk_beban_kerja.id_uk',$this->input->get('uk'));
                    }
               }else{
                   $this->db->where('abk_beban_kerja.id_uk',$user_froup);
               }

               if(empty($this->input->get('year'))){
                $this->db->where('abk_beban_kerja.tahun',date('Y'));
               }else{
                $this->db->where('abk_beban_kerja.tahun',$this->input->get('year'));
               }
                
               $this->db->where('abk_beban_kerja.author', $g);
                $res = $this->db->get('abk_beban_kerja')->row();

               
				  
			if(!empty($res)){
                $i=0;
                
               $data[$kaur]['beban']=$res->jkaur;
               $data[$sta]['beban']=$res->jsa;
               $data[$pa]['beban']=$res->jpa;

               $data[$kaur]['nama']='Ka.Ur';
               $data[$sta]['nama']='Staff Admin';
               $data[$pa]['nama']='Pekarya';

               $data[$kaur]['waktu']=$waktu;
               $data[$sta]['waktu']=$waktu;
               $data[$pa]['waktu']=$waktu;

               $data[$kaur]['subSDM']=$res->jkaur/$waktu;
               $data[$sta]['subSDM']=$res->jsa/$waktu;
               $data[$pa]['subSDM']=$res->jpa/$waktu;
               
               $data[$kaur]['skk']=$SKK;
               $data[$sta]['skk']=$SKK;
               $data[$pa]['skk']=$SKK;

               $data[$kaur]['ski']=$SKI_KAUR;
               $data[$sta]['ski']=$SKI_NONKAUR;
               $data[$pa]['ski']=$SKI_NONKAUR;

               $data[$kaur]['sdm']=($data[$kaur]['subSDM']*$data[$kaur]['skk'])+$data[$kaur]['ski'];
               $data[$sta]['sdm']=($data[$sta]['subSDM']*$data[$sta]['skk'])+$data[$sta]['ski'];
               $data[$pa]['sdm']=($data[$pa]['subSDM']*$data[$pa]['skk'])+$data[$pa]['ski'];
               $totsdm=0;
				 foreach($data as $d){
                    $arr['result'][]=array(  
                    'beban'=>$d['beban'],
                    'kategori_sdm'=>$d['nama'],
                    'waktu' => $d['waktu'],
                    'skk' =>$d['skk'],
                    'ski' =>$d['ski'],
                    'subSDM'=>$d['subSDM'],
                    'sdm'=>$d['sdm']

                );
                $totsdm += $d['sdm'];
                  }

                  $arr['result'][]=array(  
                    'beban'=>'',
                    'kategori_sdm'=>'TOTAL',
                    'waktu' => '',
                    'skk' =>'',
                    'ski' =>'',
                    'subSDM'=>'',
                    'sdm'=>round($totsdm)

                );
                    
					$arr['hasil']='success'; 
				 

			}else{
              
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listnonshift_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;

                //manual setting:
                $kaur = 5;
                $sta = 6;
                $pa = 7;
                $id_shift=51;
                $user_froup = $decodedToken->data->_pnc_id_grup;
                $id_skk = 53;
                $id_ski = 52;

                //cari jumlah shift 
                $this->db->where('id_shift', $id_shift);
                if(empty($this->input->get('year'))){
                    $this->db->where('tahun',date('Y'));
                   }else{
                    $this->db->where('tahun',$this->input->get('year'));
                   }
                   $this->db->order_by('waktu_kerja','DESC');
                 $shift = $this->db->get('abk_shift_pegawai')->row();
                 
                //beban kerja
                 $waktu = $shift->waktu_kerja;

                 //SKK
                 $this->db->select('sum(frekuensi*waktu) as totska');
                 $this->db->where('id_shift', $id_shift);
                 $this->db->where('id_faktor', $id_skk);
                 if(empty($this->input->get('year'))){
                     $this->db->where('tahun',date('Y'));
                    }else{
                     $this->db->where('tahun',$this->input->get('year'));
                    } 

                  $skk_res = $this->db->get('abk_faktor_kelonggaran')->row();
                  $total_SKK = $skk_res->totska;
                  $FKK = $total_SKK/$waktu;
                  $SKK = 1/(1-$FKK);

                
                  //SKI KEPALA URUSAN
                  $this->db->select('sum(frekuensi*waktu) as totska');
                  $this->db->where('id_shift', $id_shift);
                  $this->db->where('id_faktor', $id_ski);
                  $this->db->where('kategori_sdm', $kaur);
                  if(empty($this->input->get('year'))){
                      $this->db->where('tahun',date('Y'));
                     }else{
                      $this->db->where('tahun',$this->input->get('year'));
                     } 
 
                   $ski_kaur = $this->db->get('abk_faktor_kelonggaran')->row();
                   $total_SKI_KAUR = $ski_kaur->totska;
                   $SKI_KAUR =  $total_SKI_KAUR/$waktu;

                   //SKI ALL NOT KAUR
                  $this->db->select('sum(frekuensi*waktu) as totska');
                  $this->db->where('id_shift', $id_shift);
                  $this->db->where('id_faktor', $id_ski);
                  $this->db->where('kategori_sdm <>', $kaur);
                  if(empty($this->input->get('year'))){
                      $this->db->where('tahun',date('Y'));
                     }else{
                      $this->db->where('tahun',$this->input->get('year'));
                     } 
 
                   $ski_non_kaur = $this->db->get('abk_faktor_kelonggaran')->row();
                   $total_SKI_NONKAUR =  $ski_non_kaur->totska;
                   $SKI_NONKAUR =  $total_SKI_NONKAUR/$waktu;
                  
                 
                $this->db->select('sum(kaur) as jkaur,sum(staff_admin) as jsa,sum(pekarya)as jpa');
                $this->db->where('abk_langkah_kerja.tampilkan','1');
                $this->db->join('abk_langkah_kerja','abk_langkah_kerja.id_beban_kerja = abk_beban_kerja.id');
                $this->db->where('abk_beban_kerja.tampilkan','1');
                
                if( ($user_froup =='1') OR  ($user_froup=='6')){
                    if(!empty($this->input->get('uk'))){
                   $this->db->where('abk_beban_kerja.id_uk',$this->input->get('uk'));
                    }
               }else{
                   $this->db->where('abk_beban_kerja.id_uk',$user_froup);
               }

               if(empty($this->input->get('year'))){
                $this->db->where('abk_beban_kerja.tahun',date('Y'));
               }else{
                $this->db->where('abk_beban_kerja.tahun',$this->input->get('year'));
               }
               
                
                $res = $this->db->get('abk_beban_kerja')->row();

               
				  
			if(!empty($res)){
                $i=0;
                
               $data[$kaur]['beban']=$res->jkaur;
               $data[$sta]['beban']=$res->jsa;
               $data[$pa]['beban']=$res->jpa;

               $data[$kaur]['nama']='Ka.Ur';
               $data[$sta]['nama']='Staff Admin';
               $data[$pa]['nama']='Pekarya';

               $data[$kaur]['waktu']=$waktu;
               $data[$sta]['waktu']=$waktu;
               $data[$pa]['waktu']=$waktu;

               $data[$kaur]['subSDM']=$res->jkaur/$waktu;
               $data[$sta]['subSDM']=$res->jsa/$waktu;
               $data[$pa]['subSDM']=$res->jpa/$waktu;
               
               $data[$kaur]['skk']=$SKK;
               $data[$sta]['skk']=$SKK;
               $data[$pa]['skk']=$SKK;

               $data[$kaur]['ski']=$SKI_KAUR;
               $data[$sta]['ski']=$SKI_NONKAUR;
               $data[$pa]['ski']=$SKI_NONKAUR;

               $data[$kaur]['sdm']=($data[$kaur]['subSDM']*$data[$kaur]['skk'])+$data[$kaur]['ski'];
               $data[$sta]['sdm']=($data[$sta]['subSDM']*$data[$sta]['skk'])+$data[$sta]['ski'];
               $data[$pa]['sdm']=($data[$pa]['subSDM']*$data[$pa]['skk'])+$data[$pa]['ski'];
                $totsdm=0;
				 foreach($data as $d){
                    $arr['result'][]=array(  
                    'beban'=>$d['beban'],
                    'kategori_sdm'=>$d['nama'],
                    'waktu' => $d['waktu'],
                    'skk' =>$d['skk'],
                    'ski' =>$d['ski'],
                    'subSDM'=>$d['subSDM'],
                    'sdm'=>$d['sdm']

                );
                $totsdm += $d['sdm'];
                  }

                  $arr['result'][]=array(  
                    'beban'=>'',
                    'kategori_sdm'=>'TOTAL',
                    'waktu' => '',
                    'skk' =>'',
                    'ski' =>'',
                    'subSDM'=>'',
                    'sdm'=>round($totsdm)

                );
                    
					$arr['hasil']='success'; 
				 

			}else{
                $arr['result'][]=array(
                    'no'=> '',
                'id'=>'',
                'faktor'=>'',
                'waktu_kerja' => '',
                'keterangan' => '',
                'tahun' => ''

            );
			$arr['hasil']='success';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listform2_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
                if(!empty($this->input->get('id_shift'))){
                    $this->db->where('id_shift',$this->input->get('id_shift'));
                }
                if(empty($this->input->get('year'))){
                    $thn=date('Y');
                }else{
                    $thn = $this->input->get('year');
                }
                $this->db->select('abk_shift_pegawai.*,dm_term.nama as shift');
                $this->db->where('tahun',$thn);
                $this->db->where('abk_shift_pegawai.tampilkan','1');
                $this->db->join('dm_term','dm_term.id=abk_shift_pegawai.id_shift');
                $res = $this->db->get('abk_shift_pegawai')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){
                    $arr['result'][]=array(
                    'no'=> ++$i,
                    'id'=>$d->id,
                    'shift'=>$d->shift,
                    'faktor'=>$d->faktor,
                    'waktu_kerja' => $d->waktu_kerja,
                    'keterangan' => $d->keterangan,
                    'tahun' => $d->tahun 

                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
                $arr['result'][]=array(
                    'no'=> '',
                'id'=>'',
                'faktor'=>'',
                'waktu_kerja' => '',
                'keterangan' => '',
                'tahun' => ''

            );
			$arr['hasil']='success';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listform3_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
                $this->db->select('abk_beban_kerja.*,sys_grup_user.grup');
                if( ($user_froup =='1') OR  ($user_froup=='6')){
                    if(!empty($this->input->get('uk'))){
                   $this->db->where('id_uk',$this->input->get('uk'));
                    }
               }else{
                   $this->db->where('id_uk',$user_froup);
               }
               
               $this->db->where('tahun',$this->input->get('year'));
               $this->db->where('abk_beban_kerja.tampilkan','1' );
            
                   $this->db->join('sys_grup_user','sys_grup_user.id_grup = abk_beban_kerja.id_uk');
                
                $res = $this->db->get('abk_beban_kerja')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){
                    $arr['result'][]=array(
                        'no'=> $d->id,
                    'id'=>$d->id,
                    'id_uk'=>$d->id_uk,
                    'kegiatan_pokok' => $d->kegiatan_pokok,
                    'uraian_tugas' => $d->uraian_tugas,
                    'tahun' => $d->tahun ,
                    'produk_dihasilkan' => $d->produk_dihasilkan,
                    'jumlah'=> $d->jumlah,
                    'unit_kerja'=>$d->grup,

                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
                $arr['result'][]=array(
                    'no'=> '',
                'id'=>'',
                'faktor'=>'',
                'waktu_kerja' => '',
                'keterangan' => '',
                'tahun' => ''

            );
			$arr['hasil']='success';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listform4_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
               
               $this->db->where('id_beban_kerja',$this->input->get('id_ut'));
                
                
               $this->db->where('tampilkan','1' );
            
                
                $res = $this->db->get('abk_langkah_kerja')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){
                    $arr['result'][]=array(
                        'no'=> ++$i,
                    'id'=>$d->id,
                    'id_beban_kerja'=>$d->id_beban_kerja,
                    'langkah' => $d->langkah,
                    'frekuensi' => $d->frekuensi,
                    'waktu' => $d->waktu ,
                    'kaur' => $d->kaur,
                    'staff_admin'=> $d->staff_admin,
                    'pekarya'=>$d->pekarya,
                    'jumlah'=> ($d->kaur+ $d->staff_admin+$d->pekarya)

                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
               
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }


    public function listform5_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
               

                $this->db->select('abk_faktor_kelonggaran.*,dm_term.nama as fnama,
                uk_master.nama as kategsdm,b.nama as faktor,c.nama as shift');
                
                
                if(($user_froup=='1') OR ($user_froup=='6')){
                    if(!empty($this->input->get('uk'))){
                        $this->db->where('abk_faktor_kelonggaran.id_uk',$this->input->get('uk'));
                    }
                    
                }else{
                    $this->db->where('abk_faktor_kelonggaran.id_uk',$user_froup );
                }

                if(!empty($this->input->get('thn'))){
                    $this->db->where('abk_faktor_kelonggaran.tahun',$this->input->get('thn'));
                }else{
                    $this->db->where('abk_faktor_kelonggaran.tahun',date('Y'));
                }

                $this->db->where('abk_faktor_kelonggaran.tampilkan','1' );
                $this->db->join('dm_term as c','abk_faktor_kelonggaran.id_shift = c.id','LEFT');
                $this->db->join('dm_term as b','abk_faktor_kelonggaran.id_faktor = b.id','LEFT');
                $this->db->join('dm_term','abk_faktor_kelonggaran.id_shift = dm_term.id','LEFT');
                $this->db->join('uk_master',' uk_master.id = abk_faktor_kelonggaran.kategori_sdm','LEFT');
                $res = $this->db->get('abk_faktor_kelonggaran')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){
                    $arr['result'][]=array(
                        'no'=> ++$i,
                    'id'=>$d->id,
                    'kegiatan'=>$d->kegiatan, 
                    'frekuensi' => $d->frekuensi,
                    'waktu' => $d->waktu ,
                    'jumlah' => $d->jumlah,
                    'tahun'=> $d->tahun,
                    'faktor' =>$d->fnama,
                    'kategsdm'=>$d->kategsdm,
                    'faktor'=>$d->faktor,
                    'shift'=>$d->shift

                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
               
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listform1_get(){
        $this->load->model('System_auth_model','m');
        $headers = $this->input->request_headers(); 
      

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
               
                if( ($user_froup =='1') OR  ($user_froup=='6')){
                   // $this->db->where('sys_user.id_grup',$this->input->get('uk'));
                   $idgroups = $this->m->getdatachild($this->input->get('uk'));
                }else{
                    $idgroups = $this->m->getdatachild($user_froup);
                   
                  //  $this->db->where('sys_user.id_grup',$user_froup);
                }

               
                 $this->db->select('count(*) as jml,sys_grup_user.grup as namgroup,uk_master.nama as grup,sys_user_profile.pendidikan_akhir as nama');
                 if( ($user_froup =='1') OR  ($user_froup=='6')){
                   // $this->db->where('sys_user.id_grup',$this->input->get('uk'));
                }else{
                   
                    //$this->db->where('sys_user.id_grup',$user_froup);
                }
                
                //$this->db->where('tahun',$this->input->get('year'));
                //$this->db->where('abk_kebutuhan_sdm.tampilkan','1' );

			       
                    $this->db->join('sys_grup_user','sys_grup_user.id_grup = sys_user.id_grup','LEFT');
                    $this->db->where_in('sys_user.id_grup',$idgroups);
                    $this->db->join('sys_user_profile','sys_user.id_user = sys_user_profile.id_user','LEFT');
                    $this->db->join('uk_master','sys_user.id_uk = uk_master.id');
                    
                    $this->db->group_by('sys_grup_user.grup,uk_master.nama,sys_user_profile.pendidikan_akhir');
				    $res = $this->db->get('sys_user')->result();
				  
			if(!empty($res)){
                $i=0;
                $totalsemua=0;
				 foreach($res as $val){
                    
                    $datanama[$val->nama]=$val->nama;
                    $datauk[$val->namgroup][$val->grup]=$val->namgroup;
                    $jmlbypendidikan[$val->namgroup][$val->grup][$val->nama]=$val->jml;
                  }

                  $numero =1;  
                  $nom=0;
                  $i=0;
                  foreach($datauk as $datsun=>$hatsun){
                     
                   
                foreach($hatsun as $dat3=>$valsun){
                    ++$i;
                    $arr['result'][$nom]['no']=$i;
                    $arr['result'][$nom]['unit_kerja']=$datsun;
                    $arr['result'][$nom]['kategori_sdm']=$dat3;
                    $total=0;
                    
                              foreach($datanama as $g=>$val3){
                                   
                                  if(!empty($jmlbypendidikan[$datsun][$dat3][$g])){
                                    $jmlh = $jmlbypendidikan[$datsun][$dat3][$g];
                                    
                                  }else{
                                   $jmlh  = 0;
                                  }

                                  $total+=$jmlh;

                                  if($g=='54'){
                                      //slta
                                    $arr['result'][$nom]['slta']=$jmlh;
                                //$arr['result'][$nom]+$arrb['result'][$nom];
                                  }
                                  if($g=='55'){
                                      //d3
                                      $arr['result'][$nom]['d3']=$jmlh;
                                  }
                                  if($g=='56'){
                                    //d3
                                    $arr['result'][$nom]['s1']=$jmlh;
                                }
                                if($g=='57'){
                                    //d3
                                    $arr['result'][$nom]['s2']=$jmlh;
                                }
                                $arr['result'][$nom]['total']=$total;
                                $totalsemua+=$jmlh;
                                
                              }
                             
                              ++$nom;
                            }
           
                  
                 
                  }
 

                  $arr['result'][$nom]['no']='';
                  $arr['result'][$nom]['unit_kerja']='TOTAL';
                  $arr['result'][$nom]['kategori_sdm']='';
                  $arr['result'][$nom]['slta']='';
                  $arr['result'][$nom]['d3']='';
                  $arr['result'][$nom]['s1']='';
                  $arr['result'][$nom]['s2']='';
                  $arr['result'][$nom]['total']=$totalsemua;
					$arr['hasil']='success'; 
				 

			}else{
                $arr['result']=array();
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function hapusfrm1_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
             
                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_kebutuhan_sdm',array('tampilkan'=>'0'));
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

    public function hapusfrm3_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
             
                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_beban_kerja',array('tampilkan'=>'0'));
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

    public function hapusfrm4_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
             
                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_langkah_kerja',array('tampilkan'=>'0'));
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

    public function hapusfrm5_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
             
                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_faktor_kelonggaran',array('tampilkan'=>'0'));
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



    function addnewfrm1_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = array(

                    'tahun'=>$this->input->post('thnadd'),
                    'kategori_sdm'=>$this->input->post('katsdm'),
                    'slta'=>$this->input->post('slta'),
                    'd3'=>$this->input->post('d3'),
                    's1'=>$this->input->post('s1'),
                    's2'=>$this->input->post('s2'),
                    'date_created' => date('Y-m-d H:i:s'),
                    'author' => $decodedToken->data->_pnc_id_grup
                );

                if(($decodedToken->data->_pnc_id_grup == '1') OR ($decodedToken->data->_pnc_id_grup == '1') ){
                    $arrtam['id_uk']= $this->input->post('adduk');
                    $array = $array+$arrtam;
                }else{
                    $arrtam['id_uk']= $decodedToken->data->_pnc_id_grup;
                    $array = $array+$arrtam;
                }
                 
                $this->db->insert('abk_kebutuhan_sdm',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function addnewfrm3_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                $kp = $this->input->post('kegiatan_pokoks');
                 if(!empty($this->input->post('kegiatan_pokok_baru'))){
                    $kp = $this->input->post('kegiatan_pokok_baru');
                 }
                $array = array(

                    'tahun'=>$this->input->post('thnadd'),
                    'kegiatan_pokok'=>$kp,
                    'uraian_tugas'=>$this->input->post('uraian_tugas'),
                    'produk_dihasilkan'=>$this->input->post('produk_dihasilkan'),
                    'jumlah'=>$this->input->post('jumlah'), 
                    'author' => $decodedToken->data->_pnc_id_grup
                );

                if(($decodedToken->data->_pnc_id_grup == '1') OR ($decodedToken->data->_pnc_id_grup == '1') ){
                    $arrtam['id_uk']= $this->input->post('adduk');
                    $array = $array+$arrtam;
                }else{
                    $arrtam['id_uk']= $decodedToken->data->_pnc_id_grup;
                    $array = $array+$arrtam;
                }
                 
                $this->db->insert('abk_beban_kerja',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function addnewfrm4_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                
                $array = array(

                    'tahun'=>$this->input->post('thnadd'),
                    'kategori_sdm'=>$this->input->post('katsdmfrm4'),
                    'kegiatan'=>$this->input->post('kegiatanfrm4'),
                    'frekuensi'=>$this->input->post('frekuensifrm4'),
                    'waktu'=>$this->input->post('jumlah'), 
                    'id_shift'=>$this->input->post('shiftfrm4'), 
                    'id_faktor'=>$this->input->post('faktorfrm4'), 
                    'author' => $decodedToken->data->_pnc_id_grup
                );

                if(($decodedToken->data->_pnc_id_grup == '1') OR ($decodedToken->data->_pnc_id_grup == '1') ){
                    $arrtam['id_uk']= $this->input->post('adduk');
                    $array = $array+$arrtam;
                }else{
                    $arrtam['id_uk']= $decodedToken->data->_pnc_id_grup;
                    $array = $array+$arrtam;
                }
                 
                $this->db->insert('abk_faktor_kelonggaran',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    
    
    public function editfrm1_post(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                $ada=0;
                foreach($_POST as $dat){
					 
					$array = array( 
						'slta'=>$dat['slta'],
						'd3'=>$dat['d3'],
						's1'=>$dat['s1'],
						's2'=>$dat['s2'] 
                    );
                    
                    $this->db->where('id',$dat['id']);
                    $this->db->update('abk_kebutuhan_sdm',$array);
                    $ada=1;
                }
                
                  if($ada == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function editfrm2_post(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                $ada=0;
                foreach($_POST as $dat){
					 
					$array = array(
						'faktor'=>$dat['faktor'],
						'waktu_kerja'=>$dat['waktu_kerja'],
						'keterangan'=>$dat['keterangan']
                    );
                    
                    $this->db->where('id',$dat['id']);
                    $this->db->update('abk_shift_pegawai',$array);
                    $ada=1;
                }
                
                  if($ada == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function editfrm3_post(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                $ada=0;
                foreach($_POST as $dat){
					 
					$array = array(
						'kegiatan_pokok'=>$dat['kegiatan_pokok'],
						'uraian_tugas'=>$dat['uraian_tugas'],
						'produk_dihasilkan'=>$dat['produk_dihasilkan'],
						'jumlah'=>$dat['jumlah'] 
                    );
                    
                    $this->db->where('id',$dat['id']);
                    $this->db->update('abk_beban_kerja',$array);
                    $ada=1;
                }
                
                  if($ada == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function editfrm5_post(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                $ada=0;
                foreach($_POST as $dat){
                    
					 
					$array = array(  
                        'kegiatan'=>$dat['kegiatan'],
                        'frekuensi'=>$dat['frekuensi'],
                        'waktu'=>$dat['waktu'] 
                    );
                    
                    $this->db->where('id',$dat['id']);
                    $this->db->update('abk_faktor_kelonggaran',$array);
                    $ada=1;
                }
                
                  if($ada == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function editfrm4_post(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
            
            if ($decodedToken != false) {
                $ada=0;
                foreach($_POST as $dat){
					 
					

                    if(empty($dat['id'])){ 
                        $array = array(
                            'id_beban_kerja'=>$dat['kode'],
                            'langkah'=>$dat['langkah'],
                            'frekuensi'=>$dat['frekuensi'],
                            'waktu'=>$dat['waktu'],
                            'kaur'=>$dat['kaur'],
                            'staff_admin'=>$dat['staff_admin'],
                            'pekarya'=>$dat['pekarya'] 
                        );
                        $this->db->insert('abk_langkah_kerja',$array);
                    }else{
                        $array = array( 
                            'langkah'=>$dat['langkah'],
                            'frekuensi'=>$dat['frekuensi'],
                            'waktu'=>$dat['waktu'],
                            'kaur'=>$dat['kaur'],
                            'staff_admin'=>$dat['staff_admin'],
                            'pekarya'=>$dat['pekarya'] 
                        );
                        $this->db->where('id',$dat['id']);
                        $this->db->update('abk_langkah_kerja',$array);
                    }
                    
                    
                    $ada=1;
                }
                
                  if($ada == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function uraiankerja_get(){
		$headers = $this->input->request_headers();
	
			if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
				$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
				if ($decodedToken != false) {
					$user_froup = $decodedToken->data->_pnc_id_grup;
                    

					 $this->db->select('uraian_tugas as nama');
					 $this->db->order_by('uraian_tugas','ASC');
					 $this->db->where('tampilkan','1');
					 if( ($user_froup =='1') OR  ($user_froup=='6')){
						 if(!empty($this->input->get('uk'))){
							$this->db->where('id_uk',$this->input->get('uk'));
						 }
					 	
					}else{
						$this->db->where('id_uk',$user_froup);
                    }

                    if(empty($this->input->get('tahun'))){
                        $this->db->where('tahun',date('Y'));
                    }else{
                        $this->db->where('tahun',$this->input->get('tahun'));
                    }


                    $this->db->where('kegiatan_pokok',$this->input->get('kp'));
					 $this->db->group_by('uraian_tugas');
			  $res = $this->db->get('abk_beban_kerja')->result();
			  foreach($res as $d){
				$arr['result'][]=array('label'=>$d->nama,'value'=>$d->nama);
			  }
			  
			  $this->set_response($arr, REST_Controller::HTTP_OK);
				
					return;
				}
			}
			
			 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    public function hasilproduk_get(){
		$headers = $this->input->request_headers();
	
			if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
				$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
				if ($decodedToken != false) {
					$user_froup = $decodedToken->data->_pnc_id_grup;

					 $this->db->select('produk_dihasilkan as nama');
					 $this->db->order_by('produk_dihasilkan','ASC');
					 $this->db->where('tampilkan','1');
					 if( ($user_froup =='1') OR  ($user_froup=='6')){
						 if(!empty($this->input->get('uk'))){
							$this->db->where('id_uk',$this->input->get('uk'));
						 }
					 	
					}else{
						$this->db->where('id_uk',$user_froup);
                    }
                    if(empty($this->input->get('tahun'))){
                        $this->db->where('tahun',date('Y'));
                    }else{
                        $this->db->where('tahun',$this->input->get('tahun'));
                    }
                    $this->db->where('uraian_tugas',$this->input->get('kp'));
					 $this->db->group_by('produk_dihasilkan');
			  $res = $this->db->get('abk_beban_kerja')->result();
			  foreach($res as $d){
				$arr['result'][]=array('label'=>$d->nama,'value'=>$d->nama);
			  }
			  
			  $this->set_response($arr, REST_Controller::HTTP_OK);
				
					return;
				}
			}
			
			 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }
    
    function addnewpengajuan_post(){
        $headers = $this->input->request_headers();
        $this->load->model('System_auth_model');
     
	
			if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
				$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
				if ($decodedToken != false) {
					$user_froup = $decodedToken->data->_pnc_id_grup;
                    
					  
                    $adduk = $this->input->post('adduk');	 
                    $bahasa	 = $this->input->post('bahasa');
                    $batas_fisik	 = $this->input->post('batas_fisik');
                    $buta_warna = $this->input->post('buta_warna');
                    $f_kelamin	 = $this->input->post('f_kelamin');
                    $f_level_bahasa	 = $this->input->post('f_level_bahasa');
                    $f_level_kompi = $this->input->post('f_level_kompi');
                    $jurusan1	 = $this->input->post('jurusan1');
                    $jurusan2	 = $this->input->post('jurusan2');
                    $jurusan3	 = $this->input->post('jurusan3');
                    $kaca_mata	 = $this->input->post('kaca_mata');
                    $katsdmfrm4	  = $this->input->post('katsdmfrm4');
                    $kompetensi	 = $this->input->post('kompetensi');
                    $kompi	 = $this->input->post('kompi');
                    $lainlain	 = $this->input->post('lainlain');
                    $pendidikan	= $this->input->post('pendidikan');
                    $pengalaman	 = $this->input->post('pengalaman');
                    $syarat_khusus	 = $this->input->post('syarat_khusus');
                    $test_khusus	 = $this->input->post('test_khusus');
                    $thnadd	 = $this->input->post('thnadd');
                    $txtboleh_fisik	= $this->input->post('txtboleh_fisik');
                    $txtusiamax	 = $this->input->post('txtusiamax');
                    $txtusiamin	 = $this->input->post('txtusiamin');
                    $tinggimin  = $this->input->post('tinggimin');
                    $tinggimax  = $this->input->post('tinggimax');
                    $txtboleh_fisik = $this->input->post('txtboleh_fisik');
            
                  
            
                    $array= array( 
                    'tahun'=> $thnadd, 
                    'komputer'=>$kompi ,
                    'komputer_level'=> $f_level_kompi,
                    'kategori_sdm'=> $katsdmfrm4,
                    'kelamin'=> $f_kelamin,
                    'max_usia'=> $txtusiamax,
                    'min_usia'=> $txtusiamin,
                    'pendidikan'=> $pendidikan,
                    'jurusan_1'=>  $jurusan1,
                    'jrusan_2'=> $jurusan2,
                    'jrusan_3'=> $jurusan3,
                    'bahasa'=> $bahasa,
                    'bahasa_level'=> $f_level_bahasa,
                    'pengalaman'=> $pengalaman,
                    'tinggi_b_min'=>  $tinggimin ,
                    'tinggi_b_max'=>  $tinggimax,
                    'berat_b_min'=> $this->input->post('berat_b_min'),
                    'berat_b_max'=> $this->input->post('berat_b_max'),
                    'buta_warna'=> $buta_warna,
                    'kacamata'=> $kaca_mata,
                    'fisik_lain'=> $batas_fisik,
                    'fisik_lain_detail'=>  $txtboleh_fisik,
                    'kompetensi'=> $kompetensi,
                    'syarat_khusus'=> $syarat_khusus,
                    'test_khusus'=>  $test_khusus,
                    'lain_lain'=> $lainlain,
                'status'=>'83');

                    if(($decodedToken->data->_pnc_id_grup == '1') OR ($decodedToken->data->_pnc_id_grup == '1') ){
                        $id_parent = $this->System_auth_model->getparent($this->input->post('adduk'),'27');
                        $arrtam['id_atasan']=$id_parent;
                        $arrtam['id_uk']= $this->input->post('adduk');
                        $array = $array+$arrtam;
                    }else{
                        $id_parent = $this->System_auth_model->getparent($decodedToken->data->_pnc_id_grup,'27');
                        $arrtam['id_atasan']=$id_parent;
                        $arrtam['id_uk']= $decodedToken->data->_pnc_id_grup;
                        $array = $array+$arrtam;
                    }
 
                    $this->db->insert('abk_pengajuan_tn', $array);
                    if($this->db->affected_rows() == '1'){
                        $arr['hasil']='success';
                        $arr['message']='Data berhasil Tersimpan!';
                     }else{
                        $arr['hasil']='error';
                        $arr['message']='Data Gagal disimpan!';
                     }
			  $this->set_response($arr, REST_Controller::HTTP_OK);
				
					return;
				}
			}
			
             $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
             

    }

    public function listtk_get(){
		$headers = $this->input->request_headers();
        $arr['hasil']='error';
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
              
			
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
                if(($user_froup=='1') OR ($user_froup=='6')){
                    if((!empty($this->input->get('id_uk'))) AND ($this->input->get('id_uk')<>'null')){
                        $this->db->where('id_uk',$this->input->get('id_uk'));
                    }
                    
                }else{
                    $this->db->where('id_uk',$user_froup);
                }
                
                if(empty($this->input->get('year'))){
                    $thn=date('Y');
                }else{
                    $thn = $this->input->get('year');
                }


                $this->db->select('abk_pengajuan_tn.*,a.nama as kompi
                ,b.nama as kompi_level
                ,c.nama as kelaminsaya
                ,d.nama as pendidikan
                ,e.nama as bahasa
                ,f.nama as bahasa_level,
                g.nama as pengalaman,
                h.nama as buta_warna, 
                i.nama as kacamata,
               j.nama as fisik_lain,
               uk_master.nama as sdm,
               sys_grup_user.grup as namauk,
               k.nama as statusnama');
                $this->db->where('tahun',$thn);
                $this->db->where('abk_pengajuan_tn.tampilkan','1');
                $this->db->join('dm_term as a','abk_pengajuan_tn.komputer = a.id','LEFT');
                $this->db->join('dm_term as b','abk_pengajuan_tn.komputer_level = b.id','LEFT');
                $this->db->join('dm_term as c','abk_pengajuan_tn.kelamin = c.id','LEFT');
                $this->db->join('dm_term as d','abk_pengajuan_tn.pendidikan = d.id','LEFT');
                $this->db->join('dm_term as e','abk_pengajuan_tn.bahasa = e.id','LEFT');
                $this->db->join('dm_term as f','abk_pengajuan_tn.bahasa_level = f.id','LEFT');
                $this->db->join('dm_term as g','abk_pengajuan_tn.pengalaman = g.id','LEFT');
                $this->db->join('dm_term as h','abk_pengajuan_tn.buta_warna = h.id','LEFT');
                $this->db->join('dm_term as i','abk_pengajuan_tn.kacamata = i.id','LEFT');
                $this->db->join('dm_term as j','abk_pengajuan_tn.fisik_lain = j.id','LEFT');
                $this->db->join('dm_term as k','abk_pengajuan_tn.status = k.id','LEFT');
                $this->db->join('uk_master','abk_pengajuan_tn.kategori_sdm = uk_master.id','LEFT');
                $this->db->join('sys_grup_user','abk_pengajuan_tn.id_uk = sys_grup_user.id_grup','LEFT');
                
                
                
                $res = $this->db->get('abk_pengajuan_tn')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){

                    


                    $arr['result'][]=array(
                    'no'=> ++$i,
                    'id'=>$d->id,
                    'kompi'=>$d->kompi,
                    'kompi_lvl'  =>$d->kompi_level,
                    'tahun'  =>$d->tahun,
                    'kelamin'  =>$d->kelaminsaya,
                    'max_usia' => $d->max_usia,
                    'min_usia' => $d->min_usia,
                    'pendidikan' => $d->pendidikan,
                    'jurusan_1'=>$d->jurusan_1, 
                    'jrusan_2'  =>$d->jrusan_2,
                    'jrusan_3'  =>$d->jrusan_3,
                    'bahasa' => $d->bahasa,
                    'bahasa_level' => $d->bahasa_level,
                    'tinggi_b_min' => $d->tinggi_b_min,
                    'tinggi_b_max'=>$d->tinggi_b_max,
                    'berat_b_min' => $d->berat_b_min,
                    'berat_b_max'=>$d->berat_b_max,
                    'buta_warna'  =>$d->buta_warna,
                    'kacamata'  =>$d->kacamata,
                    'fisik_lain' => $d->fisik_lain,
                    'fisik_lain_detail' => $d->fisik_lain_detail,
                    'kompetensi' => $d->kompetensi,
                    'syarat_khusus'=>$d->syarat_khusus,
                    'test_khusus' => $d->test_khusus,
                    'lain_lain'  => $d->lain_lain,
                    'kategori_sdm' => $d->sdm,
                    'id_uk'=>$d->namauk,
                    'pengalaman' => $d->pengalaman,
                    'id_unit' => $d->id_uk,
                    'status' => $d->statusnama




                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
               
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    public function listtkdireksi_get(){
		$headers = $this->input->request_headers();
        $arr['hasil']='error';
        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
              
            //
			
            if ($decodedToken != false) {
            
                $user_froup = $decodedToken->data->_pnc_id_grup;
                
                if(($user_froup=='1') OR ($user_froup=='6')){
                    if((!empty($this->input->get('id_uk'))) AND ($this->input->get('id_uk')<>'null')){
                        $this->db->where('id_uk',$this->input->get('id_uk'));
                    }
                    
                }else{
                    if(empty($this->input->get('view'))){
                        $this->db->where('id_atasan',$user_froup);
                    }
                    
                }
                
                if(empty($this->input->get('year'))){
                    $thn=date('Y');
                }else{
                    $thn = $this->input->get('year');
                }

                if(!empty($this->input->get('status'))){
                    $this->db->where('abk_pengajuan_tn.status',$this->input->get('status'));
                }


                $this->db->select('abk_pengajuan_tn.*,a.nama as kompi
                ,b.nama as kompi_level
                ,c.nama as kelaminsaya
                ,d.nama as pendidikan
                ,e.nama as bahasa
                ,f.nama as bahasa_level,
                g.nama as pengalaman,
                h.nama as buta_warna, 
                i.nama as kacamata,
               j.nama as fisik_lain,
               uk_master.nama as sdm,
               sys_grup_user.grup as namauk,
               k.nama as namas');
                $this->db->where('tahun',$thn);
                $this->db->where('abk_pengajuan_tn.tampilkan','1'); 
                $this->db->join('dm_term as a','abk_pengajuan_tn.komputer = a.id','LEFT');
                $this->db->join('dm_term as b','abk_pengajuan_tn.komputer_level = b.id','LEFT');
                $this->db->join('dm_term as c','abk_pengajuan_tn.kelamin = c.id','LEFT');
                $this->db->join('dm_term as d','abk_pengajuan_tn.pendidikan = d.id','LEFT');
                $this->db->join('dm_term as e','abk_pengajuan_tn.bahasa = e.id','LEFT');
                $this->db->join('dm_term as f','abk_pengajuan_tn.bahasa_level = f.id','LEFT');
                $this->db->join('dm_term as g','abk_pengajuan_tn.pengalaman = g.id','LEFT');
                $this->db->join('dm_term as h','abk_pengajuan_tn.buta_warna = h.id','LEFT');
                $this->db->join('dm_term as i','abk_pengajuan_tn.kacamata = i.id','LEFT');
                $this->db->join('dm_term as j','abk_pengajuan_tn.fisik_lain = j.id','LEFT');
                $this->db->join('dm_term as k','abk_pengajuan_tn.status = k.id','LEFT');
                $this->db->join('uk_master','abk_pengajuan_tn.kategori_sdm = uk_master.id','LEFT');
                $this->db->join('sys_grup_user','abk_pengajuan_tn.id_uk = sys_grup_user.id_grup','LEFT');
                
                
                
                $res = $this->db->get('abk_pengajuan_tn')->result();
				  
			if(!empty($res)){
                $i=0;
				 foreach($res as $d){
                     
                    $arr['result'][]=array(
                    'no'=> ++$i,
                    'id'=>$d->id,
                    'kompi'=>$d->kompi,
                    'kompi_lvl'  =>$d->kompi_level,
                    'tahun'  =>$d->tahun,
                    'kelamin'  =>$d->kelaminsaya,
                    'max_usia' => $d->max_usia,
                    'min_usia' => $d->min_usia,
                    'pendidikan' => $d->pendidikan,
                    'jurusan_1'=>$d->jurusan_1, 
                    'jrusan_2'  =>$d->jrusan_2,
                    'jrusan_3'  =>$d->jrusan_3,
                    'bahasa' => $d->bahasa,
                    'bahasa_level' => $d->bahasa_level,
                    'tinggi_b_min' => $d->tinggi_b_min,
                    'tinggi_b_max'=>$d->tinggi_b_max,
                    'berat_b_min' => $d->berat_b_min,
                    'berat_b_max'=>$d->berat_b_max,
                    'buta_warna'  =>$d->buta_warna,
                    'kacamata'  =>$d->kacamata,
                    'fisik_lain' => $d->fisik_lain,
                    'fisik_lain_detail' => $d->fisik_lain_detail,
                    'kompetensi' => $d->kompetensi,
                    'syarat_khusus'=>$d->syarat_khusus,
                    'test_khusus' => $d->test_khusus,
                    'lain_lain'  => $d->lain_lain,
                    'kategori_sdm' => $d->sdm,
                    'id_uk'=>$d->namauk,
                    'pengalaman' => $d->pengalaman,
                    'id_unit' => $d->id_uk,
                    'status' => $d->namas




                );
                  }
                    
					$arr['hasil']='success'; 
				 

			}else{
               
			$arr['hasil']='error';
		  }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function gettk_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {

                $this->db->where('id',$this->input->get('id'));
                $arr['result'] = $this->db->get('abk_pengajuan_tn')->row();
                
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function edittk_post(){
        $headers = $this->input->request_headers();
	
			if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
				$decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
				if ($decodedToken != false) {
					$user_froup = $decodedToken->data->_pnc_id_grup;

					  
                    $adduk = $this->input->post('adduk');	 
                    $bahasa	 = $this->input->post('bahasa');
                    $batas_fisik	 = $this->input->post('batas_fisik');
                    $buta_warna = $this->input->post('buta_warna');
                    $f_kelamin	 = $this->input->post('f_kelamin');
                    $f_level_bahasa	 = $this->input->post('f_level_bahasa');
                    $f_level_kompi = $this->input->post('f_level_kompi');
                    $jurusan1	 = $this->input->post('jurusan1');
                    $jurusan2	 = $this->input->post('jurusan2');
                    $jurusan3	 = $this->input->post('jurusan3');
                    $kaca_mata	 = $this->input->post('kaca_mata');
                    $katsdmfrm4	  = $this->input->post('katsdmfrm4');
                    $kompetensi	 = $this->input->post('kompetensi');
                    $kompi	 = $this->input->post('kompi');
                    $lainlain	 = $this->input->post('lainlain');
                    $pendidikan	= $this->input->post('pendidikan');
                    $pengalaman	 = $this->input->post('pengalaman');
                    $syarat_khusus	 = $this->input->post('syarat_khusus');
                    $test_khusus	 = $this->input->post('test_khusus');
                    $thnadd	 = $this->input->post('thnadd');
                    $txtboleh_fisik	= $this->input->post('txtboleh_fisik');
                    $txtusiamax	 = $this->input->post('txtusiamax');
                    $txtusiamin	 = $this->input->post('txtusiamin');
                    $tinggimin  = $this->input->post('tinggimin');
                    $tinggimax  = $this->input->post('tinggimax');
                    $txtboleh_fisik = $this->input->post('txtboleh_fisik');
            
                  
            
                    $array= array( 
                    'tahun'=> $thnadd, 
                    'komputer'=>$kompi ,
                    'komputer_level'=> $f_level_kompi,
                    'kategori_sdm'=> $katsdmfrm4,
                    'kelamin'=> $f_kelamin,
                    'max_usia'=> $txtusiamax,
                    'min_usia'=> $txtusiamin,
                    'pendidikan'=> $pendidikan,
                    'jurusan_1'=>  $jurusan1,
                    'jrusan_2'=> $jurusan2,
                    'jrusan_3'=> $jurusan3,
                    'bahasa'=> $bahasa,
                    'bahasa_level'=> $f_level_bahasa,
                    'pengalaman'=> $pengalaman,
                    'tinggi_b_min'=>  $tinggimin ,
                    'tinggi_b_max'=>  $tinggimax,
                    'berat_b_min'=> $this->input->post('berat_b_min'),
                    'berat_b_max'=> $this->input->post('berat_b_max'),
                    'buta_warna'=> $buta_warna,
                    'kacamata'=> $kaca_mata,
                    'fisik_lain'=> $batas_fisik,
                    'fisik_lain_detail'=>  $txtboleh_fisik,
                    'kompetensi'=> $kompetensi,
                    'syarat_khusus'=> $syarat_khusus,
                    'test_khusus'=>  $test_khusus,
                    'lain_lain'=> $lainlain);

                    if(($decodedToken->data->_pnc_id_grup == '1') OR ($decodedToken->data->_pnc_id_grup == '1') ){
                        $arrtam['id_uk']= $this->input->post('adduk');
                        $array = $array+$arrtam;
                    }else{
                        $arrtam['id_uk']= $decodedToken->data->_pnc_id_grup;
                        $array = $array+$arrtam;
                    }
                    
                    $this->db->where('id',$this->input->post('id_tk'));
                    $this->db->update('abk_pengajuan_tn', $array);
                    if($this->db->affected_rows() == '1'){
                        $arr['hasil']='success';
                        $arr['message']='Data berhasil Tersimpan!';
                     }else{
                        $arr['hasil']='error';
                        $arr['message']='Data Gagal disimpan!';
                     }
			  $this->set_response($arr, REST_Controller::HTTP_OK);
				
					return;
				}
			}
			
             $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
             

    }

    public function deletetk_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
             
                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_pengajuan_tn',array('tampilkan'=>'0'));
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


    public function updatetk_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
              
                 $dd=array('status'=>$this->input->get('type'));
              


                $this->db->where('id',$this->input->get('id')); 
			  
                  $res = $this->db->update('abk_pengajuan_tn',$dd);
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


    public function countkelompokprofesi_get(){
		$headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
              
                $this->db->select(' count(*) as jml');
                $this->db->where('kategori_profesi',$this->input->get('id'));
                  $res = $this->db->get('sys_user_profile')->row();
                  
                  if(!empty($res)){
                    $arr['jumlah']=$res->jml;
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

    function simpandetaipengajuan_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array( 
                'id_uk_det'=> $this->input->post('id_uk_det'),
                'gaji'=> $this->input->post('gaji'),
                'id_kp'=> $this->input->post('id_kp'),
                'jml_saatini'=> $this->input->post('jml_saatini'),
                'kebutuhan_sesuai_abk'=> $this->input->post('kebutuhan_sesuai_abk'),
                'id_pengajuan'=> $this->input->post('idtk'),
                'jumlah'=> $this->input->post('jumlah'),
            );

              
                 
                $this->db->insert('abk_pengajuan_det',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function editdetaipengajuan_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array( 
                'id_uk_det'=> $this->input->post('id_uk_det'),
                'gaji'=> $this->input->post('gaji'),
                'id_kp'=> $this->input->post('id_kp'),
                'jml_saatini'=> $this->input->post('jml_saatini'),
                'kebutuhan_sesuai_abk'=> $this->input->post('kebutuhan_sesuai_abk'),
                'id_pengajuan'=> $this->input->post('idtk'),
                'jumlah'=> $this->input->post('jumlah'),
            );

              
                $this->db->where('id',$this->input->post('iddettk'));
                $this->db->update('abk_pengajuan_det',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getrowpengajuan_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
               
                $this->db->select('abk_pengajuan_det.*,a.grup,dm_term.nama');
                $this->db->where('id_pengajuan', $this->input->get('id'));
                $this->db->join('dm_term','dm_term.id=abk_pengajuan_det.id_kp','LEFT');
                $this->db->join('sys_grup_user as a','a.id_grup=abk_pengajuan_det.id_uk_det','LEFT');
               $dat = $this->db->get('abk_pengajuan_det')->row();

                if(!empty($dat)){
                    $arr['data']= array('id'=> $dat->id,
                    'id_uk_det'=> $dat->id_uk_det,
                    'gaji'=> $dat->gaji,
                    'id_kp'=> $dat->id_kp,
                    'jml_saatini'=> $dat->jml_saatini,
                    'kebutuhan_sesuai_abk'=> $dat->kebutuhan_sesuai_abk,
                    'id_pengajuan'=> $dat->id_pengajuan,
                    'jumlah'=> $dat->jumlah,
                    'namakp'=>$dat->nama,
                    'namauk'=>$dat->grup
                );
                }
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getrowpengajuandireksi_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
               
              
                 $this->db->where('id_pengajuan', $this->input->get('id'));
                $dat = $this->db->get('abk_pengajuan_det')->row();

                if(!empty($dat)){
                    $arr['data']= array('id'=> $dat->id,
                    'id_uk_det'=> $dat->id_uk_det,
                    'gaji'=> $dat->gaji,
                    'id_kp'=> $dat->id_kp,
                    'jml_saatini'=> $dat->jml_saatini,
                    'kebutuhan_sesuai_abk'=> $dat->kebutuhan_sesuai_abk,
                    'id_pengajuan'=> $dat->id_pengajuan,
                    'jumlah'=> $dat->jumlah,
                );
                }
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getrowpengajuandetail_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
               $this->db->select(' abk_pengajuan_alasan.*,a.nama as namasumber,
               b.nama as pns,c.nama as tetap');
                 $this->db->join('dm_term as a','a.id = abk_pengajuan_alasan.id_sumber','LEFT');
                 $this->db->join('m_status_pegawai as b','b.id = abk_pengajuan_alasan.status_pegawai_pns','LEFT');
                 $this->db->join('m_status_pegawai as c','c.id = abk_pengajuan_alasan.status_pegawai_tetap','LEFT');
                 $this->db->where('abk_pengajuan_alasan.id', $this->input->get('id'));

                $dat = $this->db->get('abk_pengajuan_alasan')->row();

                if(!empty($dat)){
                    $arr['data']= array('id'=> $dat->id,
                    'id_pengajuan'=> $dat->id_pengajuan,
                    'id_gantikaryawan'=> $dat->id_gantikaryawan,
                    'nama_karyawan'=> $dat->nama_karyawan,
                    'tgl_keluar'=> $dat->tgl_keluar,
                    'alasan_rekrut'=> $dat->alasan_rekrut,
                    'dampak_diharapkan'=> $dat->dampak_diharapkan,
                    'indikator'=> $dat->indikator,
                    'jangka_waktu_bln'=> $dat->jangka_waktu_bln,
                    'lain_lain'=> $dat->lain_lain,
                    'id_sumber'=> $dat->id_sumber,
                    'status_pegawai_pns'=> $dat->status_pegawai_pns,
                    'status_pegawai_tetap'=> $dat->status_pegawai_tetap,
                    'flag'=> $dat->flag,
                    'tetap' => $dat->tetap,
                    'pns'=>$dat->pns,
                    'sumber' => $dat->namasumber
                );
                }
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function simpandetaialasan_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array('id'=> $this->input->post('id_alasan'),
                    'id_pengajuan'=> $this->input->post('id_pengajuandetail'),
                    'id_gantikaryawan'=> $this->input->post('id_gantikaryawan'),
                    'nama_karyawan'=> $this->input->post('nama_karyawan'),
                    'tgl_keluar'=> $this->input->post('tgl_keluar'),
                    'alasan_rekrut'=> $this->input->post('alasan_rekrut'),
                    'dampak_diharapkan'=> $this->input->post('dampak_diharapkan'),
                    'indikator'=> $this->input->post('indikator'),
                    'jangka_waktu_bln'=> $this->input->post('jangka_waktu_bln'),
                    'lain_lain'=> $this->input->post('lain_lain'),
                    'id_sumber'=> $this->input->post('id_sumber'),
                    'status_pegawai_pns'=> $this->input->post('status_pegawai_pns'),
                    'status_pegawai_tetap'=> $this->input->post('status_pegawai_tetap'), 
            );

              
                 
                $this->db->insert('abk_pengajuan_alasan',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function editdetaialasan_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array(  
                    'id_gantikaryawan'=> $this->input->post('id_gantikaryawan'),
                    'nama_karyawan'=> $this->input->post('nama_karyawan'),
                    'tgl_keluar'=> $this->input->post('tgl_keluar'),
                    'alasan_rekrut'=> $this->input->post('alasan_rekrut'),
                    'dampak_diharapkan'=> $this->input->post('dampak_diharapkan'),
                    'indikator'=> $this->input->post('indikator'),
                    'jangka_waktu_bln'=> $this->input->post('jangka_waktu_bln'),
                    'lain_lain'=> $this->input->post('lain_lain'),
                    'id_sumber'=> $this->input->post('id_sumber'),
                    'status_pegawai_pns'=> $this->input->post('status_pegawai_pns'),
                    'status_pegawai_tetap'=> $this->input->post('status_pegawai_tetap'), 
            );

              
                 $this->db->where('id',$this->input->post('id_alasan'));
                $this->db->update('abk_pengajuan_alasan',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getposisi_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
               
              
                 $this->db->where('id_pengajuan_posisi', $this->input->get('id'));
                $dat = $this->db->get('abk_pengajuan_posisi')->row();

                if(!empty($dat)){
                    $arr['data']= array('id_posisi'=> $dat->id_posisi,
                    'id_pengajuan_posisi'=> $dat->id_pengajuan_posisi,
                    'dlm_struktur'=> $dat->dlm_struktur,
                    'urian'=> $dat->urian,
                );
                }
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function simpanposisi_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array( 
                'id_pengajuan_posisi'=> $this->input->post('id_pengajuan_posisi'),
                'dlm_struktur'=> $this->input->post('dlm_struktur'),
                'urian'=> $this->input->post('urian'),
            );
              
                 
                $this->db->insert('abk_pengajuan_posisi',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function editposisi_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = 
                array(  
                    'dlm_struktur'=> $this->input->post('dlm_struktur'),
                    'urian'=> $this->input->post('urian'),
                );

              
                 $this->db->where('id_posisi',$this->input->post('id_posisi'));
                $this->db->update('abk_pengajuan_posisi',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Data berhasil diupdate!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Data Gagal diupdate!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function chat_post(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = array(
                'id_pengajuan_comment'=> $this->input->post('idtk'),
                'tgl'=> date('Y-m-d H:i:s'),
                'isi'=> $this->input->post('isi'),
                'id_user'=> $decodedToken->data->id,
                'kategori' => $this->input->post('kategori_chat')
                );

                
                 
                $this->db->insert('abk_comment',$array);
                 
                if($this->db->affected_rows() == '1'){
					$arr['hasil']='success';
					$arr['message']='Pesan berhasil Terkirim!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Pesan Gagal kirim!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getchat_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
                $array = array(
                'id_pengajuan_comment'=> $this->input->post('idtk'),
                'tgl'=> date('Y-m-d H:i:s'),
                'isi'=> $this->input->post('isi'),
                'id_user'=> $decodedToken->data->id,
                );

                $this->db->select('sys_user.username,abk_comment.*');
                $this->db->join('sys_user','abk_comment.id_user = sys_user.id_user','LEFT');
                $this->db->where('abk_comment.id_pengajuan_comment',$this->input->get('id'));
                 $this->db->order_by('tgl','DESC');
                 $dat = $this->db->get('abk_comment')->row();

                if(!empty($dat)){
                   
                        $arr['result'][]=array('id'=> $dat->id,
                        'id_pengajuan_comment'=> $dat->id_pengajuan_comment,
                        'tgl'=> $dat->tgl,
                        'isi'=> $dat->isi,
                        'id_user'=> $dat->id_user,
                        'username'=> $dat->username
                    );
                    
                }
                 
                if($this->db->affected_rows() == '1'){
                     
					$arr['hasil']='success';
					$arr['message']='Pesan berhasil Terkirim!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Pesan Gagal kirim!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }

    function getchatall_get(){
        $headers = $this->input->request_headers();

        if (array_key_exists('Authorization', $headers) && !empty($headers['Authorization'])) {
            $decodedToken = AUTHORIZATION::validateToken($headers['Authorization']);
           
            if ($decodedToken != false) {
                 
              

                $this->db->select('abk_comment.*,sys_user.username');
               if(!empty($this->input->get('kategori'))){
                $this->db->where('abk_comment.kategori',$this->input->get('kategori'));
               }
                $this->db->where('abk_comment.id_pengajuan_comment',$this->input->get('id'));
                 $this->db->order_by('tgl','ASC');
                 $this->db->join('sys_user','abk_comment.id_user = sys_user.id_user','LEFT');
                 $res = $this->db->get('abk_comment')->result();
 
                if(!empty($res)){ 
                    foreach($res as $dat){ 
                        $arr['result'][]=array('id'=> $dat->id,
                        'id_pengajuan_comment'=> $dat->id_pengajuan_comment,
                        'tgl'=> $dat->tgl,
                        'isi'=> $dat->isi,
                        'id_user'=> $dat->id_user,
                        'username'=> $dat->username
                    );
                }
                    
                }
                 
                if($this->db->affected_rows() == '1'){
                     
					$arr['hasil']='success';
					$arr['message']='Pesan berhasil Terkirim!';
				 }else{
					$arr['hasil']='error';
					$arr['message']='Pesan Gagal kirim!';
				 }
		  $this->set_response($arr, REST_Controller::HTTP_OK);
			
                return;
			}
		}
		
		 $this->set_response("Unauthorised", REST_Controller::HTTP_UNAUTHORIZED);
    }



    

    
}