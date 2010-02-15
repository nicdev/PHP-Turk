<?php

	//This is just to try out your set up. Make sure you have the correct
	//values for $service_name, $service_version, $secret_access_key and $access_key.
	
	require_once('pturk.php');
	
	$turk = new pturk;
	
	$parameters = Array(	
							'question' => 'some_url.com',
							'title' => 'The test hit',
							'description' => 'This is a test hit',
							'external' => true
						);
	
	$new_ext_hit = $turk->create_hit($parameters);
	
	if($new_ext_hit) //The result will be a successful HIT creation or an error from Amazon. The class is working.
	{
		print_r($new_ext_hit);
	
	}
	else //The class hasn't been set up correctly.
	{
		echo 'FAIL';
	}
	
	 

?>
  		