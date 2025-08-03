<?php

/**
 * Blacklist Tast des Aktivitäts Plugin
 */

function task_at_blacklist($task)
{
    global $db, $mybb, $lang;
    add_task_log($task, "Aktivitätsplugin: Task Blacklist ausgeführt.");

    $now = new DateTime();
    $time = $now->format('Y-m-d');
    $excluded = trim($mybb->settings['activitytracker_excludeduid']);
    $usergroups = $mybb->settings['activitytracker_groups'];
    $ext_str = "";

    if ($excluded != "") {
        $ext_str =  "uid not in ({$excluded})";
    }

    $get_user_bl = $db->simple_select("users", "uid", "{$ext_str}");
    //alle user durchgehen
    add_task_log($task, "Aktivitätsplugin: Task Blacklist ausgeführt.");
    while ($user = $db->fetch_array($get_user_bl)) {
        $bl_entry = array();
        // TODO:
        // if setting spieler - nicht chara -> holen wir nur die uids mit as_uid = 0 und holen uns dazu dann die angehangenen Accounts 
        //Für den momentanen Stand nicht nötig
        $uid = $user['uid'];
        $userinfo = get_user($uid);
        $username = $userinfo['username'];

        if ($userinfo['away'] != 0) {
            $fetchlastdate = $db->fetch_field($db->simple_select("at_blacklist", "away_last", "uid = '{$uid}'", array("order by" => "max(away_last)", "limit" => 1)), "");
        } else {
            $fetchlastdate = $time;
        }
        // if (is_member($usergroups)) {
        //checken ob der User auf der BL stehen würde
        $blarray = activitytracker_check_blacklist($uid);
        //wenn ja hat das Array inhalt
        if (!empty($blarray)) {
            add_task_log($task, "{$userinfo['username']} wegen {$blarray[$uid]['reason']} auf der BL");
            if ($blarray[$uid]['reason'] == 'noingamestart') {
                $bl_entry = array(
                    "uid" => $uid,
                    "username" => $db->escape_string($username),
                    "reason" => 'noingamestart',
                    "away" => $userinfo['away'],
                    "bldate" => $time,
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
                    "away_last" => $fetchlastdate
                );
            }
        }
        if (!empty($bl_entry)) {
            $db->insert_query("at_blacklist", $bl_entry);
        }
    }
}
