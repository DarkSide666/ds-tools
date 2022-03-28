# ds-tools

This repository contains various useful tools.
Some of them rely on one another, but others are fully standalone.

## Logger

### Usage
```
// Create Logger instance
$logger = new ds-tools\Logger();

// Multiple levels of logging - success, debug, info, notice, warning, error, critical, alert, emergency
$logger->notice('Download process started');

// Can use emergency() to stop program execution
try {
    // ... something ...
} catch (\Exception $e) {
    $logger->emergency($e->getMessage());
}
```

## CKAN Downloader

### Usage
```
// Create Downloader instance
$downloader = new ds-tools\CKAN\Downloader([
    // CKAN API URL and key. For example, https://data.gov.lv/dati/api/3/
    'api_url' => API_URL,
    'api_key' => API_KEY,

    // Folder where resources (files) will be downloaded
    'download_folder' => TMP_FOLDER,

    // Folder where files will be unzipped
    'unzip_folder' => DATA_FOLDER,
]);

// This is how to download all resources (files) of particular CKAN package
$downloader->download('kadastra-informacijas-sistemas-atverti-telpiskie-dati');

// This is how to download particular resource (file) and unzip it
$files = $downloader->download('valsts-adresu-registra-informacijas-sistemas-atvertie-dati', 'aw_shp.zip', true);
```
