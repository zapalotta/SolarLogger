<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Solaranlage Scheune Wambach</title>
   <link rel="stylesheet" href="/css/main.css">
   <link rel="stylesheet" href="/js/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css" /> 

   <script src="/js/jquery-1.10.2.min.js"></script>
   <script src="/js/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
   <script type="text/javascript" src="/hc/js/highcharts.js"></script>
   <script type="text/javascript">

var chart;

var chartOptions = {
	chart: {
		renderTo: 'chart',
	},
    title: {
        text: '',
    },    
    xAxis: {
		align: 'right',
		style: {
			fontSize: '6px',
			fontFamily: 'Verdana, sans-serif',
		},
	},
    yAxis: [{
				min: 0,
			},
			{
				opposite: true,
			}],
    legend: {
		verticalAlign: 'top',
		floating: true,
		x: 90,
		y: 45,
		borderWidth: 1
    },
};

var datepickerOptions = {
    dateFormat: "ymmdd",
    showOn: "button",
    changeMonth: true,
    changeYear: true,
    maxDate: 0,
    buttonImage: "calendar.gif",
    showButtonPanel: true,
    buttonImageOnly: true,
    onSelect: function(dateText, inst) {
		drawChart ( dateText );
    },
    onChangeMonthYear: function ( year, month, picker ) {
		if ( $('#monthly:checked').val() ) {
			// Monthly checked => Update graph 140418!
			var datestr = year.toString().substr( 2, 2 ) + pad( month, 2 ) + '01';		
			drawChart ( datestr );
		}
    }
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

/*
 * Draw the chart. 
 *
 * String dateText Date in the format yymmdd. Monthly data also needs a two 
 * digit day, doesn't matter which day of the month is given, thoug
 */
function drawChart ( dateText ) {
	var diverted = 0;
	var normalize = 0;
	var energy = 0;
	var monthly = 0;
	var divertedIsChecked = $('#diverted:checked').val()?true:false;
	var normalizeIsChecked = $('#normalize:checked').val()?true:false;
	var energyIsChecked = $('#energy:checked').val()?true:false;
	var monthlyIsChecked = $('#monthly:checked').val()?true:false;  
	if( divertedIsChecked ){
    	combined = 0;
	}
	else {
		combined = 1;
	}
	if( normalizeIsChecked ){
		normalize = 1;
	}
	else {
		normalize = 0;
	}
	if( energyIsChecked ){
		energy = 1;
	}
	else {
		energy = 0;
	}
	if ( monthlyIsChecked ) {
		// Remove red as a leading color
		var colors = new Array( '#008800', '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
		// Disable energy and normalization
		$("#normalize").attr("disabled", true );
		$("#energy").attr("disabled", true );
		$("#normalize").removeAttr("checked");
		$("#energy").removeAttr("checked");
		$("label[for='normalize']").css('color', '#DDDDD');
		$("label[for='energy']").css('color', '#DDDDD');
	}
	else {
		var colors = new Array( '#FF0000', '#008800', '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
		$("#normalize").removeAttr("disabled");
		$("#energy").removeAttr("disabled");
		$("label[for='normalize']").css('color', '#000000');
		$("label[for='energy']").css('color', '#000000');
	}
	
	if ( monthlyIsChecked ) {
		// Month graph
		$.getJSON( '/index.php/solar/month_ajax', {
			date: dateText,
			combined: combined					
		}).done ( function ( json ) {
			// Remove any preexisting series from the chart
			while(chart.series.length > 0)
				chart.series[0].remove(true);
			// Set some options for the axis
			chart.xAxis[0].update( {
				type: 'linear',		
				labels: {
					// Reset the formatter
					formatter: function() {
						return this.value;
					},
				}	
			});
			chart.yAxis[0].update ( {
				title: {
					text: 'kWh'
				},
				stackLabels: {
                	enabled: true,
					formatter: function() {
						return Math.round( this.total / 1000 );
					},                    
                    style: {
                        fontWeight: 'bold',
                        color: (Highcharts.theme && Highcharts.theme.textColor) || 'gray'
                    }
                }
			});
			// Hide secondary yAxis
			chart.yAxis[1].update({
				labels: {
					enabled: false
				},
				title: {
					text: null
				},
			});

			// Set the formatter to add a total value
			chart.tooltip.options.formatter = function() {
				return '<b>'+ this.series.name +'</b><br/>' +
				this.x +': '+ Math.round(this.y / 1000) + 'kWh<br/>' +
				'Total: '+ Math.round( this.point.stackTotal/1000 ) + 'kWh';
			}
			// iterate over the data
			$.each ( json, function ( index, jSeries ) {
				// jSeries[0]: Array of Names 
				// jSeries[1]: Array of Arrays of Dates (one array per Inverter)
				// jSeries[2]: Array of Arrays of Values (one Array per Inverter)
				// jSeries[3]: Date
				var datestr = jSeries[3]+"";
				chart.setTitle({ text: datestr.substr( 2, 2 ) + " 20" + datestr.substr( 0, 2) }, { text: 'Monthly production' } ) ;
				// Add the x axis tickmarks
				chart.xAxis[0].update ( { categories: json2array( jSeries[1][0] ) } );					
				$.each( jSeries[2], function ( index, value )  {
					chart.addSeries( {
						name: jSeries[0][index],
						data: json2array( jSeries[2][index] ),
						color: colors[ chart.series.length ],
						type: 'column',
						stacking: 'normal',
					});
				});
			});
		}); // End function done (json) 
	} // End if monthly is checked
	else {
		// Day graph
		$.getJSON( '/index.php/solar/day_ajax', {
			date: dateText,
			energy: energy,
			combined: combined,
			normalize: normalize,
		}).done(function( json ) {
			// Remove unnecessary json objects
			delete json.num_inverters;
			delete json.date;
			var energyDisplayed = false;
			var energyData = new Array ();
			var cat = [];
			var typeStr = 'spline';
			while(chart.series.length > 0)
				chart.series[0].remove(true);
			// Remove possible old categories
			chart.xAxis[0].setCategories( null );
			chart.xAxis[0].update( {
				type: 'datetime',		
				labels: {
					formatter: function() {
						return ( Highcharts.dateFormat('%H:%M', this.value) );			
					},
				},
			});
			chart.tooltip.options.formatter = function() {
				var timeStr = Highcharts.dateFormat('%H:%M', this.x);
				return '<b>'+ this.series.name +'</b><br/>' +
				timeStr +': '+ Math.round(this.y * 10) / 10;
			}

			chart.setTitle({ text: $.datepicker.formatDate("dd.mm.y", $('#datepicker').datepicker("getDate"))}, { text: 'Daily production' } ) ;
			// Iterate over the json object
			$.each ( json, function ( index, jSeries ) {
				// array to store the series data
		   		ret = [];
		   		$.each ( jSeries.data, function ( sIndex, value ) {				    
			   		ret.push( json2array(new Array( dateFromString( value[0] ).getTime(), value[1])) );
				});
				chart.yAxis[0].update ({
					title: {
						text: 'Watt'
					},
				});
				// show energy!
				if ( energy == 1 ) {
					if ( chart.series.length == 0 ) {
						$.each( jSeries.energy, function ( index, val ) {
							energyData.push ( json2array( new Array( dateFromString( val[0] ).getTime() , val[1] ) ) );
						});
					}
					// Enable secondary Y axis
					chart.yAxis[1].update({
						labels: {
							enabled: true
						},
						title: {
							text: 'kWh'
						}
					});
				} // END add energy data!
				else {
					// Hide secondary yAxis
					chart.yAxis[1].update({
						labels: {
							enabled: false
						},
						title: {
							text: null
						}
					});
				}
				// Add power chart
				chart.addSeries( {
					name: jSeries.name + '(W)',
					data: ret,
					color: colors[ chart.series.length ],
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
				 // Make sure that the energy graph is only displayed once!
				 if ( !energyDisplayed ) {
					 // Not yet displayed, show!
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
			});
		});
	} // End else if monthlyIsChecked
} // End function drawChart

		
$(function () {
	//override the existing _goToToday functionality
	$.datepicker._gotoTodayOriginal = $.datepicker._gotoToday;
	$.datepicker._gotoToday = function(id) {
    	// now, optionally, call the original handler, making sure
		//  you use .aplly() so the context reference will be correct
		$.datepicker._gotoTodayOriginal.apply(this, [id]);
		$.datepicker._selectDate.apply(this, [id]);    
	};

	$( "#datepicker" ).datepicker( datepickerOptions );

	// react on changes of the filter checkboxes
    $(".filter").change(function () {
      var datestr = $.datepicker.formatDate("ymmdd", $('#datepicker').datepicker("getDate"));
      drawChart ( datestr );
    });
    
	// Initialize Chart
	chart = new Highcharts.Chart( chartOptions );

	// Create an initial chart
	var datestr = $.datepicker.formatDate("ymmdd", $('#datepicker').datepicker("getDate"));
	drawChart ( datestr );

});
        
   </script>

</head>
<body>
	<h1>Photovoltaikanlage Scheune Wambach</h1>
	<div id="menu">
		<div id="navigation">
			<div id="datepicker"></div>
			<br />
			<div id="controls" class="ui-widget ui-widget-content ui-helper-clearfix ui-corner-all controls">
				<div id="controls-header" class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all controls-header">
					Ansicht
				</div>
				<div class="format">
					<input type="checkbox" id="monthly" class="filter" /><label for="diverted">Monatswerte</label>
				</div>
				<div class="format">
					<input type="checkbox" id="diverted" class="filter" /><label for="diverted">Wechselrichter einzeln</label>
				</div>
				<div class="format">
					<input type="checkbox" id="normalize" class="filter" /><label for="normalize">Normalisieren</label>
				</div>
				<div class="format">
					<input type="checkbox" id="energy" class="filter" /><label for="energy">Ertrag anzeigen</label>
				</div>
			</div>
		</div>  <!-- End div navigation -->
	</div>   <!-- End div menu -->

	<div id="content">
		<div id="chart"  style="min-width: 310px; height: 600px; margin: 0 auto"></div>
	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>


</body>
</html>