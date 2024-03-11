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
    <button class="button">Mis Roles</button>
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
                    <span class="badge badge-pill badge-danger">Heredado de rol</span>
                  @endif
                </td>
              </tr> 
              @endforeach
            @else
              No hay permisos cargados.
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
                    <span class="badge badge-pill badge-danger">Heredado de rol</span>
                  @endif
                </td>
              </tr> 
              @endforeach
            @else
              No hay filtros cargados.
            @endif
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>

@endsection
