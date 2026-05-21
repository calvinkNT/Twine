<?php
require_once "_config.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <title>Twine</title>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap2@2.3.2/docs/assets/css/bootstrap.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap2@2.3.2/docs/assets/css/bootstrap-responsive.css">

  <style>
  	body {
  		padding-top: 0.5em;
  	}
  	.container {
  		max-width: 500px;
  	}
  </style>
</head>
<body>

  <div class="container">

  	<h1>Welcome to Twine!</h1>

  	<p>In order to connect to this instance:</p>
  	<ol>
	<li>Download VineRedirect from Cydia repository 'cydia.bag-xml.com'</li>
	<li>Go to settings</li>
	<li>Find the 'VineRedirect' panel</li>
	<li>Write <code>https://<?php echo $_SERVER['HTTP_HOST'] ?></code></li>
	<li>Restart the Vine app</li>
	</ol>
    <hr>

    <?php /* Do not remove this footer, as it is required by the license. You may modify other parts of the page.*/ ?>
    <footer>
      <p>Copyright &copy; 2026 calvink19 &middot; <a href="https://github.com/calvinkNT/Twine" target="_blank">Original Source Code</a> &middot; <a href="mailto:<?php echo $emailForDmca; ?>">DMCA Takedown</a></p>
</p>
    </footer>

  </div>

</body>
</html>