<?php
// -----
// Type: Class [genericDAO (Database Abstraction Object)]
// Filename: genericDAO.php
// Author: Matthew Heinsohn
//
// The genericDAO class is used for interacing with the DB.
// 
// It contans the following functions: 
//  -Create/Distroy DB connections
//  -Execute Database Queries [queryAll(), queryRow() and queryOne()]
//  -Prepare/Sanitize data and queries for the database
//
// -----

//Load Config File
//include_once("../../config/hr_config.php");

class genericDAO {
// ----- START ----- Database Connection Functions -----
    //MySQL DB Connect
    private function db_connect(){        
        //Create MySQL DB Connection
        $this->dbConn = new mysqli(DB_SERVER, DB_USER, DB_PASS, DB_NAME);
        //Check Connection
        if($this->dbConn->connect_error){die("Connection Failed: ".$dbConn->connect_error);}
        //else{error_log("dbConn Active!");}
        return;
    }

    //MySQL DB Disconnect
    private function db_disconnect(){
        //Disconnect from DB
        $this->dbConn->close();
        //error_log("dbConn Closed!");
        return;    
    }
// ----- END ----- Database Connection Functions -----

// ----- START ----- Generic Query Functions -----
    //Query One: Returns only the first value
    public function queryOne($query){
        $this->db_connect();
        $res = $this->dbConn->query($query);
        //Check for Error
        if($this->dbConn->error){
            $this->send_mysql_error($query);
            $this->db_disconnect();
            return null;
        }
        if($res->num_rows > 0){
            $res = $res->fetch_assoc();
            $this->db_disconnect();
            $res = array_values($res);
            return $res[0];
            //return array_values($res)[0];
        }else{
            $this->db_disconnect();
            return null;
        }
    }

    //Query Row: Returns the first row
    public function queryRow($query){
        $this->db_connect();
        $res = $this->dbConn->query($query);
        //Check for Error
        if($this->dbConn->error){
            $this->send_mysql_error($query);
            $this->db_disconnect();
            return null;
        }
        if($res->num_rows > 0){
            $res = $res->fetch_assoc();
            $this->db_disconnect();
            return $res;
        }else{
            $this->db_disconnect();
            return null;
        }
    }

    //Query All: Returns the whole query result
    public function queryAll($query){
        $this->db_connect();
        $res = $this->dbConn->query($query);
        //Check for Error
        if($this->dbConn->error){
            $this->send_mysql_error($query);
            $this->db_disconnect();
            return null;
        }
        if($res->num_rows > 0){
            while($row = $res->fetch_assoc()){$results_array[] = $row;}
            $this->db_disconnect();
            return $results_array;
        }
        else{
            $this->db_disconnect();
            return null;
        }
    }

    //Query Exec: Executes Insert/Update/Delete and returns true if successful
    public function queryExec($query){
        $this->db_connect();
        $res = $this->dbConn->query($query);
        //Check for Error
        if($this->dbConn->error){
            $this->send_mysql_error($query);
            $this->db_disconnect();
            return false;
        }        
        $this->db_disconnect();
        return $res;
    }

    //Query Exec [Insert and Return ID]: Executes Insert and returns the ID of the last inserted ID
    //***** Note: Intended for Insert statements only! *****
    public function queryExecReturnId($query){
        $this->db_connect();
        $res = $this->dbConn->query($query);
        //Check for Error
        if($this->dbConn->error){
            $this->send_mysql_error($query);
            $this->db_disconnect();
            return null;
        }        
        if($res){$res = $this->dbConn->insert_id;}
        $this->db_disconnect();
        return $res;
    }
// ----- END ----- Generic Query Functions -----
    
// ----- START ----- Misc Query Functions -----
    //Get Insert/Update query fields
    public function get_query_fields($data, $table, $exclude = null){
        //Get List of Table Columns
        $query = "SHOW COLUMNS FROM $table";
        $res = $this->queryAll($query);
        foreach($res as $key=>$item){$cols[] = $item[Field];}
        //Exclude values included in the $exclude array
        if(!empty($exclude)){
            foreach($exclude as $key=>$item){unset($cols[array_search($item, $cols)]);} 
        }
        //Prepare Insert/Update String
        foreach($data as $key=>$item){
            if(in_array($key, $cols)){$query_string[] = $key." = '".$item."'";}
        }
        if(!empty($query_string)){$query_string = implode(", ", $query_string);}
        return $query_string;
    }

    //MySQL Sanitnze Function
    public function mysql_sanitize($input){
        if(is_array($input)){
            foreach($input as $var=>$val){$output[$var] = $this->mysql_sanitize($val);}
        }else{
            if(get_magic_quotes_gpc()){$input = stripslashes($input);}
            $input = $this->clean_input($input);
            $this->db_connect();
            $output = $this->dbConn->real_escape_string($input);
            $this->db_disconnect();
        }
        return $output;
    }

    //Helper Function for $this->mysql_sanitize();
    //This function will remove any strange data in the string
    private function clean_input($input){
        $search = array(
            '@<script[^>]*?>.*?</script>@si',   //Strip JS
            '@<[\/\!]*?[^<>]*?>@si',            //Stirp HTML tags
            '@<style[^>]*?>.*?</style>@siU',    //Strip Style tags
            '@<![\s\S]*?--[ \t\n\r]*>@'         //Strip multi-line comments
        );

        $output = preg_replace($search, '', $input);
        return $output;
    }

    //MySQL Error Response
    private function send_mysql_error($query){
        //Error Log Statement
        error_log(print_r("\nMYSQL ERROR [".$this->dbConn->errno."]: ".$this->dbConn->error."\nQUERY:\n".$query ,1)); 
        return;
    }
// ----- END Misc Query Functions -----
} 
