#!/bin/sh

old_dir=$PWD
dir="$PWD/$(dirname $0)"
cd $dir

mkdir -p out diff php expect tpl

rm -f diff/*
rm -f out/*
rm -rf tmp

status=0

echo 'Launching tests.'
for file in php/*
do
    file=${file%.*}
    file=${file##*/}
    echo -n "Test for $file… "
    php php/$file.php > out/$file.out
    if diff out/$file.out expect/$file.expect > diff/$file.diff; then
	echo 'Pass.'
    else
	echo 'Error.'
	status=$(($status+1))
    fi
done
echo -n 'Tests finished. '

total=$(ls php | wc -l)
echo "[pass=$(($total-$status)), fail=$status, total=$total]"

cd $old_dir

exit $status
