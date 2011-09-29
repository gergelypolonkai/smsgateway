<?php
interface gatewayBackend
{
	/**
	 *
	 * @param String $username
	 * @param String $password
	 * @param String $ip
	 * @param String $sessionId
	 * @return String $token
	 */
	public function getToken($username, $password, $ip, $sessionId);
        
        /**
         * 
         * @param String $token
         * @param String $sessionId
         * @param String $ip
         * @return Boolean
         */
        public function checkToken($token, $sessionId, $ip);
        
        /**
         * removeToken() Remove a logged out user's token
         * 
         * @param String $token
         */
        public function removeToken($token);

	/**
         * sendSMS()
         * 
         * Send SMS message to recipient's phone number
	 * @param String $token
         * @param String $recipient
         * @param String $message
         * @param Array $passwordLocations
         * @return Boolean
	 */
	public function sendSMS($token, $recipient, $message, $passwordLocations);
        
        /**
         * auditLog() Log audit messages
         * 
         * @param String $ip
         * @param String $event
         * @param String $message
         */
        public function auditLog($ip, $event, $message);
        
        /**
         * messageLog() Log sent messages
         * 
         * @param String $recipient
         * @param String $message
         * @param String $ip
         */
        public function messageLog($recipient, $message, $ip);
}

