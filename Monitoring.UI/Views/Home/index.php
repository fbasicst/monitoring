<?php
	require_once('../../BundleConfig.php');
?>
<html ng-app="webApp">
    <meta charset="utf-8">
    <head>
		<title>MonitoringNZJZ</title>
		<link href="http://fonts.googleapis.com/css?family=Open+Sans:700,600,400" rel="stylesheet" type="text/css">
		<?php
			BundleConfig::GenerateStyleBundles(5.0);
		?>
    </head>
    <body>
		<section ui-view></section>    
    </body>
	<?php
		BundleConfig::GenerateScriptBundles(5.0);
	?>
</html>