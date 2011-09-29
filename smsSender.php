<?php
class smsSender
{
	protected $sessionId = null;

	public function __construct($backend, $sessionId)
	{
		$this->sessionId = $sessionId;
	}

	public function login($username, $password)
	{
		/*
		if (valid_user($username, $password))
		{
			$token = generate_token($ip, $session_id, $token, $start_time);
			if ($token)
			{
				audit_log('Successful login by $username from $ip');
				return $token;
			}
			else
			{
				audit_log('Could not create token for $username at $ip');
				return 'Authentication failed. Reason: Internal Server Error';
			}
		}
		else
		{
			audit_log('Unsuccessful login by $username from $ip');
			return 'Authentication failed. Reason: Bad username or password';
		}
		*/
		return array('username' => $username, 'password' => $password, 'session-id' => $this->sessionId);
	}

	public function send($token, $recipient, $message, $passwordLocations)
	{
		/*
		if (valid_token($token)
		{
			if (send_sms($recipient, $message))
			{
				audit_log('Successful message sending by $token->username at $ip');
				message_log('$message successfully sent to $recipient');
			}
			else
			{
				audit_log('Message sending failed for $token->username at $ip');
			}
		}
		else
		{
			audit_log('Message sending attempt from $ip with invalid token');
			throw new Exception('Authentication failed. Reason: Invalid Token');
		}
		*/
		return array('token' => $token, 'recipient' => $recipient, 'message' => $message, 'password-locations' => $passwordLocations, 'session-id' => $this->sessionId);
	}

	public function logout($token)
	{
		/*
		delete_token($token);
		audit_log('$token->username logged out at $ip');
		*/
		return 'success';
	}
}

