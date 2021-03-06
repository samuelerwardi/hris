<div class="row">
    <div class="tab-base mar-all">
        <!--Nav Tabs-->
        <ul class="nav nav-tabs">
            <li>
                <a href="#demo-lft-tab-1" data-toggle="tab">
                  <span class="block text-center">
                     <i class="fa fa-home fa-2x text-danger"></i> 
                 </span>
                 Dashboard
             </a>
         </li>

         <li class="active">
            <a href="#demo-lft-tab-mutasi" data-toggle="tab">
              <span class="block text-center">
                 <i class="fa fa-mail-forward fa-2x text-danger"></i> 
             </span>
             View Data Mutasi
         </a>
     </li>
     <li>
        <a href="#demo-lft-tab-3" data-toggle="tab">
          <span class="block text-center">
             <i class="fa fa-lightbulb-o fa-2x text-warning"></i> 
         </span>
         Help
     </a>
 </li>
</ul>

<div class="tab-content">
    <div class="tab-pane fade" id="demo-lft-tab-1"></div>

    <div class="tab-pane fade active in" id="demo-lft-tab-mutasi">
        <div class="col-sm-6 table-toolbar-left ">
        </div>
        <div class="dataTables_filter" id="demo-dt-addrow_filter">
            <label>Search:<input aria-controls="demo-dt-addrow" class="form-control input-sm" placeholder=""
               type="search" id="filter-text-box" oninput="onFilterTextBoxChanged()"></label>

           </div>

           <div class="ag-theme-balham" id="myGridMutasi" style="height: 400px;width:100%;">
           </div>
       </div>
       <div class="tab-pane fade" id="demo-lft-tab-3">

       </div>
   </div>
</div>


</div>

<div class="row pad-all">

    <div id="profilePage"></div>

</div>


<script charset="utf-8" type="text/javascript">
    $('.judul-menu').html('Mutasi Jabatan Pegawai');
    //<![CDATA[
    // specify the columns
    
    var columnDefsHis = [
    {headerName: "ID Mutasi", field: "id", width: 90, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Jenis Mutasi", field: "jm", width: 90, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Status", field: "status", width: 190, cellRenderer: CellRenderer},
    {headerName: "Nama", field: "nama", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Tanggal Usulan Mutasi", field: "tgl", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Keterangan", field: "keterangan", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Direktorat Tujuan", field: "dir_tujuan", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Bagian Tujuan", field: "bag_tujuan", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Sub Bagian Tujuan", field: "subbag_tujuan", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Direktorat Asal", field: "dir_asal", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Bagian Asal", field: "bag_asal", width: 190, filterParams: {newRowsAction: 'keep'}},
    {headerName: "Sub Bagian Asal", field: "subbag_asal", width: 190, filterParams: {newRowsAction: 'keep'}},

    ];

    var gridOptionsMutasi = {
        enableSorting: true,
        enableFilter: true,
        suppressRowClickSelection: false,
        groupSelectsChildren: true,
        debug: true,
        rowSelection: 'single',
        enableColResize: true,
        rowGroupPanelShow: 'always',
        pivotPanelShow: 'always',
        enableRangeSelection: true,
        columnDefs: columnDefsHis,
        pagination: false,
        paginationPageSize: 50,
        defaultColDef: {
            editable: false,
            enableRowGroup: true,
            enablePivot: true,
            enableValue: true
        }
    };

    // setup the grid after the page has finished loading
    var gridDiv = document.querySelector('#myGridMutasi');
    new agGrid.Grid(gridDiv, gridOptionsMutasi);

    function onFilterTextBoxChanged() {
        gridOptionsMutasi.api.setQuickFilter(document.getElementById('filter-text-box').value);
    }

    function loaddata() {
        $.ajax({
            url: BASE_URL + 'pegawai/listmutasiuk',
            headers: {
                'Authorization': localStorage.getItem("Token"),
                'X_CSRF_TOKEN': 'donimaulana',
                'Content-Type': 'application/json'
            },
            dataType: 'json',
            type: 'get',
            contentType: 'application/json',
            processData: false,
            success: function (data, textStatus, jQxhr) {


                gridOptionsMutasi.api.setRowData(data.result);
            },
            error: function (jqXhr, textStatus, errorThrown) {
                alert('error');
            }
        });
    }


    loaddata();

    function hasilstat() {
        loaddata();
    }

	function CellRenderer (params){
  var closeSpan = document.createElement("span");
  if(params.value ==='Ditolak'){
	closeSpan.setAttribute("class","badge badge-danger");
	closeSpan.textContent = "Ditolak";
  }else if(params.value ==='Disetujui'){
   closeSpan.setAttribute("class","badge badge-success");
   closeSpan.textContent = "Disetujui";
 }else if(params.value ==='Dikembalikan untuk dilengkapi'){
   closeSpan.setAttribute("class","badge badge-warning");
   closeSpan.textContent = "Dikembalikan untuk dilengkapi";
 }else if(params.value ==='Disetujui Direksi, menunggu Dirum'){
   closeSpan.setAttribute("class","badge badge-light");
   closeSpan.textContent = "Disetujui Direksi, menunggu Dirum";
 }else if(params.value ==='Disetujui Dirum,menunggu SDM'){
   closeSpan.setAttribute("class","badge badge-light");
   closeSpan.textContent = "Disetujui Dirum,menunggu SDM";
 }else if(params.value ==='Disetujui SDM, menunggu UK'){
   closeSpan.setAttribute("class","badge badge-light");
   closeSpan.textContent = "Disetujui SDM, menunggu UK";
 }else if(params.value ==='Disetujui Ka. Unit,menunggu Direksi'){
   closeSpan.setAttribute("class","badge badge-light");
   closeSpan.textContent = "Disetujui Ka. Unit,menunggu Direksi";
 }else if(params.value ==='Disetujui Direksi,Belum Diferivikasi HRD'){
   closeSpan.setAttribute("class","badge badge-light");
   closeSpan.textContent = "Disetujui Direksi,Belum Diferivikasi HRD";
 }else if(params.value ==='Pengajuan Baru'){
   closeSpan.setAttribute("class","badge badge-info");
   closeSpan.textContent = "Pengajuan Baru";
 }else if(params.value ==='Pengajuan Unit,menunggu Direksi'){
   closeSpan.setAttribute("class","badge badge-info");
   closeSpan.textContent = "Pengajuan Unit,menunggu Direksi";
 }else if(params.value ==='Disetujui, Kirim Ke HI'){
   closeSpan.setAttribute("class","badge badge-info");
   closeSpan.textContent = "Disetujui, Kirim Ke HI";
 }else if(params.value ==='Selesai'){
   closeSpan.setAttribute("class","badge badge-success");
   closeSpan.textContent = "Selesai";
 }
 return closeSpan;
}
</script>
<script src="js/login.js" type="text/javascript">
</script>

