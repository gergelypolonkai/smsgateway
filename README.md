SmsGateway
==========

SmsGateway is a JSON-RPC based SMS Gateway.

Features
--------

* Senders to support SMS sending as many ways as possible
 * GnokiiSender to send using gnokii
 * FileSender to store messages in files
* Authentication backends for authentication purposes
 * DatabaseAuth for a PDO based backend (non-working yet)
 * FileAuth to store users and passwords in a shadow-like file
 * NullAuth to accept everyone without a password
* Logger backends for audit and message logging
 * DatabaseLogger for PDO based logging (non-working yet)
 * FileLogger to log messages to files

Installation
------------

SmsGateway can be installed using [composer](http://getcomposer.org/):

    $ php composer.phar create-project gergelypolonkai/smsgateway


Configuration
-------------

Currently, there are no configurable parts exist, everything is hard-coded.
My plans are:

* File paths for FileSender, FileAuth and FileLogger
* Executable path for GnokiiSender
* Database parameters for DatabaseSender, DatabaseAuth and DatabaseLogger
