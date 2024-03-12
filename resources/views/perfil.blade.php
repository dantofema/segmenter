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
      align-items: center;
      position: relative; /* permite posicionamiento absoluto del botón editar*/
      flex-wrap: wrap;
    }
    .user-details {
      margin-bottom: 20px;
      display: flex;
      flex-direction: column;
      align-items: flex-start;
    }
    .username-container {
      position: relative;
      font-size: 24px;
      margin-bottom: 10px;
      margin-top: 20px;
      display: flex;
      align-items: center; 
    }
    .edit-username-input {
      font-size: 19px;
      width: 63%; 
    }
    .email-container {
      position: relative;
      font-size: 18px;
      color: #666;
      display: flex;
      align-items: center; 
    }
    .edit-email-input {
      font-size: 16px;
      width: 76%; 
    }
    .password-button {
      margin-top: 15px;
      font-size: 14px;
      padding: auto;
      background-color: #fff;
      color: orange;
      border: 1px solid orange;
      border-radius: 50px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s;
      display: none
    }
    .password-button:hover {
      background-color: orange;
      color: #fff;
    }
    .mode-button {
      position: absolute;
      top: 10px; /* posición desde la parte superior */
      right: 10px; /* posición desde la parte derecha */
      padding: 5px 10px;
      font-size: 13px;
      background-color: transparent;
      color: orange;
      border: 1px solid orange;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s;
    }
    .mode-button:hover {
      background-color: orange;
      color: #fff;
    }
    .buttons-container {
      justify-content: space-between;
      display: flex;
      flex-wrap: wrap;
      width: 100%;
      color: orange
    }
    .modal-button {
      flex: 1;
      padding: 10px 0;
      margin: 5px;
      font-size: 16px;
      background-color: orange;
      color: #fff;
      border: 1px solid orange;
      border-radius: 50px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s;
    }
    .modal-button:hover {
      background-color: transparent;
      color: orange;
    }
    .divider {
      width: 100%;
      border-top: 1px solid #ccc;
      margin-top: 20px;
      margin-bottom: 20px;
    }
    .profile-picture {
      width: 150px;
      height: 150px;
      border-radius: 50%;
    }
    .profile-picture-container {
      width: 170px;
      height: 150px;
      margin-right: 5%;
      margin-left: 15%;
      position: relative;
      display: flex;
      align-items: center;
    }
    .edit-button, .edit-photo-button {
      font-size: 12px;
      background-color: transparent;
      color: orange;
      border: 1px solid orange;
      border-radius: 5px;
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.3s, color 0.3s;
      display: none; /* Ocultar los botones por defecto */
    }
    .edit-button:hover, .edit-photo-button:hover {
      background-color: orange;
      color: #fff;
    }
    .edit-photo-button {
      position: absolute;
      top: 10px; /* Distancia desde la parte superior */
      right: 10px; /* Distancia desde la derecha */
      font-size: 12px;
    }
  </style>
</head>
<body>
<div class="profile-container">
  <div class="profile-picture-container">
    <img class="profile-picture" src="/images/mandarina.svg" alt="Foto de perfil">
    <button class="edit-photo-button" onclick="changeProfilePicture()"><i class="bi bi-pen"></i></button>
  </div>
  <div class="user-details">
    <div class="username-container">
      <div class="username">{{$usuario->name}}</div>
      <input type="text" class="edit-username-input" style="display: none;">
      <button class="edit-button ml-2" id="edit-username" onclick="toggleEditUsername()"><i class="bi bi-pen"></i></button>
    </div>
    
    <div class="email-container">
      <div class="email">{{$usuario->email}}</div>
      <input type="text" class="edit-email-input" style="display: none;">
      <button class="edit-button ml-2" id="edit-email" onclick="toggleEditEmail()"><i class="bi bi-pen"></i></button>
    </div>

    <button class="password-button" id="password-button" onclick="changePassword()">Cambiar contraseña</button>
  </div>
  <button class="mode-button" onclick="toggleEditMode()"><i class="bi bi-pen"></i></button> <!-- Botón de edición -->
  <div class="buttons-container">
    <hr class="divider"> <!-- Línea divisoria -->
    <button class="modal-button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#permisosModal">Mis Permisos</button>
    <button class="modal-button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#filtrosModal">Mis Filtros</button>
    <button class="modal-button" data-toggle="modal" id="btn-trigger-modal-permisos" data-target="#rolesModal">Mis Roles</button>
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

<script>
    function toggleEditMode() {
      var editModeButton = document.querySelector('.mode-button i');
      var passwordButton = document.querySelector('.password-button');
      var userDetails = document.querySelector('.user-details');
      var usernameContainer = document.querySelector('.username-container');
      var emailContainer = document.querySelector('.email-container');
      var editUsernameInput = document.querySelector('.edit-username-input');
      var editEmailInput = document.querySelector('.edit-email-input');

  
      if (editModeButton.classList.contains('bi-pen')) {
        editModeButton.classList.remove('bi-pen');
        editModeButton.classList.add('bi-x-lg'); //Cambio el icono
        document.querySelectorAll('.edit-button, .edit-photo-button').forEach(function(button) {
          button.style.display = 'inline-block';
        });
        passwordButton.style.display = 'block'; // Muestro el botón de cambio de contraseña
        userDetails.style.marginBottom = '10px'; 
      } else {
        editModeButton.classList.remove('bi-x-lg');
        editModeButton.classList.add('bi-pen'); //Restauro icono editar
        document.querySelectorAll('.edit-button, .edit-photo-button').forEach(function(button) {
          button.style.display = 'none';
        });
        passwordButton.style.display = 'none'; // Oculto el botón de cambio de contraseña
        userDetails.style.marginBottom = '20px';
        //si estaba editando username o email, cancelo
        if (usernameContainer.querySelector('.username').style.display == 'none') {
          usernameContainer.querySelector('.username').style.display = 'inline-block';
          editUsernameInput.style.display = 'none';
        }
        if (emailContainer.querySelector('.email').style.display == 'none') {
          emailContainer.querySelector('.email').style.display = 'inline-block';
          editEmailInput.style.display = 'none';
        }
        // le coloco el tick nuevamente a los edit buttons
        document.querySelectorAll('.edit-button').forEach(function(button) {
          button.innerHTML = '<i class="bi bi-pen"></i>';
        });
      }
    }

    function toggleEditUsername() {
      var editUsernameButton = document.querySelector('.edit-username-input');
      var usernameDiv = document.querySelector('.username');
      var editButton = document.getElementById('edit-username');

      if (editUsernameButton.style.display === 'none') {
        // Mostrar campo de entrada y ocultar nombre de usuario
        editUsernameButton.style.display = 'inline-block';
        editUsernameButton.value = usernameDiv.textContent;
        usernameDiv.style.display = 'none';
        // Cambiar icono del botón a "check"
        editButton.innerHTML = '<i class="bi bi-check-lg"></i>';
      } else {
        // Ocultar campo de entrada y mostrar nombre de usuario
        editUsernameButton.style.display = 'none';
        usernameDiv.style.display = 'inline-block';
        // Cambiar icono del botón a "pen"
        editButton.innerHTML = '<i class="bi bi-pen"></i>';
        // Actualizar nombre de usuario
        updateUsername(editUsernameButton.value);
      }
    }

    function toggleEditEmail() {
      var editEmailButton = document.querySelector('.edit-email-input');
      var emailDiv = document.querySelector('.email');
      var editButton = document.getElementById('edit-email');

      if (editEmailButton.style.display === 'none') {
        // Mostrar campo de entrada y ocultar nombre de usuario
        editEmailButton.style.display = 'inline-block';
        editEmailButton.value = emailDiv.textContent;
        emailDiv.style.display = 'none';
        // Cambiar icono del botón a "check"
        editButton.innerHTML = '<i class="bi bi-check-lg"></i>';
      } else {
        // Ocultar campo de entrada y mostrar nombre de usuario
        editEmailButton.style.display = 'none';
        emailDiv.style.display = 'inline-block';
        // Cambiar icono del botón a "pen"
        editButton.innerHTML = '<i class="bi bi-pen"></i>';
        // Actualizar nombre de usuario
        updateEmail(editEmailButton.value);
      }
    }

  </script>
@endsection
