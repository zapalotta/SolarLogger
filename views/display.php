<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Solaranlage Scheune Wambach</title>

   <link rel="stylesheet" href="/css/display.css">
   <link rel="stylesheet" href="http://code.jquery.com/ui/1.10.2/themes/smoothness/jquery-ui.css" />

   <script type="text/javascript" src="/js/jquery-2.0.0.min.js"></script>
   <script type="text/javascript" src="/js/jqueryui/ui/jquery-ui.js"></script>
   <script type="text/javascript" src="/js/jquery.mtz.monthpicker.js"></script>
   <script type="text/javascript" src="/js/hc/js/highcharts.js"></script>

<script>


   $(function() {
       var dp = $('#datepicker').datepicker({
	 onSelect: function(dateText, inst) { 
	     $('#date').html(dateText); 
	   },
	     changeMonth: true,
	     changeYear: true,
	     onChangeMonthYear: function(year, month, inst) {
	       $('#date').html(month + "/" + year); 
	     }	     
	 });

       var options = {
       chart: {
	 renderTo: 'chart',
	 type: 'spline'
       },
       series: [{}]
       };
    
       var url =  "/index.php/data/getJSON?callback=?";
       $.getJSON(url,  function(data) {
	   options.series[0].data = data;
	   var chart = new Highcharts.Chart(options);
	 });

     });

</script>
</head>
<body>

<div id="container">
   <h1>Photovoltaikanlage Scheune Wambach</h1>
   <div id="body">
     <div id="navigation">
       <div id="datepicker"></div>
     </div>
     <div id="chartwrapper">
   
   <h2 id="date"></h1>
   <div id="chart" style="height: 300px"></div>
   
     </div>
   </div>
   <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>

</body>
</html>