@extends('layouts.app')
@section('content')
<div class="text-center">
<h2 class="text-center">Segmentación </h2>
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
       <!--script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js" charset="utf-8"></script-->
        <script>
        var myChart = '';
        var SegmentosCantidad = new Array();
      @if (isset($aglomerado))
        var url = "{{url('ver-segmentacion-grafico-resumen')}}/{{$aglomerado->id}}";
      @endif
      @if (isset($localidad))
        var url = "{{url('localidad')}}/{{$localidad->id}}/grafico";
      @endif
        var Segmentos = new Array();
        var Labels = new Array();
        var Viviendas = new Array();
        var Detalle = new Array();
        $(document).ready(function(){
        var colores = {
          red: 'rgb(255, 99, 132)',
          orange: 'rgb(255, 159, 64)',
          yellow: 'rgb(255, 205, 86)',
          green: 'rgb(75, 192, 192)',
          blue: 'rgb(54, 162, 235)',
          purple: 'rgb(153, 102, 255)',
          grey: 'rgb(231,233,237)'
        };
          $.post(url, {"_token": "{{ csrf_token() }}"},function(response){
            var sum = 0;
            var n_segs= 0;
            response.forEach(function(data){
                SegmentosCantidad.push(data.cant_segmentos);
                Viviendas.push(data.vivs);
                sum += Number(data.vivs)*Number(data.cant_segmentos);
                n_segs += Number(data.cant_segmentos);
                Detalle.push(data.en_lados);
            });
            var mensaje = n_segs+' segmentos para '+sum+' viviendas, con un promedio de '+Math.round (100*sum/n_segs)/100+' viviendas x segmento';
            document.getElementById("resumen").innerHTML=mensaje;
            var ctx = document.getElementById("canvas").getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                      labels: Viviendas,
                      datasets: [{
                          label: 'Cantidad de Segmentos',
                          data: SegmentosCantidad,
                          borderWidth: 2,
                          backgroundColor: 'rgb(36, 125, 173)',
                          borderColor: 'rgb(66, 155, 213)'
                      }]
                  },
                  options: {
                      responsive: true,
                      borderRadius: 10,
                      scales: {
                          y: {
                              title: 'Cantidad de segmentos',
                              gridLines: {
                                  drawBorder: true,
                                  color: colores
                              },
                              ticks: {
                                  // valores enteros
                                  precision: 0, 
                                    // Include a dollar sign in the ticks
                                  callback: function(value, index, ticks) {
                                      if (value === 0) { return '0' };
                                      if (value === 1) { return '1 segmento'};
                                      return value + ' segmentos';
                                  },
                                  beginAtZero:true
                              }
                          },
                          x: {
                              ticks: {
                                  callback: function(value, index, ticks) {
                                      return Viviendas[value] + ' vivs ';
                                  }
                              }
                          }
                      }
                  }
              });
          });
        });
        </script>
@endsection
