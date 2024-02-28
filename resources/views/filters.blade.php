@extends('layouts.app')

@section('title', 'Filtros')

@section('content')
<div class="container">
  <div style="display: flex; align-items: center; justify-content: center;">
    <div style="width: 50rem; display: flex; align-items: center;"> 
      <h4><a href="{{route('admin.listarUsuarios')}}" class="badge badge-pill badge-primary">← Volver</a></h4>
    </div>
  </div>
	<div class="row justify-content-center">
    <div class="card" style="width: 50rem;">
      <div class="card-header">{{ __('Lista de filtros') }} 
        @can('Crear Filtros')
          <button type="button" class="badge badge-pill badge-success float-right" data-toggle="modal" id="btn-trigger-modal-nuevo-filtro" data-target="#newFilterModal">+ Nuevo filtro</button>
        @endcan
        </div>
      <div class="card-body">
        @if(Session::has('info'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('info')}}
          </div>
        @endif
        @if(Session::has('error_rename'))
        <script>
            $(function() {
                $('#editFilterModal{{Session::get('id_error')}}').modal('show');
            });
          </script>
        @elseif(Session::has('error_create'))
          <script>
            $(function() {
                $('#newFilterModal').modal('show');
            });
          </script>
        @endif
        <table class="table table-bordered" id="tabla-filtros">
          @if($filtros->count() > 0)
          <thead>
            <tr>
              <th>Filtro</th>
              @canany(['Editar Filtros', 'Eliminar Filtros'])
              <th>*</th>
              @endcan
            </tr>
          </thead>
          <tbody>
            @foreach ($filtros as $filtro)
            <tr>
              <td>{{$filtro->name}}</td>
              @canany(['Editar Filtros', 'Eliminar Filtros'])
              <td>
                <div class="text-center">
                  @can('Editar Filtros')
                  <!-- Button trigger modal -->
                  <button type="button" class="btn-sm btn-primary text-center" data-toggle="modal" id="btn-trigger-modal-edit-filtro" data-target="#editFilterModal{{$filtro->id}}">
                    Renombrar
                  </button>
                  @endcan
                  <!-- Button eliminar filtro -->
                  <!-- <button type="button" href="#" class="btn-sm btn-danger">Eliminar</button> -->
                </div>

                <!-- Modal renombrar filtro -->
                <div class="modal fade" id="editFilterModal{{$filtro->id}}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Renombrar filtro: {{$filtro->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.renombrarFiltro', $filtro->id)}}" method="put" id="form-edit-filtro{{$filtro->id}}">
                        <div class="modal-body">
                          <label for="renameInput">Nuevo nombre de filtro</label>
                          <input type="text" class="form-control" id="renameInput" name="newName" aria-describedby="renombrarFiltro">
                          @if(Session::has('error_rename'))
                            <p style="color:red">{{Session::get('error_rename')}}</p>
                          @endif
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-filtro" value="Guardar Cambios" onclick="return confirmarCambios()">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
              @endcan
          @endforeach
        </tr>
      </tbody>
      @else
      <h2>No hay filtros cargados</h2>
      @endif
    </table>
	</div>
</div>

<!-- Modal crear filtro -->
<div class="modal fade" id="newFilterModal" tabindex="-1" role="dialog" aria-labelledby="newFilterModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="newFilterModalLabel">Nuevo filtro:</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{route('admin.crearFiltro')}}" method="put" id="form-create-filtro">
        <div class="modal-body">
          <label for="nameInput">Nombre del nuevo filtro</label>
          <input type="text" class="form-control" id="nameInput" name="newFilterName" aria-describedby="crearFiltro">
          @if(Session::has('error_create'))
            <p style="color:red">{{Session::get('error_create')}}</p>
          @endif
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-filtro" value="Confirmar" onclick="return confirmarCreacion()">
        </div>
        </form>
    </div>
  </div>
</div>

@endsection
@section('footer_scripts')
<script type="text/javascript">
  function confirmarCambios(){
    return confirm("¿Estás seguro de que deseas modificar el filtro?");
  };
  function confirmarCreacion(){
    return confirm("¿Estás seguro de que deseas crear el nuevo filtro \"" + document.getElementById('nameInput').value +"\" ?");
  };
</script>

<!-- datatables -->
<script>
  $('#tabla-permisos').DataTable({
    language: {
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
    }
  });
</script>
@endsection
