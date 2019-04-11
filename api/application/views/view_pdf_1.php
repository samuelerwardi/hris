<!doctype html>
<html><head></head><body>
<style>
     @page { margin: 180px 50px; }
     #header { position: fixed; left: -10px; top: -150px; right: -10px; bottom: -150px; height: 0px; text-align: center; }
     #foote { position: fixed; left: 0px; bottom: -180px; right: 0px; height: 150px; }
     #foote { content: counter(upper-roman); }
</style>
 <div hidden="<?php echo $result["footer"]; ?>" id="header">
    <table width="100%" class="table-1" border="0">
	<tbody>
	<tr>
      <td colspan="1" width="60"><img src="<?php echo base_url(); ?>/logo2.png" width="80%"></td>
      <td colspan="2"align="center"><b>KEMENTRIAN KESEHATAN REPUBLIK INDONESIA<br>DIREKTORAT JENDRAL PELAYANAN KESEHATAN<br>RUMAH SAKIT JANTUNG DAN PEMBULU DARAH HARAPAN KITA</b></td>
	  <td colspan="1" width="70"><img src="<?php echo base_url(); ?>/logo.png" width="100%"></td>
	</tr>
	<tr>
	<td colspan="1" width="30">&nbsp;</td>
	<td colspan="2"align="center">Jalan Let. Jend. S. Parman Kv. 87 Slipi Jakarta, 11420<br>Telp. 5684085 - 093, 5684093, Faksimile: 5684230<br>Surat Elektronik: <u>website@pjnhk.go.id</u><br><u>http:www.pjnhk.go.id</u></td>
	<td colspan="1" width="30">&nbsp;</td>
	<hr/>
	</tr>
	</tbody>
	</table>
   </div>
   <div hidden="<?php echo $result["footer"]; ?>" id="foote">
     <p><h6><u>Tembusan : </u><br>- Atasan Ybs <br>- Para Direktur RSJPDHK</h6></p>
     <p><h6>Latbang_3 G:SrtTgs Intern <?php echo date("d")." ".$result["tanggal"]["tanggal_now"] ?> <?php echo $result["createdby"]; ?></h6></p>
   </div>
<div id="content">
<table border="0" class="table-1" style="margin:15px">
    <tr>
      <td colspan="3" align="center" style="font-size: 20px"><u><strong>S U R A T   -   T U G A S</strong></u></td>
    </tr>
    <tr>
      <td colspan="3" align="center" style="font-size: 15px">Nomor : KP.03.04/XX.4/       /<?php echo date('Y'); ?></td>
    </tr>
    <tr>
      <td colspan="3" align="center">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3">
	  <p align="justify">      Yang bertanda tangan di bawah ini memberikan tugas kepada :</p>
        <table width="100%" border="0" cellpadding="3">
           <tr>
      <td colspan="6">
        <table width="100%" border="1px solid" cellpadding="1" cellspacing="0" class="table2" style="margin-top: 5px">
			  <tr>
				<th align="center">No</th>
				<th align="center">Nopeg</th>
				<th align="center">Nama</th>
				<th align="center">NIP</th>
				<th align="center">Jabatan</th>
			  </tr>
			  <?php if (!empty($result["detail"])): ?>
				<?php foreach ($result["detail"] as $key => $value): ?>
				  <tr>
					<td align="center"><?php echo $key+1 ?></td>
					<td align="center"><?php echo $value["nopeg"] ?></td>
					<td width="30%"><?php echo $result["gelar_depan"].' '.$value["nama_pegawai"].', '.$result["gelar_belakang"] ?></td>
					<td><?php echo $value["nip"]; ?></td>
					<td width="40%"><?php echo $value["jabatan"]; ?></td>
				  </tr>
				<?php endforeach ?>
			  <?php endif ?>
			</table>
		  </td>
		</tr>
        </table>
      </td>
    </tr>
    <tr>
      <td colspan="3">
      <table width="100%" border="0" style="margin-top : 15px">
        <tr>
           <td width="9%" valign="top">
            <p>Untuk</p>
          </td>
          <td width="3%" valign="top">
            <p>:</p>
            <p></p>
          </td>
		  <td width="3%" valign="top">
            <p>1.</p>
          </td>
		  <td width="88%" valign="top">
            <p align="justify">Mengikuti <?php echo $result["nama_pelatihan"]; ?> sebagai <?php echo $result["pengembangan_pelatihan_kegiatan_status"]->nama; ?>, yang akan di laksanakan pada hari/tanggal <?php echo $result["tanggal"]["day_to"] . ", " . $result["tanggal"]["tanggal_to"]; ?>. Mulai pukul <?php echo $result["jam_mulai"] ?> s.d <?php echo $result["jam_sampai"] ?>. Yang diselenggarakan oleh <?php echo $result["institusi"]; ?>. Bertempat di <?php echo $result["tujuan"];  ?>.</p>
          </td>
        </tr>
		<tr>
		  <td colspan="2" valign="top">
          </td>
		  <td width="3%" valign="top">
            <p>2.</p>
          </td>
		  <td width="88%" valign="top">
            <p align="justify">Melaporkan PertanggungJawaban Biaya dan Kegiatan Perjalanan Dinas Ke Sub Bag Perbendaharaan Biaya dan Khusus Untuk Kegiatan Perjalanan Dinas ke Sub Pengembangan SDM melalui Emai: <b>subbag.pengembangan@pjnhk.go.id.</b> Paling lambat 5 (lima) hari kerja setelah Perjalanan Dinas dilaksanakan.</p>
          </td>
        </tr>
      </table>
	  <p>Agar yang bersangkutan melaksanakan tugas dengan baik dan penuh tanggung jawab.</p>
      </td>
    </tr>
    <tr>
      <td>Mengetahui</td>
      <td width="34%">&nbsp;</td>
      <td width="36%">         <?php echo $result["tanggal"]["tanggal_now"] ?></td>
    </tr>
	<tr>
      <td>Penyelenggara</td>
      <td width="34%">&nbsp;</td>
      <td width="36%"><?php if(!empty($result["phl"])){ echo "Plh. Direktur Utama,";}else{ echo "Direktur Utama,";}?></td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
      <td>(……………………)</td>
      <td>&nbsp;</td>
      <td><?php if(!empty($result["phl"])){ echo $result["aprove_phl"]->gelar_depan.' '.$result["aprove_phl"]->name.', '.$result["aprove_phl"]->gelar_belakang;}else{ echo "Dr. dr. Iwan Dakota,  SpJP(K), MARS,FACC,FESC";}?></td>
    </tr>
    <tr>
      <td><em>dimulai jam :</em></td>
      <td>&nbsp;</td>
      <td>NIP <?php if(!empty($result["phl"])){echo $result["aprove_phl"]->nip;}else{ echo "196601011996031001";}?></td>
    </tr>
    <tr>
      <td><em>Selesai  jam :</em></td>
      <td colspan="2">&nbsp;</td>
    </tr>
</table>
</div>
</body></html>