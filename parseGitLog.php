<?php

/* In theory this script could be use to check changes to any
   file / directory but the original impetus was to 
   check changes within a to-be-specified 'updates' folder
*/

// default - for demo purposes

$fileToCheck = "modules/custom/jisc_updates";
if ( isset ( $argv[1]) ){
   $fileToCheck = $argv[1];
}

$fh = popen ("git log","r");

$file = "";

while ( !feof($fh)) {
    $file .= fread($fh,16384);
}

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
                    echo "##########" . PHP_EOL;
                    echo $res;
                    echo $first_line . PHP_EOL;
                    echo $date . PHP_EOL;
                    echo $lines[$i] . PHP_EOL;
                }
            }
        }
    }
}
