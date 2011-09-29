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
	public function get_token($username, $password, $ip, $sessionId);

	/**
	 * @param
	 */
	public function 
}

