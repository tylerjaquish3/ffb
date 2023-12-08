<?php

include 'connections.php';


// Make curl call
function make_curl_request(string $method, string $final_url, string $params = '', $bearer = '')
{
    $ch = curl_init();

    if ($method == 'POST') {
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    }
    
    if ($bearer != '') {
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$bearer]);
    }
    curl_setopt($ch, CURLOPT_URL, $final_url);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

    $timeout = 2; // seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    $contents = curl_exec($ch);
    $ret_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $errno = curl_errno($ch);
    $error_str = curl_error($ch);

    if( $errno || $error_str ) {
    //print "Error: ${error_str} (${errno})\n";
    }

    curl_close($ch);

    $data = [
        'return_code' => $ret_code,
        'contents'    => $contents,
        'error_str'   => $error_str,
        'errno'       => $errno
    ];

    return $data;
}

///////////////////////////////////////////////////////////////////////////////
//  FUNCTION oauth_response_to_array
/// @brief Break up the oauth response data into an associate array
///////////////////////////////////////////////////////////////////////////////
function oauth_response_to_array(string $response) {
    $data = [];
    foreach (explode('&', $response) as $param) {
        $parts = explode( '=', $param );
        if (count( $parts ) == 2) {
            $data[urldecode($parts[0])] = urldecode($parts[1]);
        }
    }

    return $data;
}

/**
 * Better GI than print_r or var_dump -- but, unlike var_dump, you can only dump one variable.  
 * Added htmlentities on the var content before echo, so you see what is really there, and not the mark-up.
 * 
 * Also, now the output is encased within a div block that sets the background color, font style, and left-justifies it
 * so it is not at the mercy of ambient styles.
 *
 * Inspired from:     PHP.net Contributions
 * Stolen from:       [highstrike at gmail dot com]
 * Modified by:       stlawson *AT* JoyfulEarthTech *DOT* com 
 *
 * @param mixed $var  -- variable to dump
 * @param string $var_name  -- name of variable (optional) -- displayed in printout making it easier to sort out what variable is what in a complex output
 * @param string $indent -- used by internal recursive call (no known external value)
 * @param unknown_type $reference -- used by internal recursive call (no known external value)
 */
function do_dump(&$var, $var_name = NULL, $indent = NULL, $reference = NULL)
{
    $do_dump_indent = "<span style='color:#666666;'>|</span> &nbsp;&nbsp; ";
    $reference = $reference.$var_name;
    $keyvar = 'the_do_dump_recursion_protection_scheme'; $keyname = 'referenced_object_name';
    
    // So this is always visible and always left justified and readable
    echo "<div style='text-align:left; background-color:white; font: 100% monospace; color:black;'>";

    if (is_array($var) && isset($var[$keyvar]))
    {
        $real_var = &$var[$keyvar];
        $real_name = &$var[$keyname];
        $type = ucfirst(gettype($real_var));
        echo "$indent$var_name <span style='color:#666666'>$type</span> = <span style='color:#e87800;'>&amp;$real_name</span><br>";
    }
    else
    {
        $var = array($keyvar => $var, $keyname => $reference);
        $avar = &$var[$keyvar];

        $type = ucfirst(gettype($avar));
        if($type == "String") $type_color = "<span style='color:green'>";
        elseif($type == "Integer") $type_color = "<span style='color:red'>";
        elseif($type == "Double"){ $type_color = "<span style='color:#0099c5'>"; $type = "Float"; }
        elseif($type == "Boolean") $type_color = "<span style='color:#92008d'>";
        elseif($type == "NULL") $type_color = "<span style='color:black'>";

        if(is_array($avar))
        {
            $count = count($avar);
            echo "$indent" . ($var_name ? "$var_name => ":"") . "<span style='color:#666666'>$type ($count)</span><br>$indent(<br>";
            $keys = array_keys($avar);
            foreach($keys as $name)
            {
                $value = &$avar[$name];
                do_dump($value, "['$name']", $indent.$do_dump_indent, $reference);
            }
            echo "$indent)<br>";
        }
        elseif(is_object($avar))
        {
            echo "$indent$var_name <span style='color:#666666'>$type</span><br>$indent(<br>";
            foreach($avar as $name=>$value) do_dump($value, "$name", $indent.$do_dump_indent, $reference);
            echo "$indent)<br>";
        }
        elseif(is_int($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_string($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color\"".htmlentities($avar)."\"</span><br>";
        elseif(is_float($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".htmlentities($avar)."</span><br>";
        elseif(is_bool($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> $type_color".($avar == 1 ? "TRUE":"FALSE")."</span><br>";
        elseif(is_null($avar)) echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> {$type_color}NULL</span><br>";
        else echo "$indent$var_name = <span style='color:#666666'>$type(".strlen($avar).")</span> ".htmlentities($avar)."<br>";

        $var = $var[$keyvar];
    }
    
    echo "</div>";
}

function query($sql)
{
    global $conn, $DB_TYPE;

    if ($DB_TYPE == 'sqlite') {
        $sql = str_replace("if(", "iif(", $sql);
        $sql = str_replace("IF(", "IIF(", $sql);
        $sql = str_replace("IF (", "IIF (", $sql);

        return $conn->query($sql);
    }

    return mysqli_query($conn, $sql);
}

function fetch_array($result)
{
    global $DB_TYPE;

    if ($DB_TYPE == 'sqlite') {
        return $result->fetchArray();
    } 
        
    return mysqli_fetch_array($result);
}

/**
 * Look in table for rows matching params. If found, update. If not found, insert.
 */
function updateOrCreate(string $table, array $params, array $values)
{
    global $conn;
    $query = "SELECT * FROM {$table} WHERE ";
    foreach ($params as $key => $value) {
        $query .= "{$key} = '{$value}' AND ";
    }
    $query = substr($query, 0, -5);
    $result = query($query);
    $row = fetch_array($result);
    if ($row) {
        // update
        $query = "UPDATE {$table} SET ";
        foreach ($values as $key => $value) {
            $query .= "{$key} = '{$value}', ";
        }
        $query = substr($query, 0, -2);
        $query .= " WHERE ";
        foreach ($params as $key => $value) {
            $query .= "{$key} = '{$value}' AND ";
        }
        $query = substr($query, 0, -5);
        $result = query($query);
    } else {
        // insert
        $query = "INSERT INTO {$table} (";
        foreach ($params as $key => $value) {
            $query .= "{$key}, ";
        }
        foreach ($values as $key => $value) {
            $query .= "{$key}, ";
        }
        $query = substr($query, 0, -2);
        $query .= ") VALUES (";
        foreach ($params as $key => $value) {
            $query .= "'{$value}', ";
        }
        foreach ($values as $key => $value) {
            $query .= "'{$value}', ";
        }
        $query = substr($query, 0, -2);
        $query .= ")";
        $result = query($query);
    }

    // return the id of the item just inserted
    if ($conn->lastInsertRowID()) {
        return $conn->lastInsertRowID();
    } else {
        return $row['id'];
    }
}

?>