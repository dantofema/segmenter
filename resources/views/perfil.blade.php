@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <style>
    .profile-container {
      max-width: 600px;
      margin: 20px auto;
      padding: 20px;
      border: 1px solid #ccc;
      border-radius: 5px;
      box-shadow: 0 0 10px rgba(0,0,0,0.1);
      display: flex;
      align-items: center; /* Centro vertical */
      position: relative; /* Permite posicionamiento absoluto del botón */
      flex-wrap: wrap; /* Permitir que los elementos se envuelvan en varias líneas si es necesario */
    }
    .profile-picture {
      width: 150px;
      height: 150px;
      border-radius: 50%;
      margin-right: 10%;
      margin-left: 10%;
    }
    .user-details {
      margin-bottom: 20px;
    }
    .username {
      font-size: 24px;
      margin-bottom: 10px;
    }
    .email {
      font-size: 18px;
      color: #666;
    }
    .edit-button {
      position: absolute;
      top: 10px; /* Posición desde la parte superior */
      right: 10px; /* Posición desde la parte derecha */
      padding: 5px 10px;
      font-size: 13px;
      background-color: transparent; /* Botón transparente */
      color: orange; /* Texto naranja */
      border: 1px solid orange; /* Borde naranja */
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s; /* Transición suave */
    }
    .edit-button:hover {
      background-color: orange; /* Cambia el fondo al pasar el cursor */
      color: #fff; /* Cambia el color del texto al pasar el cursor */
    }
    .buttons-container {
      justify-content: space-between;
      display: flex;
      flex-wrap: wrap;
      width: 100%;
      color: orange
    }
    .button {
      flex: 1;
      padding: 10px 0;
      margin: 5px;
      font-size: 16px;
      background-color: orange; /* Color naranja */
      color: #fff;
      border: 1px solid orange; /* Borde naranja */
      border-radius: 50px; /* Forma de píldora */
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s; /* Transición suave */
    }
    .button:hover {
      background-color: transparent; /* Cambia el fondo al pasar el cursor */
      color: orange; /* Cambia el color del texto al pasar el cursor */
    }
    .divider {
      width: 100%;
      border-top: 1px solid #ccc; /* Línea divisoria */
      margin-top: 20px; /* Espacio superior */
      margin-bottom: 20px; /* Espacio inferior */
    }
  </style>
</head>
<body>
<div class="profile-container">
  <img class="profile-picture" src="https://i.pinimg.com/1200x/01/bf/df/01bfdf2066554fb5b021b992465c3e86.jpg" alt="Foto de perfil">
  <div class="user-details">
    <div class="username">{{$usuario->name}}</div>
    <div class="email">{{$usuario->email}}</div>
  </div>
  <button class="edit-button"><i class="bi bi-pen"></i></button> <!-- Botón de edición -->
  <div class="buttons-container">
    <hr class="divider"> <!-- Línea divisoria -->
    <button class="button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#permisosModal">Mis Permisos</button>
    <button class="button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#filtrosModal">Mis Filtros</button>
    <button class="button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#rolesModal">Mis Roles</button>
  </div>
</div>

<!-- Modal permisos del usuario -->
<div class="modal fade" id="permisosModal" tabindex="-1" role="dialog" aria-labelledby="permisoModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="permisoModalLabel">Mis Permisos</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table" id="tabla-permisos">
          <tbody>
            @if ($permisos->count() > 0)
              @foreach ($permisos as $permiso)
              <tr>                                         
                <td class="col align-self-center">
                  {{$permiso->name}}
                  @if ($permisos_roles->contains($permiso->name) or $usuario->hasRole('Super Admin'))
                    <span class="badge badge-pill badge-danger ml-2">Heredado de rol</span>
                  @endif
                </td>
              </tr> 
              @endforeach
            @else
              No tenés permisos asignados.
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal filtros del usuario -->
<div class="modal fade" id="filtrosModal" tabindex="-1" role="dialog" aria-labelledby="filtroModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filtroModalLabel">Mis Filtros</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table" id="tabla-filtros">
          <tbody>
            @if ($filtros->count() > 0)
              @foreach ($filtros as $filtro)
              <tr>                                         
                <td class="col align-self-center">
                  {{$filtro->name}} 
                  @if ($filtros_roles->contains($filtro->name))
                    <span class="badge badge-pill badge-danger ml-2">Heredado de rol</span>
                  @endif
                </td>
              </tr> 
              @endforeach
            @else
              No tenés filtros asignados.
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal roles del usuario -->
<div class="modal fade" id="rolesModal" tabindex="-1" role="dialog" aria-labelledby="rolesModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="rolesModalLabel">Mis Roles</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <table class="table" id="tabla-roles">
          <tbody>
            @if ($roles->count() > 0)
              @foreach ($roles as $rol)
              <tr>
                <td class="col align-self-center">
                  {{$rol->name}}
                  @if ($rol->guard_name == "web")
                    <span class="badge badge-pill badge-warning">Permisos</span>
                  @else
                    <span class="badge badge-pill badge-info">Filtros</span>
                  @endif
                  <button type="button" class="btn-sm btn-primary float-right btn-detalles" data-toggle="modal" data-dismiss="modal" data-role-id="{{ $rol->id }}" data-target="#detailsModal">
                    Detalles
                  </button>
                </td>                                
              </tr> 
              @endforeach
            @else
              No tenés roles asignados.
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Modal de detalles del rol -->
<div class="modal fade" id="detailsModal" aria-hidden="true" aria-labelledby="detailsModalLabel" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detailsModalLabel"></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <h4 class='authorization-label'></h4>
        <table class="authorization-table" style="width: 100%;">
          <tbody class="modal-authorization-table-body" style="width: 100%;">
          </tbody>
        </table>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary btn-volver" data-target="#rolesModal{{$usuario->id}}" data-toggle="modal" data-dismiss="modal">Volver</button>
      </div>
    </div>
  </div>
</div>

</body>
</html>

@endsection

@section('footer_scripts')
<script>
  $(document).ready(function(){
      $('.btn-detalles').click(function(){
          var role = $(this).data('role-id');
          $.ajax({
              url: 'roles/' + role + '/detail',
              type: 'GET',
              dataType: 'json',
              success: function(response){
                  if (response) {
                      // Configuro correctamente el modal al que dirige el boton "Volver"
                      $('#detailsModal .btn-volver').attr('data-target', '#rolesModal');
                      $('#detailsModal .modal-title').html('Detalles del Rol ' + response.rol.name);
                      if(response.rol.name === 'Super Admin') {
                        console.log("Super Admin");
                        // Muestro unicamente el mensaje para Super Admin
                        $('#detailsModal .authorization-label').hide();
                        $('#detailsModal .modal-authorization-table-body').html('Este rol tiene todos los permisos.');
                      } else {
                        // Vacío el contenido de la tabla autorizaciones antes de mostrar las nuevas
                        $('#detailsModal .modal-authorization-table-body').empty();
                        // Vacío el contenido del label de la tabla autorizaciones antes de mostrar el nuevo
                        $('#detailsModal .authorization-label').empty();
                        // Muestro nuevamente el label de la tabla autorizaciones
                        $('#detailsModal .authorization-label').show();
                        // Actualizo el label
                        if(response.rol.guard_name === 'web'){
                          $('#detailsModal .authorization-label').append('<label class="badge badge-pill badge-warning" for="authorization-table">Permisos</label>');
                        } else if(response.rol.guard_name === 'filters'){
                          $('#detailsModal .authorization-label').append('<label class="badge badge-pill badge-info" for="authorization-table">Filtros</label>');
                        }
                        console.log("No Super Admin");
                        console.log("Autorizaciones: " + response.autorizaciones);
                        if(response.autorizaciones.length > 0){
                          $.each(response.autorizaciones, function(index, autorizacion) {
                            $('#detailsModal .modal-authorization-table-body').append('<tr><td class="col align-self-center">'+autorizacion+'</td></tr>');
                          });
                        } else {
                          console.log(response.autorizaciones_usuario);
                          if(response.rol.guard_name === 'web'){
                            if(response.autorizaciones_usuario.includes('Administrar Roles')) {
                              $('#detailsModal .modal-authorization-table-body').append('<tr class=detail_info_row><td class="col align-self-center">Los permisos pertenecientes a este rol no se encuentran cargados en el sistema.</td></tr>');
                              $('#detailsModal .detail_info_row').append('<td><a href="{{route('admin.listarRoles')}}"">Administrar Roles</a></td>');
                            } else {
                              $('#detailsModal .modal-authorization-table-body').append('<tr><td class="col align-self-center">Los permisos pertenecientes a este rol no se encuentran cargados en el sistema. Comunicarse con un administrador.</td></tr>');
                            }
                          } else if(response.rol.guard_name === 'filters'){
                            if(response.autorizaciones_usuario.includes('Administrar Roles') || response.autorizaciones_usuario.includes('Administrar Filtros')) {
                              $('#detailsModal .modal-authorization-table-body').append('<tr class=detail_info_row><td class="col align-self-center">Los filtros pertenecientes a este rol no se encuentran cargados en el sistema.</td></tr>');
                              if(response.autorizaciones_usuario.includes('Administrar Roles')) {
                                $('#detailsModal .detail_info_row').append('<td><a href="{{route('admin.listarRoles')}}"">Administrar Roles</a></td>');
                              }
                              if(response.autorizaciones_usuario.includes('Administrar Filtros')) {
                                $('#detailsModal .detail_info_row').append('<td><a href="{{route('admin.listarFiltros')}}"">Administrar Filtros</a></td>');
                              }
                            } else {
                              $('#detailsModal .modal-authorization-table-body').append('<tr><td class="col align-self-center">Los filtros pertenecientes a este rol no se encuentran cargados en el sistema. Comunicarse con un administrador.</td></tr>');
                            }
                          }
                        };
                      }
                  } else {
                      console.log('El rol no pudo ser encontrado.');
                  }
              },
              error: function(xhr, status, error) {
                  console.error('Error al obtener detalles del rol:', error);
              }
          });
      });
      // Limpio el contenido del modal cuando se cierra
      $('#detailsModal').on('hidden.bs.modal', function () {
          $('#detailsModal .modal-permissions-table-body').empty();
          $('#detailsModal .modal-filters-table-body').empty();
      });
  });
</script>
@endsection
