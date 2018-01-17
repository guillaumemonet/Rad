<?php

namespace Rad\Utils;

use ErrorException;

/**
 * @require php ssh2 lib
 * @author Guillaume Monet
 */
class SFTPConnection {

    private $connection;
    private $sftp;
    private $host;

    public function __construct(string $host, int $port = 22) {
        $this->host = $host;
        $this->connection = @ssh2_connect($host, $port) || $this->throwError("Could not connect to $host on port $port.");
    }

    /**
     * 
     * @param string $username
     * @param string $password
     * @throws ErrroException
     * @throws ErrorException
     */
    public function login(string $username, string $password) {
        @ssh2_auth_password($this->connection, $username, $password) || $this->throwError("Could not authenticate with username $username and password $password.");
        $this->sftp = @ssh2_sftp($this->connection) || $this->throwError("Could not initialize SFTP subsystem.");
    }

    /**
     * 
     * @param string $local_file
     * @param string $remote_file
     * @throws ErrorException
     */
    public function uploadFile(string $local_file, string $remote_file) {
        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . "/./in/$remote_file", 'w') || $this->throwError("Could not open file: $remote_file");
        $data_to_send = file_get_contents($local_file) || $this->throwError("Could not open local file: $local_file.");
        fwrite($stream, $data_to_send) || $this->throwError("Could not send data from file: $local_file.");
        fflush($stream);
        fclose($stream);
    }

    /**
     * 
     * @param string $local_file
     * @param string $remote_file
     * @throws ErrorException
     */
    public function downloadFile(string $local_file, string $remote_file) {
        $stream = fopen("ssh2.sftp://" . intval($this->sftp) . "/./$remote_file", 'r') || $this->throwError("Could not open file: $remote_file");
        $local = fopen($local_file, 'w') || $this->throwError("Could not open local file: $local_file.");
        $read = 0;
        $filesize = filesize("ssh2.sftp://" . intval($this->sftp) . "/./$remote_file");
        while (($read < $filesize) && ($buffer = fread($stream, $filesize - $read))) {
            $read += strlen($buffer);
            fwrite($local, $buffer) || $this->throwError("Could not write data to file: $local_file.");
        }
        fflush($local);
        fclose($local);
        fclose($stream);
    }

    /**
     * 
     * @param string $directory
     * @return array
     * @throws ErrorException
     */
    public function listDirectory(string $directory): array {
        $files = [];
        $stream = opendir("ssh2.sftp://" . intval($this->sftp) . "/./$directory");
        if (!$stream) {
            throw new ErrorException("Could not open directory: $directory");
        }
        while (false != ($entry = readdir($stream))) {
            if (!in_array($entry, array(".", ".."))) {
                $files[] = $entry;
            }
        }
        closedir($stream);
        return $files;
    }

    /**
     * 
     * @param string $message
     * @throws ErrorException
     */
    private function throwError(string $message = "") {
        throw new ErrorException($message);
    }

}
