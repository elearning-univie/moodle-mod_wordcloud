Wordcloud Module
==========================

This file is part of the mod_wordcloud plugin for Moodle - <http://moodle.org/>

*Author:* Adrian Czermak, Angela Baier, Thomas Wedekind

*Copyright:* 2020 [University of Vienna](https://www.univie.ac.at/)

*License:* [GNU GPL v3 or later](http://www.gnu.org/copyleft/gpl.html)


Description
-----------

The wordcloud activity is a place where you and your course participants can collect terms 
and display them in real time by using different font colors and sizes according to their frequency.
Submitted terms can also be displayed as a list.

Participants can work in groups via the group mode. The data can be displayed either for
individual groups or as aggregated data.

The way the data is stored makes it impossible for teachers to trace who submitted what terms.
The output can also be exported in different formats for further use.


Usage
-----

A teacher wants their students to participate interactively in their lessons using the wordcloud.

Possible scenarios are:
* Collect terms on various topics.
* Create a base for further discussion.
* Assess the current state of knowledge.
* Collect unclear issues or topics.
* Get a mood picture on a topic.
* Do a brainstorming process.
* Get anonymous feedback.

The students collect terms on a specific topic in small groups, each in their own wordcloud. Afterwards, the teacher displays a combined wordcloud to address similarities and differences of the given answers.

After the session, the teacher exports the submitted terms to use them when preparing for the following lessons.


How Is the Wordcloud Rendered?
------------------------------

The following default display settings are used for the classic renderer (JavaScript HTML renderer from [html2canvas](https://github.com/niklasvh/html2canvas)):
* Color scheme (random coloring, sequentially shaded palette)
* Font 'Arial (sans-serif)'
* Text alignment 'Horizontal only'

Further display settings are available with the modern renderer only (available from v5.1-r1, [WordCloud](https://github.com/timdream/wordcloud2.js)).
If any of these further options are set (e.g. Font 'Courier New (Monospace)', Text alignment 'Vertical only'), the render style will automatically switch from classic to modern.


Installation
------------

* Copy the module code directly to the *moodleroot/mod/wordcloud* directory.

* Log into Moodle as administrator.

* Open the administration area (*http://your-moodle-site/admin*) to start the installation
  automatically.


Admin Settings
--------------
_Site administration -> Plugins -> Activity modules -> Wordcloud_

An administrator can adjust the following settings:

* Refresh time (wordcloud | refresh)  
  Auto refresh interval in seconds.

* Textcolor 1 to 6 (wordcloud | fontcolor1 ... fontcolor6)  
  The textcolor for font size 1 to 6. The font colors of the wordcloud are also adjustable in the settings (hexcode).

* Default font (wordcloud | defaultfont)  
  What the default font should be for new wordclouds (sans-serif, serif, monospace and cursive fonts).

* Default text alignment (wordcloud | defaulttextalignment)  
  How the terms should be aligned by default in new wordclouds (individually or in combination: horizontally, vertically, diagonally)

* Further style options (wordcloud | rendersettings)  
  Add further options (in JSON format) to define the appearance of wordclouds. Further information on the available settings of the used third-party library 'Wordcloud' can be found here: https://github.com/timdream/wordcloud2.js/blob/gh-pages/API.md
  
  Please note: The following style options...
  * are currently AVAILABLE: gridSize, backgroundColor, shrinkToFit, drawOutOfBound, minSize
  * are PREDEFINED by other settings of the module or are in the code only. These settings have no effect, even if they are added to 'Further options': weightFactor, color, minRotation, maxRotation, rotationsteps, rotateRatio


Third-Party Libraries
---------------------

This plugin uses the following third-party libraries:
* JavaScript HTML renderer from [html2canvas](https://github.com/niklasvh/html2canvas)
* WordCloud (https://github.com/timdream/wordcloud2.js)


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
