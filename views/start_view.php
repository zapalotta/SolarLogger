<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Welcome to CodeIgniter</title>

   <link rel="stylesheet" href="/css/main.css">

   <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
   <script type="text/javascript" src="/js/highcharts.js"></script>
      
   <script type="text/javascript" src="/js/themes/gray.js"></script>                                                                                    
  

   <!-- 1b) Optional: the exporting module -->
   <script type="text/javascript" src="/js/modules/exporting.js"></script>
         
   <!-- 2. Add the JavaScript to initialize the chart on document ready -->
   <script type="text/javascript">
	      var chart;
$(document).ready(function() {
    chart = new Highcharts.Chart({
      chart: {
	renderTo: 'container',
	    type: 'areaspline'
	    },
	  title: {
	text: 'Tageswerte'
	    },
	  subtitle: {
	text: '<?php echo $date_today; ?>'
	    },
	  xAxis: {
	type: 'datetime',
	    dateTimeLabelFormats: { // don't display the dummy year
	  month: '%e. %b',
	      year: '%b'
	      }
	},
	  yAxis: [{
	title: {
	  text: 'Pac (W)'
	      },
	    min: 0
	      },{
	  title: {
	    text: 'Energie (kWh)'
		},
	      min: 0,
	      opposite: true
	      }],
	  series: [{
	  name: 'Kombinierte Leistung AC',
	      type: 'areaspline',
	      tooltip: {
	formatter: function() {
                                                return '<b>'+ this.series.name +'</b><br/>'+
						  Highcharts.dateFormat('%e. %b', this.x) +': '+ this.y +' m';
	    }
	  },

data: [

<?php
echo $daypowervalues; 
?>
                    ]
	      }, {
	  name: 'Energie',
	      type: 'spline',
	      yAxis: 1,
	      data: [

<?php
echo $dayenergydata; 
?>

		     ]
	      }]
      });


  });


   </script>

</head>
<body>

<div id="container">
	<h1>Photovoltaikanlage Scheune Wambach</h1>

	<div id="body">
   <?php
   echo $currentday;
   ?>

   <div id="container2" style="width: 600px; height: 400px; margin: 0 auto"></div>




	</div>

	<p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>