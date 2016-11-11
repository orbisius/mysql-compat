# mysql-compat
This simple one file library which recreates the removed the old mysql_* functions using mysqli extension.

This file is convenient if you're not yet ready to switch to php 5.6.
Not all of the mysql_* functions are implemented.
If you find a bug create an issue.
If you have a little bit of time do help.

# Usage:

Just include it.

```
<?php

require_once( __DIR__ . "/orbisius_mysql_compat.php" );

```

# Author
Svetoslav (Slavi) Marinov [Orbisius](http://orbisius.com/)
