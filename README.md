Wordcloud Module
==========================

This file is part of the mod_wordcloud plugin for Moodle - <http://moodle.org/>

*Author:* Adrian Czermak, Angela Baier, Thomas Wedekind

*Copyright:* 2020 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The wordcloud activity provides a place to collect terms together with your course participants
and display them in real time by using different font colors and sizes according to their frequency.
Alternatively, submitted terms can also be displayed as a list.

Participants can work in groups via the group mode. The data can be displayed either for
individual groups or as aggregated data.

In general the data is stored in such a way that it is not possible for teachers to trace words.
Furthermore the output can be exported in different formats for further use.


Usage
-----

A teacher wants his students to participate interactively in his lessons using the wordcloud.

Possible scenarios are:
* Collect words on various topics.
* Create a base for a further discussion.
* Query the current knowledge.
* Collect unclear issues or topics, to work on it.
* Get a mood picture on a topic.
* Do a brainstorming process.
* Get anonymous feedback.

The students collect terms on a specific topic in small groups. Afterwards the teacher displays an overall wordcloud to adress similarities and differences of the given answers.

After the session, the teacher exports the submitted words to use them for the preparation of the following lessons.


Installation
------------

* Copy the module code directly to the *moodleroot/mod/wordcloud* directory.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.


Admin Settings
--------------

An administrator can adjust the instance wide refresh time for the wordcloud, which sets the interval
on how often the client ask the server if there is a change to a specific wordcloud. 
The font colors of the wordcloud are also adjustable in the settings.

_Site administration -> Plugins -> Activity modules -> Wordcloud_

* Refresh time (wordcloud | refresh)  
  Auto refresh interval in seconds.

* Textcolor 1 to 6 (wordcloud | fontcolor1 ... fontcolor6)  
  Textcolor for font size 1 to 6. Colors are set as hexcode.


Third-party Libraries
---------------------

This plugin uses the following third-party libraries:
* JavaScript HTML renderer from [html2canvas](https://github.com/niklasvh/html2canvas)


Privacy API
-----------

The plugin fully implements the Moodle Privacy API.


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
