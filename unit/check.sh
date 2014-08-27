#!/bin/sh


which colordiff && DIFF=colordiff || DIFF=diff

FILE=rewriting
php $FILE.php > $FILE.out
$DIFF $FILE.out $FILE.expect

FILE=rewrite-engine
php $FILE.php > $FILE.out
$DIFF $FILE.out $FILE.expect

