#!/bin/bash 

OLDPWD=$PWD
directory=$1
if [ "X$directory" = "X" ] ; then
    directory="updates"
fi

cd `dirname $0`

for file in `php parseGitLog.php $OLDPWD/$directory`
do
testUpdate=`./checkIfUpdateApplied $file`
if [ "X$testUpdate" = "X" ] ; then
   echo running $file
   $file
   ./addUpdateScriptToDb $file
fi
done

