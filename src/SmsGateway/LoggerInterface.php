<?php
namespace SmsGateway;

interface LoggerInterface
{
    const LOG_AUDIT_LOGIN = 1;

    /**
     * Log an audit event
     *
     * @param  integer $type     The message type
     * @param  string  $username The user this audit log connects to. Can be
     *                           NULL if the user is unauthenticated
     * @param  string  $message  The audit message
     * @return boolean           TRUE if the message was saved, FALSE otherwise
     */
    public function auditLog($type, $username, $message);

    /**
     * Log a sent message
     *
     * @param string  $username  The username who sent this message
     * @param string  $recipient The recipient of the message
     * @param string  $message   The message itself
     */
    public function messageLog($username, $recipient, $message, array $passwordLocations);
}