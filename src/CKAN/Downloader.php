<?php
declare(strict_types=1);
namespace dsTools\CKAN;

use dsTools\Logger;

/**
 * This class can download one resource or all resources from data.gov.lv into particular folder.
 *
 * @todo Maybe some day we can add progress bar here also https://www.php.net/manual/en/function.stream-notification-callback.php
 */
class Downloader
{
    /** @var string required URL of open data API */
    public $api_url;

    /** @var string optional key of open data API */
    public $api_key;

    /** @var string folder where to download files */
    public $download_folder;

    /** @var string folder where to unzip files. By default, the same as download_folder */
    public $unzip_folder;

    /** @var Logger */
    public $logger;

    /** @var CKAN client */
    protected $ckan;

    /**
     * Use constructor to set or change object properties.
     */
    public function __construct(array $props = [])
    {
        foreach ($props as $k => $v) {
            $this->{$k} = $v;
        }

        if (!$this->logger) {
            $this->logger = new Logger();
        }

        if (!$this->ckan) {

            // validation
            if (!$this->api_url) {
                $this->logger->emergency('API URL not set');
            }

            $this->ckan = new CkanClient($this->api_url, $this->api_key);
        }
    }

    /**
     * Find package by name and download all resource files of this package or only one specific package file.
     *
     * @param string $package_name  Required package name
     * @param string $resource_name Optional resource file name. If omitted, then all package files will be downloaded
     * @param bool   $unzip         If true, then downloaded files will be unzipped
     *
     * @return array Array of paths to downloaded files. If unzip is enabled, then array of paths to folders where files were extracted
     */
    public function download(string $package_name, string $resource_name = null, bool $unzip = false): array
    {
        $this->logger->notice('Downloader / download process started');

        // validation
        if (!$this->download_folder) {
            $this->logger->emergency('Download folder not set');
        }

        // find package
        $this->logger->debug('Searching for package <mark>' . $package_name . '</mark> ...');
        $packages = $this->ckan->package_search('', 'name:' . $package_name);
        $packages = json_decode($packages)->result->results ?? null;
        if (empty($packages)) {
            $this->logger->emergency('Can not find package <mark>' . $package_name . '</mark>');
        }
        if (count($packages) > 1) {
            $this->logger->emergency('Found more than one package with name <mark>' . $package_name . '</mark>');
        }
        $package = $packages[0];
        $this->logger->debug('Found package <mark>' . $package_name . '</mark> with <mark>' . count($package->resources) . '</mark> resource files');

        // download resources
        $this->logger->debug('Starting to download resources');
        $targets = [];
        foreach ($package->resources as $r) {
            $f_name = basename($r->url);
            if ($resource_name && $f_name != $resource_name) {
                continue;
            }

            // downloading
            $target = rtrim($this->download_folder, '/') . '/' . $f_name;
            $this->logger->debug('Downloading: <mark>' . $f_name . '</mark> [' . round($r->size/1000000) . ' Mb] ...');
            $f_from = fopen($r->url, 'r');
            $f_to = fopen($target, 'w');
            stream_copy_to_stream($f_from, $f_to);
            fclose($f_from);
            fclose($f_to);
            $this->logger->debug('Done');

            // unzipping
            if ($unzip) {
                $this->logger->debug('Unzipping: <mark>' . $f_name . '</mark> ...');
                $zip = new \ZipArchive();
                if ($zip->open($target) === true) {
                    $target = rtrim($this->unzip_folder ?? $this->download_folder) . '/' . $f_name . '_extracted';
                    if (!is_dir($target)) {
                        mkdir($target, 0777, true);
                    }
                    $zip->extractTo($target); // folder named the same as archive file
                    $zip->close();
                    $this->logger->debug('Done');
                } else {
                    $this->logger->warning('Failed');
                }
            }

            $targets[] = $target;
        }
        $this->logger->debug('Resources download finished');

        $this->logger->success('Downloader / download process finished');
        return $targets;
    }
}