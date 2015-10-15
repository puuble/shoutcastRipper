<?php
  error_reporting(E_ALL);
  set_time_limit(0);
  date_default_timezone_set('Europe/Istanbul');

  require 'lib/ripper.php';
  function hourToSecond($saat){
    $birSaat = '3600'; //saniye
    $sonuc = $birSaat * $saat;
    return $sonuc;
  }

  if(isset($_GET['saat']) != ""){
    $saat = $_GET['saat'];
  } else {
    $saat = 1;
  }

  $url = "http://188.227.181.186:9450";
  $ripper = new SHOUTcastRipper\Ripper(array(
    'path'               => '../',
    'split_tracks'       => true,
    'max_track_duration' => 60,

  ));

// */1 * * * * /usr/bin/curl  -o temp.txt http://
  echo "ripping...\n";
  $ripper->start($url);

?>
