=== Plugin Name ===
Contributors: Arnaud Lejosne
Donate link: http://creations.lochrider.com
Tags: linkedIn, resume, CV, curriculum, vitae
Requires at least: 2.0
Tested up to: 3.2.1
Stable tag: 2.00

Display your CV (also called Resume or Curriculum vitae) on your blog from your linkedIn public page informations.

== Description ==

Display your CV (also called Resume or Curriculum vitae) on your blog from your linkedIn public page informations.

== Installation ==

1. Download It
2. Install it
3. Add [linkedinresume] on the page where you want your cv to appear
Note : by default, the language will be your wordpress language but you can specify one by adding the attribute "lang" if you have a multilanguage profile to choose which one you want to display.
Per example : [linkedinresume lang="fr"] will display the french version of your resume
4. Change the url on the setting page to your linkedin public page url
5. Enjoy ;)

== Frequently Asked Questions ==

= An error occurs saying that you need cURL =

Go edit your php.ini and enable cURL by renaming ";extension=php_curl.dll" to "extension=php_curl.dll"

= I get this error : "Parse error: syntax error, unexpected T_STRING, expecting T_OLD_FUNCTION or T_FUNCTION or T_VAR or ‘}’" =

It's just because you're using an old version of PHP (4.x). If you want to use my plugin (and a lot of other plugins ;)) you will have to upgrade... If you are running your Wordpress on a hosted server then, perhaps that you just have to enable it... For that, just create a file called ".htaccess" at the root of your Wordpress and write those two lines in it :
AddType x-mapp-php5 .php
AddHandler x-mapp-php5 .php

== Screenshots ==

1. A preview of the result on the public blog
2. A preview of the admin page

== Changelog ==

= 2.00 =
Some code cleanup and fixes due to some code modifications on the linkedin side

= 1.94 =
Belorussian translation thanks to <a href="http://pc.de/">Marcis G. (http://pc.de/)</a>

= 1.91, 1.92 & 1.93 =
A lot of enhancements on the education regexp

= 1.9 =
* Regexp compatibility bugs fixed
* Optimisation of some regexps

= 1.8 =
* Additional notes about schools added

= 1.7 =
* Better regexps

= 1.6 =
* __e bugfix

= 1.5 =
* Implementation of the Wordpress translation files for english, portugese and french.

= 1.4 =
* Implementation of the multilanguage system

= 1.3 =
* Bug fixed for the summary part
* Creation of a real readme file and new screenshots taken.
* Disabling the parts that contains unused fields
* Code optimisation

= 1.2 =
* Some bugfixes

== Copyright ==

All rights reserved Arnaud Lejosne
