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
         * @param String $ip
         * @param String $sessionId
         * @param String $token
         */
        public function removeToken($ip, $sessionId, $token);

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
         * @param Integer $senderId
         * @param String  $recipient
         * @param String  $message
         * @param String  $ip
         */
        public function messageLog($senderId, $recipient, $message, $ip);
}

