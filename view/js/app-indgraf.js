function carregaLocalidades(seletor,objLocs) {
	/*** carrega lista de localidades ***/	
	var elLista = $(seletor)
	$.each(objLocs.regioes, function(i) {
		var li = $('<li/>')
			.appendTo(elLista);
		var a = $('<a/>')
			.text( this.ufSigla + ' - '+ this.regiaoNome)
			.attr("href","#"+this.regiaoId)
			.attr("name",this.regiaoId)
            .attr("class","loc")
			.appendTo(li);
	});
	$(seletor+' a').click(function() {
		var codsel = $(this).attr('name');
		config.localidades=[];
		console.log(config);
		config.localidades.push(codsel);
		mostragrafico();
		
	});

}	


function mostragrafico(){
	//#cria um gráfico novo no dashboard
	for (var i=0;i < config.localidades.length;i++){
	   var exp=/rcod/ig;	
	   var codloc = config.localidades[i];
	   var data_source_url= config.apidados + config.grafico + '/regiao/'+codloc+'/ano/'+config.ano;
	   geradiv("#graficos",codloc);
	   renderGrafPyramid('chart_'+codloc,data_source_url);
	}		
}

function geradiv(containerPai,codloc){
   if ($('#chart_'+codloc).length==false ){
		$('<div>',{
			id: 'chart_'+codloc,
			class: 'chart',
			appendTo : containerPai
		});
   }	
}

//#graficoPiramide
 function renderGrafPyramid(container,dataref){
	var chart = AmCharts.makeChart(container, {
		  "type": "serial",
		  "numberFormatter":{precision:-1, decimalSeparator:',', thousandsSeparator:'.'},	  
		  "dataLoader": {
			"url": dataref,
			"format": "json"
		  },
		  "titles": [
		  {
			"text": config.title,
			"size": 15
		  }
		  ],
		  "legend": {
		    "useGraphSettings": true
		  },
		  "colors": ["#1E90FF","#FF8B8B"],
		  "rotate": true,
		  "marginBottom": 50,
		  "graphs": [{
			 "fillAlphas": 1,
			"lineAlpha": 0.2,
			"type": "column",
			"valueField": "Homens",
			"title": "Homens",
			//"labelText": "[[value]]",
			"clustered": false,
			"labelFunction": function(item) {
			  return number_format(Math.abs(item.values.value),0,',','.');
			},
			"balloonFunction": function(item) {
			  return "[Homens] "+item.category + ": " + number_format(Math.abs(item.values.value),0,',','.');
			}
		  }, {
			"fillAlphas": 0.8,
			"lineAlpha": 0.2,
			"type": "column",
			"valueField": "Mulheres",
			"title": "Mulheres",
			//"labelText": "[[value]]",
			"clustered": false,
			"labelFunction": function(item) {
			  return Math.abs(item.values.value);
			},
			"balloonFunction": function(item) {
			  return "[Mulheres] "+item.category + ": " + number_format(Math.abs(item.values.value),0,',','.');
			}
		  }],
		  "categoryField": "idade",
		  "categoryAxis": {
			"gridPosition": "start",
			"gridAlpha": 0.2,
			"axisAlpha": 0
		  },
		  "valueAxes": [{
			"gridAlpha": 0,
			"ignoreAxisWidth": false,
			"labelFunction": function(value) {
			  return number_format(Math.abs(value),0,',','.');
			},
			"guides": [{
			  "value": 0,
			  "lineAlpha": 0.2
			}]
		  }],
		  "balloon": {
			"fixedPosition": false
		  },
		  "chartCursor": {
			"valueBalloonsEnabled": false,
			"cursorAlpha": 0.05,
			"fullWidth": true
		  },
		  "allLabels": [{
			"text": "Homens",
			"x": "28%",
			"y": "97%",
			"bold": true,
			"align": "middle"
		  }, {
			"text": "Mulheres",
			"x": "75%",
			"y": "97%",
			"bold": true,
			"align": "middle"
		  }],
		   "export": {
			"enabled": true,
			"menuReviver": function(item,li) {
			  if (item.format === "CSV")
				li.style.display = "none";
			  return li;
			}
		  }
	})
	
} //renderGraf	