<?php
require_once 'smsBackend.php';
class gnokiiSMSBackend implements smsBackend
{
    protected $gnokii_path = '/usr/bin/gnokii';

    public function __construct($gnokii_path = null)
    {
        if ($gnokii_path !== null)
        {
            $this->gnokii_path = $gnokii_path;
        }

        if (!is_executable($this->gnokii_path))
        {
            throw new Exception('Gnokii executable not found (should be at ' . $this->gnokii_path . ')');
        }
    }

    /**
     * sendSMS() Sends an SMS message to the given recipient
     *
     * @param String $recipient
     * @param String $message
     * @return Boolean
     */
    public function sendSMS($recipient, $message)
    {
        $descriptors = array(
            0 => array('pipe', 'r'),
            1 => array('pipe', 'w'),
            2 => array('pipe', 'w'),
        );
        $cmd = escapeshellcmd($this->gnokii_path) . ' --sendsms ' . escapeshellarg($recipient);
        $process = proc_open($cmd, $descriptors, $pipes);
        if (is_resource($process))
        {
            fwrite($pipes[0], $message);
            fclose($pipes[0]);

            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);

            $return_value = proc_close($process);
            if ($return_value != 0)
            {
                throw new Exception('Unable to send SMS: ' . $stderr . '; ' . $stdout);
            }
        }
        else
        {
            throw new Exception('Unable to send SMS: cannot start gnokii');
        }
    }
}

