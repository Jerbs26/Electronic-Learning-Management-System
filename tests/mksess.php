<?php
// usage: php mksess.php SID user_id role first last
session_id($argv[1]); session_start();
$_SESSION = ['user_id'=>(int)$argv[2],'role'=>$argv[3],'first_name'=>$argv[4],'last_name'=>$argv[5],'username'=>'t'];
session_write_close(); echo session_save_path() ?: sys_get_temp_dir(), "\n";
