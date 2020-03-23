#!/bin/bash 

for file in `php webci-myjisc/scripts/parseGitLog.php`
do
testUpdate=`webci-myjisc/scripts/checkIfUpdateApplied $file`
if [ "X$testUpdate" = "X" ] ; then
   echo running $file
   $file
   webci-myjisc/scripts/addUpdateScriptToDb $file
fi
done

