<?php
/*
Plugin Name: Sample JSON API plugin
Version: 0.1
Plugin URI: http://github.com/eelias
Description: Sample
Author: Evan Elias
Author URI: http://github.com/eelias

Copyright 2010

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

require_once('6a-plugin.php');


// Base class for namespaced sub-classes in Conversations.  Contains utility functions
// usable by all subclasses.
// Do NOT put actions, filters, etc into this class, since they will get inherited by
// all subclasses, and therefore will be registered multiple times.
class SamplePlugin extends WPPluginBase {
    protected $already_notified = array();
    protected $repo_url = 'http://conversations.typepad.com';

    function get_plugin_basename() {
        return plugin_basename(__FILE__);
    }
    
    public function __construct() {
        parent::__construct();
    }
    
    // More to come
}


// Load and register components 
$sample_plugin = new SamplePlugin();
$sample_plugin->register();

?>