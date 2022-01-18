@extends('layouts.app')
@section('content')
<div class="text-center">
<h2 class="text-center">Histograma</h2>
@if (isset($titulo))
<h3 class="text-center">({{ $titulo }})</h3>
@endif
@if (isset($provincia))
<h3 class="text-center">({{ $provincia->codigo }}) {{ $provincia->nombre }}</h3>
@endif
@if (isset($aglomerado))
<h3 class="text-center">Aglomerado ({{ $aglomerado->codigo }}) {{ $aglomerado->nombre }}</h3>
@endif
@if (isset($localidad))
<h4 class="text-center">Localidad ({{ $localidad->codigo }}) {{ $localidad->nombre }}</h4>
@endif
<div id ="resumen"></div>
<canvas id="canvas" style="padding: 20px 50px 20px 50px; max-height: 500px; " height="280" width="600"></canvas>
</div>
@endsection
@section('footer_scripts')
        <script>
        var myChart = '';
        var Cantidad = new Array();
      @if (isset($url_data))
        var url = "{{$url_data}}";
      @endif
      @if (isset($aglomerado))
        var url = "{{url('ver-segmentacion-grafico-resumen')}}/{{$aglomerado->id}}";
      @endif
      @if (isset($localidad))
        var url = "{{url('localidad')}}/{{$localidad->id}}/grafico";
      @endif
        var Hechos = new Array();
        var Labels = new Array();
        var Viviendas = new Array();
        var Detalle = new Array();
        $(document).ready(function(){
          $.post(url, {"_token": "{{ csrf_token() }}"},function(response){
            var sum = 0;
            var n_cants= 0;
            response.forEach(function(data){
                Cantidad.push(data.cant);
                Hechos.push(data.hecho);
                sum += Number(data.cant);
                n_cants++;
                Detalle.push(data.detalle);
            });
            var mensaje = sum+' en '+n_cants+' días, con un promedio de '+Math.round (sum/n_cants)+' x día';
            document.getElementById("resumen").innerHTML=mensaje;
            var ctx = document.getElementById("canvas").getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'line',
                  data: {
                      labels: Hechos,
                      datasets: [{
                          label: 'Radios',
                          data: Cantidad,
                      }]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          y: {
                              title: 'Radios ',
                              grid: {
                                  drawBorder: true,
                                  color:
                                  function (context) {
                                   const colores = [
                                      'rgb(255, 99, 132)',
                                      'rgb(255, 159, 64)',
                                      'rgb(255, 205, 86)',
                                      'rgb(75, 192, 192)',
                                      'rgb(54, 162, 235)',
                                      'rgb(153, 102, 255)',
                                      'rgb(231,233,237)'
                                    ];
                                   return colores[context.tick.value % 7];
                                  }
                              },
                              ticks: {
                                  // valores enteros
                                  precision: 0, 
                                  beginAtZero:true
                              },
                              title: {
                                display: true,
                                text: 'Cantidad'
                              }
                          },
                          x: {
                              title: {
                                display: true,
                                text: 'Fecha'
                              }
                          }
                      }
                  }
              });
          });
        });
        </script>
@endsection
