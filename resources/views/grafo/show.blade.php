@extends('layouts.app')
@section('content')
<div>Grafo de segmentación ({{ $aglomerado->codigo}}) {{ $aglomerado->nombre}}</div>
<div>Radio: {{ $radio->codigo}}</div>
<pre style="line-height: initial;font-size: 75%;">
{{ $radio->Resultado ?? 'No hay resultado de segmenta' }}
</pre>
<div>MiniMap: {!! $radio->getSVG() !!}</div>
@endsection
@section('content')
@endsection
@section('header_scripts')
<script src="https://unpkg.com/numeric/numeric-1.2.6.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.14.0/cytoscape.min.js"></script>
<script src="https://unpkg.com/layout-base/layout-base.js"></script>
<script src="https://unpkg.com/cose-base/cose-base.js"></script>
<script src="/js/cytoscape-fcose.js"></script>
<script src="/js/cytoscape-cola.js"></script>
<script src="/js/cola.min.js"></script>
<style>
#cy {
  width: 800px;
  height: 400px;
  display: block;
}
</style>
@endsection
@section('content_main')
	<div width= 800px;
         height= 400px
         id=cy>
	<button type="button" class="btn btn-primary" onClick="ordenar();"value="Ordenar">ReOrdenar</button>
    </div>
@endsection
@section('footer_scripts')
	<script>
    let arrayOfClusterArrays = @json($segmentacion) ;  
    let clusterColors = ['#FF0', '#0FF', '#F0F', '#4139dd', '#d57dba', '#8dcaa4'
                        ,'#555','#CCC','#A00','#0A0','#00A','#F00','#0F0','#00F','#008','#800','#080'];
		var cy = cytoscape({

  container: document.getElementById('cy'), // container to render in

  elements: [ // list of graph elements to start with
    @foreach ($nodos as $nodo)
        { data: { group: 'nodes',mza: '{{ $nodo->mza_i }}',label: '{{ $nodo->label }}', conteo: '{{ $nodo->conteo }}', id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}'  } },
    @endforeach    
    @foreach ($relaciones as $nodo)
        { data: { group: 'edges',tipo: '{{ $nodo->tipo }}', id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}->{{ $nodo->mza_j }}-{{ $nodo->lado_j }}', source:'{{ $nodo->mza_i }}-{{ $nodo->lado_i }}', target:'{{ $nodo->mza_j }}-{{ $nodo->lado_j }}'} },
    @endforeach    
  ],
  style: [ // the stylesheet for the graph
    {
      selector: 'node',
      style: {
        'background-color': function (ele) {
					for (let i = 0; i < arrayOfClusterArrays.length; i++)
						if (arrayOfClusterArrays[i].includes(ele.data('id')))
                           if (i>clusterColors.length) {n=i-clusterColors.length;
                                                        if (n<0) n=-n;}
                            else n=i;
						if (clusterColors[n]!=null) return clusterColors[n];
					return '#000000';
				},
        'label': 'data(conteo)',
        'width': function(ele){ return (ele.data('conteo')/2)+10; },
        'height': function(ele){ return (ele.data('conteo')/2)+10; },
      }
    },
    {
      selector: 'edge',
      style: {
        'width': 3,
        'line-color': function (ele) { if (ele.data('tipo')=='dobla') return '#555'; else return '#ccc'; },
        'target-arrow-color': '#aae',
        'target-arrow-shape': 'triangle',
        'label': function (ele) { return ''; if (ele.data('tipo')=='dobla') return 'd'; else if (ele.data('tipo')=='enfrente') return 'e'; }
      }
    }
      ],
    layout: {
        name: 'grid',
        rows: 25
    }
    });
    cy.on('click', 'node', function(evt){
      var node = evt.target;
      alert( 'Lado: ' + node.id() );
    });
    var layout = cy.layout({ name: 'random'});
    layout.run();
    function ordenar(){
        var layout = cy.layout({ name: 'cose'});
        layout.run();
    }
    ordenar();
    </script>
@endsection
