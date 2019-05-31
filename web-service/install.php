<?php
/**
* +---------------------------------------------------------------------------+
* | Copyright (c) 2015, Jort de Vreeze                                        |
* | All rights reserved.                                                      |
* |                                                                           |
* | Redistribution and use in source and binary forms, with or without        |
* | modification, are not permitted.                                          |
* +---------------------------------------------------------------------------+
* | jService 1.0                                                              |
* +---------------------------------------------------------------------------+
* | install.php                                                               |
* +---------------------------------------------------------------------------+
* | Author: Jort de Vreeze <j.devreeze@iwm-tuebingen.de>                      |
* +---------------------------------------------------------------------------+
*/

/**
 * Determine installation process.
 */
$method = filter_input(INPUT_SERVER, 'REQUEST_METHOD');
if ($method == 'POST') {
    
    $host = filter_input(INPUT_POST, 'host');
    $username = filter_input(INPUT_POST, 'username');
    $dbpassword = filter_input(INPUT_POST, 'dbpassword1');
    $dbname = filter_input(INPUT_POST, 'dbname');
    
    $number = filter_input(INPUT_POST, 'number');
    $max = filter_input(INPUT_POST, 'max');
    $range = filter_input(INPUT_POST, 'range');
	
	$apikey = md5(time());
    
    if (null !== $username && 
        null !== $dbpassword &&
        null !== $dbname &&
        null === $max && 
        null === $range) {
    
        if (null === $host || empty($host)) {
            $host = 'localhost';
        }
     
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta name="google" value="notranslate">
    <title>jService Installation</title>
    <link rel="stylesheet" type="text/css" href="public/styles/layout.css" />
    <script src="public/js/jquery-1.11.3.min.js"></script>
    <script src="public/js/jquery.validate.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
            $(".jService-form").validate({
                rules: {                   
                   max: {
                        required: true,
                        digits: true
                    },
                    range: {
                        required: true,
                        digits: true
                    },
                    apikey: {
                        required: true
                    }
                },
                messages: {
                    max: "Please enter the sample size for each condition.",
                    name: "Please enter the range of the variable used to match participants.",
					apikey: "Please enter a valid API key."
                }
            });
            
        });
    </script>
  </head>

  <body>
    <p>&nbsp;</p>
    <div class="jService-header"></div>
    <form action="install.php" method="post" class="jService-form">
        <h1>Step 2 - Add Experiment Details 
            <span>Please fill all the texts in the fields.</span>
        </h1>
        <label>
            <span>Condition Size :</span>
            <input id="max" type="text" name="max" placeholder="Enter the sample size for each condition" />
        </label>
        <label>
            <span>Variable Range :</span>
            <input id="range" type="text" name="range" placeholder="Enter the range of the variable used to match participants" />
        </label>
		<label>
            <span>Default API key :</span>
            <input id="apikey" type="text" name="apikey" value="<?php echo $apikey; ?>" />
        </label>		
        <input id="host" type="hidden" name="host" value="<?php echo $host; ?>" />
        <input id="username" type="hidden" name="username" value="<?php echo $username; ?>" />
        <input id="dbpassword1" type="hidden" name="dbpassword1" value="<?php echo $dbpassword; ?>" />
        <input id="dbname" type="hidden" name="dbname" value="<?php echo $dbname; ?>" />
         <label>
            <span>&nbsp;</span> 
            <input type="submit" class="button" value="Finish" />
        </label>    
    </form>
  </body>
</html>
            
<?php
    } else {        
        if (null !== $host &&
            null !== $username && 
            null !== $dbpassword &&
            null !== $dbname &&
			null !== $max &&
            null !== $range && 
            null !== $apikey) {
            
            /**
             * Create configuration file
             */ 
            if (!$handle = fopen('settings.ini', 'w')) {
                echo "Cannot create the configuration file";
                exit;
            }    

            fwrite($handle, "[model]\n");
            fwrite($handle, sprintf("host='%s'\n", $host));
            fwrite($handle, sprintf("username='%s'\n", $username));
            fwrite($handle, sprintf("password='%s'\n", $dbpassword));
            fwrite($handle, sprintf("dbname='%s'\n", $dbname));
			
			fwrite($handle, "[secret_keys]\n");
			fwrite($handle, sprintf("trial='%s'\n", $apikey));
			
			fwrite($handle, "[conditions]\n");
			fwrite($handle, sprintf("number='%s'\n", 4));
			fwrite($handle, sprintf("max='%s'\n", $max));
			fwrite($handle, sprintf("range='%s'\n", $range));

            fclose($handle);

            /**
             * Create database tables
             */
            $connection = @new mysqli($host, $username, $dbpassword);

            if (!$connection) {
                die('Connect Error: ' . mysqli_connect_error());
            }

            if (false === mysqli_select_db($connection, $dbname)) {     
                $connection->query(
                    sprintf("CREATE DATABASE %s", $dbname)  
                );
                mysqli_select_db($connection, $dbname);
            }

            $sql[] = 'CREATE TABLE IF NOT EXISTS trial ( '.
                'id INT(11) NOT NULL AUTO_INCREMENT, '.
                'timestamp datetime NOT NULL, '.
                'active tinyint(1) NOT NULL, '.
                'PRIMARY KEY ( id ))';
            $sql[] = 'CREATE TABLE IF NOT EXISTS subject ( '.
                'id INT(11) NOT NULL AUTO_INCREMENT, '.
                'trial_id INT(11) NOT NULL, '.
                'name VARCHAR(255) NOT NULL, '.
                'timestamp datetime NOT NULL, '.
                'assigned tinyint(1) NOT NULL, '.
                'value INT(11) NOT NULL, '.
                'c INT(11) NOT NULL, '.
				'ready tinyint(1) NOT NULL, '.
                'PRIMARY KEY ( id ))';
            $sql[] = 'CREATE TABLE IF NOT EXISTS dyad ( '.
                'id INT(11) NOT NULL AUTO_INCREMENT, '.
                'trial_id INT(11) NOT NULL, '.
                'session VARCHAR(255) NOT NULL, '.
                'l INT(11) NOT NULL, '.
                'r INT(11) NOT NULL, '.
                'PRIMARY KEY ( id ))';

            foreach ($sql as $query) {
                if($connection->query(strval($query)) === false) {
                    echo "Cannot configure the database";
                    exit;
                }
            }
            
            
            if($connection->query(strval($query)) === false) {
                echo "Cannot configure the database";
                exit;
            }

            
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta name="google" value="notranslate">
    <title>jService Installation</title>
    <link rel="stylesheet" type="text/css" href="public/styles/layout.css" />
    <script src="public/js/jquery-1.11.3.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
            window.setTimeout(function() {
                window.location = 'index.php';
            }, 5000);
            
        });
    </script>
  </head>

  <body>
    <p>&nbsp;</p>
    <div class="jService-header">
    </div>
    <form action="install.php" method="post" class="jService-form">
        <h1>Finished 
            <span>The installation is completed</span>
        </h1>
        <p>You are being redirected to the main page in five seconds</p>
    </form>
  </body>
</html>

<?php
            unlink(__FILE__);

        } else {
            echo "An unknown error occured. The installation is not able to continue.";
            exit;
        }
    }

} else {
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" 
  "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta charset="UTF-8">
    <meta name="google" value="notranslate">
    <title>jService Installation</title>
    <link rel="stylesheet" type="text/css" href="public/styles/layout.css" />
    <script src="public/js/jquery-1.11.3.min.js"></script>
    <script src="public/js/jquery.validate.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            
            $(".jService-form").validate({
                rules: {
                    dbname: "required",
                    username: "required",
                    dbpassword1: {
                        required: true,
                        minlength: 5
                    },
                    dbpassword2: {
                        equalTo: "#dbpassword1"
                    }
                },
                messages: {
                    dbname: "Please enter a valid database name.",
                    username: "Please enter a valid username.",
                    dbpassword1: {
                        required: "Please provide a valid password.",
                        minlength: "Your password must be at least 5 characters long."
                    },
                    dbpassword2: {
                        required: "Please provide a valid password.",
                        minlength: "Your password must be at least 5 characters long.",
                        equalTo: "Your passwords must match."
                    }                    
                }
            });
            
        });
    </script>
  </head>

  <body>
    <p>&nbsp;</p>
    <div class="jService-header">
    </div>
    <form action="install.php" method="post" class="jService-form">
        <h1>Step 1 - Configure Database 
            <span>Please fill all the texts in the fields.</span>
        </h1>
        <label>
            <span>Hostname :</span>
            <input id="host" type="text" name="host" placeholder="Enter the hostname" />
        </label>
        <label>
            <span>Database Name :</span>
            <input id="dbname" type="text" name="dbname" placeholder="Enter the database name" />
        </label>
        <label>
            <span>Username :</span>
            <input id="username" type="text" name="username" placeholder="Enter your username" />
        </label>
        <label>
            <span>Password :</span>
            <input id="dbpassword1" type="password" name="dbpassword1" placeholder="Enter your password" />
        </label>
        <label>
            <span>Confirm Password :</span>
            <input id="dbpassword2" type="password" name="dbpassword2" placeholder="Confirm your password" />
        </label>

         <label>
            <span>&nbsp;</span> 
            <input type="submit" class="button" value="Next" />
        </label>    
    </form>
  </body>
</html>
<?php } ?>
