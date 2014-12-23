Freeder
=======

Freeder is an open-source RSS feeds reader.

Screenshots
-----------

*Zen theme*

![Freeder installation page](https://raw.githubusercontent.com/FreederTeam/Freeder/master/doc/screenshots/zen_install.png)

![Freeder welcome page](https://raw.githubusercontent.com/FreederTeam/Freeder/master/doc/screenshots/zen_welcome.png)

![Freeder index page](https://raw.githubusercontent.com/FreederTeam/Freeder/master/doc/screenshots/zen_index.png)

![Freeder settings page](https://raw.githubusercontent.com/FreederTeam/Freeder/master/doc/screenshots/zen_settings.png)

*tmos theme*

![Freeder installation page](https://raw.githubusercontent.com/FreederTeam/Freeder/master/doc/screenshots/install.png)


Installation
------------

### Cloning this repository

Freeder git repo is available at `https://github.com/FreederTeam/Freeder.git`. To clone it, follow these instructions:

 * `git clone https://github.com/FreederTeam/Freeder.git`
 * `cd Freeder`
 * `git submodule init`
 * `git submodule update`

Note that we use git submodules only to include the wiki inside the project for now, so you can ignore the last two commands if you don't need it. It is actually useful to be able to access the wiki pages while not connected to the Internet.

### Dependencies

 * `php` >= 5.3
 * `php5-sqlite`
 * `php5-curl`

### Installation

Assuming that Freeder php files are run by the user `www-data`, make sure `www-data` is able to write in `data/` and `tmp/`.

Then just load the index page and it will automatically install Freeder if it's the first time you run it.

### Reinstallation

If you want to reinit your Freeder installation, you just have to clean up the `data/` directory.


Development branch
------------------

The `dev` branch is more likely to be up to date and includes last improvments.

But from time to time, when you `git pull` the `dev` branch, your Freeder install might not work anymore (blank screen in most cases, or database related errors).

As we are currently in heavy WIP, we might change the database structure from time to time, and not insure backward compatibility. In such cases, please refer to the commit log for more infos, or reinstall Freeder if you don't want to bother.


Getting help
------------

Please report any issue you might find using the Github issues.

We also have an IRC channel for live chat: #freeder on freenode. Please wait up to a few hours after saying anything, we may not be always around.


Contributing
------------

If you want to contribute — or if you are just curious — you should have a look
at the [CONTRIBUTORS.md](https://github.com/FreederTeam/Freeder/wiki/CONTRIBUTORS) wiki page.


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

For more information on this license, please have a look at [this brief summary](https://creativecommons.org/licenses/by-sa/4.0/).


Related information
-------------------

* [Quick and dirty benchmark on RSS / ATOM feed parsing](http://phyks.me/2014/07/benchmark_rss.html)
* [PHP CURL multi (French)](http://lehollandaisvolant.net/index.php?d=2014/05/20/19/21/36-php-faire-plusieurs-requetes-http-simultanees-avec-curl)


Troubleshooting
---------------

### Enable pdo_sqlite

 1. Make sure `php-sqlite` has been installed.
 2. Check whether `/etc/php/php.ini` contains a line `extension=pdo_sqlite.so`
 3. Restart apache2/nginx/httpd/…
