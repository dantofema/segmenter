<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    @include('includes.head_sidebar')
</head>
<body>
<div id="app">

  <!-- sidebar content -->
  <div id="sidebar" class="wrapper">
      @include('includes.sidebar')
  </div>

  <div class="container content">
    
    <header class="row">
        @include('includes.header_sidebar')
    </header >
    <div class="row">
      <div id="main" class="row justify-content-center">
        <!-- main content -->
            @yield('content')
      </div>
    </div>
    <footer class="row">
        @include('includes.footer')
    </footer>
  </div>

</div>
</body>
<script>
$(document).ready(function () {
    $('#sidebarCollapse').on('click', function () {
        $('#sidebar').toggleClass('active');
    });
});
</script>
</html>
