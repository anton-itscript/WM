<?php

/**
 * Class FtpClient
 *
 *
$errors = $ftpClient->connect($destination->destination_ip,$destination->destination_ip_port)
                    ->login($destination->destination_ip_user,$destination->destination_ip_password)
                    ->setFolder($destination->destination_ip_folder)
                    ->openLocalFile($file_path)
                    ->upload($file_name)
                    ->closeLocalFile()
                    ->getErrors();
 */
class FtpClient
{
    protected $ip;
    protected $port;
    protected $folder;
    protected $username;
    protected $password;
    protected $mode_ftp;
    protected $mode_file;

    protected $errors;
    protected $logined;
    protected $conn_id;
    protected $fp;

    public function __construct()
    {
        $this->setModeAscii();
        $this->logined      =   false;
        $this->errors       =   array();
        $this->conn_id      =   false;
        return $this;
    }

    public function connect($ip,$port=21)
    {
        $this->ip           =   $ip;
        $this->port         =   $port;
        $this->conn_id      = @ftp_connect($this->ip, $this->port);
        if ($this->conn_id===false) {
            $this->errors[] = 'Connection with FTP '.$this->ip.':'.$this->port.' was failed';
        }
        return $this;
    }

    public function login($username,$password)
    {
        $this->username     =   $username;
        $this->password     =   $password;

        if ($this->conn_id!==false) {
            $this->logined = @ftp_login($this->conn_id, $this->username, $this->password);

            if ($this->logined) {

            }  else {

                $this->errors[] = 'Connection with FTP ' . $this->ip . ':' . $this->port . ' was failed with given login (' . $this->username . ') & password';

            }

        }

        return $this;
    }

    public function setFolder($folder)
    {
        $this->folder = $folder;

        if ($this->conn_id!==false && $this->logined && !empty($this->folder)) {
            $result = @ftp_chdir($this->conn_id, $this->folder);
            if ($result) {

            } else {
                $this->errors[] = 'Can not change ftp directory to ' . $this->folder;
            }

        }

        return $this;
    }


    public function setModeBinary()
    {
        $this->mode_ftp = FTP_BINARY;
        $this->mode_file = 'rb' ;
        return $this;
    }


    public function setModeAscii()
    {
        $this->mode_ftp = FTP_ASCII;
        $this->mode_file = 'r' ;
        return $this;
    }


    public function openLocalFile($file_path)
    {
        $this->fp = @fopen($file_path, $this->mode_file);

        if (!$this->fp) {
            $this->errors[] = 'Can not open stream to copy file to '. $file_path;
        }
        return $this;
    }

    public function closeLocalFile()
    {
        if ($this->fp) {
            @fclose($this->fp);
        }
        return $this;
    }

    public function upload($file_name)
    {

        if ($this->fp !== false && $this->conn_id!==false && $this->logined)
        {
            // try to upload $file
            $res = @ftp_pasv($this->conn_id, true);

            if ($res === true)
            {
                if (!@ftp_fput($this->conn_id, $file_name, $this->fp, $this->mode_ftp))
                {
                    $this->errors[] = 'There was a problem with uploading file '. $file_name .' to ftp ' . $this->ip . ':' . $this->port . '.';
                }
            }
            else
            {
                $this->errors[] = 'Can not set passive mode';
            }

        }

        return $this;
    }


    public function getErrors()
    {
        return $this->errors;
    }



}

?>