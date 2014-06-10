<?php



/*
	@author Nic Rosental <nicrosental@gmail.com>
	@copyright Copyright (C) 2010  Nic Rosental
	@license <http://www.gnu.org/licenses/gpl.txt>
	@version 0.1
	
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
*/
  
class pturk {

		
		private $service_name = 'AWSMechanicalTurkRequester';
		private $service_version = '2008-08-02';
		private $secret_access_key = '';
		private $access_key = '';
		private $amazon_url = 'http://mechanicalturk.sandbox.amazonaws.com'; //Sandbox
		//private $amazon_url = 'http://mechanicalturk.amazonaws.com/'; //Production
		
	/**
	 * Insantiate a new PTurk.
	 *
	 * 
	 */
	  
    	function __construct() {
    	    	
	    	if(empty($this->service_name) || empty($this->service_version) || empty($this->secret_access_key) || empty($this->access_key))
	   		{
		   	
	   			$return_value = false;
	   			
	   		}
	   	
		}
	  
	/**
	 * Create a new hit
	 *
	 * @todo add support for worker qualifications
	 * @uses pturk::hmac_sha1 | pturk::generate_signature
	 * @return mixed
	 */
	
    
    function create_hit($parameters){
    		
    		//Optional parameters have default values.
    		
    		$question = $parameters['question'];
    		$title = $parameters['title'];
    		$description = $parameters['description'];
    		$frame_height = (empty($parameters['frame_height'])) ? 400 : $parameters['frame_height']; //Only affects external questions
    		$currency_code = (empty($parameters['currency_code'])) ? 'USD' : $parameters['currency_code']; 
    		$reward = (empty($parameters['reward'])) ? 0 : $parameters['reward']; 
    		$max_assignments = (empty($parameters['max_assignments'])) ? 1 : $parameters['max_assignments']; 
    		$assignment_duration = (empty($parameters['assignment_duration'])) ? 3600 : $parameters['assignment_duration']; 
    		$assignment_lifetime = (empty($parameters['assignment_lifetime'])) ? $assignment_duration : $parameters['assignment_lifetime']; //The default value is equal to the assignment's duration. 
    		$auto_approval = (empty($parameters['auto_approval'])) ? false : $parameters['auto_approval']; 
    		$qualification_reqs = (empty($parameters['qualification_reqs'])) ? false : $parameters['qualification_reqs'];  
    		$keywords = (empty($parameters['tags'])) ? false : $parameters['tags'];
    		$notes = (empty($parameters['notes'])) ? false : $parameters['notes'];
    		
    		//Return false if any of the required parameters are missing
    		if(empty($this->service_name) || empty($this->service_version) || empty($this->secret_access_key) || empty($this->access_key) || empty($question) || empty($title) || empty($description))
    		{
    			$return_value = false;
    			
    		}
    		else //We have everything we need
    		{
    		
    			$timestamp = gmdate('Y-m-d\TH:i:s\\Z');    	
				$operation = 'CreateHIT';
				$signature = $this->generate_signature($this->service_name, $operation, $timestamp, $this->secret_access_key);
				
				if($parameters['external']) //External question should be a URL like some_url.com
				{
					
					$task_url = '<ExternalQuestion xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2006-07-14/ExternalQuestion.xsd">
					  <ExternalURL>'.$question.'</ExternalURL>
					  <FrameHeight>'.$frame_height.'</FrameHeight>
					</ExternalQuestion>';
				
				}
				else //This is a properly formatted question form. For more info see http://docs.amazonwebservices.com/AWSMturkAPI/2008-08-02/index.html?ApiReference_WsdlLocationArticle.html
				{
				
					$task_url = '<QuestionForm xmlns="http://mechanicalturk.amazonaws.com/AWSMechanicalTurkDataSchemas/2005-10-01/QuestionForm.xsd">'.$question.'
				 	</QuestionForm>';
				
				}		
								
		 		// Construct the request
	     		$url = $this->amazon_url.'?Service=AWSMechanicalTurkRequester&AWSAccessKeyId='
	     				 .urlencode($this->access_key)
						 .'&Version='.urlencode($this->service_version)
						 .'&Operation='.urlencode($operation)
				 		 .'&Signature='.urlencode($signature)
						 .'&Timestamp='.urlencode($timestamp)
						 .'&Title='.urlencode($title)
						 .'&Description='.urlencode($description)
						 .'&Reward.1.Amount='.$reward
						 .'&Reward.1.CurrencyCode='.urlencode($currency_code)
						 .'&Question='.urlencode($task_url)
						 .'&MaxAssignments='.$max_assignments
						 .'&AssignmentDurationInSeconds='.$assignment_duration
						 .'&LifetimeInSeconds='.$assignment_lifetime
						 .'&AutoApprovalDelayInSeconds='.$auto_approval
						 .'&Keywords='.urlencode($keywords)
						 .'&RequesterAnnotation='.urlencode($notes);
				 
		
				
				// Make the request
		
				$xml = simplexml_load_file($url);
			
				if ($xml->OperationRequest->Errors) //Something went wrong
				{	
		  		
		  			$return_value = $xml->OperationRequest->Errors->Error;
				
				}
				else //Request created
				{
					$return_value = $xml;

				}
				
	    	}
	    		
			return $return_value;
				
		}
	
    
    /**
     * Encryption routine, hmac_sha1. This one was taken literally from the examples found here http://docs.amazonwebservices.com/AWSMechanicalTurkGettingStartedGuide/2006-10-31/MakingARequest.html
     * @copyright Amazon Mechanical Turk Getting Started Guide (API Version 2006-10-31) Copyright � 2006 Amazon Web Services LLC or its affiliates. All rights reserved. 
     *
     */
    
		protected function hmac_sha1($key, $s) {
		
  			return pack("H*", sha1((str_pad($key, 64, chr(0x00)) ^ (str_repeat(chr(0x5c), 64))) 
  		    .pack("H*", sha1((str_pad($key, 64, chr(0x00)) ^ (str_repeat(chr(0x36), 64))) . $s))));
  		    
		}


	/**
	 * Generate signature. This one was taken literally from the examples found here http://docs.amazonwebservices.com/AWSMechanicalTurkGettingStartedGuide/2006-10-31/MakingARequest.html
	 * @copyright Amazon Mechanical Turk Getting Started Guide (API Version 2006-10-31) Copyright � 2006 Amazon Web Services LLC or its affiliates. All rights reserved. 
	 *
	 */
	
		protected function generate_signature($service_name, $operation, $timestamp, $secret_access_key) {
		
  			$string_to_encode = $service_name . $operation . $timestamp;
  			
			$hmac = $this->hmac_sha1($secret_access_key, $string_to_encode);
  			$signature = base64_encode($hmac);
  
  			return $signature;
		}


}

?>
