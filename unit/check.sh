#!/bin/sh

old_dir=$PWD
dir="$PWD/$(dirname $0)"
cd $dir

mkdir -p out diff test expect tpl tmp

rm -f diff/*
rm -f out/*
rm -f tmp/*

status=0

echo 'Launching tests.'
for file in test/*
do
    ext=${file##*.}
    file=${file%.*}
    file=${file##*/}
    echo -n "Test for $file… "
    $ext test/$file.$ext > out/$file.out 2> /dev/null
    if diff out/$file.out expect/$file.expect > diff/$file.diff; then
	echo 'Pass.'
    else
	echo 'Error.'
	cat diff/$file.diff
	status=$(($status+1))
    fi
done
echo -n 'Tests finished. '

total=$(ls -1 test | wc -l)
echo "[pass=$(($total-$status)), fail=$status, total=$total]"

cd $old_dir

exit $status
