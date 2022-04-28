@extends ('layouts.app')
@section ('content_main') 
<div class="container text-center">
<div class="spinner-border text-muted"></div>
<div class="spinner-border text-primary"></div>
<div class="spinner-border text-success"></div>
<div class="spinner-border text-info"></div>
<div class="spinner-border text-warning"></div>
<div class="spinner-border text-danger"></div>
<div class="spinner-border text-secondary"></div>
<div class="spinner-border text-dark"></div>
<div class="spinner-border text-light"></div>
<ul class="card-columns text-center">
@foreach (App\Models\User::all()->sortBy('random()') as $user)
<div class="card">
  <div class="card-body">
    {{ $user->name }}
  </div>
</div>
@endforeach
</ul>
</div>
@endsection
<?php // </body> </html> ?>
