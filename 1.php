<?
/* 
Yenten Crypto Web Notepad Online - PHP 1.0.1

Features:
 - works in one php file and works on almost any hosting  
 - you can work with 6 online notebooks, quickly switch between them
 - an encrypted file is stored on the hosting, i.e. if the files are leaked, they will not be opened and what is inside will not be read. Decryption occurs with a key (it is also the second password) when opening the file and encrypted with it when saving. 
 - the first password is to close access to the folder by password (.htpasswd on folder)
 - automatic saving of the file when typing (it monitors that there were pressing or changes every 5 seconds and, if necessary, saves)
 - manual saving by pressing the button
 - manual backup by button (the ability to save to a remote FTP server)
 - automatic backup (1, 7, 30 days)
 - lightweight, small, opens instantly in almost any browser

 Why did I do it at all: I didn't want to store my data with someone, but access to them is needed from different devices. And here everything is reliable, hosting is your own, files are encrypted + backups, you can make these files by simply saving them to your hard drive. For myself - a convenient thing. 
*/


//error_reporting(0);
error_reporting(E_ERROR | E_PARSE);
//@ini_set('display_errors', 0);


/*configure*/
//name
$names="|Text1|Text2|Text3|Text4|Text5|Text6";
$enable_ftp_upload="0";

$names_arr=explode("|",$names);

$default_file="";

//read mumber from cookies
if ($_COOKIE[note_file]) {
    $number_cook_file = $_COOKIE[note_file];
    $default_file="$number_cook_file";
    //echo "read cookies file: $default_file";
}

if(isset($_GET['file'])){
    $_GET[file] = (int)$_GET[file];
    $default_file="$_GET[file]";
    setcookie("note_file", $_GET['file'], time() + 21600);
}    

$default_file_unlune="_$default_file";
$default_file_int=$default_file;

$note_name = "note$default_file_unlune.txt";


//get pass and safe to cookies
if(isset($_POST['pass'])){
    //echo "pass ".$_POST['pass'];
    setcookie("key_passw", $_POST['pass'], time() + 21600);
    header("refresh: 0;");
    //header ( 'location: ?' );
    die;
}

//authorization
if (!$_COOKIE[key_passw]) {
       //echo "Cookies not installed, authorization required:<br><br>";
       echo "Please enter password, authorization required:<br><br>";
       echo "<form action='' method='POST'><input type='text' name='pass'> <input type='submit' value='Enter'></form>";
       die;
       }

//read cookies
$key_passw = $_COOKIE[key_passw];


$note_content = '';
if( file_exists($note_name) ){
	$note_content = htmlspecialchars( file_get_contents($note_name) );
// Decode
$ciphertext = $note_content;
$c = base64_decode($ciphertext);
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = substr($c, 0, $ivlen);
$hmac = substr($c, $ivlen, $sha2len=32);
$ciphertext_raw = substr($c, $ivlen+$sha2len);
$plaintext = openssl_decrypt($ciphertext_raw, $cipher, $key_passw, $options=OPENSSL_RAW_DATA, $iv);
$calcmac = hash_hmac('sha256', $ciphertext_raw, $key_passw, $as_binary=true);
if (hash_equals($hmac, $calcmac))
{
    //echo $plaintext;
    $note_content = "$plaintext";
} else {$note_content="ERROR DECODE"; die;}
}

//backup every open
copy($note_name, "backup_auto_note$default_file_unlune.bak");


//check the backup 1 day
//copy($note_name, 'backup_1_note.bak');
if (file_exists("backup_1_note$default_file_unlune.bak")) {
    $ustarelo_sek=time()-filemtime("backup_1_note$default_file_unlune.bak");
    if ($ustarelo_sek>"86400") {copy($note_name, "backup_1_note$default_file_unlune.bak");}
} else {echo "no such file - create a file for backup<br>"; copy($note_name, "backup_1_note$default_file_unlune.bak");}

//check the backup 7 day
//copy($note_name, 'backup_7_note.bak');
if (file_exists("backup_7_note$default_file_unlune.bak")) {
    $ustarelo_sek=time()-filemtime("backup_7_note$default_file_unlune.bak");
    if ($ustarelo_sek>"604800") {copy($note_name, "backup_7_note$default_file_unlune.bak");}
} else {echo "no such file - create a file for backup<br>"; copy($note_name, "backup_7_note$default_file_unlune.bak");}

//check the backup 30 day
//copy($note_name, 'backup_30_note.bak');
if (file_exists("backup_30_note$default_file_unlune.bak")) {
    $ustarelo_sek=time()-filemtime("backup_30_note$default_file_unlune.bak");
    if ($ustarelo_sek>"2592000") {copy($note_name, "backup_30_note$default_file_unlune.bak");}
} else {echo "no such file - create a file for backup<br>"; copy($note_name, "backup_30_note$default_file_unlune.bak");}



if(isset($_SERVER['HTTP_X_REQUESTED_WITH'])){
	// Запрос AJAX 

	if(isset($_POST['note'])){
		// Writing the file to disk
		$_POST['note'] = iconv( "UTF-8","cp1251", $_POST['note']);

//manually display the password
//echo "{\"saved\":1,\"time\":\"$key_passw\"}"; die;

//manually file name
//echo "{\"saved\":1,\"time\":\"$note_name\"}"; die;
    
// Encrypt - crypt sha
$plaintext = $_POST['note'];
$ivlen = openssl_cipher_iv_length($cipher="AES-128-CBC");
$iv = openssl_random_pseudo_bytes($ivlen);
$ciphertext_raw = openssl_encrypt($plaintext, $cipher, $key_passw, $options=OPENSSL_RAW_DATA, $iv);
$hmac = hash_hmac('sha256', $ciphertext_raw, $key_passw, $as_binary=true);
$ciphertext = base64_encode( $iv.$hmac.$ciphertext_raw );
//echo $ciphertext.'<br>';
$_POST['note']=$ciphertext;    
    
    file_put_contents($note_name, $_POST['note']);
    $vreama_min=date("H:i:s");
		echo "{\"saved\":1,\"time\":\"$vreama_min\"}";
	}
  
	//get request for manual backup
  if(isset($_POST['backup_manual'])){
		copy($note_name, "backup_manual_note$default_file_unlune.bak");
    $vreama_min=date("H:i:s");

if ($enable_ftp_upload=="1") {
    //загружаем файл по ftp
    $ftp_server = "ftp.domain.com"; // Address of FTP server.
    $ftp_user_name = "user"; // Username
    $ftp_user_pass = "pass"; // Password
    $conn_id = ftp_connect($ftp_server);
    $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
    // enable passive ftp mode
    ftp_pasv($conn_id, true);
    //note_1.txt
    $file_to_ftp = $note_name;
    if (ftp_put($conn_id, $file_to_ftp, $file_to_ftp, FTP_ASCII)) {
     //echo "$file_to_ftp successfully uploaded to remote server\n";
     $vreama_min =$vreama_min.", successfully uploaded to remote server";
    } else {
     $vreama_min =$vreama_min.", Failed upload $file_to_ftp to remote server";
     
    }
    // close the connection
    ftp_close($conn_id);
}

		echo "{\"saved\":1,\"time\":\"$vreama_min\"}";
	}  

	exit;
}




?>

<!DOCTYPE html>
<html>
    <head>
        <meta charset="win-1251" />
        <title>My notebook <?=$default_file?></title>
        <!-- Шрифт из коллекции Google -->
        <link href="https://fonts.googleapis.com/css2?family=Roboto&display=swap" rel="stylesheet">
        <!--[if lt IE 9]>
          <script src="https://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
        <![endif]-->
    </head>
    <body>

<style>
*{
	margin:0;
	padding:0;
}

html{
	background:url('img/background.jpg') no-repeat center center;
	min-height:100%;
	background-size:cover;
}

body{
	font:14px/1.3 'Segoe UI',Arial, sans-serif;
	color:#444;
}

a, a:visited {
	outline:none;
	color:#43819b;
}

a:hover{
	text-decoration:none;
}

section, footer, header{
	display: block;
}


/*-------------------------
	Pad
--------------------------*/

#pad{
	position:relative;
	width: 75%;
	margin: 20px auto 10px;
}

#note{
	font-family: 'Roboto', sans-serif;
	line-height: 17px;
	color:#444;
	background-color: #eeeeee;
	display: block;
	border: none;
	width: 100%;
	min-height: 370px;
	/*overflow: hidden;*/
  overflow: initial;
	resize: none;
	outline: 0px;
	padding: 0 10px 0 35px;
  padding-top: 10px;
}

#pad h2{
	background-color: #195378;
	overflow: hidden;
  text-indent: 34px;
	height: 48px;
  padding-top: 13px;  
	position: relative;
    color: #d6d6d6;
}

#pad:after{
	position:absolute;
	content:'';
  background-color: #bdbdbd;
	width:100%;
	height:40px;
}

.buttons {
	width: 75%;
	margin: 20px auto 10px;
  margin-top: 60px;
  color: #e8e8e8;
}

button {
    padding: 5px 30px;
    color: #2032bb;
}

.afile {
    padding: 5px 16px;
}

.block_backup {
    float: left;
    width: 390px;
    margin: 10px;
    padding: 4px;
    border: 2px dashed #a5a5a5;
}

.block_backup a{
    color: #8f9eca;
}    

</style>

<div id="pad">
  &nbsp;
<?
for ($i = 1; $i <= 6; $i++) {
    // if there is a name in the $names_arr array, then display the name of the arrays
    if ($names_arr[$i]!="") {$name_razdel=$names_arr[$i];} else {$name_razdel="";} 
    echo "<a class=\"afile\" href=\"?file=$i\">$i) $name_razdel</a> | ";
}
?>
	<h2>My notebook: <?=$default_file?></h2>
	<textarea id="note"><?php echo $note_content ?></textarea>
  
  
</div>

<div style="clear: both;"></div>



<div class="buttons">
    <button id='newButton'>Save notepad...</button>
    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
    <button id='newBackup'>Create backup</button>
    <br><br>
<?
for ($i_back = 1; $i_back <= 6; $i_back++) {
    if (!file_exists("backup_manual_note_$i_back.bak")) {
        echo "no such file - create a file for backup"; copy("note_$i_back.txt", "backup_manual_note_$i_back.bak");
    }
?>
<div class="block_backup">
    <a target="_blank" href="backup_auto_note_<?=$i_back?>.bak">backup_auto_note_<?=$i_back?>.bak</a> &nbsp;&nbsp;&nbsp; [<? echo date ("d F Y H:i:s",strtotime('+3 hour', filemtime("backup_auto_note_$i_back.bak")) ); ?>]
    <br>
    <a target="_blank" href="backup_manual_note_<?=$i_back?>.bak">backup_manual_note_<?=$i_back?>.bak</a> &nbsp;&nbsp;&nbsp; [<? echo date ("d F Y H:i:s",strtotime('+3 hour', filemtime("backup_manual_note_$i_back.bak")) ); ?>]
    <br>
    <a target="_blank" href="backup_1_note_<?=$i_back?>.bak">backup_1_note_<?=$i_back?>.bak</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [<? echo date ("d F Y H:i:s",strtotime('+3 hour', filemtime("backup_1_note_$i_back.bak")) ); ?>]
    <br>
    <a target="_blank" href="backup_7_note_<?=$i_back?>.bak">backup_7_note_<?=$i_back?>.bak</a> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp; [<? echo date ("d F Y H:i:s",strtotime('+3 hour', filemtime("backup_7_note_$i_back.bak")) ); ?>]
    <br>
    <a target="_blank" href="backup_30_note_<?=$i_back?>.bak">backup_30_note_<?=$i_back?>.bak</a> &nbsp;&nbsp;&nbsp; [<? echo date ("d F Y H:i:s",strtotime('+3 hour', filemtime("backup_30_note_$i_back.bak")) ); ?>]
    <br>
</div>    
<?
}
?>    
            
</div>



        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-1.8.1.min.js"></script>
        <script src="script.js"></script>

    </body>
</html>
<?


?>
