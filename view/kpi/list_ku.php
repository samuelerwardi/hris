<?php session_start();?>
<div class="row"> 
  <div class="col-md-6"> 
    <div class="box box-primary"> 
      <div class="box-body">
       
        <div class="row pad-top"> 
         <div class="form-group">
          <label class="col-sm-2 control-label" for="inputstatus">Bulan</label>
          <div class="col-sm-3">
            <select class="form-control select2" id="bulanuk" name="bulanuk" style="width: 100%;">
              <option value="">Bulan</option>
              <?php for($i=1;$i<=12;$i++){?>
                <option value="<?php echo $i?>"><?php echo $i?></option>
              <?php }?>
            </select> 
          </div>
          
        </div> 
        <div class="form-group">
          <label class="col-sm-2 control-label" for="inputstatus">Tahun</label>
          <div class="col-sm-3">
            <select class="form-control select2" id="thnuk" name="thnuk" style="width: 100%;">
              <option value="">--TAHUN--</option>
              <?php for($i=date('Y');$i>=2010;$i--){?>
                <option value="<?php echo $i?>"><?php echo $i?></option>
              <?php }?>
            </select> 
          </div>
          
        </div> 
      </div>
      <?php if(($_SESSION['userdata']['group']=='1') OR ($_SESSION['userdata']['group']=='6') ){?>
        <div class="admininput">
          <div class="row pad-top"> 
            <div class="form-group">
              <label class="col-sm-2 control-label" for="inputstatus">Unit Kerja</label>
              <div class="col-sm-9">
                <select class="form-control select-chosen" id="txtdirektoratuk" name="txtdirektoratuk" style="width: 100%;"> 
                </select> 
              </div>
              
            </div>
          </div>
          
          
        </div>
        
      <?php }?>
      <div class="row "> 
        <div class="form-group">
          <label class="col-sm-2 control-label" for="inputstatus"></label>
          <div class="col-sm-5">
           
            <div class="row  text-left"> 
              <button class="btn btn-primary mar-all" onClick="searchtk();return false;">Search</button> 
            </div>
          </div>
        </div>
      </div>
      
    </div>
  </div>
</div>

</div>
<div class="pad-btm form-inline" style="border-top:1px solid #dedede;padding:10px">
  <div class="row">
    
    <div class="col-sm-12 table-toolbar-right">
     <button class="btn btn-default"  onCLick="downloadku();return false;"><i class="fa fa-file-excel-o"></i> Download Excel</button>
   </div>
 </div>
</div>                                
<div class="ag-theme-balham" id="gridUK" style="height: 300px;width:100%;">
</div>
<script>
  $('.judul-menu').html('Persetujuan KPI'); 

  var listUK = [
  {headerName: "No", field: "no", width: 60, filterParams:{newRowsAction: "keep"}},
  {headerName: "No.Pegawai", field: "nopeg", width: 90, filterParams:{newRowsAction: "keep"}},
  {headerName: "Nama Pegawai", field: "nama", width: 160, filterParams:{newRowsAction: "keep"}},
  {headerName: "Unit Kerja", field: "unit", width: 190, filterParams:{newRowsAction: "keep"}},
  {headerName: "Nilai IKU", field: "iku", width: 90, filterParams:{newRowsAction: "keep"}},
  {headerName: "Status", field: "status", width: 120, cellRenderer: CellRenderer},
  {headerName: "Keterangan", field: "ket", width: 190, filterParams:{newRowsAction: "keep"}},
  {headerName: "Bulan", field: "bulan", width: 90, filterParams:{newRowsAction: "keep"}},
  {headerName: "Tahun", field: "tahun", width: 90, filterParams:{newRowsAction: "keep"}},
  ]; 




  var autoGroupColumnDef = {
    headerName: 'Group',
    width: 200,
    field: 'nama_group',
    valueGetter: function(params) {
      if (params.node.group) {
        return params.node.key;
      } else {
        return params.data[params.colDef.field];
      }
    },
    headerCheckboxSelection: true,
// headerCheckboxSelectionFilteredOnly: true,
cellRenderer:'agGroupCellRenderer',
cellRendererParams: {
  checkbox: true
}
};

var gridTK = {
  enableSorting: true,
  enableFilter: true,
  suppressRowClickSelection: false, 
  groupSelectsChildren: true,
  debug: true,
  rowSelection: 'single', 
  enableColResize: true, 
  rowGroupPanelShow: 'always',
  onRowDoubleClicked: ukdetail,
  pivotPanelShow: 'always',
  enableRangeSelection: true,
  columnDefs: listUK,
  pagination: true,
  paginationPageSize: 50,   
  defaultColDef:{
    editable: false,
    enableRowGroup:true,
    enablePivot:true,
    enableValue:true
  } 
};



// setup the grid after the page has finished loading 
var gridDiv = document.querySelector('#gridUK');
new agGrid.Grid(gridDiv,gridTK);

function ukdetail(){
  var selectedRows = gridTK.api.getSelectedRows();
// alert('>>'+selectedRows+'<<<');
if(selectedRows == ''){
  onMessage('Silahkan Pilih Unit kerja Terlebih dahulu!');
  return false;
}else{
  var selectedRowsString = '';
  var level = '';
  selectedRows.forEach( function(selectedRow, index) {

    if (index!==0) {
      selectedRowsString += ', ';
    }
    selectedRowsString += selectedRow.id;
  });

  bootbox.dialog({ 
    message:$('<div></div>').load('view/kpi/listkpi.php?id=16&pid='+selectedRowsString),
    animateIn: 'bounceIn',
    animateOut : 'bounceOut',
    backdrop: false,
    size:'large',
    buttons: {


      main: {
        label: "Close",
        className: "btn-warning",
        callback: function() {

        }
      }
    }
  });

}
}

function detailaction(){
  var iddettk = $('#iddettk').val();	
}
function listFromuk(){
  var thn= $('#thnuk').val(); 
  var uk =  $('#txtdirektoratuk').val();
  var uri = BASE_URL+'kpi/mpenilaian/listiku_uk?tahun='+thn+'&status=16';
  if(empty(thn)){
    var d = new Date();
    var n = d.getFullYear();
    thn = n;
  }

  if(!empty(thn)){
    uri = BASE_URL+'kpi/mpenilaian/listiku_uk?tahun='+thn+'&id_uk='+uk+'&status=16';
  }

  $('#thnuk').val(thn);

  getJson(loadfrmuk,uri);
}

function loadfrmuk(result){
  if(!empty(result)){
    if(result.hasil ==='success'){
      gridTK.api.setRowData(result.result);
    }else{
      gridTK.api.setRowData([]);
    }
  }else{
    gridTK.api.setRowData([]);
  }
}

listFromuk();

function searchtk(){
  var thn=$('#thnuk').val();
  var bulan=$('#bulanuk').val();
  var uk=$('#txtdirektoratuk').val();
  var group = localStorage.getItem('group');
  var uri = BASE_URL+'kpi/mpenilaian/listiku_uk?bulan='+bulan+'&tahun='+thn+'&id_uk='+uk+'&status=16'; 
  if(empty(thn)){
    alert('Tahun harus dipilih');
    return false;
  }



  getJson(loadfrmuk,uri);
}
function downloadku(){
  var params = { 
    fileName: 'KPI Pimpinan',
    sheetName: 'KPI Pimpinan'
  };

  gridTK.api.exportDataAsExcel(params);
}
</script>
<?php if(($_SESSION['userdata']['group']=='1') OR ($_SESSION['userdata']['group']=='6') ){?>
  <script>
    $('.select-chosen').chosen();
    $('.chosen-container').css({"width": "100%"});
    getOptions("txtdirektoratuk",BASE_URL+"master/direktoratSub");
  </script>
  <?php } ?> 