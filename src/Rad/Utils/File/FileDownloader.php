<?php

/**
 * @license http://www.opensource.org/licenses/mit-license.php MIT (see the LICENSE file)
 * @author Guillaume Monet
 * @link https://github.com/guillaumemonet/Rad
 * @package Rad
 */

namespace Rad\Utils\File;

use Rad\Log\Log;

/**
 * Description of Downloader
 *
 * @author Guillaume Monet
 */
class FileDownloader {

    /**
     * 
     * @param array $files
     * @param bool $overwrite
     */
    public function downloadMulti(array $files, bool $overwrite = false) {

        $filesChunck = array_chunk($files, 1, true);
        foreach ($filesChunck as $chunk) {
            $this->fetchAndProcessUrls($chunk, function ($destinationFile, $content) {
                file_put_contents($destinationFile, $content);
            }, $overwrite);
        }
    }

    /**
     * 
     * @param string $originalFile
     * @param string $destinationFile
     * @param bool $overwrite
     */
    public function download(string $originalFile, string $destinationFile, bool $overwrite = false) {
        if ($overwrite || !file_exists($destinationFile)) {
            file_put_contents($destinationFile, file_get_contents($originalFile));
        }
    }

    /**
     * 
     * @param array $urls
     * @param callable $f
     * @param bool $overwrite
     */
    private function fetchAndProcessUrls(array $urls, callable $f, bool $overwrite) {
        $multi = curl_multi_init();
        $reqs  = [];
        array_walk($urls, function ($destinationFile, $originFile) use (&$reqs, $multi, $overwrite) {
            if ($overwrite || !file_exists($destinationFile)) {
                $reqs[$destinationFile] = $this->buildCurl($multi, $originFile);
            }
        });
        $this->waitForExec($multi);
        array_walk($reqs, function ($req, $destinationFile)use ($multi, $f) {
            $f($destinationFile, curl_multi_getcontent($req));
            curl_multi_remove_handle($multi, $req);
        });
        curl_multi_close($multi);
    }

    /**
     * 
     * @param type $multi
     * @param type $originFile
     * @return type
     */
    private function buildCurl($multi, $originFile) {
        Log::getHandler()->debug($originFile);
        $req = curl_init();
        curl_setopt($req, CURLOPT_URL, $originFile);
        curl_setopt($req, CURLOPT_HEADER, false);
        curl_setopt($req, CURLOPT_VERBOSE, false);
        curl_setopt($req, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($req, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($req, CURLOPT_SSL_VERIFYPEER, 0);
        curl_multi_add_handle($multi, $req);
        return $req;
    }

    /**
     * 
     * @param type $multi
     */
    private function waitForExec($multi) {
        $active = null;
        do {
            $mrc = curl_multi_exec($multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($multi) != -1) {
                do {
                    $mrc = curl_multi_exec($multi, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            }
        }
    }

}
