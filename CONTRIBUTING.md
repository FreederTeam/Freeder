Freeder - Contributing
======================

This documents aims to help newcomers to understand how and why the project is
organised. It is recommanded to everybody to:

 * Read it carefully before contributing.
 * Complete and modify it such as it is always up to date and coherent with the
   actual code.

**Important note** : Please consider pull-requesting **only** toward the `dev` branch so that master can always be a clean and stable version of Freeder.


Keywords
--------

Make sure you think about it while working on Freeder!

 * **Free**
 * **Clean** Clean user interface, Clean code, Clean documentation
 * **Super easy** Easy to set-up, Easy to use, Easy to contribute
 * **Extensibile**


Easy tasks
----------

Some issues are labeled `easytask`. These issues, based on what LibreOffice does, aims at being very easy to handle for new contributors, for a first contact with the Freeder source code. Such issues are generally easy to solve, they can be either programming or translation or whatever necessary for Freeder, so feel free to have a look =)

Usually, there are all the necessary informations in the issue to be able to find the relevant parts of the code, and to solve it.



Coding guidelines
-----------------

All contributions are welcome. But in order to maintain some code consistency,
please use the following conventions.

### Tabs or spaces

The famous "tabs or spaces" question has to be quickly solved since refactoring
whitespaces is nothing but a waste of time, especially if each contributor has
its own convention and systematically refactors other people's work because they
are convinced that he is right.

Although some of us prefere tabs while the others wanted spaces, we all agree
that mixing tabs and spaces is a very bad practice.

We voted in favor of tabs because this is what tabs are made for and so everyone
can choose how to display it.

Note that if you use vim you can set the use of tabs for the whole git project
as explained [here](http://phyks.me/2014/07/specific_vim_config_git.html).

### Misc guidelines

Here are some snippet expliciting our conventionnal coding style:

```
<?php
if (condition) {
}

for (a ; b ; c) {
}
```

 * Indent code with tabs. (*cf* bellow)
 * There are spaces around parenthesis but not inside them.
 * Opening brackets are not on new lines in order to have more compact code.
   This is less easy to read but far more efficient when you code on a small
   screen.
 * `<?php` tag is not closed at the end of file. It avoid empty end line bugs,
   and some even says it leads to better perfs.
 * Use underscore\_case for vars and functions, use [PascalCase](https://en.wikipedia.org/wiki/PascalCase) for classes, unless this element refers to some well established, standardized name (such as `pubDate` in the RSS specification).
 * **Never** leave traling spaces.
 * Use doxygen parsable docstrings in your functions comments.
 * When a comment applies to a full block and not only the next (few) line(s), put it before the block, not inside it.


File organisation
-----------------

### Rights

The user that runs Freeder must be able to write in `data/` and `tmp/`
directories. There should be no other directory in which writing right is
required.

Of course if the user have the rights on the whole Freeder directory, it works
well, but some people prefere give the minimum rights to `www-data` or whatever
file serving user.

### tmp/

The `tmp/` directory is used by [Rain TPL](http://www.raintpl.com) to store
cached pages.

### data/

The `data/` directory contains every user specific data. It includes a
`config.php` file that defines some constants and the sqlite database file for
example.

### inc/

Files in `inc/` are designed to be included in other ones instead of being
directly called to serve a page.

File with the `.class.php` extension are Object Oriented. They define the class
named as the file is.

### tpl/

The `tpl/` directory is where you would put Freeder themes. Theme list is just
the list of directories in `tpl/` and they have to contain each Rain TPL view
templates.

See `tpl/README.md` for more information about template conventions.

### unit/

This is the unit testing directory. Its content is not used for the Freeder application itself but it contains some scripts that automate unit testing.

Always run `unit/check.sh` before pulling a request. It should output nothing.

You are also welcome to add unit tests. Usually, it is composed of:

 1. An input file.
 2. A processing script, using the unit you want to test.
 3. An expected output file, to be compared by `check.sh`


Installation
------------

To know whether it has been set up or not, Freeder fetch `data/config.php`. It
considers that the existence of that file means that Freeder is installed.


Database organisation
---------------------

TODO


Misc
----

If you contribute in any form to Freeder, we will list you on the [CONTRIBUTORS wiki page](https://github.com/FreederTeam/Freeder/wiki/CONTRIBUTORS). If you want us to put a custom link (instead of a link to your github account) or do not want to appear on this page, just let us know :)

If you want to add some tips but you don't know where it should be, put it here. If many items of that list are about the same topic, please take time to add a new section for it!

 * Use `die` for erronous situations that way not happend in a usual flow. It whould never be raised for a basic use of Freeder but could help developpers and code hackers to debug.
 * Don't rely on what an included file includes itself, unless it is officially documented. It may change. For example, although `inc/init.php` includes `inc/users.php` right now, you should include `inc/users.php` again in you file — if you use functions that come from it, of course — even if you include `inc/init.php`.
 * Prefix branch names with the appropriate prefix: `feature_`, `refactor_`, `bugfix_`. You can use your name as prefix if it is a private branch.




License
-------

As in every project, the question of the license has been raised. Here are some
points we dicussed about.

### Non Commercial disposition

We did not want to include any Non Commercial clause in our license because:

  1. This attempt to license freeness since people are not really free to do
	 whatever they want with the code.
  2. If there is a commercial use of our software, we will get more feedback
	 since more people will deal with it.
  3. If the commercial application adds some simple features, it could give us
	 improvment ideas.
  4. If the commercial application adds a big feature that we can afford to
	 implement by ourselves, well: that's their job! We would be glad to see
	 that they used our software as basis and done something big from it.

### Beerware

This — or its variants such as wtfpl — is a funny license. It fits very well for
little scripts and utilities that we just want to share. But this is no serious
for a full software and don't provide any law support in case of real conflict.

### Creative Commons

Creative Commons haven't been design for code so we will not use it for backend
licensing. But it's well for contents so we decided to license themes under
Creative Commons BY SA. (still no NC disposition!)

### GPL

GPL is a complete license, designed for source codes. But we were not
confortable with its SA clause since it would be anoying for potential
aplications (commercial or not).

### MIT

We finally choosed the MIT license for the source code.

 * It is easy to understand.
 * It is designed for source code.
 * It includes an attribution clause for our contributors not to be forgotten in
   further applications.
