<!DOCTYPE html>
 
<html lang="es">
<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>Laravel DataTable With Custom Filter - Tuts Make</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">  
<link  href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>  
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
</head>
<body>
 <div class="container">
   <h2>Listado de Provincias</h2>
   <br>
   <div class="row">
    <div class="form-group col-md-6">
    <h5>Codigo<span class="text-danger"></span></h5>
    <div class="controls">
        <input type="numeric" name="codigo" id="codigo" class="form-control " placeholder="Por favor introduzca un código"> <div class="help-block"></div></div>
    </div>
    <!--div class="form-group col-md-6">
    <h5>End Date <span class="text-danger"></span></h5>
    <div class="controls">
        <input type="date" name="end_date" id="end_date" class="form-control datepicker-autoclose" placeholder="Please select end date"> <div class="help-block"></div></div>
    </div-->
    <div class="text-left" style="
    margin-left: 15px;
    ">
    <button type="text" id="btnFiterSubmitSearch" class="btn btn-info">Submit</button>
    </div>
    </div>
    <br>
    <table class="table table-bordered  stripe hover order-column" id="laravel_datatable">
       <thead>
          <tr>
             <th>Id</th>
             <th>Codigo</th>
             <th>Nombre</th>
             <th>Cant. Deptos</th>
          </tr>
       </thead>
    </table>
 </div>
 <script>
 $(document).ready( function () {
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
  $('#laravel_datatable').DataTable({
         language: //{url:'https://cdn.datatables.net/plug-ins/1.10.20/i18n/Spanish.json'},
{
	"sProcessing":     "Procesando...",
                "sLengthMenu":     "Mostrar _MENU_ registros",
                "sZeroRecords":    "No se encontraron resultados",
                "sEmptyTable":     "Ningún dato disponible en esta tabla =(",
                "sInfo":           "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "sInfoEmpty":      "Mostrando registros del 0 al 0 de un total de 0 registros",
                "sInfoFiltered":   "(filtrado de un total de _MAX_ registros)",
                "sInfoPostFix":    "",
                "sSearch":         "Buscar:",
                "sUrl":            "",
                "sInfoThousands":  ",",
                "sLoadingRecords": "Cargando...",
                "oPaginate": {
                    "sFirst":    "Primero",
                    "sLast":     "Último",
                    "sNext":     "Siguiente",
                    "sPrevious": "Anterior"
                },
                "oAria": {
                    "sSortAscending":  ": Activar para ordenar la columna de manera ascendente",
                    "sSortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad"
                }
},
         processing: true,
         serverSide: true,
         ajax: {
          url: "{{ url('provs-list') }}",
          type: 'GET',
          data: function (d) {
          d.codigo = $('#codigo').val();
          }
         },
         columns: [
                  { data: 'id', name: 'id' },
                  { data: 'codigo', name: 'codigo' },
                  { data: 'nombre', name: 'nombre' },
                  { searchable: false , data: 'departamentos_count', name: 'departamentos_count' }
               ]
      });
   });
 
  $('#btnFiterSubmitSearch').click(function(){
     $('#laravel_datatable').DataTable().draw(true);
  });
</script>
</body>
</html>
