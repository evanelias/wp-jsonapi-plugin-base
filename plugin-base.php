<?php
/**
 * Base class for WordPress plugins.  Your plugin should implement a class that
 * extends this one.  See plugin-sample.php for example usage.
 */

/*  (c) 2010 Evan Elias

    This program is free software: you can redistribute it and/or modify it 
    under the terms of the GNU General Public License as published by the Free 
    Software Foundation, either version 3 of the License, or (at your option) 
    any later version.

    This program is distributed in the hope that it will be useful, but WITHOUT
    ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
    FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for
    more details.

    You should have received a copy of the GNU General Public License along 
    with this program.  If not, see <http://www.gnu.org/licenses/>
*/

require_once('json.php');


class WPPluginBase {
    protected $api_base;
    protected $timeout;
    
    /** 
     * Returns plugin basename. Your plugin subclass MUST override this function,
     * in order for __FILE__ to be executed in the proper context!  Just
     * cut-and-paste this one method into your subclass, exactly as-is.
     */
    function get_plugin_basename() {
        return plugin_basename(__FILE__);
    }

    function __construct($api_base_url="", $api_timeout=15) {
        $this->api_base = $api_base_url;
        $this->timeout = $api_timeout;
    }
    
    /** 
     * Return true if the given response body and code are acceptable,
     * false otherwise. Your subclass may wish to override this, for
     * instance, if you blank responses are indicative of an error.
     */
    function acceptable_response($raw_response, $http_response_code) {
        // This implementation does not yet support redirect codes,
        // so we consider any 3xx, 4xx, or 5xx response code an error.
        if ($http_response_code >= 300)
            return false;
        return true;
    }

    /** 
     * Makes an HTTP request to $url using HTTP method $method, containing
     * request body $body if supplied.  Note that the caller should urlencode
     * $body if desired; this function will not do it for you, since many
     * JSON APIs do not encode their POST or PUT bodies.
     */
    function http_api($url, $method="GET", $body='') {
        $url = $this->api_base . $url;
        $method = strtoupper($method);
        
        // Use WP_HTTP for only GET or POST
        if ($method == 'GET' || $method == 'POST') {
            $args = array(
                'method' => $method, 
                'timeout' => $this->api_timeout,
            );
            
            if ($method == 'POST')
                $args['body'] = $body;
                
            $wp_response = wp_remote_request($url, $args);
            
            if (is_wp_error($wp_response)) {
                $this->debug("[ERROR] $method $url $body");
                throw new WPJsonException($method, $url, $wp_response->get_error_message());
            }
            
            $json_raw = wp_remote_retrieve_body($wp_response);
            $http_status_code = wp_remote_retrieve_response_code($wp_response);
        }
        
        // Use libcurlemu for all other HTTP methods
        else {
            require_once('libcurlemu/libcurlemu.inc.php');
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($body)
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
            $json_raw = curl_exec($ch);
            if (curl_errno($ch)) {
                $this->debug("[ERROR] $method $url $body");
                throw new WPJsonException($method, $url, curl_error($ch));
            }
            $http_status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        }
        
        $this->debug("[$http_status_code] $method $url $body");

        if (!acceptable_response($json_raw, intval($http_status_code))) 
            throw new WPJsonException($method, $url, "HTTP status code $http_status_code");

        // Do not attempt JSON-decoding a blank response.
        if (!$json_raw)
            return null;
        
        $json = (array)(json_decode($json_raw));
        return $json;
    }
    
    /** 
     * Auto-discovers WordPress hooks and filters by naming convention.
     */
    function register() {
        $methods = get_class_methods($this);
        foreach ($methods as $method) {
            // Register activation and deactivation hooks. Method name must literally be called
            // "activation_hook" or "deactivation_hook".
            if ($method == 'activation_hook')
                register_activation_hook($this->get_plugin_basename(), array($this, $method));
            if ($method == 'deactivation_hook')
                register_deactivation_hook($this->get_plugin_basename(), array($this, $method));

            // Register any method function with _action in the name as an action hook.
            // If the action name also contains 'admin', only register the action if user
            // is an admin.
            $hook = str_ireplace('_action', '', $method);
            if ($hook != $method && (strpos($hook, 'admin') === false || is_admin())) {
                add_action($hook, array($this, $method), 10, 5);
                continue;
            }
            
            // Register any method function with _filter in the name as a filter hook.
            $filter = str_ireplace('_filter', '', $method);
            if ($filter != $method) {
                add_filter($filter, array($this, $method), 10, 5);
                continue;
            }
        }
    }

    /** 
     * Simple wrapper around PHP's error_log function.
     */
    function log_error($error) {
        error_log($error);
    }
    
    /** 
     * Displays a debug message only if PLUGIN_DEBUG is defined in the WP config.
     */
    function debug($message) {
        if (defined("PLUGIN_DEBUG") && PLUGIN_DEBUG)
            error_log("[DEBUG] $message");
    }
}

class WPLoggedException extends Exception {
     public function __construct($message, $error_code = 0) {
         error_log(plugin_basename(__FILE__) . ": $message");
         parent::__construct($message, $error_code);
     }
}

class WPJsonException extends WPLoggedException {
     public function __construct($httpmethod, $endpoint, $error, $code = 0) {
         $message = "JSON error from $httpmethod $endpoint: $error";
         parent::__construct($message, $code);
     }
}


?>