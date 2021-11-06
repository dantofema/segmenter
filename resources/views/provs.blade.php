@extends('layouts.app')

@section ('content_main')
   <!-- Modal -->
   <div class="modal fade" id="empModal" role="dialog">
    <div class="modal-dialog">
 
     <!-- Modal content-->
     <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Info de Provincia</h4>
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
   <h2>Listado de Provincias</h2>
   <br>
   <div class="row">
    <div class="form-group col-md-6">
     <h5>Codigo<span class="text-danger"></span></h5>
     <div class="controls">
	<input type="numeric" name="codigo" id="codigo" class="form-control " placeholder="Por favor introduzca un código">
        <div class="help-block"></div>
     </div>
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
   <div class="row">
   <div class="col-lg-12">
    <table class="table table-striped table-bordered dataTable hover order-column" id="laravel_datatable">
       <thead>
          <tr>
             <th>Id</th>
             <th>Código</th>
             <th>Nombre</th>
             <th>Cantidad de Comunas / Partidos / Departamentos</th>
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
        "pageLength": -1,
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
                  { visible: false, data: 'id', name: 'id' },
                  { data: 'codigo', name: 'codigo' },
                  { data: 'nombre', name: 'nombre' },
                  { searchable: false , data: 'departamentos_count', name: 'departamentos_count' },
                  { orderable: false, searchable: false , data: function ( row, type, val, meta ) {
                                var html =  '<button type="button" class="btn_departamentos btn-sm btn-primary" > Ver </button> ';
                                 html +=  '<button type="button" class="btn_prov btn-sm btn-primary" > Ver 2 </button>';
                              return html;
                            }
                }
        ]
      });

   table.on( 'click', 'tr', function () {
        var data = table.row( this ).data();
// AJAX request
   $.ajax({
    url: "{{ url('prov') }}"+"\\"+data.id,
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

// Función de botón Ver 2.
    table.on('click', '.btn_prov', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Ver Provincia: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('prov') }}"+"/"+data.id;
            $(location).attr('href',url);
           };
    });

// Función de botón Departamentos.
    table.on('click', '.btn_departamentos', function () {
      var row = $(this).closest('tr');
      var data = table.row( row ).data();
      console.log('Ver Departamentos de: '+data.codigo);
        if (typeof data !== 'undefined') {
            url= "{{ url('prov/deptos') }}"+"/"+data.id;
            $(location).attr('href',url);
           };
    });
  
  $('#btnFiterSubmitSearch').click(function(){
     $('#laravel_datatable').DataTable().draw(true);
  });

} );

</script>
@endsection
