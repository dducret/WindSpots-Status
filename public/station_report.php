<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title>WindSpots - Station Report</title>
</head>
<body>
<?php 
  $rootPath=__FILE__;
  $scriptPath=baseName($rootPath);
  $rootPath=str_replace($scriptPath,'',$rootPath);
  $rootPath=realPath($rootPath.'../');
  $rootPath=str_replace('\\','/',$rootPath);
  date_default_timezone_set('Europe/Zurich');
  $windspotsLog =  $rootPath."/log";
  $SQLiteDB = "/data/sqlite/station_report.db";
  $db = null;
  // log
  function logIt($message, $station = false) {
    global $windspotsLog;
    $dirname = dirname(__FILE__);
    if(!$station) {
      $logfile = "/error.log";
    } else {
      $logfile = "/".$station.".log";
    }
    $wlHandle = fopen($windspotsLog.$logfile, "a");
    // echo $windspotsLog.$logfile.'\n';
    $t = microtime(true);
    $micro = sprintf("%06d",($t - floor($t)) * 1000000);
    $micro = substr($micro,0,3);
    fwrite($wlHandle, Date("H:i:s").".$micro"." station_report.php: ".$message."\n");
    fclose($wlHandle);
  }
  // Station name
  // Ex: CHGE01   CH=Country GE=Location 01=Identity
  if(!isset($_POST['station'])) {
    logIt('ERROR no POST.');
    exit();
  }
  $station = $_POST['station'];
  if(strlen($station) != 6) {
    logIt('ERROR Station: ' . $station);
    exit();
  }
  logIt($station, $station);
  // Station date
  $date = $_POST['date'];
  if(strlen($date) != 15) {
    if( strlen($date) != 14) {
      logIt('ERROR Date('.strlen($date).'): ' . $date, $station);
      exit();
    }
  } else {
    // Remove the trailing newline char
    if( ord( $date[ strlen($date) -1 ] ) == 0x0A )
      $date = substr( $date, 0, strlen($date) - 1 );
  } 
  // Station data
  $data = $_POST['data'];
  if( strlen($data) > 80) {
    logIt('   Too much data.', $station);
    exit();
  }
  $data=trim($data);
  if( strlen($data) < 8) {
    logIt('   Not enough data.', $station);
    exit();
  }
  $data_array = explode("\t",$data);
  logIt('Data: '. json_encode($data_array));
  // Station location
  $location = $_POST['location'];
  $location=trim($location);
  if( strlen($location) > 26) {
    logIt('   Wrong location.', $station);
    exit();
  }
  $location_array = explode("\t",$location);
  //logIt('Location: '. json_encode($location_array));
  // format weather station time
  list($hour, $minute) = explode('.', $data_array[0]);
  $min = floor($minute/(1000/6));
  $sec = round(($minute - ($min * (1000/6))),0);
  if ($sec > 166) {
    $min++;
    $sec=$sec-166;
  }
  $sec = abs(floor(($sec*60)/166));
  if($sec>=60){
    $min++;
    $sec=$sec-60;
  }
  if($hour > 23)
    $hour=0;
  if($hour < 1)
    $hour=0;
  if($min > 59)
    $min=0;
  if($min < 1)
    $min=0;
  if($sec > 59)
    $sec=0;
  if($sec < 1)
    $sec=0;
  if($min < 10)
    $min = "0".$min;
  if($sec < 10)
    $sec = "0".$sec;
  $data_array[0]=$hour.$min.$sec;
  // 
  $battery = 0;
  if(isset($data_array[6])) {
    $battery = round($data_array[6], 1);
  }
  try { 
    $db = new SQLite3($SQLiteDB); 
  } catch(Exception $exception) { 
    logIt('ERROR Open DB: '.$exception->getMessage()); 
    die();
  }
  $db->enableExceptions();
  try { 
    $db->exec("CREATE TABLE IF NOT EXISTS station_report (station TEXT PRIMARY KEY, name TEXT, stndate TEXT, stntime TEXT,
                direction TEXT, speed TEXT, averagespeed TEXT, temperature TEXT, barometer TEXT, battery TEXT, imageage TEXT,
                altitude TEXT, latitude TEXT, longitude TEXT, version TEXT)");
  } catch(Exception $exception) { 
    $db->close();
    logIt('ERROR Create table: '.$exception->getMessage()); 
    die();
  }
  $db->busyTimeout(5000);
  $stationName = SQLite3::escapeString( $data_array[7] );
  $db->exec("INSERT OR REPLACE INTO station_report (station, name,
            stndate, stntime,
            direction, speed, averagespeed,
            temperature, barometer, battery, imageage,
            altitude, latitude, longitude, version) 
          VALUES('$station', '$stationName',
            '$date','$data_array[0]',
            '$data_array[1]','$data_array[2]', '$data_array[3]',
            '$data_array[4]','$data_array[5]', '$battery', '$data_array[8]',
            '$location_array[0]','$location_array[1]','$location_array[2]',
            '$data_array[9]')");
  $db->close();
?>
</body>
</html> 
