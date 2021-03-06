<?php

/**
 * @file
 * Contains the main purging functionality and error handling
 */

/**
 * Purges urls from reverse proxy caches
 *
 * @param $purge_urls
 *   Array of urls to remove from the proxy cache using the http purge method.
 *
 * @return
 *   Array of urls and their http status codes after purging.
 */
function purge_urls($purge_urls) {
  // Populate the proxy array
  $drecords=dns_get_record('ftwcache.nyi.net');
  $proxy_urls = array();
  foreach ($drecords as $record) {
        if ($record['type'] == 'A' && $record['ip'])
                $proxy_urls[] = 'http://' . $record['ip'] . ':8080';
  }
  $purge_requests = array();
  $current_purge_request = 0;

  // Find out the url parts we need from the urls to be purged
  foreach ($purge_urls as $purge_url) {
    $purge_url_parts = parse_url($purge_url);
   $purge_url_parts['host'] = variable_get('purge_domain', '');
   if ($purge_url_parts['host'] == '') {
	// Override with default domain name if not set - the default
  	$dmatch=array();
  	preg_match('/(https?:..)?(www.)?(?P<domain>.*)\/?/',$GLOBALS['base_url'],$dmatch);

	$purge_url_parts['host'] = $dmatch['domain'];
   }
    if (isset($purge_url_parts['host'])) {
      $purge_url_host = $purge_url_parts['host'];
      // Add portnames to the host if any are set
      if (array_key_exists('port', $purge_url_parts)) {
        $purge_url_host = $purge_url_host . ":" . $purge_url_parts['port'];
      }
   }

    // Process all urls for each proxy
    foreach ($proxy_urls as $proxy_url) {
      // Add url and proxy url to the array for later processing
      $purge_requests[$current_purge_request]['purge_url'] = $purge_url;
      $purge_requests[$current_purge_request]['proxy_url'] = $proxy_url;

      // Select which method to use
      $proxy_url_parts = parse_url($proxy_url);
      if (array_key_exists('query', $proxy_url_parts)) {
        if (strstr($proxy_url_parts['query'], 'purge_method=get')) {
          $method = 'get';
        }
        if (strstr($proxy_url_parts['query'], 'purge_method=ah')) {
          $method = 'ah';
        }

      }
      else {
        $method = 'purge';
      }

      // The default PURGE method. Native to Squid and configurable in Varnish and Nginx
      if ($method == 'purge') {
        // Make it a PURGE request (not GET or POST)
        $purge_requests[$current_purge_request]['request_method'] = 'PURGE';
        // Construct a new url
        $proxy_url_base = '';
        if (isset($proxy_url_parts['host'])) {
         $proxy_url_base = $proxy_url_parts['scheme'] . "://" . $proxy_url_parts['host'];
         if (array_key_exists('port', $proxy_url_parts)) {
           $proxy_url_base = $proxy_url_base . ":" . $proxy_url_parts['port'];
         }
        }
        // Construct a new path retaining the url query string (#1299776 by djboddydrake)
        $purge_path = array_key_exists('query', $purge_url_parts) ? $purge_url_parts['path']
          . '?' . $purge_url_parts['query'] : $purge_url_parts['path'];
        $purge_requests[$current_purge_request]['purge_url'] = $proxy_url_base . $purge_path;
        // Set the host header to the sites hostname
        if (isset($purge_url_host)) {
          $purge_requests[$current_purge_request]['headers'] = array("Host: " . $purge_url_host);
        }
        else {
          $base_url_parts = parse_url($base_url);
          $purge_url_host = $base_url_parts['host'];
          $purge_requests[$current_purge_request]['headers'] = array("Host: " . $purge_url_host);
        }
      }

      // The GET method, native suport on nginx
      elseif ($method == 'get') {
        $purge_requests[$current_purge_request]['request_method'] = 'GET';
        // Construct a new url
        $proxy_url_base = $proxy_url_parts['scheme'] . "://" . $proxy_url_parts['host'];
        if (array_key_exists('port', $proxy_url_parts)) {
          $proxy_url_base = $proxy_url_base . ":" . $proxy_url_parts['port'];
        }
        // Construct a new path retaining the proxy url path (needed for nginx/get methods)
        if (array_key_exists('path', $proxy_url_parts)) {
          $purge_path = '/' . trim($proxy_url_parts['path'], '/') . '/' . ltrim($purge_url_parts['path'], '/');
        }
        else {
          $purge_path = $purge_url_parts['path'];
        }
        // Check for a query and add it
        if (array_key_exists('query', $purge_url_parts)) {
          $purge_path = $purge_path . '?' . $purge_url_parts['query'];
        }
        $purge_requests[$current_purge_request]['purge_url'] = $proxy_url_base . $purge_path;
        //  Set the host header to the sites hostname
        $purge_requests[$current_purge_request]['headers'] = array("Host: " . $purge_url_host);
      }

      // The AH (Acquia Hosting) option.
      elseif ($method == 'ah') {
        // Make it a PURGE request (not GET or POST)
        $purge_requests[$current_purge_request]['request_method'] = 'PURGE';
        // Construct a new url
        $proxy_url_base = $proxy_url_parts['scheme'] . "://" . $proxy_url_parts['host'];
        // Construct a new path retaining the url query string (#1299776 by djboddydrake)
        $purge_path = array_key_exists('query', $purge_url_parts) ? $purge_url_parts['path']
          . '?' . $purge_url_parts['query'] : $purge_url_parts['path'];
        $purge_requests[$current_purge_request]['purge_url'] = $proxy_url_base . $purge_path;
        //  Set the Acquia purge header to the Acquia Hosting site name
        $purge_requests[$current_purge_request]['headers'] = array('Accept-Encoding: gzip', 'X-Acquia-Purge:' . $_SERVER['AH_SITE_NAME']);
      }
      $current_purge_request++;
    }
  }
  // Issue the requests using curl (for now)
  $purge_request_results = purge_issue_requests_curl($purge_requests);
  return $purge_request_results;
}

/**
 * Issue purge request using curl
 *
 */
function purge_issue_requests_curl($purges) {
  // Initialise a curl_multi object
  $curl_purges = curl_multi_init();
  $current_curl_purge = 0;
  foreach ($purges as $purge) {
    $curl_purge[$current_curl_purge] = curl_init();
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_CUSTOMREQUEST, $purge['request_method']);
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_URL, $purge['purge_url']);
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_HEADER, 1);
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_TIMEOUT, 4);
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_HTTPHEADER, $purge['headers']);
    curl_setopt($curl_purge[$current_curl_purge], CURLOPT_RETURNTRANSFER, 0);
    curl_multi_add_handle($curl_purges, $curl_purge[$current_curl_purge]);
    $current_curl_purge++;
  }
  // Execute the purge requests
  ob_start();
  do {
    // This loop is only necessary for libcurl earlier than 7.20.0, however
    // RHEL 5 has just that. Otherwise a simple call would do.
    do {
      $multi_result = curl_multi_exec($curl_purges, $active);
    } while ($multi_result == CURLM_CALL_MULTI_PERFORM);
    // Block until there is activity on any of the handlers. Avoids
    // busywaiting.
    if ($multi_result == CURLM_OK) {
      $select_result = curl_multi_select($curl_purges);
    }
    //if ($multi_result != CURLM_OK || $select_result == -1) {
    if ($multi_result != 0 && $select_result == -1) {
    // @TODO: error handling. Something truly awkward happened.
    return FALSE;
    }
  } while ($select_result != -1 && $active && $multi_result == CURLM_OK);
  ob_end_clean();

  // Result collection. Collects the http code returned for each url purged
  $current_curl_purge = 0;
  foreach ($purges as $purge) {
    $info = curl_getinfo($curl_purge[$current_curl_purge]);
    $purges[$current_curl_purge]['http_code'] = $info['http_code'];
    curl_multi_remove_handle($curl_purges, $curl_purge[$current_curl_purge]);
    $current_curl_purge++;
  }
  curl_multi_close($curl_purges);
  return $purges;
}


/**
 * Logs successful purges and errors to the watchdog.
 *
 * @param $purge_request_results
 *   array of url with their http status code
 */
function purge_logging($purge_request_results) {
  $purge_success = 0;
  $purge_blocking = array();
  $purge_success_log = array();
  $purge_error_log = array();
  foreach ($purge_request_results as $purge_request_result) {
    switch ($purge_request_result['http_code']) {
      // First check if everything went ok.
      case 200:
	$purge_success_log[] = $purge_request_result['purge_url'] . ' OK';
        $purge_success++;
        break;
      // Redirects are acceptable
      case 301:
	$purge_success_log[] = $purge_request_result['purge_url'] . ' OK';
        $purge_success++;
        break;
      case 302:
        $purge_success_log[] = $purge_request_result['purge_url'] . ' Temporary Redirect';
        $purge_success++;
        break;
      // Notice if the request was not found in the proxy cache
      case 404:
        $purge_success_log[] = $purge_request_result['purge_url'] . ' Not Found in Cache';
        $purge_success++;
        break;
      // Collect all proxy hosts that are blocking the url requests
      case 405:
	$purge_error_log[] = $purge_request_result['purge_url'] . ' Forbidden';
        $purge_blocking[] = parse_url($purge_request_result['purge_url'], PHP_URL_HOST);
        break;
      // Collect all urls and their http error codes
      default:
        $purge_error_log[] = $purge_request_result['purge_url'] . ' Error code ' . $purge_request_result['http_code'];
        break;
    }

  }

  // Watchdog barking
  $purge_errors = count($purge_request_results) - $purge_success;
  // Report both successes and failures
    if ($purge_success === 1) {
      watchdog(
        'purge',
        '1 URL has been successfully purged from the reverse proxy caches: !$purge_success_log',
        array('!$purge_success_log' => expire_print_r($purge_success_log))
      );
    }
    else {
      watchdog(
        'purge',
        '!purge_success_count URLs have been successfully purged from the reverse proxy caches: !$purge_success_log',
        array('!purge_success_count' => $purge_success, '!$purge_success_log' => expire_print_r($purge_success_log))
      );
    }
    // Report all urls with errors
    if ($purge_errors === 1) {
      watchdog(
        'purge',
        '1 error has been encountered when purging URLs !$purge_error_log',
        array('!$purge_error_log' => expire_print_r($purge_error_log)),
        $severity = WATCHDOG_ERROR
      );
    }
    else {
      watchdog(
        'purge',
        '!purge_errors_count errors have been encountered when purging these URLs. !$purge_error_log',
        array('!purge_errors_count' => $purge_errors, '!$purge_error_log' => expire_print_r($purge_error_log)),
        $severity = WATCHDOG_ERROR
      );
    }
    // Report on proxy servers that block the purge requests.
    if (!empty($purge_blocking)) {
      foreach ($purge_blocking as $purge_blocking_server) {
        watchdog(
          'purge',
          'The proxy server host %blocking_server is blocking purge requests. Please notify NYI support.',
          array('%blocking_server' => $purge_blocking_server),
          $severity = WATCHDOG_ERROR
        );
      }
    }
}
