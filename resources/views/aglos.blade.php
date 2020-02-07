<!DOCTYPE html>
 
<html lang="es">
<head>
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name', 'Laravel') }}</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet">  
<link  href="https://cdn.datatables.net/1.10.20/css/jquery.dataTables.min.css" rel="stylesheet">
<script src="//ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.js"></script>  
<!--script src="https://code.jquery.com/jquery-3.4.1.slim.min.js" integrity="sha384-J6qa4849blE2+poT4WnyKhv5vZF5SrPo0iEjwBvKU7imGFAV0wwj1yYfoRSJoZ+n" crossorigin="anonymous"></script -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js" integrity="sha384-wfSDF2E50Y2D1uUdj0O3uMBJnjuUD4Ih7YwaYd1iqfktj0Uod8GCExl3Og8ifwB6" crossorigin="anonymous"></script>
<!-- script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script -->
<script src="https://cdn.datatables.net/1.10.20/js/jquery.dataTables.min.js"></script>
<link  href="https://cdn.datatables.net/buttons/1.6.1/css/buttons.dataTables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>  

</head>
<body>
 <div class="container">
   <!-- Modal -->
   <div class="modal fade" id="empModal" role="dialog">
    <div class="modal-dialog">
 
     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Info de Aglomerado</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>
      <div class="modal-body">
 
      </div>
      <div class="modal-footer">
       <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
      </div>
     </div>
    </div>
   </div>

   <h2>Listado de Aglomerados</h2>
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
    <div class="text-left" style="margin-left: 15px;">
    <button type="text" id="btnFiterSubmitSearch" class="btn btn-info">Buscar</button>
    </div>
    </div>
    <br>
    <table class="table table-bordered  stripe hover order-column" id="laravel_datatable_aglos">
       <thead>
          <tr>
             <th>Id</th>
             <th>Codigo</th>
             <th>Nombre</th>
             <th>Cartografía</th>
             <th>Listado</th>
             <th></th>
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
      var table =  $('#laravel_datatable_aglos').DataTable({
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
          url: "{{ url('aglos-list') }}",
          type: 'POST',
          data: function (d) {
           d.codigo = $('#codigo').val();
          }
         },
         columns: [
                  { visible: false, data: 'id', name: 'id' },
                  { data: 'codigo', name: 'codigo' },
                  { data: 'nombre', name: 'nombre' },
                  { searchable: false , data: 'carto', name: 'carto' },
                  { searchable: false , data: 'listado', name: 'listado' },
                  { searchable: false , data: function ( row, type, val, meta ) {
                                if ((row.codigo == '0777') || (row.codigo == '0001')) {
                                    var botones =  '<input type="button" class="segmentar btn btn-primary" value="Segmentar"/>';
                                    botones = botones+ '<input type="button" disabled=true class="muestrear btn btn-primary" value="Muestrear"/>';
                                    return botones;
                                }else{return '';}
                            }},
               ],
      });
table.on('click', '.segmentar', function () {
  var row = $(this).closest('tr');
  
  var data = table.row( row ).data().codigo;
  console.log('Segmentar: '+data);
});
  
  
table.on('click', '.muestrear', function () {
  var row = $(this).closest('tr');
  
  var data = table.row( row ).data().codigo;
  console.log('Muestrear: '+data);
});
    table.on( 'click', 'tr', function (e) {
      if ((e.target.value != 'Segmentar') && (e.target.value != 'Muestrear')){

        var data = table.row( this ).data();
        if (typeof data !== 'undefined') {
        // AJAX request
           $.ajax({
            url: "{{ url('aglo') }}"+"\\"+data.id,
            type: 'post',
            data: {id: data.id,format: 'html'},
            success: function(response){ 
              // Add response in Modal body
              $('.modal-body').html(response);

              // Display Modal
              $('#empModal').modal('show'); 
            }
           });
           console.log( 'You clicked on '+data.id+'\'s row' );
        }else{
            console.log( 'You clicked on NO DATA\'s row' );
        }
      }
   });

  $('#btnFiterSubmitSearch').click(function(){
  $('#laravel_datatable_aglos').DataTable().draw(true);
  });

} );

</script>
</body>
</html>
