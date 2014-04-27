<!DOCTYPE html>

<!--
Call this using a getter like

<?php 
echo file_get_contents($_GET['url']); 
?>
-->
<html lang="en">
<head>
   <meta charset="utf-8">
   <title></title>
   <script type="text/javascript" src="/js/highcharts.js"></script>
   <script type="text/javascript">
var chart;

var prevDay;
var nextDay;	   
	   
	   
var chartOptions = {
	chart: {
		renderTo: 'chart',
		marginBottom: 70,
	},
    title: {
        text: '',
        style: {
			fontSize: '12px',
			fontFamily: 'Verdana, sans-serif',
		},

        
    },    
    xAxis: {
		align: 'right',
		labels: {
			style: {
				fontSize: '8px',
				fontFamily: 'Verdana, sans-serif',
			},
		},
	},
    yAxis: [{
				min: 0,
				title: {
					text: 'W',
					align: 'high',
			        offset: 20,
					rotation: 0,
					y: -10
				},
				labels: {
					style: {
						fontSize: '8px',
						fontFamily: 'Verdana, sans-serif',
					},
				},
			},
			{	
		        offset: 0,
				title: {
					text: 'kWh',
					align: 'high',
			        offset: 30,
					rotation: 0,
					y: -10
				},
				labels: {
					style: {
						fontSize: '8px',
						fontFamily: 'Verdana, sans-serif',
					},
				},
				opposite: true,
			}],
    legend: {
			width: 430,
            floating: true,
            align: 'left',
            x: 25, // = marginLeft - default spacingLeft
            itemWidth: 100,
            y: -10,
            borderWidth: 0,
			itemStyle: {
                color: '#000000',
                fontSize: "10px"
            }
    },
};

/**
 * Prepend a 0 to a number.
 */ 
function pad( num, size ) {
    var ret = num+"";
    while (ret.length < size) ret = "0" + ret;
    return ret;
}

/**
 * Create a javascript date object from a formatted string
 */
function dateFromString(str) {
  var m = str.match(/(\d+).(\d+).(\d+)\s+(\d+):(\d+):(\d+)/);
  // NOTICE: +1h due to timezone miscalculation
  return new Date("20"+m[3], +m[2] - 1, +m[1], +m[4] + 1, +m[5], +m[6] * 100);
}

/*
 * Create an array from a json object
 */ 
function json2array(json){
  var result = [];
  var keys = Object.keys(json);
  keys.forEach(function(key){
      result.push(json[key]);
    });
  return result;
}

function drawChart ( dateText ) {
	var energy = 1;
	var chartDate;
	var combined = 0;
	var normalize = 0;
	var colors = new Array( '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
	$.getJSON( '/getter.php?url=http://solar.doerflinger.org/index.php/solar/day_ajax/' + dateText + '/0', {
			date: dateText,
			energy: energy,
			combined: combined,
			normalize: normalize,
			foo: "bar"
		}).done(function( json ) {
		delete json.num_inverters;
		chartDate = $.datepicker.formatDate("dd.mm.y", $.datepicker.parseDate("ymmdd",  json.date ) );
		$("#chartDate").val( json.date );
		delete json.date;
		// Remove any preexisting series from the chart
		while(chart.series.length > 0)
			chart.series[0].remove(true);

		var energyDisplayed = false;
		var energyData = new Array ();

		$.each ( json, function ( index, jSeries ) {
			// array to store the series data
		   	ret = [];
		   	$.each ( jSeries.data, function ( sIndex, value ) {				    
				ret.push( json2array(new Array( dateFromString( value[0] ).getTime(), value[1])) );
			});
			if ( chart.series.length == 0 ) {
				$.each( jSeries.energy, function ( index, val ) {
					energyData.push ( json2array( new Array( dateFromString( val[0] ).getTime() , val[1] ) ) );
				});
			}
						
			chart.xAxis[0].update( {
				type: 'datetime',		
				labels: {
					formatter: function() {
						return ( Highcharts.dateFormat('%H:%M', this.value) );			
					},
				},
			});

			
			if ( !energyDisplayed ) {
					 chart.addSeries( {
					 	type: 'areaspline',
					 	name: 'Ertrag (kWh)',
					 	data: energyData,
					 	yAxis: 1,
					 	color: '#BBBBBB',
					 	fillColor: {
						 	linearGradient: [0, 0, 0, 700],
						 	stops: [
						 		[0, 'rgb(200, 200, 200)'],
						 		[1, 'rgba(255,255,255,0.2)']
						 	]
						 },
						 marker: { 
							 enabled: false,
							 states: {
								 hover: {
									 enabled: true,
									 radius: 3
								 }
							 }
						 }, 
					}); // End chart.addSeries()
					energyDisplayed = true;
			}
			chart.addSeries( {
					name: jSeries.name + '(W)',
					data: ret,
					color: '#ff0000',
					type: 'spline',
					marker: { 
						enabled: false,
						states: {
							hover: {
								enabled: true,
								radius: 3
							}
						}
					},

			}); // End chart.addSeries
			// Only display first graph (sum)
			return false;	 
		});
	chart.setTitle( { text: chartDate } );
	});
		
		
}

function newDay ( direction ) {

	var d = $.datepicker.parseDate("ymmdd",  $("#chartDate").val() );

	if ( direction == "next" ) {
		newDate = $.datepicker.formatDate("ymmdd", new Date(d.getTime() + (24 * 60 * 60 * 1000) )  );
	}
	else {
		newDate = $.datepicker.formatDate("ymmdd", new Date(d.getTime() - (24 * 60 * 60 * 1000) )  );
	}
	
	if ( new Date(d.getTime() + (24 * 60 * 60 * 1000) >= new Date () ) )
		// today already, no dates in the future!
		$("#chartDate").val( $.datepicker.formatDate("ymmdd", new Date(d.getTime() ) ) );
		
	else 
		$("#chartDate").val( newDate );

	drawChart ( newDate );
}

 
 $(function () {
	var today = $.datepicker.formatDate("ymmdd",  new Date() );
	$("#chartDate").val( today );
 	// Initialize Chart
	chart = new Highcharts.Chart( chartOptions );

 	drawChart ( today );
 	
 	chart.renderer.text('http://solar.doerflinger.org', chart.chartWidth/2 - 70, 37)
    .css({
        color: '#4572A7',
        fontSize: '10px',
        cursor: 'pointer' // make it look clickable
    }).add().on('click',function(){		
		window.open( "http://solar.doerflinger.org" );
    });



 	chart.renderer.text('<', 20, 375)
    .css({
        color: '#4572A7',
        fontSize: '16px',
        cursor: 'pointer' // make it look clickable
    }).add().on('click',function(){		
		newDay( "prev" );
    });
    
    


 	chart.renderer.text('>', chart.chartWidth - 20, 375)
    .css({
        color: '#4572A7',
        fontSize: '16px',
        cursor: 'pointer' // make it look clickable
    }).add().on('click',function(){
		newDay( "next" );
    });	

 	
 	
 });
   
   </script>

</head>
<body>
	<div id="embedcontent">
		<input type="hidden" id="chartDate" />
		<div id="chart"></div>
	</div>
</body>
</html>
