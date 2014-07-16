Freeder
=======

Freeder is a new open-source RSS feeds reader, as announced [here](http://phyks.me/2014/07/lecteur_rss_ideal.html).


Disclaimer
----------

It is currently a WIP, more information will come soon. In the mean time, it may be only interesting for devs.

_Note :_ There are some TODOs in the files. If you are a developper and want to give a hand, this is what you should look for :)

Installation
------------

### Dependencies

 * `php5-sqlite`
 * `php5-curl`

Coding guidelines
-----------------

All PR are welcome. Just look at the actual files, and try to reproduce it (or propose to update it to enhance coding guidelines :).

Here are the major things:
* Use tabs for indentation, no PR with spaces will be accepted.
* Use underscore\_case for vars and functions, use [PascalCase](https://en.wikipedia.org/wiki/PascalCase) for classes, unless this element refers to some well established, standardized name (such as `pubDate` in the RSS specification).
* Do not leave trailing whitespaces.

Please avoid commits with large number of modifications due to spaces to tabs conversions and stuff like this, as their diff is really unreadable.


License
-------

These licenses apply unless something else is specified in the file. All scripts should contain a license indication. If not, feel free to ask us. Please note that files where it is difficult to state such a license note (especially image files) are distributed under the same terms.

For more detailed license info, please refer to LICENSE file.

_Note:_ If you ever reuse any code from Freeder, please let us know. You do not have to, but we would really enjoy knowing what you used it for :)

### Base code
The base code of Freeder is released under the MIT license :
```
Copyright (c) 2014 Freeder

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
```


### Template
The template (files under `tpl/default/`) is licensed under a specific license, it is under Creative Commons CC BYSA.

**TODO:** Add more license info on CC.


Related information
-------------------

* [Quick and dirty benchmark on RSS / ATOM feed parsing](http://phyks.me/2014/07/benchmark_rss.html)
* [PHP CURL multi (French)](http://lehollandaisvolant.net/index.php?d=2014/05/20/19/21/36-php-faire-plusieurs-requetes-http-simultanees-avec-curl)
