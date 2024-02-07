@extends('layouts.app')

@section('title', 'Roles')

@section('content')
<div class="container">
	<div class="row justify-content-center">
    <div class="card" style="width: 50rem;">
      <div class="card-header">{{ __('Lista de roles') }} 
        @can('Crear Roles')
          <button type="button" class="badge badge-pill badge-success float-right" data-toggle="modal" id="btn-trigger-modal-nuevo-rol" data-target="#newRoleModal">+ Nuevo rol</button></div>
        @endcan
      <div class="card-body">
        @if(Session::has('info'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('info')}}
          </div>
        @endif
        @if(Session::has('error_rename') or Session::has('error_permissions_edit'))
        <script>
            $(function() {
                $('#editRoleModal{{Session::get('id_error')}}').modal('show');
            });
          </script>
        @elseif(Session::has('error_create') or Session::has('error_permissions_new'))
          <script>
            $(function() {
                $('#newRoleModal').modal('show');
            });
          </script>
        @endif
        <table class="table table-bordered" id="tabla-roles">
          @if($roles[0] !== null)
          <thead>
            <tr>
              <th>Rol</th>
              @canany(['Editar Roles', 'Eliminar Roles'])
              <th>*</th>
              @endcan
            </tr>
          </thead>
          <tbody>
            @foreach ($roles as $rol)
            <tr>
              <td>{{$rol->name}}</td>
              @canany(['Editar Roles', 'Eliminar Roles'])
              <td>
                <div class="text-center">
                  @can('Editar Roles')
                  <!-- Button trigger modal -->
                  <button type="button" class="btn-sm btn-primary text-center" data-toggle="modal" id="btn-trigger-modal-edit-role" data-target="#editRoleModal{{$rol->id}}">
                    Editar
                  </button>
                  @endcan
                  <!-- Button eliminar rol -->
                  <!-- <button type="button" href="#" class="btn-sm btn-danger">Eliminar</button> -->
                </div>

                <!-- Modal editar rol -->
                <div class="modal fade" id="editRoleModal{{$rol->id}}" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="editModalLabel">Editar rol: {{$rol->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      @if ($rol->name == 'Super Admin')
                        <div class="modal-body">
                          No puede editarse este rol 
                          <span class="badge badge-pill badge-danger">Super Admin</span>
                        </div>
                      @else
                        <form action="{{route('admin.renombrarRol', $rol->id)}}" method="put" id="form-edit-rol{{$rol->id}}">
                          <div class="modal-body">
                            <label for="renameInput">Nuevo nombre de rol</label>
                            <input type="text" class="form-control" id="renameInput" name="newName" aria-describedby="renombrarRol">
                            @if(Session::has('error_rename'))
                              <p style="color:red">{{Session::get('error_rename')}}</p>
                            @endif
                          <br>
                          <!-- Tabla de permisos -->
                          <table class="table" id="tabla-permisos">
                            <tbody>
                              @php 
                                $role_permissions = $rol->permissions;
                              @endphp
                              @foreach ($permisos as $permiso)
                              <tr>                                         
                                <td class="col align-self-center">
                                  @if ($role_permissions->contains($permiso))
                                    <input type="checkbox" class="toggle-checkbox" checked id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                  @else
                                    <input type="checkbox" class="toggle-checkbox" id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                  @endif
                                    <label class="form-check-label" for="{{$permiso->name}}">
                                      {{$permiso->name}}
                                    </label>
                                  </td>
                              </tr> 
                              @endforeach
                              </tbody>  
                            </table>
                            @if(Session::has('error_permissions_edit'))
                              <p style="color:red">{{Session::get('error_permissions_edit')}}</p>
                            @endif
                          </div>
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-permiso" value="Guardar Cambios" onclick="return confirmarCambios()">
                          </div>
                        </form>
                      @endif
                    </div>
                  </div>
                </div>

                <!-- Modal crear rol -->
                <div class="modal fade" id="newRoleModal" tabindex="-1" role="dialog" aria-labelledby="newRoleModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="newRoleModalLabel">Nuevo rol:</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.crearRol')}}" method="put" id="form-create-rol">
                        <div class="modal-body">
                          <label for="nameInput">Nombre del nuevo rol</label>
                          <input type="text" class="form-control" id="nameInput" name="newRoleName" aria-describedby="crearRol">
                          @if(Session::has('error_create'))
                            <p style="color:red">{{Session::get('error_create')}}</p>
                          @endif
                          <br>
                          <!-- Tabla de permisos -->
                          <label for="tabla-permisos">Permisos del rol</label>
                          <table class="table" id="tabla-permisos">
                              <tbody>
                                @foreach ($permisos as $permiso)
                                <tr>                                         
                                  <td class="col align-self-center">
                                      <input type="checkbox" class="toggle-checkbox" id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                      <label class="form-check-label" for="{{$permiso->name}}">
                                        {{$permiso->name}}
                                      </label>
                                    </td>
                                </tr> 
                                @endforeach
                              </tbody>
                            </table>
                            @if(Session::has('error_permissions_new'))
                              <p style="color:red">{{Session::get('error_permissions_new')}}</p>
                            @endif
                          <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                            <input type="submit" name="btn"  class="btn btn-primary btn-submit-edit-permiso" value="Confirmar" onclick="return confirmarCreacion()">
                          </div>
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
      <h1>No hay roles cargados</h1>
      @endif
    </table>
	</div>
</div>

@endsection
@section('footer_scripts')
<script type="text/javascript">
  function confirmarCambios(){
    return confirm("¿Estás seguro de que deseas modificar el rol?");
  };
  function confirmarCreacion(){
    return confirm("¿Estás seguro de que deseas crear el nuevo rol \"" + document.getElementById('nameInput').value +"\" ?");
  };
</script>

<!-- datatables -->
<script>src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"</script>
<script>src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"</script>
<script>
  $('#tabla-roles').DataTable({
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
