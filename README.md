wp-jsonapi-plugin-base
======================
(c) 2010 Evan Elias, except where indicated in bundled libraries.  
Released under the GPL.

-----

**This is a base class for making WordPress plugins easier and cleaner to write, with a particular focus on plugins that interact with JSON APIs.**

Ordinarily, WordPress plugin authors working with JSON APIs face the following challenges:

* The WP docs encourage you to use WP\_HTTP for all HTTP requests, but WP\_HTTP does not reliably support PUT or DELETE methods on many systems, which are commonly used in web services APIs.

* Relying on cURL instead of WP\_HTTP is risky, since it requires libcurl on the server, and PHP must be compiled --with-curl. These aren't safe assumptions.

* The PHP module for JSON functions wasn't bundled and enabled by default until PHP 5.2+, but many hosts are still running PHP 5.0 or 5.1.

* To make up for that deficiency, WordPress started bundling the excellent Services\_JSON PHP library starting with WP 2.9+. But that means if you want your plugin to support WP 2.8 or lower, you can't just blindly bundle Services\_JSON yourself without getting a conflict in WP 2.9+


**This repo provides a base class that addresses all of those issues!** It also provides a clean, object-oriented approach to writing WordPress plugins in general.

1. Bundles Services_JSON (http://pear.php.net/package/Services_JSON/, released under BSD license) and wraps it safely.  It will only be loaded if the PHP JSON module isn't found, nor is Services_JSON already loaded via WP 2.9+.

1. Bundles libcurlemu (http://code.blitzaffe.com/pages/phpclasses/category/52/fileid/7, by Steve Blinch, released under GPL2), which only inits itself if cURL support in PHP is not found.  wp-jsonapi-plugin-base uses WP\_HTTP for GET and POST requests, but uses libcurlemu for all others, since WP\_HTTP cannot be reliably used for PUT, DELETE, HEAD, OPTIONS, etc.

1. Provides a clean interface for hitting JSON API endpoints.



----

This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  If not, see <http://www.gnu.org/licenses/>
