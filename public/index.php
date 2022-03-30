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
//
function station_spot($row) {
  $html = "<tr>\r\n";
  $html = $html . "\t\t\t<!-- $row[0] - $row[1] - $row[2] - $row[3] - $row[4] - $row[5] - $row[6] - $row[7] - $row[8] - $row[9] - $row[10] - $row[11] - $row[12] - $row[13] - $row[14]-->\r\n\t\t\t";
  $ws_station_name = $row[0]; // station
  $ws_short_name = $row[1]; // name
  $ws_time = $row[2]; // Transmit date and time
  $ws_weather_station_time = $row[3]; // time
  $ws_wind_current_dir = $row[4]; // direction
  $ws_wind_max_speed_kt = round((floatval($row[5]) * 1.943844),0); // speed
  $ws_wind_current_speed_kt = round((floatval($row[6]) * 1.943844),0); // average speed
  $ws_air = $row[7]; // temperature
  $ws_barometer = $row[8]; // barometer
  $ws_battery = $row[9]; // battery
  $ws_image_age = $row[10]; // image age
  $ws_altitude = $row[11]; // altitude
  $ws_latitude = $row[12]; // latitude
  $ws_longitude = $row[13]; // longitude
  $ws_version = $row[14]; // version
  //
  $compass = array(
      "N", "NNE", "NE", "ENE",
      "E", "ESE", "SE", "SSE",
      "S", "SSW", "SW", "WSW",
      "W", "WNW", "NW", "NNW"
  );
  $ws_wind_current_dir_alpha = $compass[round($ws_wind_current_dir / 22.5) % 16];
  if($ws_time == NULL)
    $ws_time = "19000101000000";
  $hour = substr($ws_time,-6,2);
  $minute = substr($ws_time,-4,2);
  $second = substr($ws_time,-2,2);
  $day = substr($ws_time,-8,2);
  $month = substr($ws_time,-10,2);
  $year = substr($ws_time,-14,4);
  $station_time = mktime($hour, $minute, $second, $month, $day, $year);
  $now = time();
  $diff = ceil(($now - $station_time) / (60));
  // Camera
  $filestation = $ws_station_name."1";
  $filename ="/data/sites/www/data/capture/".$filestation.".jpg";
  $filetime = 0;
  $filesize = 0;
  if(file_exists($filename)) {
    $filetime = filemtime($filename);
    $filesize = filesize($filename);
  }
  $filenow = time();
  $filediff = ceil(($filenow - $filetime) / (60));
  // Station Name
  $bgcolor="";
  if($diff <= 5) { 
      $bgcolor="<td bgcolor=\"#FFFFFF\">";
      if($ws_wind_max_speed_kt==0) {
        if($ws_wind_current_dir==0) {
          $bgcolor="<td bgcolor=\"#C0C0C0\">";
        }
      }
  } else {
    if($diff <= 15)  {
      $bgcolor="<td bgcolor=\"#FF9900\">";
    } else {
      if($filediff < 10) {
        $bgcolor="<td bgcolor=\"#ADD8E6\">";
      } else {
        $bgcolor="<td bgcolor=\"#FF3300\">";
      }
    } 
  }
  $html = $html . $bgcolor;
  $humanDiff="";
  if($diff > 15)
    $humanDiff = " - " . floor($diff/1440) . " D " . floor(($diff - floor($diff/1440)*1440) / 60) . " H";
    $html = $html . "<a href=\"http://windspots.org/images.php?imagedir=capture&image=".$ws_station_name."1.jpg&uid=&text=Fermer\" target=\"_self\" >".$ws_station_name." ".$ws_short_name.$humanDiff."</a>";
    $html = $html . "<a href=\"https://www.google.com/maps/search/?api=1&query=".$ws_latitude.",".$ws_longitude."\"  target=\"_blank\" rel=\"noopener noreferrer\">&nbsp;&nbsp;[atitude ".$ws_altitude."m]</a>";
    $html = $html . "<div style='float:right'>".$ws_version."&nbsp;</div></td>";
  // Station (PC) Status
  if($diff > 15)
    $html = $html . "<td bgcolor=\"#FF3300\"></td>";
  else
    $html = $html . "<td bgcolor=\"#33FF00\"></td>";
  // Weather Stations Status
  if($ws_weather_station_time==0) {
    if($diff > 15) 
      $html = $html ."<td bgcolor=\"#FF3300\"></td>";
    else
      $html = $html ."<td bgcolor=\"#FFFF00\"></td>";
  } else {
    if($diff > 15)
      $html = $html ."<td bgcolor=\"#FF3300\"></td>";
    else
      $html = $html ."<td bgcolor=\"#33FF00\"></td>";
  }
  // Camera
  if($diff < 15) {
    if($ws_image_age > 75) {
      if($ws_image_age > 120) {
        $html = $html ."<td align=\"center\" bgcolor=\"#FF3300\">&nbsp;</td>";
      } else {
        $html = $html ."<td align=\"center\" bgcolor=\"#FFFF00\">&nbsp;</td>";
      }
    } else {
      $html = $html ."<td bgcolor=\"#33FF00\">&nbsp;</td>";
    }
  } else {
    $html = $html ."<td align=\"center\" bgcolor=\"#FF3300\">&nbsp;</td>";
  }
  // Battery
  if($ws_battery == 0) {
    $html = $html ."<td align=\"center\" bgcolor=\"#FFFFFF\">&nbsp;</td>";
  } else {
    if($ws_battery < 50) {
      $html = $html ."<td align=\"center\" bgcolor=\"#FF3300\">".$ws_battery."%</td>";
    } else {
      $html = $html ."<td align=\"center\" bgcolor=\"#33FF00\">".$ws_battery."%</td>";
    }
  }
  // Wind Direction and Speed
  if($diff < 15) {
    // Color depending on Wind Speed
    switch($ws_wind_current_speed_kt) {
      case 0:
      case 1:
      case 2:
      case 3:
      case 4:
        $bgcolor = "<td align=\"center\" bgcolor=\"#FFFFFF\">";
        break;
      case 5:
      case 6:
      case 7:
      case 8:
      case 9:
        $bgcolor = "<td align=\"center\" bgcolor=\"#00CCFF\">";
        break;
      case 10:
      case 11:
      case 12:
      case 13:
      case 14:
        $bgcolor = "<td align=\"center\" bgcolor=\"#FFCCFF\">";
        break;
      case 15:
      case 16:
      case 17:
      case 18:
      case 19:
        $bgcolor = "<td align=\"center\" bgcolor=\"#FF9900\">";
        break;
      default:
        $bgcolor = "<td align=\"center\" bgcolor=\"#FF3300\">";
        break;
    }
    $html = $html .$bgcolor .$ws_wind_current_dir_alpha ."</td>";
    $html = $html .$bgcolor .$ws_wind_current_speed_kt ."</td>";;
  } else {
    $html = $html ."<td align=\"center\" bgcolor=\"#FFFFFF\">-</td>";
    $html = $html ."<td align=\"center\" bgcolor=\"#FFFFFF\">HS</td>";
  }
  $html = $html ."<td align=\"center\">";
  // air temperature
  if($ws_air == 0)
    $html = $html ."n/a";
  else {
    if($diff < 15)
      $html = $html .$ws_air;
    else
      $html = $html ."-";
  }
  // hour
  $html = $html . "</td><td align=\"center\">".$hour. ":" .$minute."</td>";
  // date
  $html = $html . "<td align=\"center\">".$day. "/" .$month."</td>\r\n\t\t</tr>\r\n";
  print_r($html);
}
?>
<!DOCTYPE html>
<html lang="FR">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow"/>
    <meta name="description" content="Real-Time Wind Information - Le Vent en Temps R&eacute;el"/>  <meta name="keywords" content="Real-Time, Wind, Information, Vent, Temps R&eacute;el, WindSpots"/>
    <title>WindSpots Station Status</title>
    <style type="text/css">
    .normal     { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: normal; font-style: normal }
    .bold       { font-weight: bold; font-style: normal }        
    img         { vertical-align: middle; border-style: none; padding-bottom: 5px }        
    a           { font-weight: normal; font-style: normal; color: black;text-decoration: none; }        
    a:visited   { font-weight: normal; font-style: normal; color: black;text-decoration: none; }        
    a:hover     { font-weight: normal; font-style: normal; color: black;text-decoration: none; }
    table       { border: 0px none black; border-spacing: 0px }        
    td          { font-family: Arial, Helvetica, sans-serif; font-size: 10pt; font-weight: normal; font-style: normal }        
    .style1     { font-family: Arial, Helvetica, sans-serif; font-size: 18pt; font-weight: bold; color: #6666ff }
    </style>
  </head>
  <body onload="javascript:setTimeout(function(){ location.reload(); },60000);">
    <div align="center" class="style1"><img src="logo.png" alt="logo" /></div>
      <table cellspacing="1" cellpadding="0" border="1" style="border-top: 2px solid #524b98; border-bottom: 2px solid #e0e3ce; border-left: 2px solid #b8b6c1; border-right: 2px solid #8b87a0; width: 100%">
        <tr class="bgkhaki">
          <td>Station</td>
          <td style="width: 10px;">S</td>
          <td style="width: 10px;">W</td>
          <td align="center" style="width:10px;">C</td>
          <td align="center" style="width:40px;">Bat</td>
          <td align="center">Dir</td>    
          <td align="center">Knots</td>
          <td align="center">Air</td>    
          <td align="center">Time</td>
          <td align="center">Date</td>  
        </tr>
        <?php 
          try { 
            $db = new SQLite3($SQLiteDB); 
          } catch(Exception $exception) { 
            logIt('ERROR Open DB: '.$exception->getMessage()); 
            $html = "<tr><td bgcolor=\"#FF3300\">ERROR Open DB: ".$exception->getMessage()."</td></tr>";
            print_r($html);
            die();
          }
          $results = $db->query('SELECT * FROM station_report order by station');
          while ($row = $results->fetchArray()) {
            station_spot($row);
          }
          $db->close();
        ?>
      </table>
    <div class="normal">
      <br/>Version 1.42 &copy; WindSpots.org 2022<br/>
    </div>
  </body>
</html>