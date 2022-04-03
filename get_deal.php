<?php
ini_set("display_errors","1");
ini_set("display_startup_errors","1");
ini_set('error_reporting', E_ALL);
writetolog($_REQUEST, 'new request');

$cnt = $_REQUEST['cnt'];
$deal = $_REQUEST['deal'];

//AUTH Б24
require_once('auth.php');

$countForDeal = 0;
$totalSummOfDeal = 0;
$next = 'N';
$globalcount = 0;
$start = 0;
$totalDeal = 1;

$date = new DateTime();
$date->modify('-32 day');
$newDate = $date->format('Y-m-d');

$dealinfo = executeREST(
    'crm.deal.list',
    array(
            'order' => array(
                'CLOSEDATE' => 'DESC',
            ),
            'filter' => array(
                'CONTACT_ID' => $cnt,
                'STAGE_ID' => 'C7:WON',
                '>CLOSEDATE' => $newDate,
            ),
            'select' => array(
                'ID', 'CLOSEDATE',
            ),
        ),
    $domain, $auth, $user);

    $totalWinDeals = $dealinfo['total'];

    //writetolog($totalWinDeals, 'totalWinDeals');

    if($totalWinDeals > 0){
    while($globalcount <= $totalDeal){    
        $next = 'Y';
        $dealinfo = executeREST(
        'crm.deal.list',
        array(
                'order' => array(
                    'CLOSEDATE' => 'DESC',
                ),
                'filter' => array(
                    'CONTACT_ID' => $cnt,
                    'STAGE_ID' => 'C7:WON',
                ),
                'select' => array(
                    'ID', 'OPPORTUNITY',
                ),
                'start' => $start,
            ),
    $domain, $auth, $user);
    $totalDeal = $dealinfo['total'];
    
    if($totalDeal != 0){
    
        while($countForDeal <= 49){
        $summOfDeal = $dealinfo['result'][$countForDeal]['OPPORTUNITY'];
        $totalSummOfDeal = $totalSummOfDeal + $summOfDeal;
        $globalcount++;
        $countForDeal++;
        }
        $countForDeal = 0;
        $start = $start + 50;
        $nextPage = $dealinfo['next'];
    }
}
    writetolog($totalSummOfDeal, 'dealinfo');
    }else{
        $next = 'N';
    }

    $merge = 'DEAL_' . $deal;
if($totalSummOfDeal > 1 and $next == 'Y'){
    writeToLog('Give discount');
    $startworkflow = executeREST(
    'bizproc.workflow.start',
    array(
            'TEMPLATE_ID' => '227',	
            'DOCUMENT_ID' => array (
                'crm', 'CCrmDocumentDeal', $merge,
            ),
            'PARAMETERS' => array(
                'Parameter1' => 'Y',
                'Parameter2' => $totalSummOfDeal,
            ),
        ),
    $domain, $auth, $user);
}else{
    writeToLog('NOT give discount');
}

function executeREST ($method, array $params, $domain, $auth, $user) {
    $queryUrl = 'https://'.$domain.'/rest/'.$user.'/'.$auth.'/'.$method.'.json';
    $queryData = http_build_query($params);
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $queryUrl,
        CURLOPT_POSTFIELDS => $queryData,
    ));
    return json_decode(curl_exec($curl), true);
    curl_close($curl);
}

function writeToLog($data, $title = '') {
$log = "\n------------------------\n";
$log .= date("Y.m.d G:i:s") . "\n";
$log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
$log .= print_r($data, 1);
$log .= "\n------------------------\n";
file_put_contents(getcwd() . '/get_deal.log', $log, FILE_APPEND);
return true;
}

?>