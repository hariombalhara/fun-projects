<?php
header('Access-Control-Allow-Origin:*');
header('Content-Type:text/html');
$uuid = $_COOKIE['uuid'];
//echo $uuid;
$host = $_ENV['OPENSHIFT_DB_HOST'];
$pass = $_ENV['OPENSHIFT_DB_PASSWORD'];
$username = $_ENV['OPENSHIFT_DB_USERNAME'];
$port = $_ENV['OPENSHIFT_DB_PORT'];
$conn = mysql_connect($host.":".$port,$username,$pass);
$name = $_GET['name']; //TODO CHANGE TO POST
$email = $_GET['email'];
$score = $_GET['score'];
$firstget = $_GET['firstget'];
define('DEFAULT_SCORE','-1');
if(!$conn) {
    die('Error Connecting:'.mysql_error());
}
mysql_select_db('php',$conn);
function run_query($query, $is_get_or_set/*1 for GET and 2 for SET*/){
    $res = mysql_query($query) or die(mysql_error());
    $res = array();
    $i = 0;
    if($is_get_or_set == 1) {
        while($row = mysql_fetch_array($res,MYSQL_BOTH)) {
            $res[$i++] = $row;
        }
        return $res;
    }
}
function select_all_who_match($columnName,$value) {
    $query = 'Select * from `snake` where `'.$columnName.'` = "'.$value.'"';
    $res = run_query($query,1);
    return $res;
}
function print_result_json($row) {
    echo "{
            'email':'".$row['email']."',
            'score':".$row['highestScore'].",
            'name':'".$row['username']."',
          }";
}
if(!empty($uuid)) {
    if($firstget == '1') {
        $res = select_all_who_match('sessionId',$uuid);
        print_result_json($res[0]);
    } else {
        if(!empty($score)) {
            $query = 'Update `snake` set `highestScore` = '.$score.' where `sessionId` = "'.$uuid.'"';
            run_query($query,2);
        }
    }
  
} else if($firstget == '1'){
   $uuid = uniqid();
   $res = select_all_who_match('email',$email);
   $row = $res[0];
   if(empty($row)) {
       $query = "Insert into `snake` (`email`,`sessionId`,`username`) values ('".$email."','".$uuid."','".$name."')";
       run_query($query,2);
       setcookie('uuid',$uuid,time()+20*365*24*3600);
       $row['email'] = $email;
       $row['highestScore'] = DEFAULT_SCORE;
       $row['username'] = $name;
   }
    print_result_json($row);
} else {
    die('Unknown Handler');
}
?>