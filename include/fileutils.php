<?php
   if(!function_exists('scandir')) {
    function scandir($DIRECTORY, $ORDER=0) {
        //  array scandir(string directory [,int sorting_order])
        //inputs: $PATH
        //returns an array of files
        //mimics the value of php v5 scandir function
        //Returns an array of files and directories from the directory.
        //If directory  is not a directory, then boolean FALSE is returned

        //By default, the sorted order is alphabetical in ascending order.
        // If the optional sorting_order is used (set to 1),
        // then sort order is alphabetical in descending order.
        if(is_dir(!$DIRECTORY)) return FALSE;

        $file_array = Array();
        $handle = opendir($DIRECTORY);
        if($handle == FALSE) return FALSE;

        while (false !== ($file = readdir($handle))) {
             if ($file != "." && $file != "..") {
                  if (!is_dir($file)) {  //workaround, is_file seems to fail
                      $file_array[] = $file;
                  }
             }
        }

        closedir($handle);
        if($order)
           rsort($file_array);
        else
           sort ($file_array);

        reset ($file_array);

        return $file_array;
    }
   }
?>