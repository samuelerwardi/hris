<form id="form1-upload" method="post" enctype="multipart/form-data">
    <div class="row mar-all"> 
        <div class="form-group">
            <label class="col-sm-2 control-label" for="inputstatus">Tahun</label>
            <div class="col-sm-5">
                <select class="form-control select2" id="thn" name="thn" style="width: 100%;">
                    <option value="">--TAHUN--</option>
                    <?php for($i=2010;$i<= date('Y');$i++){?>
                        <option value="<?php echo $i?>"><?php echo $i?></option>
                    <?php }?>
                </select> 
            </div>

        </div> 
    </div>
    <div class="row mar-all"> 
        <div class="form-group">
            <label class="col-sm-2 control-label" for="inputstatus">File Xlsx</label>
            <div class="col-sm-9">
                <input type="file" id="fileform1" name="fileform1" />
                <input type="text" id="author" name="author" style="display:none" />
            </div>

        </div>

    </div>
    <div class="row mar-all"> 
        <div class="form-group">
            <div class="alert alert-danger">
                <strong>Perhatian!</strong> Pastikan format excel yg akan anda upload sudah sesuai dengan format dasar, <strong><a href="xls/form1default.xlsx">Download contoh file disini </a></strong>
            </div>
        </div>

    </div>
</form>
<script>

    $('#author').val(localStorage.getItem('group'));
</script>