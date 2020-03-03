@extends('layouts.app')
@section('content')
<h3 class="text-center">Aglomerado ({{ $aglomerado->codigo }}) {{ $aglomerado->nombre }}</h3>
<canvas id="canvas" style="padding: 20px;" height="280" width="600"></canvas>
@endsection
@section('footer_scripts')
       <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js" charset="utf-8"></script>
        <script>
        var url = "{{url('ver-segmentacion-grafico-resumen')}}/{{$aglomerado->id}}";
        var SegmentosCantidad = new Array();
        var Labels = new Array();
        var Viviendas = new Array();
        $(document).ready(function(){
          $.post(url, {"_token": "{{ csrf_token() }}"},function(response){
            response.forEach(function(data){
                SegmentosCantidad.push(data.cant_segmentos);
                Viviendas.push(data.vivs);
            });
            var ctx = document.getElementById("canvas").getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                      labels:Viviendas,
                      datasets: [{
                          label: 'Número de Segmentos',
                          data: SegmentosCantidad,
                          borderWidth: 1,
                          backgroundColor: 'rgb(36, 125, 173)',
                          borderColor: 'rgb(66, 155, 213)'
                      }]
                  },
                  options: {
                      responsive: true,
                      scales: {
                          yAxes: [{
                              gridLines: {
                                  drawBorder: true,
                                  color: ['pink', 'red', 'orange', 'yellow', 'green', 'blue', 'indigo', 'purple']
                        }     ,
                              ticks: {
                                  beginAtZero:true
                              }
                          }]
                      }
                  }
              });
          });
        });
        </script>
@endsection
