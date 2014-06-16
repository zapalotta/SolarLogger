<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Solaranlage Scheune Wambach</title>
   <link rel="stylesheet" href="/css/main.css">
   <link rel="stylesheet" href="/js/jquery-ui-1.10.3.custom/css/smoothness/jquery-ui-1.10.3.custom.min.css" /> 
   <link href="/js/lightbox/css/lightbox.css" rel="stylesheet" />   
   

   <script src="/js/jquery-1.10.2.min.js"></script>
   <script src="/js/jquery-ui-1.10.3.custom/js/jquery-ui-1.10.3.custom.min.js"></script>
   <script src="/js/jquery.plugin.min.js"></script>
   <script src="/js/jquery.timer.min.js"></script>
   <script src="/js/lightbox/js/lightbox.min.js"></script>
   <script type="text/javascript" src="/js/highcharts.js"></script>
   <script type="text/javascript" src="/js/draggable-legend.js"></script>
   <script type="text/javascript">

var chart;

	var strings = [ [1,1,2,7,7,7,7,7,7,8,8,8,8],
					[1,1,2,7,7,7,7,7,7,8,8,8,8],
					[1,1,2,7,7,7,0,0,8,8,8,8,8],
					[5,5,2,3,3,0,0,0,8,8,6,6,6],
					[5,5,2,3,3,0,0,0,0,4,6,6,6],
					[5,5,2,3,0,0,0,0,0,4,4,6,6],
					[5,5,5,3,0,0,0,0,0,4,4,6,6],
					[5,5,5,0,0,0,0,0,0,0,4,6,6],
				   ];
	
	var stringsOnInverter = { 	0 : "none",
								1 : "3A",
								2 : "3B",
								3 : "1A",
								4 : "1B",
								5 : "2A",
								6 : "2B",
								7 : "4A",
								8 : "4B"	
	};
	var inverters = [	 "Gesamt pac(W)",
						 "QS3200/Pro(W)",
						 "XS6500(W)",
						 "QS3500(W)",
						 "Powador7200xi(W)",
						 "Powador4202DE(W)"
		
	];


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
		
		borderRadius: 3,
		backgroundColor: '#FFFFFF',
		align: 'right',
		layout: 'vertical',
		floating: true,
		draggable: true,
		x: -10,
		y: 25,
		borderWidth: 1,
		zIndex: 20,
		title: {
				fontSize: '56px',
                text: '↔'
            },
    },
    plotOptions: {
		series: {
			events: {
				mouseOver: function() {
					var inverter = inverters.indexOf (  this.name );
					$( ".solarpanel-" + inverter ).css("border-color", "#ff0000");
				},
				mouseOut: function() {
					var inverter = inverters.indexOf (  this.name );
					$( ".solarpanel-" + inverter ).css("border-color", "grey");
				}
			}
		},
		column: {
			stickyTracking: false,
			point: {
			events: {
				mouseOver: function() {
					var inverter = inverters.indexOf (  this.series.name + '(W)' );
					$( ".solarpanel-" + inverter ).css("border-color", "#ff0000");
				},
				mouseOut: function() {
					var inverter = inverters.indexOf (  this.series.name + '(W)' );
					$( ".solarpanel-" + inverter ).css("border-color", "grey");
				}
			}
			}
			
		}
	}
};

var datepickerOptions = {
    dateFormat: "ymmdd",
    showOn: "button",
    changeMonth: true,
    changeYear: true,
    maxDate: 0,
    firstDay: 1,
    buttonImage: "calendar.gif",
    showButtonPanel: true,
    buttonImageOnly: true,
    onSelect: function(dateText, inst) {
		drawChart ( );
    },
    onChangeMonthYear: function ( year, month, picker ) {
		if ( $("input[name='displaytype']:checked").attr("id") != "daily" ) {
			// Monthly checked => Update graph 140418!
			var datestr = year.toString().substr( 2, 2 ) + pad( month, 2 ) + '01';	
			$('#datepicker').datepicker("setDate", new Date( '20' + datestr.substr(0, 2) + '-' + datestr.substr(2, 2) + '-' + datestr.substr(4, 2) ) );
			drawChart();
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
  // NOTICE: +2h due to timezone miscalculation
  return new Date("20"+m[3], +m[2] - 1, +m[1], +m[4] + 2, +m[5], +m[6] * 100);
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

function setRadio(id) {
    var radio = $('#' + id);
    radio[0].checked = true;
    radio.button("refresh");
}

/*
 * Draw the chart. 
 *
 * String dateText Date in the format yymmdd. Monthly data also needs a two 
 * digit day, doesn't matter which day of the month is given, thoug
 */
function drawChart (  ) {
	var dateText = $.datepicker.formatDate("ymmdd", $('#datepicker').datepicker("getDate"));
	var diverted = 0;
	var normalize = 0;
	var energy = 0;
	var monthly = 0;
	var fixy = 0;
	var displayType = $("input[name='displaytype']:checked").attr("id");
	var divertedIsChecked = $('#diverted:checked').val()?true:false;
	var normalizeIsChecked = $('#normalize:checked').val()?true:false;
	var energyIsChecked = $('#energy:checked').val()?true:false;
	var monthlyIsChecked = $('#monthly:checked').val()?true:false;  
	var fixyIsChecked = $('#fixy:checked').val()?true:false;  

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
	if( fixyIsChecked ){
		fixy = 1;
	}
	else {
		fixy = 0;
	}
	if( energyIsChecked ){
		energy = 1;
	}
	else {
		energy = 0;
	}

	updatePermalink();
	switch ( displayType ) {
		case "daily":
		var colors = new Array( '#FF0000', '#008800', '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
		$("#normalize").removeAttr("disabled");
		$("#energy").removeAttr("disabled");
		$("#live").removeAttr("disabled");
		$("label[for='normalize']").css('color', '#000000');
		$("label[for='energy']").css('color', '#000000');
		$("label[for='live']").css('color', '#000000');

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

			chart.setTitle({ text: $.datepicker.formatDate("dd.mm.y", $('#datepicker').datepicker("getDate"))}, { text: 'Tagesproduktion' } ) ;
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
				if ( fixy == 1 ) {
					chart.yAxis[0].update( {
						max:20000
					});
				}
				else {
					chart.yAxis[0].update( {
						max:null
					});
				}
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
					if ( fixy == 1 ) {
						chart.yAxis[1].update( {
							max:160
						});
					}
					else {
						chart.yAxis[1].update( {
							max:null
						});
					}

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


		break;
		case "monthly":


//		alert ( $('#datepicker').datepicker("getDate") );
		// Remove red as a leading color
		if ( divertedIsChecked ) {
			var colors = new Array( '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
		}
		else {
			var colors = new Array( '#00BB00' );
			
		}
		// Disable energy and normalization, stop autoreload
		$('#content').timer( 'stop' );

		$("#normalize").attr("disabled", true );
		$("#energy").attr("disabled", true );
		$("#live").attr("disabled", true );
		$("#normalize").removeAttr("checked");
		$("#energy").removeAttr("checked");
		$("#live").removeAttr("checked");
		$("label[for='normalize']").css('color', '#DDDDD');
		$("label[for='energy']").css('color', '#DDDDD');
		$("label[for='live']").css('color', '#DDDDD');

		// Month graph
		$.getJSON( '/index.php/solar/month_ajax', {
			date: dateText,
			combined: combined,
			normalize: normalize,
					
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
			if ( fixy == 1 ) {
				chart.yAxis[0].update( {
					max:160000
				});
			}
			else {
				chart.yAxis[0].update( {
					max:null
				});
			}

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
				this.x +'</a>: '+ Math.round(this.y / 1000) + 'kWh<br/>' +
				'Total: '+ Math.round( this.point.stackTotal/1000 ) + 'kWh';
			}			
			// iterate over the data
			$.each ( json, function ( index, jSeries ) {
				// jSeries[0]: Array of Names 
				// jSeries[1]: Array of Arrays of Dates (one array per Inverter)
				// jSeries[2]: Array of Arrays of Values (one Array per Inverter)
				// jSeries[3]: Date
				var datestr = jSeries[3]+"";
				chart.setTitle({ text: $.datepicker.formatDate("M yy", $.datepicker.parseDate ( "ymd", dateText ) ) }, { text: 'Produktion pro Tag' } ) ;
//				chart.setTitle({ text: $.datepicker.formatDate("M yy", $('#datepicker').datepicker("getDate")) }, { text: 'Produktion im Monat' } ) ;
				// Add the x axis tickmarks
				chart.xAxis[0].update ( { categories: json2array( jSeries[1][0] ) } );					
				$.each( jSeries[2], function ( index, value )  {
					chart.addSeries( {
						name: jSeries[0][index],
						data: json2array( jSeries[2][index] ),
						color: colors[ chart.series.length ],
						type: 'column',
						stacking: 'normal',
						events: {
							click: function(event) {
								var dateStr = event.point.category;
								setRadio( "daily" );
								$('#datepicker').datepicker("setDate", new Date( '20' + dateStr.substr(6, 2) + '-' + dateStr.substr(3, 2) + '-' + dateStr.substr(0, 2) ) );
//								drawChart( dateStr.substr(6, 2) + dateStr.substr(3, 2) + dateStr.substr(0, 2) );
								drawChart();
							}
						}
					});
				});
			});
		}); // End function done (json) 

		
		break;
		case "yearly":

		// Remove red as a leading color
		if ( divertedIsChecked ) {
			var colors = new Array( '#00FFFF', '#00FF00', '#0000FF', '#000000', '#ffad40' );  
		}
		else {
			var colors = new Array( '#00BB00' );
			
		}
		// Disable energy and normalization, stop autoreload
		$('#content').timer( 'stop' );

		$("#normalize").attr("disabled", true );
		$("#energy").attr("disabled", true );
		$("#live").attr("disabled", true );
		$("#normalize").removeAttr("checked");
		$("#energy").removeAttr("checked");
		$("#live").removeAttr("checked");
		$("label[for='normalize']").css('color', '#DDDDD');
		$("label[for='energy']").css('color', '#DDDDD');
		$("label[for='live']").css('color', '#DDDDD');

		// Month graph
		$.getJSON( '/index.php/solar/year_ajax', {
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
//						var val = this.value;
						return( this.value );						
//						return val.substr( 3, 2 );
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
			if ( fixy == 1 ) {
				chart.yAxis[0].update( {
					max:3500000
				});
			}
			else {
				chart.yAxis[0].update( {
					max:null
				});
			}

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
				this.x +'</a>: '+ Math.round(this.y / 1000) + 'kWh<br/>' +
				'Total: '+ Math.round( this.point.stackTotal/1000 ) + 'kWh';
			}			
			// iterate over the data
			$.each ( json, function ( index, jSeries ) {
				// jSeries[0]: Array of Names 
				// jSeries[1]: Array of Arrays of Dates (one array per Inverter)
				// jSeries[2]: Array of Arrays of Values (one Array per Inverter)
				// jSeries[3]: Date
				var datestr = jSeries[3]+"";
				chart.setTitle({ text: $.datepicker.formatDate("yy", $.datepicker.parseDate ( "ymd", dateText ) ) }, { text: 'Produktion pro Monat' } ) ;
				// Add the x axis tickmarks
				chart.xAxis[0].update ( { categories: json2array( jSeries[1][0] ) } );					
				$.each( jSeries[2], function ( index, value )  {
					chart.addSeries( {
						name: jSeries[0][index],
						data: json2array( jSeries[2][index] ),
						color: colors[ chart.series.length ],
						type: 'column',
						stacking: 'normal',
						events: {
							click: function(event) {
							
								var month = pad( event.point.category, 2 );
								var year = $("select.ui-datepicker-year").val() ;
								console.log( this.options );
								$('#datepicker').datepicker("setDate", new Date( year + '-' + month + '-01'  ) );
								setRadio( "monthly" );
								drawChart();
							}
						}
					});
				});
			});
		}); // End function done (json) 

		break;
		
	}


	if ( monthlyIsChecked ) {

	} // End if monthly is checked
	else {

	} // End else if monthlyIsChecked
} // End function drawChart

function createTable(){

	var colors = {	"none" : "#FFFFFF",
					"1A" : "#00FFFF",
					"1B" : "#00BBFF",
					"2A" : "#00FF00",
					"2B" : "#00BB00",
					"3A" : "#0000FF",
					"3B" : "#0000CC",
					"4A" : "#666666",
					"4B" : "#999999",
					"5"  : "#ffad40"
	}	
	
	var classes = [ "none", "1a", "1b", "2a", "2b", "3a", "3b", "4a", "4b", "5a", "5b" ];

	// 1: QS3200 	#00FFFF
	// 2: XS6500	#00FF00
	// 3: QS3500	#0000FF
	// 4: PW 7200	#000000
	// 5: PW 4202	#FFAD40
	
	var ntbl  = document.createElement('table');
	ntbl.setAttribute("class", "stringtable");
    ntbl.style.width='100%'
    ntbl.style.border = "1px solid black";
    
    $("#strings").append( '<span>Norddach</span>' );
    // Norddach
    for ( var i = 0; i < 2; i++ ) {
        var tr = ntbl.insertRow(-1);
        for(var j = 0; j < 16; j++){
                var td = tr.insertCell(-1);
                td.appendChild(document.createTextNode(' '))
				td.setAttribute( "id", "td" + pad( j, 2 ) + pad ( i, 2 ) );
				td.style.height = "14px";
				td.style.backgroundColor = colors [ 5 ];
				td.setAttribute( "class", "solarpanel solarpanel-5" );
        }	    
    }
   
    $("#strings").append(ntbl);


    $("#strings").append( "<span>S&uuml;ddach</span><a href=\"/img/pvscheune.jpg\" data-lightbox=\"image-1\"><span class=\"ui-icon ui-icon-newwin helptext\" title=\"Foto\"></span></a>" );

    var stbl  = document.createElement('table');
	stbl.setAttribute("class", "stringtable");
    stbl.style.width='100%'
    stbl.style.border = "1px solid black";

	// Sueddach
    for( var i = 0; i < 8; i++ ){
        var tr = stbl.insertRow(-1);
        for(var j = 0; j < 13; j++){
                var td = tr.insertCell(-1);
                td.appendChild(document.createTextNode(' '))
				td.setAttribute( "id", "td" + pad( j, 2 ) + pad ( i, 2 ) );
				td.style.backgroundColor = colors [ stringsOnInverter [ strings[i][j] ] ];
				td.setAttribute( "class", "solarpanel solarpanel-" + stringsOnInverter [ strings[i][j] ].substr( 0, 1 ) );
				if ( strings[i][j] == 0 ) {
					td.style.borderColor = "#FFFFFF";
				}
        }
    }

    $("#strings").append(stbl);
    
}

function elementResize() {
    var browserWidth = $(window).width();
    if ((browserWidth) < "1024"){
        $("body").addClass("less1024");
        $("body").removeClass("over1024");
        $("#menu").addClass("dynamic");
        $("#menu").removeClass("fix");
    } else {
        $("body").addClass("over1024");
        $("body").removeClass("less1024");
        $("#menu").addClass("fix");
        $("#menu").removeClass("dynamic");
    }
}

function updatePermalink() {
	var view = $("input[name='displaytype']:checked").attr("id");
	var dateText = $.datepicker.formatDate("ymmdd", $('#datepicker').datepicker("getDate"));
	var uri = "http://solar.doerflinger.org?d=";
	switch ( view ) {
		case "daily":
			uri += dateText;
		break;
		case "monthly":
			uri += dateText.substr( 0, 4 );
		break;
		case "yearly":
			uri += dateText.substr( 0, 2 );		
		break;		
	}

	$("#permalink").text( uri );
	$("#permalink").attr("href", uri)
	console.log( view + " " + dateText );

}		
		
		
$(function () {
			
	elementResize();

    $(window).bind("resize", function(){
        elementResize();
    });
	
	 $( "#explain" ).accordion({
      collapsible: true,
      event: "click hoverintent",
      active: false
    });
    
	$('#content').timer({
		delay: 300000,
		repeat: true,
		autostart: $('#live:checked').val(),
		callback: function( index ) {
			drawChart ();
		}
	});
	
	createTable();
	// Thanks: http://www.blogrammierer.de/jquery-ui-datepicker-in-deutscher-sprache/
	$.datepicker.regional['de'] = {clearText: 'löschen', clearStatus: 'aktuelles Datum löschen',
                closeText: 'schließen', closeStatus: 'ohne Änderungen schließen',
                prevText: '<zurück', prevStatus: 'letzten Monat zeigen',
                nextText: 'Vor>', nextStatus: 'nächsten Monat zeigen',
                currentText: 'heute', currentStatus: '',
                monthNames: ['Januar','Februar','März','April','Mai','Juni',
                'Juli','August','September','Oktober','November','Dezember'],
                monthNamesShort: ['Jan','Feb','Mär','Apr','Mai','Jun',
                'Jul','Aug','Sep','Okt','Nov','Dez'],
                monthStatus: 'anderen Monat anzeigen', yearStatus: 'anderes Jahr anzeigen',
                weekHeader: 'Wo', weekStatus: 'Woche des Monats',
                dayNames: ['Sonntag','Montag','Dienstag','Mittwoch','Donnerstag','Freitag','Samstag'],
                dayNamesShort: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayNamesMin: ['So','Mo','Di','Mi','Do','Fr','Sa'],
                dayStatus: 'Setze DD als ersten Wochentag', dateStatus: 'Wähle D, M d',
                dateFormat: 'dd.mm.yy', firstDay: 1, 
                initStatus: 'Wähle ein Datum', isRTL: false};
	$.datepicker.setDefaults($.datepicker.regional['de']);
 
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
      drawChart ( );
    });
	$("#radio").buttonset();
	$("#radio").buttonset().find('label').css('width', '32.6%');
    
    // Get date parameter for a fixed date uri
    // Six digits: day view, yymmdd
    // Four digits: month view, yymm
    // Two digits: year view, yy
    
	var externalDate = "<?php echo $date; ?>";
	if ( ( externalDate.length == 6 ) && ( /^\d+$/.test(externalDate) ) ) {
		$('#datepicker').datepicker("setDate", new Date( '20' + externalDate.substr(0, 2) + '-' + externalDate.substr(2, 2) + '-' + externalDate.substr(4, 2) ) );

	}
	else if ( ( externalDate.length == 4 ) && ( /^\d+$/.test(externalDate) ) ) {
		$('#datepicker').datepicker("setDate", new Date( '20' + externalDate.substr(0, 2) + '-' + externalDate.substr(2, 2) + '-01' ) );
		setRadio( "monthly" );
	}
	else if ( ( externalDate.length == 2 ) && ( /^\d+$/.test(externalDate) ) ) {
		$('#datepicker').datepicker("setDate", new Date( '20' + externalDate.substr(0, 2) + '-01-01' ) );
		setRadio( "yearly" );	
	}

	updatePermalink();
    
	// Initialize Chart
	chart = new Highcharts.Chart( chartOptions );

	// Create an initial chart
	drawChart ( );
	
	
	$(".helptext").tooltip();

	$("#strings-header").tooltip();
	
	
    $("input[name='displaytype']").change( function() {
      drawChart ( );
    });


	
	$("#live").change( function () {
		if ( $('#live:checked').val()  ) {
			$('#content').timer( 'start' );
		}
		else {
			$('#content').timer( 'stop' );
		}
	});
	
	$(document).on('click','.opener.show',function(){
	    $( ".opener, #menu" ).animate({
          left: "+=280"
		  }, 500 );
		  $(this).html('&laquo;');
		  $(this).removeClass('show');
		  $(this).addClass('hide');
    });
	
	$(document).on('click','.opener.hide',function(){
	    $( ".opener, #menu" ).animate({
          left: "-=280"
		  }, 500 );
		  $(this).html('&raquo;');
		  $(this).removeClass('hide');
		  $(this).addClass('show');
    });

});
        
   </script>

</head>
<body>
<!--	<a href="#" id="toggle">Toggle</a>-->
	<div id="menu" class="fix">
		<div id="navigation">
			<div id="datepicker"></div>
			<br />
			<div id="controls" class="ui-widget ui-widget-content ui-helper-clearfix ui-corner-all controls">
				<div id="controls-header" class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all controls-header">
					Ansicht
				</div>
				<div class="format">
				<form>
				<div id="radio">
    <input type="radio" checked="checked" id="daily" name="displaytype">
    <label for="daily">Tag</label>
    
    <input type="radio" id="monthly" name="displaytype">
    <label for="monthly">Monat</label>
    
    <input type="radio" id="yearly" name="displaytype">
    <label for="yearly">Jahr</label>  
				</div>
				</form>
				<!--
					<input type="checkbox" id="monthly" class="filter" /><label for="monthly">Monatswerte</label><span class="ui-icon ui-icon-comment helptext" title="Daten monatsweise anzeigen"></span>
					-->
				</div>
				<div class="format">
					<input type="checkbox" id="diverted" class="filter" /><label for="diverted">Wechselrichter einzeln</label><span class="ui-icon ui-icon-comment helptext" title="Einzelnen Graphen f&uuml;r jeden Wechselrichter anzeigen"></span>
				</div>
				<div class="format">
					<input type="checkbox" id="fixy" class="filter" /><label for="fixy">Y-Achse fixieren</label><span class="ui-icon ui-icon-comment helptext" title="H&ouml;he der Y-Achse f&uuml;r bessere Vergleichbarkeit fixieren" ></span>
				</div>
				<div class="format">
					<input type="checkbox" id="normalize" class="filter" /><label for="normalize">Normalisieren</label><span class="ui-icon ui-icon-comment helptext" title="Graphen auf eine Anlagengr&ouml;sse von 1kW Peak normalisieren (Nur bei Tagesansicht)"></span>
				</div>
				<div class="format">
					<input type="checkbox" id="energy" class="filter" /><label for="energy">Ertrag anzeigen</label><span class="ui-icon ui-icon-comment helptext" title="Verlauf der produzierten Energie am Tag anzeigen" ></span>
				</div>
				<div class="format">
					<input type="checkbox" id="live" class="filter" /><label for="live">Automatisch aktualisieren</label><span class="ui-icon ui-icon-comment helptext" title="Grafik automatisch jede Minute aktualisieren (Daten vom Logger k&ouml;nnten sich seltener aktualisieren" ></span>
				</div>
			</div>
			<br />
			<div id="strings" class="ui-widget ui-widget-content ui-helper-clearfix ui-corner-all controls">
				<div id="strings-header" class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all controls-header">
					Strings<span class="ui-icon ui-icon-comment helptext" title="Darstellung der verschalteten Solarmodule ('Strings') auf den Dachteilen. In der Tagesansicht werden die einzelnen Strings beim ausw&auml;hlen des Graphen des jeweiligen Wechselrichters hervorgehoben. Weisse Felder markieren L&uuml;cken im Modulfeld durch die Gaube, siehe Foto."></span>
				</div>
			</div>
			<br />
			<div id="links" class="ui-widget ui-widget-content ui-helper-clearfix ui-corner-all controls">
				<div id="links-header" class="ui-datepicker-header ui-widget-header ui-helper-clearfix ui-corner-all controls-header">
					Links
				</div>
				<div class="links">
					&copy; Dirk D&ouml;rflinger, <a href="http://www.doerflinger.org" target="_blank">www.doerflinger.org</a>
				</div>
				<div class="links">
					Code on GitHub: <a href="https://github.com/zapalotta/SolarLogger" target="_blank">github.com/zapalotta/SolarLogger</a>
				</div>
				<div class="links">
					Uses <a href="http://ellislab.com/codeigniter" target="_blank">CodeIgniter</a>, <a href="http://jquery.com" target="_blank">jQuery</a> and <a href="http://www.highcharts.com" target="_blank">Highcharts</a>
				</div>
				<div class="links">
				Permalink: <a id="permalink" href="http://solar.doerflinger.org">solar.doerflinger.org</a> 
				</div>
				
				
			</div>
		</div>  <!-- End div navigation -->
	</div>  <!-- End div menu -->
	<a href="javascript:void(0);" class="opener show">&raquo;</a>

	<div id="header">
		<h1>Photovoltaikanlage Scheune Wambach</span></fh1>
	</div>

	<div id="content">
		<div id="chart"></div>
		<div id="explain">
		<h3>Mehr Informationen</h3>
		<div>
		<h4>Kurvendarstellung</h4>
		Die Darstellung als Linie zeigt die elektrische Leistung, die die Solaranlage im Laufe des gew&auml;hlten <b>Tages</b> geliefert hat. Zus&auml;tzlich kann auch der Ertrag (also die produzierte elektrische Energie) angezeigt werden. <br />
		Die rote Kurve (Pac) zeigt die Summe der Leistung aller Anlagenteile, w&auml;hrend die (optional eingeblendeten) bunten Linien den Ertrag der einzelnen Wechselrichter darstellt.<br />
		Die Monatsdarstellung zeigt in Balkenform die elektrische Energie, die pro Tag produziert und ins Stromnetz eingespeist wurde.<br />
		Die Grafik links unten zeigt schematisch den Aufbau der Anlage aus den einzelnen Solarmodulen (&quot;Panels&quot;). Diese Module sind gruppenweise zu sogenannten &quot;Strings&quot; zusammengeschlossen, diese Strings wiederum in meinem Fall in in Zweierpaaren an die sogenannten Wechselrichter angeschlossen. <br />
		Die Wechselrichter sind elektronische Ger&auml;te, welche den von den Solarmodulen gelieferten Strom so anpassen, dass er in das allgemeine Stromnetz fliessen kann. <br />
		Es handelt sich bei den einzelnen Wechselrichtern um unterschiedliche Ger&auml;te mit unterschiedlicher Leistung, weswegen auch teilweise eine unterschiedliche Anzahl an Modulen an die Wechselrichter angeschlossen sind. Um die Leistung der Wechselrichter dennoch vergleichen zu k&ouml;nnen, lassen sich die Kurven normalisieren, jeder Anlagenteil wird so betrachtet, als w&auml;re er genau 1kWp gross. Im idealfall m&uuml;ssten alle normalisierten Kurven deckungsgleich &uuml;bereinander liegen. Der Idealfall wird in der Praxis freilich nicht erreicht, u.a. weil manche Module teilweise verschattet werden (siehe Foto) und auch die Wechselrichter leicht unterschiedlich arbeiten.
		<br />
		Die Orange Kurve zeigt ebenso wie die orangefarbigen Module im Schema links den Anlagenteil auf dem Norddach. Sie erreicht mangels direkter Sonneneinstrahlung nur ca. 2/3 der Leistung (normalisiert) der S&uuml;danlage, dies wird z.B. am 05.08.2013 deutlich.
	 Bei sehr geringer Leistung (Regentag) liefert sie allerdings in etwa gleich viel wie die S&uuml;danlage.
	 	<h4>Balkendarstellung</h4>
	 	Die Balken stellen den Ertrag pro Tag im Verlauf des Monats bzw. pro Monat im Lauf des Jahres dar. Der Ertrag (in kWh, Kilowattstunden) ist die Energie, die produziert und in das elektrische Netz eingespeist wurde, im Prinzip das Selbe wie beim Verbrauch elektrischer Energie durch z.B. ein Haushaltsgerät. Normalisierung w&uuml;rde in dieser Ansicht keinen sinnvollen Informationsgehalt liefern, darum wurde darauf verzichtet.  
		<h4>Technische Daten</h4>
		<dl>
			<dt>Nennleistung gesamt</dt>
			<dd>22,56kWp</dd>
			<dt>Baujahr</dt>
			<dd>2010</dd>
			<dt>Standort</dt>
			<dd>Wambach 9a, D-79692 Kleines Wiesental, <br/>
			N 47° 44' 26.940" E 7° 44' 58.260", 750m NHN</dd>
		</dl> 
		<h5>Südanlage</h5>
		<dl>
			<dt>Solarmodule</dt>
			<dd>17,96kWp: 78 Stück SolarWorld SunModule Plus 230 Mono, 230Wp, 8 Strings</dd>
			<dt>Montagesystem</dt>
			<dd>TRITEC TriRoof Pilotanlage (indach)</dd>
			<dt>Wechselrichter</dt>
			<dd><ul>
				<li>KACO New Energy Powador 7200xi</li>
				<li>Mastervolt XS6500</li>
				<li>Mastervolt QS3500</li>
				<li>Mastervolt QS3200</li>
				</ul>
			</dd>
		</dl>
		<h5>Nordanlage</h5>
		<dl>
			<dt>Solarmodule</dt>
			<dd>4,6kWp: 40 Stück Heliosphera HS115 (micromorph), 115Wp, 10 Strings</dd>
			<dt>Montagesystem</dt>
			<dd>FATH Solar Aufdach</dd>
			<dt>Wechselrichter</dt>
			<dd>KACO New Energy Powador 4002</dd>
		</dl>
		</div>
		
		</div>
	</div>
	
<!-- Piwik -->
<script type="text/javascript">
  var _paq = _paq || [];
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u=(("https:" == document.location.protocol) ? "https" : "http") + "://piwik.doerflinger.org/";
    _paq.push(['setTrackerUrl', u+'piwik.php']);
    _paq.push(['setSiteId', 5]);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0]; g.type='text/javascript';
    g.defer=true; g.async=true; g.src=u+'piwik.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<noscript><p><img src="http://piwik.doerflinger.org/piwik.php?idsite=5" style="border:0;" alt="" /></p></noscript>
<!-- End Piwik Code -->


</body>
</html>