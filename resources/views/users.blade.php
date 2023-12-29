@extends('layouts.app')

@section('title', 'Usuarios')

@section('content')
<div class="container">
	<div class="row justify-content-center">
    <div class="card" style="width: 50rem;">
      <div class="card-header">{{ __('Lista de usuarios') }}</div>
      <div class="card-body">
        @if(Session::has('info'))
          <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            {{Session::get('info')}}
          </div>
        @endif
        <table class="table table-bordered" id="tabla-usuarios">
          @if($usuarios[0] !== null)
          <thead>
            <tr>
              <th>Nombre</th>
              <th>Email</th>
              @can('Testear Permisos')
              <th> 
                Permisos <a href="{{route('admin.listarPermisos')}}" class="badge badge-pill badge-primary">+</a>
              </th>
              @endcan
              @can('Asignar Roles', 'Quitar Roles')
              <th>
                Roles
                <!-- <a href="#" class="badge badge-pill badge-primary">+</a> TODO? ->  crear roles nuevos -->
              </th>
              @endcan
            </tr>
          </thead>
          <tbody>
            @foreach ($usuarios as $usuario)
            <tr>
              <td>{{$usuario->name}}</td>
              <td>{{$usuario->email}}</td>
              @can('Testear Permisos')
              <td>
                <div class="text-center">
                  <!-- Button trigger modal -->
                  <button type="button" class="btn-sm btn-primary text-center" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#permisosModal{{$usuario->id}}">
                    Administrar Permisos
                  </button>
                </div>

                <!-- Modal permisos del usuario -->
                <div class="modal fade" id="permisosModal{{$usuario->id}}" tabindex="-1" role="dialog" aria-labelledby="permisoModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="permisoModalLabel">Permisos de {{$usuario->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.editarPermisoUsuario', $usuario->id)}}" method="put" id="form-permisos{{$usuario->id}}">
                        <div class="modal-body">
                          <table class="table" id="tabla-permisos">
                            <tbody>
                              @php 
                                $user_permissions = $usuario->getPermissionsViaRoles()->pluck('name');
                              @endphp
                              @foreach ($permisos as $permiso)
                              <tr>                                         
                                <td class="col align-self-center">
                                  @if ($user_permissions->contains($permiso->name) or $usuario->hasRole('Super Admin'))
                                    <input type="checkbox" class="toggle-checkbox" disabled checked id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                  @else
                                    @if ($usuario->hasPermissionTo($permiso->name, $permiso->guard_name ))
                                      <input type="checkbox" class="toggle-checkbox" checked id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                    @else
                                      <input type="checkbox" class="toggle-checkbox" id="{{$permiso->name}}" name="permisos[]" value="{{$permiso->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                    @endif
                                  @endif
                                    <label class="form-check-label" for="{{$permiso->name}}">
                                      {{$permiso->name}}
                                    </label>
                                    @if ($user_permissions->contains($permiso->name) or $usuario->hasRole('Super Admin'))
                                      <span class="badge badge-pill badge-danger">Heredado de rol</span>
                                    @endif
                                  </td>
                              </tr> 
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          <input type="submit" name="btn"  class="btn btn-primary btn-submit-permisos" value="Guardar Cambios" onclick="return confirmarCambios('permisos')">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </td>
              @endcan
              @can('Asignar Roles', 'Quitar Roles')
              <td>
                <div class="text-center">
                  <!-- Button trigger modal -->
                  <button type="button" class="btn-sm btn-primary" data-toggle="modal" id="btn-trigger-modal-roles" data-target="#rolesModal{{$usuario->id}}">
                    Administrar Roles
                  </button>
                </div>

                <!-- Modal roles del usuario -->
                <div class="modal fade" id="rolesModal{{$usuario->id}}" tabindex="-1" role="dialog" aria-labelledby="rolesModalLabel" aria-hidden="true">
                  <div class="modal-dialog" role="document">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="rolesModalLabel">Roles de {{$usuario->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <form action="{{route('admin.editarRolUsuario', $usuario->id)}}" method="put" id="form-roles{{$usuario->id}}">
                        <div class="modal-body">
                          <table class="table" id="tabla-roles">
                            <tbody>
                              @foreach ($roles as $rol)
                              <tr>
                                <td class="col align-self-center">
                                  @if ($rol->name == 'Super Admin')
                                    @if ($usuario->hasRole($rol->name))
                                      @if ($usuario->email != Auth::user()->email || $superadmins == 1)
                                      <input type="checkbox" class="toggle-checkbox" disabled checked id="{{$rol->name}}" name="roles[]" value="{{$rol->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data ze="xs" data-style="ios">
                                      @else
                                      <input type="checkbox" class="toggle-checkbox" checked id="{{$rol->name}}" name="roles[]" value="{{$rol->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                      @endif
                                    @else
                                      <input type="checkbox" class="toggle-checkbox" id="{{$rol->name}}" name="roles[]" value="{{$rol->id}}" data-on=" " data-off=" " data-offstyle="secondary" data-width="10" data-toggle="toggle" data-size="xs" data-style="ios">
                                    @endif
                                  @endif
                                  <label class="form-check-label" for="{{$rol->name}}">
                                    {{$rol->name}}
                                  </label>
                                  @if ($usuario->hasRole($rol->name))
                                    @if ($usuario->email != Auth::user()->email)
                                    <span class="badge badge-pill badge-danger">No se puede quitar este rol</span>
                                    @elseif ($superadmins == 1)
                                    <span class="badge badge-pill badge-danger">Único Super Admin</span>
                                    @endif
                                  @endif
                                  <button type="button" class="btn-sm btn-primary float-right" data-toggle="modal" data-dismiss="modal" data-target="#detailsModal{{$rol->id}}{{$usuario->id}}">
                                    Detalles
                                  </button>
                                </td>                                
                              </tr> 
                              @endforeach
                            </tbody>
                          </table>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                          <input type="submit" name="btn"  class="btn btn-primary btn-submit-roles" value="Guardar Cambios" onclick="return confirmarCambios('roles')">
                        </div>
                      </form>
                    </div>
                  </div>
                </div>

                <!-- Modal de detalles del rol -->
                <div class="modal fade" id="detailsModal{{$rol->id}}{{$usuario->id}}" aria-hidden="true" aria-labelledby="detailsModalLabel" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title" id="detailsModalLabel">Permisos del rol {{$rol->name}}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                          <span aria-hidden="true">&times;</span>
                        </button>
                      </div>
                      <div class="modal-body">
                      <table class="table">
                        <tbody>
                          @if($rol->name == 'Super Admin')
                            Este rol tiene todos los permisos.
                          @else
                            @foreach ($rol->permissions as $permiso)
                            <tr>
                              <td class="col align-self-center">
                                  {{$permiso->name}}
                              </td> 
                            </tr>                               
                            @endforeach
                          @endif
                        </tbody>
                      </table>
                      </div>
                      <div class="modal-footer">
                        <button class="btn btn-primary" data-target="#rolesModal{{$usuario->id}}" data-toggle="modal" data-dismiss="modal">Volver</button>
                      </div>
                    </div>
                  </div>
                </div>
              </td>
              @endcan
          @endforeach
        </tr>
      </tbody>
      @else
      <h1>No hay usuarios registrados</h1>
      @endif
    </table>
	</div>
</div>

@endsection
@section('footer_scripts')
<script type="text/javascript">
  function confirmarCambios(tipo){
    return confirm("¿Estás seguro de que deseas guardar los nuevos " + tipo + "?");
  };
</script>

<!-- datatables -->
<script>src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"</script>
<script>src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"</script>
<script>
  $('#tabla-usuarios').DataTable({
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
