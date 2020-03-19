<?php

/* In theory this script could be use to check changes to any
   file / directory but the original impetus was to 
   check changes within a to-be-specified 'updates' folder
*/

// default - for demo purposes

$toRun = [];
$fileToCheck = "updates";
if ( isset ( $argv[1]) ){
   $fileToCheck = $argv[1];
}

$ph = popen ("git log","r");

$file = "";

while ( !feof($ph)) {
    $file .= fread($ph,16384);
}

pclose ($ph);

$lines = explode( PHP_EOL, $file);

for  ($i = 0 ; $i < count($lines) ; $i++ ){
    if ( preg_match ("/^commit/", $lines[$i] )) {
        $first_line = $lines[$i];
        $i++; // author
        if ( preg_match("/^Merge:/", $lines[$i])) {

            $merge = $lines[$i];
            $i++; // author
            $i++;
            $date = $lines[$i];
            $i++;
            $i++;// branch ?
            if (preg_match("/pull/" , $lines[$i])
                &&
                (
                    !preg_match("/develop/",$lines[$i])
                    &&
                    !preg_match("/staging/", $lines[$i])
                )
            ) {
                //$merge = preg_replace("/Merge:/", "git diff --name-only " , $merge);
                $merge = preg_replace("/Merge:/", "git diff --name-only " , $merge);
                $merge .= " --exit-code $fileToCheck ";

 
                ob_start();
                system("$merge 2>/dev/null | grep -v 'git diff'" , $exit_code);

                $res = ob_get_clean();
                if ( preg_match("!$fileToCheck!", $res)) {
//                    echo "##########" . PHP_EOL;
                    $toRun[] = trim($res);
//                    echo $res;
//                    echo $first_line . PHP_EOL;
//                    echo $date . PHP_EOL;
//                    echo $lines[$i] . PHP_EOL;
                }
            }
        }
    }
}

/* Now get any other tasks that we might want to run 
 * which have not been merged in via a PR yet 
 */

function getAdditionalCommands(&$toRun, $fileToCheck) {

    $lsOutput="";
    $lsPH = popen("find $fileToCheck -type f", "r");
    while (!feof($lsPH)) {
        $lsOutput .= fread($lsPH,16384);
    }
    pclose ($lsPH);
    $lsOutputLines = explode(PHP_EOL, $lsOutput);
    foreach ($lsOutputLines as $lsOutputLine ){
        if (!in_array($lsOutputLine, $toRun) && $lsOutputLine != "" ){
            array_unshift($toRun, $lsOutputLine);
        }
    }

}

function printCommands($toRun) {

    if ($toRun){
        foreach ( array_reverse($toRun) as $commandsToRun) {
            echo $commandsToRun . PHP_EOL;
        }
    }
}

getAdditionalCommands($toRun, $fileToCheck);
printCommands($toRun);
