#!/bin/bash 

cd `dirname $0`

for file in `php parseGitLog.php ../../../updates`
do
testUpdate=`./checkIfUpdateApplied $file`
if [ "X$testUpdate" = "X" ] ; then
   echo running $file
   $file
   ./addUpdateScriptToDb $file
fi
done

