<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta name="google" value="notranslate">
    <title>jService</title>
    <link rel="stylesheet" type="text/css" href="<?php echo $this->getPublicPath('styles'); ?>layout.css" />
    <script src="<?php echo $this->getPublicPath('scripts'); ?>jquery-1.11.3.min.js"></script>
    <script src="<?php echo $this->getPublicPath('scripts'); ?>jquery.validate.min.js"></script>	
  </head>

  <body>
  <br />
  <br />
    <p>&nbsp;</p>
    <div class="jService-header">
    </div>
    <form action="<?php echo $this->link('trial/start'); ?>" method="post" class="jService-form">
        <h1>Trial Form 
            <span>Press start to start a new experimental trial.</span>
        </h1> 
        <label>
			<input type="hidden" id="key" name="key" value="3858f62230ac3c915f300c664312c63f">
            <input type="submit" class="button center-btn" value="Start" onclick="return confirm('Are you sure you want to start a new trial.\n\nMake sure all participants are done with the current one, otherwise their data will get lost.\n\nSelect OK to continue.')" />
        </label>    
    </form>

	
  </body>
</html>
