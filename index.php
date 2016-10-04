<?php
ob_start();

// Start Session 
session_start();

//Include API autoloader
require_once __DIR__ . '/vendor/autoload.php';
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>Gmail Template</title>

    <!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="container">	
	<div class="row">
		<div class="col-md-12">
			<h1 class="text-center">Welcome to GMail App</h1>
			<hr>
		</div>
	</div>
  </div>
<?php

// Create Google Client
$client = new Google_Client();
$client->setClientId('381092800085-prviiv8lajhram7j2bm9omltls9f0m7j.apps.googleusercontent.com');
$client->setClientSecret('NtSRYuoQYZWPtsKg8gtu6DU-');
$client->setRedirectUri('http://localhost:8888/gmail/');
$client->addScope('https://mail.google.com/');

//Create Google Service
$service = new Google_Service_Gmail($client);

if(isset($_REQUEST['logout'])){
	unset($_SESSION['access_token']);
}

// Check if there is an auth token 
if(isset($_GET['code']))
{
	$client->authenticate($_GET['code']);
	$_SESSION['access_token'] = $client->getAccessToken();
	$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
	header('Location: ' . filter_var($url, FILTER_VALIDATE_URL));
}

// Check for access token in session 
if(isset($_SESSION['access_token']))
{
	$client->setAccessToken($_SESSION['access_token']);
} else 
{
	$loginUrl = $client->createAuthUrl();
	echo '<div class="container">';
		echo '<div class="row"';
			echo '<div="col-md-12">';
				echo '<a href="' . $loginUrl . '" class="btn btn-primary center-block"> Click here to login</a>';
			echo '</div>';
		echo '</div>';
	echo '</div>';
}
 
// Check if we have an access token ready for API Call
try
{
    if(isset($_SESSION['access_token']) && $client->getAccessToken())
    {
        //make API calls
        $messages = $service->users_messages->listUsersMessages('me',['maxResults'=> 5, 'labelIds'=> 'INBOX']);

        foreach ($messages as $message) 
        {
            
           $message = $service->users_messages->get('me', $message->getId());
           // var_dump($message);

           echo '<div class="container">';
           		// Delivered to:
           		echo '<div class="row"';
           			echo '<p> <b>Delivered :</b> ' . $message['payload']['headers'][0]['value'] . '</p>';
           		echo '</div>';
           		//from
           		echo '<div class="row"';
           			echo '<p> <b>From :</b> ' . $message['payload']['headers'][9]['value'] . '</p>';
           		echo '</div>';

           		// Subject Line
	           	echo '<div class="row"';
	           		echo '<p> <b>Subject:</b> ' . $message['snippet'] . '</p>';
	           	echo '</div>';

	           	// Body 
	           	echo '<div class="row"';
           			echo '<p> <b>Body :</b> ' .  utf8_encode(base64_decode($message['payload']['parts'][0]['body']['data'])) . '</p>';
           		echo '</div>';

           		echo '<hr>';
           echo '</div>';
           
        }

        return $messages;
    }
}
catch (Google_Auth_Exception $e)
{
	echo 'Looks like you dont have an access token or its expired';
}
?>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
  </body>
</html>





