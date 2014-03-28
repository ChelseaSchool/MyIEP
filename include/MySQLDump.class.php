<?php

/* Notes
 * This code is cribbed from another dev, who is credited incompletely.
 * Purpose: provide a dump of database structure and content; prolly for analysis and troubleshooting
 * Slightly modified by core dev
 * output is not escaped as far as I can tell
 * This can by achieved through MySQL from cli or phpMyadmin via web interface; there is probably a function to get an sqldump to a specific place in a format the database can interpret.
 * note: this is the first class (object-oriented structure) I've encountered.
 * To see whether this collection of functions is effective, we need to search and see how it's called and then used
 */
/*
MySQL Dump PHP class by Avram, avramyu@gmail.com
*/

class MySQLDump {

    var $tables = array();
    var $connected = false;
    var $output;
    var $droptableifexists = false;
    var $mysql_error;
    
function connect($host,$user,$pass,$db) {   
    $return = true;
    $conn = @mysql_connect($host,$user,$pass);
    if (!$conn) { $this->mysql_error = mysql_error(); $return = false; }
    $seldb = @mysql_select_db($db);
    if (!$seldb) { $this->mysql_error = mysql_error();  $return = false; }
    $this->connected = $return;
    return $return;
}

function list_tables() {
    $return = true;
    if (!$this->connected) { $return = false; }
    $this->tables = array();
    $sql = mysql_query("SHOW TABLES");
    while ($row = mysql_fetch_array($sql)) {
        array_push($this->tables,$row[0]);
    }
    return $return;
}

function list_values($tablename) {
    $sql = mysql_query("SELECT * FROM $tablename");
    $this->output .= "\n\n-- Dumping data for table: $tablename\n\n";
    while ($row = mysql_fetch_array($sql)) {
        $broj_polja = count($row) / 2;
        $this->output .= "INSERT INTO `$tablename` VALUES(";
        $buffer = '';
        for ($i=0;$i < $broj_polja;$i++) {
            $vrednost = trim($row[$i]);
            $vrednost = str_replace("\n",'',$vrednost);
            if (!is_integer($vrednost)) { $vrednost = "'".mysql_real_escape_string($vrednost)."'"; } 
            $buffer .= $vrednost.', ';
        }
        $buffer = substr($buffer,0,count($buffer)-3);
        $this->output .= $buffer . ");\n";
    }   
}

function dump_table($tablename) {
    $this->output = "";
    $this->get_table_structure($tablename); 
    $this->list_values($tablename);
    return $this->output;
}

function get_table_structure($tablename) {
    $this->output .= "\n\n-- Dumping structure for table: $tablename\n\n";
    if ($this->droptableifexists) { $this->output .= "DROP TABLE IF EXISTS `$tablename`;\nCREATE TABLE `$tablename` (\n"; }
        else { $this->output .= "CREATE TABLE `$tablename` (\n"; }
    $sql = mysql_query("DESCRIBE $tablename");
    $this->fields = array();
    while ($row = mysql_fetch_array($sql)) {
        $name = $row[0]; //name
        $type = $row[1]; //type
        $null = $row[2]; //null?
        if (empty($null)) { $null = "NOT NULL"; }
        //begin added M. Nielsen
        if ($null[0] == "N") { $null = "NOT NULL"; }
        if ($null[0] == "Y"){ $null = "NULL"; }
        //end added M. Nielsen
        $key = $row[3]; //(primary) key - PRI za primary
        if ($key == "PRI") { $primary = $name; }
        $default = $row[4]; //default
        $extra = $row[5];
        if ($extra !== "") { $extra .= ' '; } //makeup
        $this->output .= "  `$name` $type $null $extra,\n";
    }
    $this->output .= "  PRIMARY KEY  (`$primary`)\n);\n";
}

}
?>
