<!DOCTYPE html>
<html lang="en">
<head>
   <meta charset="utf-8">
   <title>Solaranlage Scheune Wambach</title>
   <link rel="stylesheet" href="/css/main.css">
   <script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.6.1/jquery.min.js"></script>
   <script type="text/javascript" src="/js/highcharts.js"></script>
   <script type="text/javascript" src="/js/themes/gray.js"></script>                                                                                    

   <!-- 1b) Optional: the exporting module -->
   <script type="text/javascript" src="/js/modules/exporting.js"></script>
         
   <!-- 2. Add the JavaScript to initialize the chart on document ready -->
   <script type="text/javascript">
      <?php echo $chart; ?> 
   </script>

</head>
<body>
<div id="container">
  <h1>Photovoltaikanlage Scheune Wambach</h1>
  <div id="body">
     <div id="navigation">
       <?php echo $nav; ?>
     </div>  <!-- End div navigation -->
     <div id="chart" style="width: 740px; height: 400px; margin: 0 auto"></div>

  </div>   <!-- End div body -->

  <p class="footer">Page rendered in <strong>{elapsed_time}</strong> seconds</p>
</div>  <!-- End div container -->

</body>
</html>