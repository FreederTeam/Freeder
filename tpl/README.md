
Template directory
==================

This directory contains [Rain TPL](http://www.raintpl.com/) templates for each view.

Convention
----------

Templates whose name begins with an underscore (`_`) are include templates.
That means that they should not be called directly but included in other templates.

These templates should always contain a brief description of how to use them, in
Rain TPL comments (`{* ... *}`).

Common page template
--------------------

Except in some cases, a page template looks like this :

```
{include="_begin"}

Your page content

{include="_end"}
```

You content will be placed at DOM position `/html/body/main`.
