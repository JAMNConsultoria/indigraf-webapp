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
	   //renderGraf('chart_'+codloc,data_source_url);
	   renderGraf('chart_'+codloc);	   
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
 //function renderGraf(container,dataref){
 function renderGraf(container){
	 
	var chart = AmCharts.makeChart(container, {
		"type": "serial",
		//"numberFormatter":{precision:-1, decimalSeparator:',', thousandsSeparator:'.'},	  
		//"dataLoader": {
		//	"url": dataref,
		//	"format": "json"
		//},	
		 "theme": "light",
		"categoryField": "year",
		"startDuration": 1,
		"categoryAxis": {
			"gridPosition": "start",
			"position": "left"
		},
		"trendLines": [],
		"graphs": [
			{
				"balloonText": "Income:[[value]]",
				"fillAlphas": 0.8,
				"id": "AmGraph-1",
				"lineAlpha": 0.2,
				"title": "Income",
				"type": "column",
				"valueField": "income"
			},
			{
				"balloonText": "Expenses:[[value]]",
				"fillAlphas": 0.8,
				"id": "AmGraph-2",
				"lineAlpha": 0.2,
				"title": "Expenses",
				"type": "column",
				"valueField": "expenses"
			}
		],
		"guides": [],
		"valueAxes": [
			{
				"id": "ValueAxis-1",
				"position": "top",
				"axisAlpha": 0
			}
		],
		"allLabels": [],
		"balloon": {},
		"titles": [],
		"dataProvider": [
			{
				"year": "2005/2010",
				"income": 23.5,
				"expenses": 18.1
			},
			{
				"year": 2006,
				"income": 26.2,
				"expenses": 22.8
			},
			{
				"year": 2007,
				"income": 30.1,
				"expenses": 23.9
			},
			{
				"year": 2008,
				"income": 29.5,
				"expenses": 25.1
			},
			{
				"year": 2009,
				"income": 24.6,
				"expenses": 25
			}
		],
		"export": {
			"enabled": true
		 }

	});	 
 }//render