@extends('layouts.app')

@section ('content_main')
   <!-- Modal -->
   <div class="modal fade" id="empModal" role="dialog">
    <div class="modal-dialog">
 
     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Info de Archivo</h4>
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

  <div class="container">
    @if(Session::has('message'))
      <div class="alert alert-danger alert-dismissible" role="alert">
        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        {{Session::get('message')}}
      </div>
    @endif
   <h2>Listado de Archivos</h2>
   @can('Administrar Archivos')
   <h4><a href="{{route('limpiar_archivos')}}" class="badge badge-pill badge-danger"> Eliminar repetidos</a></h4>
   @endcan
   <br>
   <div class="row">
    <div class="form-group col-md-6">
     <h5>Codigo<span class="text-danger"></span></h5>
     <div class="controls">
	<input type="numeric" name="codigo" id="codigo" class="form-control " placeholder="Por favor introduzca un código">
        <div class="help-block"></div>
     </div>
    </div>
    <div class="text-left" style="
    margin-left: 15px;
    ">
    <button type="text" id="btnFiterSubmitSearch" class="btn btn-info">Submit</button>
    </div>
   </div>
   <div class="row">
   <div class="col-lg-12">
    <table class="table table-striped table-bordered dataTable table-hover order-column" id="laravel_datatable">
       <thead>
          <tr>
             <th>Id</th>
             <th>Nombre</th>
             <th>Id-Nombre</th>
             <th>Usuario</th>
             <th>Tipo</th>
             <th>Mime</th>
             <th>Checksum</th>
             <th>Tamaño</th>
             <th> * </th>
          </tr>
       </thead>
    </table>
   </div>
   </div>
 </div>
@endsection
@section('footer_scripts')
 <script>
 $(document).ready( function () {
     $.ajaxSetup({
          headers: {
              'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
      });
      var table =  $('#laravel_datatable').DataTable({
        "pageLength": 10,
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
          url: "{{ url('archivos') }}",
          type: 'GET',
          data: function (d) {
          d.codigo = $('#nombre').val();
          }
         },
         columns: [
                  { visible: false, data: 'id', name: 'id' },
                  { data: 'nombre_original', name: 'nombre_original' },
                  { visible: false, data: 'nombre', name: 'nombre' },
                  { visible: false, data: 'user_id', name: 'user_id' },
                  { data: 'tipo', name: 'tipo' },
                  { data: 'mime', name: 'mime' },
                  { data: 'checksum', name: 'checksum' },
                  { data: 'size', name: 'size' },
                  { data: 'action', name: 'action', ordenable: 'false'}
        ]
      });

   table.on( 'click', 'tr', function () {
        var data = table.row( this ).data();
// AJAX request
   $.ajax({
    url: "{{ url('archivo') }}"+"\\"+data.id,
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
   });

    table.on('click', '.btn_arch', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Ver Archivo: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo') }}"+"/"+data.id;
            $(location).attr('href',url);
           };
    });

// Función de botón Procesar.
    table.on('click', '.btn_arch_procesar', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Procesar Archivo: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo') }}"+"/"+data.id+"/procesar";
            $(location).attr('href',url);
           };
    });

// Función de botón Descarga.
    table.on('click', '.btn_descarga', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Descargando: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('archivo/') }}"+"/"+data.id+"/descargar";
            $(location).attr('href',url);
           };
    });
  
// Función de botón Borrar.
    table.on('click', '.btn_arch_delete', function () {
      var $ele = $(this).parent().parent();
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      if (typeof data !== 'undefined') {
      $.ajax({
         url: "{{ url('archivo') }}"+"\\"+data.id,
         type: "DELETE",
	 data: {id: data.id,
                _token:'{{ csrf_token() }}'},
         success: function(response){ 
	     // Add response in Modal body
	     if(response.statusCode==200){
	          row.fadeOut().remove();
	     }
	     if(response.statusCode==405){
	          alert("Error al intentar borrar");
	     }
      if(response.statusCode==500){
            alert("Error al intentar borrar. En el servidor");
        }
      alert("Se eliminó el registro del archivo");
	     row.fadeOut().remove();
	     $('.modal-body').html(response);

           }
      });
      };
    });

  // Función de botón Dejar de ver.
  table.on('click', '.btn_arch_detach', function () {
      var $ele = $(this).parent().parent();
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      if (typeof data !== 'undefined') {
      $.ajax({
         url: "{{ url('archivo') }}"+"\\"+data.id+"/detach",
         type: "PUT",
	 data: {id: data.id,
                _token:'{{ csrf_token() }}'},
         success: function(response){ 
	     // Add response in Modal body
	     if(response.statusCode==200){
	          row.fadeOut().remove();
	     }
	     if(response.statusCode==405){
	          alert("Error al intentar borrar");
	     }
      if(response.statusCode==500){
            alert("Error al intentar borrar. En el servidor");
        }
      alert("Ya no se visualizará el archivo");
	     row.fadeOut().remove();
	     $('.modal-body').html(response);

           }
      });
      };
    });
  
  $('#btnFiterSubmitSearch').click(function(){
     $('#laravel_datatable').DataTable().draw(true);
  });

} );

</script>
@endsection
