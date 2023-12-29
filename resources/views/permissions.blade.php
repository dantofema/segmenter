@extends('layouts.app')

@section('title', 'Permisos')

@section('content')
<div class="container">
	<div class="row justify-content-center">
    <div class="card" style="width: 50rem;">
      <div class="card-header">{{ __('Lista de permisos') }} 
        @can('Asignar Roles')
          <button type="button" class="badge badge-pill badge-success float-right" data-toggle="modal" id="btn-trigger-modal-nuevo-permiso" data-target="#newPermissionModal">+ Nuevo permiso</button></div>
        @endcan
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
                $('#editPermissionModal{{Session::get('id_error')}}').modal('show');
            });
          </script>
        @elseif(Session::has('error_create'))
          <script>
            $(function() {
                $('#newPermissionModal').modal('show');
            });
          </script>
        @endif
        <table class="table table-bordered" id="tabla-permisos">
          @if($permisos[0] !== null)
          <thead>
            <tr>
              <th>Permiso</th>
              @can('Testear Permisos')
              <th>*</th>
              @endcan
            </tr>
          </thead>
          <tbody>
            @foreach ($permisos as $permiso)
            <tr>
              <td>{{$permiso->name}}</td>
              @can('Testear Permisos')
              <td>
                <div class="text-center">
                  <!-- Button trigger modal -->
                  <button type="button" class="btn-sm btn-primary text-center" data-toggle="modal" id="btn-trigger-modal-edit-permiso" data-target="#editPermissionModal{{$permiso->id}}">
                    Renombrar
                  </button>
                  <!-- Button eliminar permiso -->
                  <!-- <button type="button" href="{{route('admin.listarPermisos')}}" class="btn-sm btn-danger">Eliminar</button> -->
                </div>

                <!-- Modal renombrar permiso -->
                <div class="modal fade" id="editPermissionModal{{$permiso->id}}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Renombrar permiso: {{$permiso->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.renombrarPermiso', $permiso->id)}}" method="put" id="form-edit-permiso{{$permiso->id}}">
                        <div class="modal-body">
                          <label for="renameInput">Nuevo nombre de permiso</label>
                          <input type="text" class="form-control" id="renameInput" name="newName" aria-describedby="renombrarPermiso">
                          @if(Session::has('error_rename'))
                            <p style="color:red">{{Session::get('error_rename')}}</p>
                          @endif
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-permiso" value="Guardar Cambios" onclick="return confirmarCambios()">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal crear permiso -->
                <div class="modal fade" id="newPermissionModal" tabindex="-1" role="dialog" aria-labelledby="newPermissionModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="newPermissionModalLabel">Nuevo permiso:</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.crearPermiso')}}" method="put" id="form-create-permiso">
                        <div class="modal-body">
                          <label for="nameInput">Nombre del nuevo permiso</label>
                          <input type="text" class="form-control" id="nameInput" name="newPermissionName" aria-describedby="crearPermiso">
                          @if(Session::has('error_create'))
                            <p style="color:red">{{Session::get('error_create')}}</p>
                          @endif
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                          <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-permiso" value="Confirmar" onclick="return confirmarCreacion()">
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
      <h1>No hay permisos cargados</h1>
      @endif
    </table>
	</div>
</div>

@endsection
@section('footer_scripts')
<script type="text/javascript">
  function confirmarCambios(){
    return confirm("¿Estás seguro de que deseas guardar modificar el permiso?");
  };
  function confirmarCreacion(){
    return confirm("¿Estás seguro de que deseas crear el nuevo permiso \"" + document.getElementById('nameInput').value +"\" ?");
  };
</script>

<!-- datatables -->
<script>src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"</script>
<script>src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"</script>
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
