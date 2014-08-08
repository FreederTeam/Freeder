#!/bin/sh

FILE=rewriting

which colordiff && DIFF=colordiff || DIFF=diff

php $FILE.php > $FILE.out
$DIFF $FILE.out $FILE.expect

