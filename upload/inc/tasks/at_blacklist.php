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

        $fetchlastdate = NULL;

        if ($userinfo['away'] != 0) {
            $away_this = $time;

            // Prüfen, ob es schon Blacklist-Einträge gibt
            $check_previous = $db->simple_select("at_blacklist", "away_this, away_last", "uid = '{$uid}'", array("order_by" => "blid DESC", "limit" => 1));
            if ($previous_entry = $db->fetch_array($check_previous)) {
                //Wenn das letzte mal während der Blacklist away gewesen, dann speichern wir das datum im away_last
                //wenn nicht, dann übernehmen wir den alten wert (ist null, wenn noch nie away)
                $fetchlastdate = !empty($previous_entry['away_this']) ? $previous_entry['away_this'] : $previous_entry['away_last'];
            }
        } else {
            $away_this = NULL;
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

        if (!empty($bl_entry)) {
            $db->insert_query("at_blacklist", $bl_entry);
        }
    }
    //datum eintragen, für die letzte ausführung 
    $db->insert_query("at_bl_lastrun", array("bl_lastrun" => $time));
}
