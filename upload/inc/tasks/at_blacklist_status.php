<?php

/**
 * Blacklist Status - Deaktiviert die Blacklist nach Ablauf der Deadline
 */

function task_at_blacklist_status($task)
{
    global $db, $mybb, $lang;

    // Task nur wenn die Blacklist aktiv ist
    if ($mybb->settings['activitytracker_bl_activ'] != 1) {
        return;
    }

    // Deadline aus den Settings holen (Tage)
    $deadlineDays = (int)$mybb->settings['activitytracker_bl_deadline'];
    if ($deadlineDays <= 0) {
        return; //abfangen wenn ungültiger wert oder leer
    }

    // Datum aus der lastrun tabelle holen
    $get_date_query = $db->simple_select("at_bl_lastrun", "bl_lastrun", "", array("order_by" => "blid DESC", "limit" => 1));
    $get_date = $db->fetch_field($get_date_query, "bl_lastrun");

    if (empty($get_date)) {
        return; // kein datum gefunden
    }

    // datetime zu timestamp zum rechnen
    $lastRun = strtotime($get_date);
    $now = TIME_NOW;

    // Ablaufzeit berechnen
    $expiryTime = $lastRun + ($deadlineDays * 86400);

    // ist die frist abgelaufen? 
    if ($now >= $expiryTime) {
        //setting speichern
        $db->update_query("settings", ["value" => 0], "name='activitytracker_bl_activ'");
        $db->update_query("users", ["activitytracker_bl_view" => 0], "");
        $db->update_query("users", ["activitytracker_bl_view_info" => 0], "");

        rebuild_settings();

        add_task_log($task, "Aktivitätsplugin: Blacklist automatisch deaktiviert. Ansichtseinstellung für User zurückgesetzt.");
    } else {
        add_task_log($task, "Aktivitätsplugin: Blacklist bleibt aktiv (noch nicht abgelaufen).");
    }
}
