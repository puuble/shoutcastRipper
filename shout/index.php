<?php
error_reporting(E_ALL);
set_time_limit(0);
date_default_timezone_set('Europe/Istanbul');
require 'lib/ripper.php';
require_once __DIR__.'/vendor/autoload.php';


$app = new Silex\Application();

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array (

        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'shout',
        'user'      => 'root',
        'password'  => '',
        'charset'   => 'utf8mb4',

    ),
));

$app['debug']  = true;

$app->get('/start/{hour}', function($hour) use($app) {

    $workdays = getMonthDays('workdays');
    $weekends = getMonthDays('weekends');
    $date = date('Y/n/d');

    if(in_array(date('d'),$workdays)){
        $table = 'workdays';
    } else {
        $table = 'weekends';
    }

    $get_name = date('l', strtotime($date)); //get week day
    $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars
    $day_name = strtolower($day_name); // Trim day name to 3 chars
    $date = new DateTime('now');
    $date->setTime($hour, 00);
    $hour = $date->format('H:i:s') . "\n";

    $sql = "SELECT m.shout_id, m.program_id, p.name as program_name,s.name as shout_name , m.started_at, s.url,p.is_mon,p.is_tue,p.is_wed,p.is_thu,p.is_fri,p.is_sat,p.is_sun FROM $table as m INNER JOIN programs  as  p ON m.program_id=p.id INNER JOIN shouts as s ON m.shout_id = s.id WHERE s.status = 1 AND p.is_$day_name = 1 AND m.started_at = '$hour'";
    $data = $app['db']->fetchAll($sql);

    foreach ($data as $d) {
        $url = $d['url'];
        $estimated_hour = '';
        rip($url,$estimated_hour);

    }


    return $app->json(['data'=>$data]); // $app->escape($hour); //rip($url,$saat);
});


function getMonthDays($days){

    $workdays = array();
    $type = CAL_GREGORIAN;
    $month = date('n'); // Month ID, 1 through to 12.
    $year = date('Y'); // Year in 4 digit 2009 format.
    $day_count = cal_days_in_month($type, $month, $year); // Get the amount of days

//loop through all days
    for ($i = 1; $i <= $day_count; $i++) {

        $date = $year.'/'.$month.'/'.$i; //format date
        $get_name = date('l', strtotime($date)); //get week day
        $day_name = substr($get_name, 0, 3); // Trim day name to 3 chars

        //if not a weekend add day to array
        if($day_name != 'Sun' && $day_name != 'Sat'){
            $workdays['workdays'][] = $i;
        } else {
            $workdays['weekends'][] = $i;
        }



    }

    return $workdays[$days];
}

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

function rip($url,$saat){

    $ripper = new SHOUTcastRipper\Ripper(array(
        'path'               => '../',
        'split_tracks'       => true,
        'max_track_duration' => hourToSecond($saat),
    ));
// */1 * * * * /usr/bin/curl  -o temp.txt http://

    $ripper->start($url);
    return ['succes'=>1];
}





$app->run();