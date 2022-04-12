<?php
ini_set("display_errors", "1");
ini_set("display_startup_errors", "1");
ini_set('error_reporting', E_ALL);

$lead = $_REQUEST['lead'];

//AUTH Б24
require_once 'auth.php';

sleep(2);

$actlist = executeREST(
    'crm.activity.list',
    array(
        'order' => array(
            "DATE_CREATE" => "DESC",
        ),
        'filter' => array(
            "OWNER_TYPE_ID" => 1,
            'OWNER_ID' => $lead,
            'DIRECTION' => 1,
            'TYPE_ID' => 4,

        ),
        'select' => array(
            "ID", "DESCRIPTION",
        ),
    ),
    $domain, $auth, $user);

$instr = $actlist['result'][0]['DESCRIPTION'];

$array = array();
preg_match_all('/[a-z0-9-\.\_\-]+@\w+\.[a-zрф]{2,}/iu', $instr, $array);
$email = $array[0][0];

preg_match("/(tiu.ru)/", $email, $matchesTiu);
preg_match("/(all-pribors.ru)/", $email, $matchesAll);
preg_match("/(turbo.yandex.ru)/", $email, $matchesTur);
preg_match("/(satom.ru)/", $email, $matchesSat);

if (empty($email)) {
    $leadget = executeREST(
        'crm.lead.get',
        array(
            'ID' => $lead,
        ),
        $domain, $auth, $user);

    $countForEmail = $leadget['result']['EMAIL'][0]['ID'];

    $leadedit = executeREST(
        'crm.lead.update',
        array(
            'ID' => $lead,
            'FIELDS' => array(

                'EMAIL' => array(array("ID" => $countForEmail, "VALUE" => ' ', "VALUE_TYPE" => "WORK")),

            ),
        ),
        $domain, $auth, $user);
    exit;
} else {

    if (empty($matchesTiu and empty($matchesAll) and empty($matchesSat) and empty($matchesTur))) {

        $leadget = executeREST(
            'crm.lead.get',
            array(
                'ID' => $lead,
            ),
            $domain, $auth, $user);

        $countForEmail = $leadget['result']['EMAIL'][0]['ID'];
        $countForEmail1 = $leadget['result']['EMAIL'][1]['ID'];

        $leadedit = executeREST(
            'crm.lead.update',
            array(
                'ID' => $lead,
                'FIELDS' => array(

                    'EMAIL' => array(array("ID" => $countForEmail, "VALUE" => $email, "VALUE_TYPE" => "WORK")),
                    'EMAIL' => array(array("ID" => $countForEmail1, "VALUE" => ' ', "VALUE_TYPE" => "WORK")),

                ),
            ),
            $domain, $auth, $user);
        $merge = 'LEAD_' . $lead;
        $startworkflow = executeREST(
            'bizproc.workflow.start',
            array(
                'TEMPLATE_ID' => '1112',
                'DOCUMENT_ID' => array(
                    'crm', 'CCrmDocumentLead', $merge,
                ),
                'PARAMETERS' => array(
                    //'Parameter1' => $source,
                ),
            ),
            $domain, $auth, $user);
    } else {

    }
}
function executeREST($method, array $params, $domain, $auth, $user)
{
    $queryUrl = 'https://' . $domain . '/rest/' . $user . '/' . $auth . '/' . $method . '.json';
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

function writeToLog($data, $title = '')
{
    $log = "\n------------------------\n";
    $log .= date("Y.m.d G:i:s") . "\n";
    $log .= (strlen($title) > 0 ? $title : 'DEBUG') . "\n";
    $log .= print_r($data, 1);
    $log .= "\n------------------------\n";
    file_put_contents(getcwd() . '/generateconto.log', $log, FILE_APPEND);
    return true;
}
