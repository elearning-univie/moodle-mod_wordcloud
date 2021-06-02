Wordcloud Module
==========================

*Author:* Adrian Czermak, Angela Baier, Thomas Wedekind

*Copyright:* 2020 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------
With the wordcloud activity, it is possible to collect terms together with your course participants in a brainstorming process and to display them graphically in order of frequency. This can be used, for example, to capture a picture of the mood or to create a basis for a discussion.


Examples
--------
* Use the Wordcloud as an introduction to a new topic.
* Query the prior knowledge of your students.
* Get a mood picture on a topic.


Requirements
------------
The plugin is available for Moodle 3.10+.


Installation
------------

* Copy the module code directly to the *moodleroot/mod/wordcloud* directory.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.


Admin Settings
--------------
An administrator can adjust the instance wide refresh time for the wordcloud, which sets the interval on how often the client ask the server if there is a change to a specific wordcloud. 
The font colors of the wordcloud are also adjustable in the settings.

_Site administration -> Plugins -> Activity modules -> Wordcloud_

* Refresh time (wordcloud | refresh)  
  Auto refresh interval in seconds.

* Textcolor 1 to 6 (wordcloud | fontcolor1 ... fontcolor6)  
  Textcolor for font size 1 to 6. Colors are set as hexcode.


Documentation
-------------
You can find further information to the plugin on the [Github wiki](https://github.com/elearning-univie/moodle-mod_wordcloud/wiki/)


Third-party Libraries
---------------------
This plugin uses the following third-party libraries:
* JavaScript HTML renderer from [html2canvas](https://github.com/niklasvh/html2canvas)


Bug Reports / Support
---------------------

We try our best to deliver bug-free plugins, but we can not test the plugin for every platform,
database, PHP and Moodle version. If you find any bug please report it on
[GitHub](https://github.com/elearning-univie/moodle-mod_wordcloud/issues/). Please
provide a detailed bug description, including the plugin and Moodle version and, if applicable, a
screenshot.

You may also file a request for enhancement on GitHub. If we consider the request generally useful
and if it can be implemented with reasonable effort we might implement it in a future version.

You may also post general questions on the plugin on GitHub, but note that we do not have the
resources to provide detailed support.


License
-------

This plugin is free software: you can redistribute it and/or modify it under the terms of the GNU
General Public License as published by the Free Software Foundation, either version 3 of the
License, or (at your option) any later version.

The plugin is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
General Public License for more details.

You should have received a copy of the GNU General Public License with Moodle. If not, see
<http://www.gnu.org/licenses/>.


Good luck and have fun!
