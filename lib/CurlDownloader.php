<?php

/**
 * Class CurlDownloader
 */
class CurlDownloader {
    private $remoteFileName = NULL;
    private $ch = NULL;
    private $headers = array();
    private $response = NULL;
    private $fp = NULL;
    private $debug = FALSE;
    private $fileSize = 0;
    private $tmpPath='';
    private $responseStr=NULL;

    const DEFAULT_FNAME = 'remote.out';

    public function __construct($url) {
        $this->init($url);
    }

    public function toggleDebug() {
        $this->debug = !$this->debug;
    }

    public function init($url) {
        if (!$url)
            throw new InvalidArgumentException("Need a URL");

        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_HEADERFUNCTION,
            array($this, 'headerCallback'));
        curl_setopt($this->ch, CURLOPT_WRITEFUNCTION,
            array($this, 'bodyCallback'));
    }

    public function headerCallback($ch, $string) {
        $len = strlen($string);
        if (!strstr($string, ':')) {
            $this->response = trim($string);
            return $len;
        }
        list($name, $value) = explode(':', $string, 2);
        if (strcasecmp($name, 'Content-Disposition') == 0) {
            $parts = explode(';', $value);
            if (count($parts) > 1) {
                foreach ($parts AS $crumb) {
                    if (strstr($crumb, '=')) {
                        list($pname, $pval) = explode('=', $crumb);
                        $pname = trim($pname);
                        if (strcasecmp($pname, 'filename') == 0) {
                            // Using basename to prevent path injection
                            // in malicious headers.
                            $this->remoteFileName =$this->tmpPath.basename(
                                $this->unquote(trim($pval)));
                            $this->fp = fopen($this->remoteFileName, 'wb');
                        }
                    }
                }
            }
        }

        $this->headers[$name] = trim($value);
        return $len;
    }

    public function bodyCallback($ch, $string) {
        if (!$this->fp) {
            trigger_error("No remote filename received, trying default",
                E_USER_WARNING);
            $this->remoteFileName = self::DEFAULT_FNAME;
            $this->responseStr=$string;
            return 0;
        }else{
            $len = fwrite($this->fp, $string);
            $this->fileSize += $len;
            return $len;
        }
    }

    public function download() {
        $retval = curl_exec($this->ch);
        if ($this->debug)
            var_dump($this->headers);
        fclose($this->fp);
        curl_close($this->ch);
        return $this->fileSize;
    }

    public function getFileName() {
        return $this->remoteFileName;
    }

    private function unquote($string) {
        return str_replace(array("'", '"'), '', $string);
    }

    public function getResponseStr(){
        return $this->responseStr;
    }
}
?>