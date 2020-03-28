<script src="https://cdnjs.cloudflare.com/ajax/libs/cytoscape/3.14.0/cytoscape.min.js"></script>
<script src="https://unpkg.com/numeric/numeric-1.2.6.js"></script>
<script src="https://unpkg.com/layout-base/layout-base.js"></script>
<script src="https://unpkg.com/cose-base/cose-base.js"></script>
<script src="/js/cytoscape-fcose.js"></script>
<script src="/js/cytoscape-cola.js"></script>
<script src="/js/cola.min.js"></script>
<style>
#cy {
  width: 1200px;
  height: 600px;
  display: block;
}
</style>
<body>
	<button onClick="ordenar();"value="Ordenar">ReOrdenar</button>
	<div id=cy></div>
</body>
	<script>
    let arrayOfClusterArrays = @json($segmentacion) ;  
    let clusterColors = ['#756D76', '#3ac4e1', '#ad277e', '#4139dd', '#d57dba', '#8ab23c', '#8dcaa4'
                        ,'#AAA','#BBB','#CCC','#A00','#0A0','#00A','#F00','#0F0','#00F'];
		var cy = cytoscape({

  container: document.getElementById('cy'), // container to render in

  elements: [ // list of graph elements to start with
    @foreach ($nodos as $nodo)
        { data: { group: 'nodes',mza: '{{ $nodo->mza_i }}',label: '{{ $nodo->label }}', id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}'  } },
    @endforeach    
    @foreach ($relaciones as $nodo)
        { data: { group: 'edges',id: '{{ $nodo->mza_i }}-{{ $nodo->lado_i }}->{{ $nodo->mza_j }}-{{ $nodo->lado_j }}', source:'{{ $nodo->mza_i }}-{{ $nodo->lado_i }}', target:'{{ $nodo->mza_j }}-{{ $nodo->lado_j }}'} },
    @endforeach    
  ],

  style: [ // the stylesheet for the graph
    {
      selector: 'node',
      style: {
        'background-color': function (ele) {
					for (let i = 0; i < arrayOfClusterArrays.length; i++)
						if (arrayOfClusterArrays[i].includes(ele.data('id')))
							return clusterColors[i];

					return '#756D76';
				},
        'label': 'data(label)'
      }
    },

    {
      selector: 'edge',
      style: {
        'width': 2,
        'line-color': '#ccc',
        'target-arrow-color': '#ccc',
        'target-arrow-shape': 'triangle'
      }
    }
  ],

  layout: {
    name: 'grid',
    rows: 25
  }

});
var layout = cy.layout({ name: 'random'});
layout.run();
function ordenar(){
var layout = cy.layout({ name: 'cose'});
layout.run();
}
	</script>
