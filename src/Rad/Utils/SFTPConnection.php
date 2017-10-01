<?php

namespace Rad\Utils;

use Exception;

class SFTPConnection {

    private $connection;
    private $sftp;
    private $host;

    public function __construct($host, $port = 22) {
        $this->host = $host;
        $this->connection = @ssh2_connect($host, $port);
        if (!$this->connection) {
            throw new Exception("Could not connect to $host on port $port.");
        }
    }

    public function login($username, $password) {
        if (!@ssh2_auth_password($this->connection, $username, $password)) {
            throw new Exception("Could not authenticate with username $username and password $password.");
        }

        $this->sftp = @ssh2_sftp($this->connection);
        if (!$this->sftp) {
            throw new Exception("Could not initialize SFTP subsystem.");
        }
    }

    public function uploadFile($local_file, $remote_file) {
        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . "/./in/$remote_file", 'w');

        if (!$stream) {
            throw new Exception("Could not open file: $remote_file");
        }

        $data_to_send = file_get_contents($local_file);
        if ($data_to_send === false) {
            throw new Exception("Could not open local file: $local_file.");
        }

        if (fwrite($stream, $data_to_send) === false) {
            throw new Exception("Could not send data from file: $local_file.");
        }
        fflush($stream);
        fclose($stream);
    }

    public function downloadFile($local_file, $remote_file) {
        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . "/./$remote_file", 'r');
        if (!$stream) {
            throw new Exception("Could not open file: $remote_file");
        }
        $local = fopen($local_file, 'w');
        if (!$local) {
            throw new Exception("Could not open local file: $local_file.");
        }

        $read = 0;
        $filesize = filesize("ssh2.sftp://" . intval($this->sftp) . "/./$remote_file");
        while (($read < $filesize) && ($buffer = fread($stream, $filesize - $read))) {
            $read += strlen($buffer);
            if (fwrite($local, $buffer) === FALSE) {
                throw new Exception("Could not write data to file: $local_file.");
            }
        }
        fflush($local);
        fclose($local);
        fclose($stream);
    }

    public function listDirectory($directory) {
        $files = array();
        $stream = opendir("ssh2.sftp://" . intval($this->sftp) . "/./$directory");
        if (!$stream) {
            throw new Exception("Could not open directory: $directory");
        }
        while (false != ($entry = readdir($stream))) {
            if (!in_array($entry, array(".", ".."))) {
                $files[] = $entry;
            }
        }
        closedir($stream);
        return $files;
    }

}
