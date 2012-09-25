SmsGateway
==========

SmsGateway is a JSON-RPC based SMS Gateway.

Features
--------

* Senders to support SMS sending as many ways as possible
 * Currently only Gnokii is supported, but with SenderInterface, anyone can
write a new one.
* Authentication backends for authentication purposes
 * DatabaseAuth for a PDO based backend
 * NullAuth to accept everyone without a password
* Logger backends for audit and message logging
 * DatabaseLogger for PDO based logging
