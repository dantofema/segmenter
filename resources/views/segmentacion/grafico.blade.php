@extends('layouts.app')
@section('content')
@if (isset($aglomerado))
<h3 class="text-center">Aglomerado ({{ $aglomerado->codigo }}) {{ $aglomerado->nombre }}</h3>
@endif
@if (isset($localidad))
<h4 class="text-center">Localidad ({{ $localidad->codigo }}) {{ $localidad->nombre }}</h4>
@endif
<canvas id="canvas" style="padding: 20px;" height="280" width="600"></canvas>
@endsection
@section('footer_scripts')
       <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.6.0/Chart.bundle.js" charset="utf-8"></script>
        <script>
      @if (isset($aglomerado))
        var url = "{{url('ver-segmentacion-grafico')}}/{{$aglomerado->id}}";
      @endif
      @if (isset($localidad))
        var url = "{{url('localidad')}}/{{$localidad->id}}/grafico";
      @endif
        var Segmentos = new Array();
        var Labels = new Array();
        var Viviendas = new Array();
        $(document).ready(function(){
          $.post(url, {"_token": "{{ csrf_token() }}"},function(response){
            response.forEach(function(data){
                Segmentos.push(data.segmento_id);
                Labels.push(data.mzas);
                Viviendas.push(data.vivs);
            });
            var ctx = document.getElementById("canvas").getContext('2d');
                var myChart = new Chart(ctx, {
                  type: 'bar',
                  data: {
                      labels:Segmentos,
                      datasets: [{
                          label: 'Vivendas en Segmentos',
                          data: Viviendas,
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
                              },
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
