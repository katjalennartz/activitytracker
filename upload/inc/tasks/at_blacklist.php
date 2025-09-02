<?php

/**
 * Blacklist Tast des Aktivitäts Plugin
 */

function task_at_blacklist($task)
{
    global $db, $mybb, $lang;
    // add_task_log($task, "Aktivitätsplugin: Task Blacklist ausgeführt.");

    // nur jede 2. Woche ausführen
    if ($mybb->settings['activitytracker_bl_turnus'] == '2weekly') {
        $now = new DateTime();
        // aktuelle Kalenderwoche
        $weekNumber = (int) $now->format('W'); 

        // nur jede 2. Woche
        if ($weekNumber % 2 !== 0) {
            add_task_log($task, "Aktivitätsplugin: Task wird diese Woche übersprungen (alle 2 Wochen).");
            return;
        }
    }
    
    $icelist =  $mybb->settings['activitytracker_ice'];
    $ice = 0;
    $now = new DateTime();
    $time = $now->format('Y-m-d');
    $excluded = trim($mybb->settings['activitytracker_excludeduid']);
    $usergroups = $mybb->settings['activitytracker_groups'];
    $ext_str = "";

    if ($excluded != "") {
        $ext_str =  "uid not in ({$excluded})";
    }
    $uid = "";
    //test auf eisliste 
    if ($mybb->settings['activitytracker_ice'] == 1) {
        //Ist der Charakter gerade auf Eis gelegt
        if ($db->num_rows(($db->simple_select("at_whitelist", "*", "uid = '{$uid}'")))) {
            $ice = 1;
        } else {
            $ice = 0;
        }
    }

    $get_user_bl = $db->simple_select("users", "uid", "{$ext_str}");
    //alle user durchgehen
    // add_task_log($task, "Aktivitätsplugin: Task Blacklist ausgeführt.");
    while ($user = $db->fetch_array($get_user_bl)) {
        // echo "hal";
        $bl_entry = array();
        // TODO:
        // if setting spieler - nicht chara -> holen wir nur die uids mit as_uid = 0 und holen uns dazu dann die angehangenen Accounts 
        //Für den momentanen Stand nicht nötig
        $uid = $user['uid'];
        $userinfo = get_user($uid);
        $username = $userinfo['username'];

        if ($userinfo['away'] != 0) {
            //wir holen uns das letzte datum, wo der user abwesend gemeldet war
            $fetchlastdate = $db->fetch_field($db->simple_select("at_blacklist", "away_last", "uid = '{$uid}'", array("order by" => "max(away_last)", "limit" => 1)), "");
            //Der user ist dieses Mal abwesend gemeldet
            $away_this = $time;
            //im last_away steht nichts drin - er war vorher noch nie abwesend gemeldet

            //es gibt ein last_away

            //Der User war noch nie abwesend gemeldet während der Blacklist
            // if (is_null($fetchlastdate)) {
            //     $fetchlastdate = $time;
            // } else {
            // //Der User war schon einmal abgemeldet

            // }
        }
        // if (is_member($usergroups)) {
        //checken ob der User auf der BL stehen würde
        $blarray = activitytracker_check_blacklist($uid);
        //wenn ja hat das Array inhalt
        if (!empty($blarray)) {
            // add_task_log($task, "{$userinfo['username']} wegen {$blarray[$uid]['reason']} auf der BL");
            if ($blarray[$uid]['reason'] == 'noingamestart') {
                $bl_entry = array(
                    "uid" => $uid,
                    "username" => $db->escape_string($username),
                    "reason" => 'noingamestart',
                    "away" => $userinfo['away'],
                    "bldate" => $time,
                    "ice" => $ice,
                    "away_this" => $away_this,
                    "away_last" => $fetchlastdate
                );
            } elseif ($blarray[$uid]['reason'] == 'tooldscenes') {
                $bl_entry = array(
                    "uid" => $uid,
                    "username" => $db->escape_string($username),
                    "reason" => 'tooldscenes',
                    "tids" => $blarray[$uid]['tid'],
                    "away" => $userinfo['away'],
                    "bldate" => $time,
                    "ice" => $ice,
                    "away_this" => $away_this,
                    "away_last" => $fetchlastdate
                );
            } elseif ($blarray[$uid]['reason'] == 'noingamescene') {
                $bl_entry = array(
                    "uid" => $uid,
                    "username" => $db->escape_string($username),
                    "reason" => 'noingamescene',
                    "tids" => $blarray[$uid]['tid'],
                    "away" => $userinfo['away'],
                    "bldate" => $time,
                    "ice" => $ice,
                    "away_this" => $away_this,
                    "away_last" => $fetchlastdate
                );
            } elseif ($blarray[$uid]['reason'] == 'noapplication') {
                $bl_entry = array(
                    "uid" => $uid,
                    "username" => $db->escape_string($username),
                    "reason" => 'noapplication',
                    "tids" => $blarray[$uid]['tid'],
                    "away" => $userinfo['away'],
                    "bldate" => $time,
                    "ice" => $ice,
                    "away_this" => $away_this,
                    "away_last" => $fetchlastdate
                );
            }
        }
        var_dump($blarray);
        if (!empty($bl_entry)) {
            $db->insert_query("at_blacklist", $bl_entry);
        }
    }
}
