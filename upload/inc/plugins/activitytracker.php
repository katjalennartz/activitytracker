<?php

/**
 * Aktivitätstracker für RPGS
 * Blacklist - Whitelist - User auf Eis setzen
 * 
 * Risuena im sg
 * https://storming-gates.de/member.php?action=profile&uid=39
 * 
 * https://github.com/katjalennartz
 * https://github.com/katjalennartz/
 */

// Fehleranzeige 
// error_reporting(-1);
// ini_set('display_errors', true);

// Disallow direct access to this file for security reasons
if (!defined("IN_MYBB")) {
  die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}


function activitytracker_info()
{
  return array(
    "name"      => "Activity Tracker",
    "description"  => "Aktivitätstracker für RPGS - Blacklist / Characterstatus / Eisliste / Whitelist in Kombination oder einzeln.",
    "website"    => "https://github.com/katjalennartz",
    "author"    => "risuena",
    "authorsite"  => "https://github.com/katjalennartz",
    "version"    => "1.0.0",
    "compatibility" => "*"
  );
}

function activitytracker_install()
{
  global $db, $cache;
  // RPG Stuff Modul muss vorhanden sein
  if (!file_exists(MYBB_ADMIN_DIR . "/modules/rpgstuff/module_meta.php")) {
    flash_message("Das ACP Modul <a href=\"https://github.com/little-evil-genius/rpgstuff_modul\" target=\"_blank\">\"RPG Stuff\"</a> muss vorhanden sein!", 'error');
    admin_redirect('index.php?module=config-plugins');
  }

  activitytracker_add_db();
  activitytracker_add_settings("install");
  activitytracker_add_templates();

  /**
   * Tasks Anlegen
   */
  //Task, der per Default einmal im Monat ausgeführt wird.
  $db->insert_query('tasks', array(
    'title' => 'blacklist',
    'description' => 'Stellt die Blacklist zusammen.',
    'file' => 'at_blacklist',
    'minute' => '1',
    'hour' => '0',
    'day' => '1',
    'month' => '*',
    'weekday' => '*',
    'nextrun' => TIME_NOW + 60,
    'lastrun' => 0,
    'enabled' => 0,
    'logging' => 1,
    'locked' => 0,
  ));

  // $db->insert_query('tasks', array(
  //   'title' => 'Blacklist Autooff',
  //   'description' => 'Deaktiviert/Versteckt die Blacklist wieder.',
  //   'file' => 'activitytracker_blacklist_autooff',
  //   'minute' => '1',
  //   'hour' => '0',
  //   'day' => '1',
  //   'month' => '*',
  //   'weekday' => '*',
  //   'nextrun' => TIME_NOW + 60,
  //   'lastrun' => 0,
  //   'enabled' => 0,
  //   'logging' => 1,
  //   'locked' => 0,
  // ));

  // $db->insert_query('tasks', array(
  //   'title' => 'Blacklist Benachrichtigung',
  //   'description' => 'Benachrichtigt User per Mail oder PN wenn sie auf der Blacklist stehen',
  //   'file' => 'activitytracker_blacklist_alert',
  //   'minute' => '1',
  //   'hour' => '0',
  //   'day' => '1',
  //   'month' => '*',
  //   'weekday' => '*',
  //   'nextrun' => TIME_NOW + 60,
  //   'lastrun' => 0,
  //   'enabled' => 0,
  //   'logging' => 1,
  //   'locked' => 0,
  // ));


  // $db->insert_query('tasks', array(
  //   'title' => 'Whitelist',
  //   'description' => 'Stellt die Whitelist zusammen.',
  //   'file' => 'activitytracker_whitelist',
  //   'minute' => '1',
  //   'hour' => '0',
  //   'day' => '1',
  //   'month' => '*',
  //   'weekday' => '*',
  //   'nextrun' => TIME_NOW + 60,
  //   'lastrun' => 0,
  //   'enabled' => 0,
  //   'logging' => 1,
  //   'locked' => 0,
  // ));

  // $db->insert_query('tasks', array(
  //   'title' => 'Whitelist Autooff',
  //   'description' => 'Deaktiviert/Versteckt die Whitelist wieder.',
  //   'file' => 'activitytracker_whitelist_autooff',
  //   'minute' => '1',
  //   'hour' => '0',
  //   'day' => '1',
  //   'month' => '*',
  //   'weekday' => '*',
  //   'nextrun' => TIME_NOW + 60,
  //   'lastrun' => 0,
  //   'enabled' => 0,
  //   'logging' => 1,
  //   'locked' => 0,
  // ));

  // $db->insert_query('tasks', array(
  //   'title' => 'Eisliste',
  //   'description' => 'Überprüft die Eisliste und trägt Charaktere eventuell aus.',
  //   'file' => 'activitytracker_icelist',
  //   'minute' => '1',
  //   'hour' => '0',
  //   'day' => '1',
  //   'month' => '*',
  //   'weekday' => '*',
  //   'nextrun' => TIME_NOW + 60,
  //   'lastrun' => 0,
  //   'enabled' => 0,
  //   'logging' => 1,
  //   'locked' => 0,
  // ));
  // $cache->update_tasks();
}

//überprüft ob das Plugin in installiert ist
function activitytracker_is_installed()
{
  global $db;
  if ($db->table_exists("at_blacklist")) {
    return true;
  }
  return false;
}

//Deinstallation des Plugins
function activitytracker_uninstall()
{
  global $db, $cache;
  //nur felder löschen, die existieren

  if ($db->table_exists("at_blacklist")) {
    $db->drop_table("at_blacklist");
  }
  if ($db->table_exists("at_blacklist_strokes")) {
    $db->drop_table("at_blacklist_strokes");
  }
  if ($db->table_exists("at_whitelist")) {
    $db->drop_table("at_whitelist");
  }
  if ($db->table_exists("at_icelist")) {
    $db->drop_table("at_icelist");
  }
  if ($db->table_exists("at_scenereminder")) {
    $db->drop_table("at_scenereminder");
  }
  if ($db->table_exists("at_blacklist_deletelist")) {
    $db->drop_table("at_blacklist_deletelist");
  }
  if ($db->table_exists("at_reminder")) {
    $db->drop_table("at_reminder");
  }
  if ($db->table_exists("at_bl_lastrun")) {
    $db->drop_table("at_bl_lastrun");
  }

  //templates entfernen
  $db->delete_query("templates", "title LIKE 'activitytracker_%'");
  // Einstellungen entfernen
  $db->delete_query('settinggroups', "name = 'activitytracker'");
  $db->delete_query('settings', "name like 'activitytracker%'");

  rebuild_settings();

  // Tasks löschen
  $db->delete_query("tasks", "file='at_%'");
  $cache->update_tasks();

  if ($db->field_exists("activitytracker_bl_view", "users")) {
    $db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP activitytracker_bl_view");
  }
  if ($db->field_exists("activitytracker_bl_view_info", "users")) {
    $db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP activitytracker_bl_view_info");
  }
  if ($db->field_exists("activitytracker_bl_ice", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP activitytracker_bl_ice");
  }
  if ($db->field_exists("activitytracker_bl_ice_date", "users")) {
    $db->write_query("ALTER TABLE " . TABLE_PREFIX . "users DROP activitytracker_bl_ice_date");
  }
}

//Plugin Aktivieren
function activitytracker_activate()
{
  global $db;
  include  MYBB_ROOT . "/inc/adminfunctions_templates.php";
  find_replace_templatesets("index", "#" . preg_quote('{$header}') . "#i", '{$header}{$blacklist_index}');
  //einfügen im profil
  find_replace_templatesets("usercp_profile", "#" . preg_quote('{$contactfields}') . "#i", '{$contactfields}{$blacklist_ucp_edit}');

  //enable task
  $db->update_query('tasks', array('enabled' => 1), "file = 'at_blacklist'");
  $db->update_query('tasks', array('enabled' => 1), "file = 'at_blacklist_status'");

  //TODO: enable the other tasks
}

function activitytracker_deactivate()
{
  global $db;
  include  MYBB_ROOT . "/inc/adminfunctions_templates.php";
  find_replace_templatesets("index", "#" . preg_quote('{$blacklist_index}') . "#i", '');
  //im profil noch entfernen
  find_replace_templatesets("usercp", "#" . preg_quote('{$blacklist_ucp}') . "#i", '');
  // find_replace_templatesets("usercp_profile", "#" . preg_quote('{$blacklist_ucp_ice}') . "#i", '');

  // Disable the task
  $db->update_query('tasks', array('enabled' => 0), "file = 'at_blacklist'");
  $db->update_query('tasks', array('enabled' => 0), "file = 'at_blacklist_status'");
}

//ADMIN CP STUFF
$plugins->add_hook("admin_config_settings_change", "activitytracker_settings_change");
// Set peeker in ACP
function activitytracker_settings_change()
{
  global $db, $mybb, $activitytracker_settings_peeker;

  $result = $db->simple_select("settinggroups", "gid", "name='activitytracker'", array("limit" => 1));
  // echo "in peeker";
  $group = $db->fetch_array($result);
  $activitytracker_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}

$plugins->add_hook("admin_settings_print_peekers", "activitytracker_settings_peek");
// Add peeker in ACP
function activitytracker_settings_peek(&$peekers)
{
  global $activitytracker_settings_peeker;
  // echo "in peeker";

  if ($activitytracker_settings_peeker) {

    //Weitere Einstellungen im ACP anzeigen, je nach auswahl
    //blacklist soll aktiv sein
    $peekers[] = 'new Peeker($(".setting_activitytracker_bl"), $("#row_setting_activitytracker_bl_activ, #row_setting_activitytracker_bl_guest, #row_setting_activitytracker_bl_turnus_day, #row_setting_activitytracker_bl_turnus,#row_setting_activitytracker_bl_turnus_particular,#row_setting_activitytracker_bl_deadline, #row_setting_activitytracker_bl_autooff,#row_setting_activitytracker_bl_duration,#row_setting_activitytracker_bl_duration_days,#row_setting_activitytracker_bl_type, #row_setting_activitytracker_bl_noscenes,#row_setting_activitytracker_bl_noscenes_days,#row_setting_activitytracker_bl_applicationduration,#row_setting_activitytracker_bl_einreichung,#row_setting_activitytracker_bl_bewerberfid,#row_setting_activitytracker_bl_ingamestart,#row_setting_activitytracker_bl_ingamestart_days,#row_setting_activitytracker_bl_wobdate,#row_setting_activitytracker_bl_wobdate_thread,#row_setting_activitytracker_bl_away,#row_setting_activitytracker_bl_noaway,#row_setting_activitytracker_bl_noaway_days,#row_setting_activitytracker_bl_reminder,#row_setting_activitytracker_bl_reminder_isonbl,#row_setting_activitytracker_bl_alert,#row_setting_activitytracker_bl_alerttype,#row_setting_activitytracker_bl_reminder_stroke,#row_setting_activitytracker_bl_reminder_stroke_count,#row_setting_activitytracker_bl_reminder_stroke_reset,#row_setting_activitytracker_bl_tracker,#row_setting_activitytracker_bl_tracker_order,#row_setting_activitytracker_fidexcluded,#row_setting_activitytracker_tidexcluded"),/1/,true)';

    $peekers[] = 'new Peeker($(".setting_activitytracker_wl"), $("#row_setting_activitytracker_wl_activ,#row_setting_activitytracker_wl_guest, #row_setting_activitytracker_wl_deadline, #row_setting_activitytracker_wl_turnus_day, #row_setting_activitytracker_wl_turnus,#row_setting_activitytracker_wl_autooff,#row_setting_activitytracker_wl_regulations, #row_setting_activitytracker_wl_regulations_type, #row_setting_activitytracker_wl_regulations_days, #row_setting_activitytracker_wl_reminder"),/1/,true)';

    $peekers[] = 'new Peeker($(".setting_activitytracker_bl_turnus"), $("#row_setting_activitytracker_bl_turnus_particular"),"particular",true)';
    // $peekers[] = 'new Peeker($(".setting_activitytracker_bl_turnus"), $("#row_setting_activitytracker_bl_turnus_day"),"monthly",true)';
    // $peekers[] = 'new Peeker($(".setting_activitytracker_bl_turnus"), $("#row_setting_activitytracker_bl_turnus_day"),"particular",true)';

    $peekers[] = 'new Peeker($(".setting_activitytracker_bl_duration"), $("#row_setting_activitytracker_bl_duration_days"),"days",true)';
    $peekers[] = 'new Peeker($(".setting_activitytracker_bl_wobdate"), $("#row_setting_activitytracker_bl_duration_days"),"thread",true)';



    $peekers[] = 'new Peeker($(".setting_activitytracker_ice"), $("#row_setting_activitytracker_ice_iceplugin, #row_setting_activitytracker_ice_duration, #row_setting_activitytracker_ice_lock, #row_setting_activitytracker_ice_lock_days,#row_setting_activitytracker_ice_type"),/1/,true)';

    // ,, ,#row_setting_activitytracker_bl_deadline",#row_setting_activitytracker_bl_autooff,#row_setting_activitytracker_bl_duration,#row_setting_activitytracker_bl_duration_days,#row_setting_activitytracker_bl_type, #row_setting_activitytracker_bl_noscenes,#row_setting_activitytracker_bl_noscenes_days,#row_setting_activitytracker_bl_applicationduration,#row_setting_activitytracker_bl_einreichung,#row_setting_activitytracker_bl_bewerberfid,#row_setting_activitytracker_bl_ingamestart,#row_setting_activitytracker_bl_ingamestart_days,#row_setting_activitytracker_bl_wobdate,#row_setting_activitytracker_bl_wobdate_thread,#row_setting_activitytracker_bl_away,#row_setting_activitytracker_bl_noaway,#row_setting_activitytracker_bl_noaway_days,#row_setting_activitytracker_bl_reminder,#row_setting_activitytracker_bl_reminder_isonbl,#row_setting_activitytracker_bl_alert,#row_setting_activitytracker_bl_alerttype,#row_setting_activitytracker_bl_reminder_stroke,#row_setting_activitytracker_bl_reminder_stroke_count,#row_setting_activitytracker_bl_reminder_stroke_reset,#row_setting_activitytracker_bl_tracker,#row_setting_activitytracker_bl_tracker_order,#row_setting_activitytracker_fidexcluded,#row_setting_activitytracker_tidexcluded

    // $peekers[] = 'new Peeker($(".activitytracker_bl_noaway"), $("#activitytracker_bl_noaway_days"),/1/,true)';

    // $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_iceduration"),/1/,true)';
    // $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_icelock"),/1/,true)';
    // $peekers[] = 'new Peeker($(".activitytracker_bl_icelock"), $("#activitytracker_bl_icelock_days"),/1/,true)';
    // $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_icenumber"),/1/,true)';
  }
}

function activitytracker_add_db($type = "install")
{
  global $db;
  //Tabellen erstellen - für Blacklist
  if (!$db->table_exists("at_blacklist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_blacklist` (
    `blid` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL DEFAULT 0,
    `reason` varchar(500) DEFAULT '',
    `username` varchar(50) NOT NULL DEFAULT '',
    `tids` varchar(50) NOT NULL DEFAULT '',
    `bldate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `away` int(1) NOT NULL DEFAULT 0,
    `ice` int(1) NOT NULL DEFAULT 0,
    `away_this` datetime NULL DEFAULT NULL,
    `away_last` datetime NULL DEFAULT NULL,
    PRIMARY KEY (`blid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }
  if (!$db->table_exists("at_blacklist_strokes")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_blacklist_strokes` (
    `blsid` int(10) NOT NULL AUTO_INCREMENT,
    `uid` INT(10) NOT NULL DEFAULT '0',
    `strokedate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `stroke_mod` INT(1) NOT NULL DEFAULT 0,
    PRIMARY KEY (`blsid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  if (!$db->table_exists("at_blacklist_deletelist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_blacklist_deletelist` (
    `bldid` int(10) NOT NULL AUTO_INCREMENT,
    `uid` INT(10) NOT NULL DEFAULT '0',
    `markdate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`bldid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  //Tabelle erstellen - für Whitelist
  if (!$db->table_exists("at_whitelist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_whitelist` (
      `wlid` int(10) NOT NULL AUTO_INCREMENT,
      `uid` int(10) NOT NULL DEFAULT 0,
      `username` varchar(50) NOT NULL DEFAULT '',
      `reported_back` int(0) NOT NULL DEFAULT 0,
      PRIMARY KEY (`wlid`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }
  //Tabelle erstellen - für Eislise
  if (!$db->table_exists("at_icelist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_icelist` (
    `ilid` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL DEFAULT 0,
    `icedate` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`ilid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  //Tabelle erstellen - für Reminder
  if (!$db->table_exists("at_scenereminder")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_scenereminder` (
      `srid` int(10) NOT NULL AUTO_INCREMENT,
      `uid` int(10) NOT NULL DEFAULT 0,
      `tid` int(10) NOT NULL DEFAULT 0,
      `ignore` int(10) DEFAULT 0,
      PRIMARY KEY (`srid`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }
  if (!$db->table_exists("at_reminder")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_reminder` (
    `r_id` int(10) NOT NULL AUTO_INCREMENT,
    `uid` INT(10) NOT NULL DEFAULT '0',
    `type` varchar(50) NULL DEFAULT NULL,
    `hide` INT(10) NOT NULL DEFAULT '0',
    PRIMARY KEY (`r_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  if (!$db->table_exists("at_bl_lastrun")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_bl_lastrun` (
    `bl_id` int(10) NOT NULL AUTO_INCREMENT,
    `bl_lastrun` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`bl_id`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  if (!$db->field_exists("activitytracker_bl_view", "users")) {
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `activitytracker_bl_view` INT(1) NOT NULL DEFAULT '1'");
  }
  if (!$db->field_exists("activitytracker_bl_view_info", "users")) {
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `activitytracker_bl_view_info` INT(1) NOT NULL DEFAULT '1'");
  }
  if (!$db->field_exists("activitytracker_bl_ice", "users")) {
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `activitytracker_bl_ice` INT(1) NOT NULL DEFAULT '1'");
  }
  if (!$db->field_exists("activitytracker_bl_ice_date", "users")) {
    $db->query("ALTER TABLE `" . TABLE_PREFIX . "users` ADD `activitytracker_bl_ice_date` INT(1) NOT NULL DEFAULT '1'");
  }
}

function activitytracker_add_settings($type = "install")
{
  global $db;
  if ($type == "install") {
    //Einstellungs Gruppe anlegen
    $setting_group = array(
      'name' => 'activitytracker',
      'title' => 'Activity Tracker',
      'description' => 'Einstellungen für den Activity Tracker',
      'disporder' => 6, // The order your setting group will display
      'isdefault' => 0
    );
    $gid = $db->insert_query("settinggroups", $setting_group);
  } else {
    //update, keine Installation, Gruppe ist also schonv vorhanden
    $gid = $db->fetch_field($db->simple_select("settinggroups", "gid", "name = 'activitytracker'"), "gid");
  }

  $setting_array = activitytracker_settingarray();

  if ($type == "install") {
    foreach ($setting_array as $name => $setting) {
      $setting['name'] = $name;
      $setting['gid'] = $gid;
      $db->insert_query('settings', $setting);
    }
  } else {
    //array mit settings durchgehen
    foreach ($setting_array as $name => $setting) {
      $setting['name'] = $name;
      $setting['gid'] = $gid;

      //alte einstellung aus der db holen
      $check = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");
      $check2 = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");

      $check = $db->num_rows($check);
      //noch gar nicht vorhanden, also hinzufügen
      if ($check == 0) {
        $db->insert_query('settings', $setting);
        echo "Setting: {$name} wurde hinzugefügt.<br>";
      } else {
        //die einstellung gibt es schon, wir testen ob etwas verändert wurde
        while ($setting_old = $db->fetch_array($check2)) {
          if (
            $setting_old['title'] != $setting['title'] ||
            $setting_old['description'] != $setting['description'] ||
            $setting_old['optionscode'] != $setting['optionscode'] ||
            $setting_old['disporder'] != $setting['disporder']
          ) {
            //wir wollen nicht den zuvor gespeicherten wert ändern, deswegen löschen wir den value vor dem update raus.
            unset($setting['value']);
            $db->update_query('settings', $setting, "name='{$name}'");
            echo "Setting: {$name} wurde aktualisiert.<br>";
          }
        }
      }
    }
    echo "<p>Einstellungen wurden überprüft.</p>";
  }
  rebuild_settings();
}

function activitytracker_settingarray()
{
  $setting_array = array();

  //einstellungen 
  $setting_array = array(
    /*Blacklist einstellungen*/
    //Wie oft soll die BL erscheinen
    'activitytracker_bl' => array(
      'title' => 'Blacklist',
      'description' => 'Soll die Blacklist genutzt werden?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 1
    ),
    //Ist die Blacklist gerade aktiv
    'activitytracker_bl_activ' => array(
      'title' => 'Blacklist - Status',
      'description' => 'Ist die Blacklist gerade aktiv und kann von den Usern eingesehen werden.',
      "optionscode" => "yesno",
      'value' => '0', // Default
      'disporder' => 2
    ),
    //Blacklist - Gäste
    'activitytracker_bl_guest' => array(
      'title' => 'Blacklist - Sichtbarkeit Gäste?',
      'description' => 'Dürfen Gäste die Blacklist sehen?',
      "optionscode" => "yesno",
      'value' => '0', // Default
      'disporder' => 3
    ),
    //Wie oft soll die BL erscheinen?
    'activitytracker_bl_turnus' => array(
      'title' => 'Blacklist - Erscheinen Turnus',
      'description' => 'Wie oft soll die Blacklist erscheinen? <ul><li>Wöchentlich - immer Montags</li>
      <li>alle 2 Wochen - jeder 2. Montag</li>
      <li>Monatlich - Immer am X. im Monat. Tag im nächsten Feld auswählen.</li>  
      <li>bestimmte Monate - Nur an bestimmten Monaten. Tag und Monate in den nächsten Feldern auswählen</li>
      <li>Manuell - Der Task per Hand ausgeführt und die Blacklist anschließend manuell aktiviert werden.</li></ul>',
      'optionscode' => "radio\nweekly=wöchentlich\n2weekly=alle 2 Wochen\nmonthly=monatlich\nparticular=bestimmte Monate\nmanuel=manuell",
      'value' => 'monthly', // Default
      'disporder' => 4
    ),
    //An welchem Tag soll die Blacklist erscheinen?
    'activitytracker_bl_turnus_day' => array(
      'title' => 'Blacklist - Erscheinen Tag',
      'description' => "Diese Auswahl ist nur nötig wenn du bei \'Erscheinen Turnus\' Monat, oder bestimmte Monate ausgewählt hast.<br>
      Bestimmt dann an welchem Tag der Turnus für regelmäßige automatische Ausführung starten.",
      'optionscode' => 'numeric',
      'value' => '1', // 2628
      'disporder' => 5
    ),
    //Soll die Blacklist nur in bestimmten Monaten erscheinen?
    'activitytracker_bl_turnus_particular' => array(
      'title' => 'Blacklist - Bestimmte Monate',
      'description' => 'An welchen Monaten soll die Blacklist erscheinen?',
      'optionscode' => "checkbox\n1=Januar\n2=Februar\n3=März\n4=April\n5=Mai\n6=Juni\n7=Juli\n8=August\n9=September\n10=Oktober\n11=November\n12=Dezember",
      'value' => '1', // Default
      'disporder' => 6
    ),
    //An welchem Tag soll die Blacklist erscheinen?
    'activitytracker_bl_turnus_extrainfo_month' => array(
      'title' => 'Blacklist - Auswertung Liste gleicher Monat?',
      'description' => "Setze hier <b>\'Ja\'</b>, wenn die Blacklist für den gleichen Monat generiert werden soll. Also wenn du zum Beispiel am 31. die Blacklist veröffentlicht, damit die Blacklist für den gleichen Monat prüft. Wenn du <b>\'Nein\'</b> setzt, wird die Blacklist für den vorherigen Monat generiert. Also wenn du am 1. Juni die Blacklist veröffentlichst, wird die Blacklist für Mai generiert.",
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 6
    ),
    //Wie oft soll die BL erscheinen
    'activitytracker_bl_deadline' => array(
      'title' => 'Blacklist - Frist',
      'description' => 'Wie lange soll die Frist der Blacklist sein (Tage)? Für eine Woche z.B. 7 eintragen',
      'optionscode' => 'numeric',
      'value' => '7', // Default
      'disporder' => 7
    ),
    //Automatische Deaktiverung
    'activitytracker_bl_autooff' => array(
      'title' => 'Blacklist - Deaktivierung',
      'description' => 'Soll die Blacklist automatisch nach der deadline wieder deaktiviert/unsichtbar gemacht werden?',
      'optionscode' => 'yesno',
      'value' => 'yes', // Default
      'disporder' => 8
    ),
    //Zeitraum in Tagen - angenommene Charas
    'activitytracker_bl_duration' => array(
      'title' => 'Blacklist - Zeitraum?',
      'description' => '<ul><li>Monatlich - letzter Monat (BL im Juni, wurde im Mai gepostet?)</li>
      <li>Wöchentlich (BL Montags, wurde Mo-So davor geopostet)</li>
      <li>Tage (Wurde in den letzten X Tagen (seit letztem post in der Szene) gepostet -> Tage im nächsten Feld angebe).</li></ul><b>Achtung:</b> Hier bitte überlegen was sinnvoll ist. Bei der Betrachtung pro Szene, ist es evt. nicht sinnvoll Monat oder Woche auszuwählen (Empfehlung hier, eher: Wurde in den letzten 30 Tagen gepostet)',
      'optionscode' => "radio\nmonth=Monat\nweek=Woche\ndays=Tage",
      'value' => 'monthly', // Default
      'disporder' => 9
    ),
    //Zeitraum in Tagen - angenommene Charas
    'activitytracker_bl_duration_days' => array(
      'title' => 'Blacklist - Zeitraum?',
      'description' => 'Wenn du Tage ausgewählt hast, sage hier wieviele.',
      'optionscode' => 'numeric',
      'value' => '92', // Default
      'disporder' => 10
    ),
    // //Typ der Berücksichtigung
    // 'activitytracker_bl_type_user' => array(
    //   'title' => 'Blacklist - Spieler oder Charakter?',
    //   'description' => 'Soll die Blacklist nach Spieler oder Charakter unterschieden werden?',
    //   'optionscode' => 'radio
    //       player=Spieler
    //       character=Character',
    //   'value' => 'character', // Default
    //   'disporder' => 10
    // ),
    //Art der Berücksichtigung

    'activitytracker_bl_type' => array(
      'title' => 'Blacklist - Berücksichtigungsart',
      'description' => 'Soll nur der allgemein letzte Post im Ingame des Charakters gezählt werden, oder gilt der erlaubte Zeitraum pro Szene? Bei erlaubtem Zeitraum pro Szene, wird vom letzten Post in der Szene gerechnet.',
      'optionscode' => 'radio
      scene=pro Szene
      lastingame= letzter Post Ingame',
      'value' => 'scene', // Default
      'disporder' => 11
    ),
    //Keine Aktuelle Ingameszene
    'activitytracker_bl_noscenes' => array(
      'title' => 'Blacklist - Keine aktuelle Ingameszene',
      'description' => 'Wie soll es behandelt werden, wenn ein Charakter keine offene Szene im Ingame hat? (Keine offene Szene = Blacklist, Geschlossene Szene = normaler Zeitraum, Geschlossene Szene mit gesondertem Zeitraum)',
      'optionscode' => 'yesno',
      "optionscode" => "radio\n0=keine offene Szene -> Blacklist\n1=geschlossene Szene -> normaler Zeitraum\n2=geschlossene Szene -> gesonderter Zeitraum",
      'value' => '1', // Default
      'disporder' => 12
    ),
    //geschlossene Szenen gar nicht beachten -> keine szene der chara landet auf der Blacklist
    //geschlossene Szenen mit normalen Zeitraum betrachten -> gab es noch einen Post im Zeitraum wie posts, alles fein
    //geschlossene Szenen mit gesondertem Zeitraum betrachten -> Last Post X Tage her, dann ist okay

    //Keine aktuelle Ingameszene Zeitraum
    'activitytracker_bl_noscenes_days' => array(
      'title' => 'Blacklist - geschlossene Szene -> gesonderter Zeitraum"',
      'description' => 'Wieviele Tage hat ein Charakter Zeit eine neue Szene im Ingamezeitraum zu öffnen bevor er auf der Blacklist landet? (ausgehend vom letzten Post der geschlossenen Szenen)',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 13
    ),
    //Zeitraum für Steckbriefe
    'activitytracker_bl_applicationduration' => array(
      'title' => 'Blacklist - Zeitraum Steckbriefe?',
      'description' => 'Wieviel Zeit(Tage) haben Bewerber einen Steckbrief zu posten?',
      'optionscode' => 'numeric',
      'value' => '21', // Default
      'disporder' => 14
    ),
    //wie wird eine Bewerbung gepostet
    'activitytracker_bl_einreichung' => array(
      'title' => 'Einreichung Steckbrief',
      'description' => 'Wie wird der Steckbrief eingereicht? Wenn ihr anderes auswählt, wird die Frist immer vom Registrierungs Datum berechnet. Damit wird berechnet, ob der Steckbrief Fristgerecht einegreicht wurde.',
      "optionscode" => "radio\nstecki_anders=Anderes(Registrierung)\nstecki_risu=Risuenas Steckbriefplugin\nstecki_thread=Erstellung eines Threads (z.B. Steckbriefarea)",
      'value' => 'reg', // Default
      'disporder' => 15
    ),
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_bewerberfid' => array(
      'title' => 'Blacklist - Bewerbungsarea',
      'description' => 'In welches Forum posten eure Bewerber die Steckbriefe?',
      'optionscode' => 'forumselect',
      'value' => '1', // Default
      'disporder' => 16
    ),
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_ingamestart' => array(
      'title' => 'Blacklist - Ingameeinstieg',
      'description' => 'Soll der Ingameeinstieg eine gesonderte Frist haben?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 17
    ),
    //TODO vergleich mit registrierungsdatum //wobdate risus //stecki in area
    //TODO wenn steck in area ausgewaählt fid
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_ingamestart_days' => array(
      'title' => 'Blacklist - Ingameeinstieg - Zeitraum',
      'description' => 'Wieviele Tage soll ein frisch angenommener Charakter haben ins Ingame einzusteigen?',
      'optionscode' => 'numeric',
      'value' => '14', // Default
      'disporder' => 18
    ),
    //Wie soll das wob date zugeordnet werden
    'activitytracker_bl_wobdate' => array(
      'title' => 'Blacklist - Berechnung WoB Date.',
      'description' => 'Wie soll das Datum des WoBs ermittelt werden? (Für Berechnung des Ingameeinstiegs)',
      "optionscode" => "radio\nreg=Registrierungsdatum\nrisu_reg=WoB Risuenas Steckbriefplugin\nthread=Erstellung eines Threads (z.B. Steckbriefarea)\nales_wob=WoB Bewerberchecklist von Ales",
      'value' => 'reg', // Default
      'disporder' => 19
    ),
    //Wenn Thread -> Welche Area
    'activitytracker_bl_wobdate_thread' => array(
      'title' => 'Blacklist - Berechnung WoB Date.',
      'description' => 'Wenn du Threads ausgewählt hast, in welchem Forum werden diese gepostet. Elternforum reicht.',
      "optionscode" => "forumselect",
      'value' => '0', // Default
      'disporder' => 20
    ),
    //Abwesenheit ja oder nein?
    'activitytracker_bl_away' => array(
      'title' => 'Blacklist - Abwesenheit beachten?',
      'description' => 'Soll beachtet werden, dass ein User abwesend gemeldet ist und er dann nicht auf die BL kommen?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 21
    ),
    //3 Monatsregel?
    'activitytracker_bl_noaway' => array(
      'title' => 'Blacklist - Abwesenheit Sonderregel?',
      'description' => 'Gibt es einen Zeitraum, bei dem der Charakter auf die BL kommt, auch wenn er abwesend ist?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 22
    ),
    //3 Monatsregel - Zeitraum?
    'activitytracker_bl_noaway_days' => array(
      'title' => 'Blacklist - Sonderregel - Zeitraum?',
      'description' => 'Nach wievielen Tagen soll die Abwesenheit nicht mehr als Schutz gelten? 0 Wenn es keine Begrenzung gibt.',
      "optionscode" => "numeric",
      'value' => '91', // Default
      'disporder' => 23
    ),
    //Der Charakter würde am ausgewählten Tag auf der Blacklist stehen
    'activitytracker_bl_reminder' => array(
      'title' => 'Blacklist - Warnung',
      'description' => 'Bekommt der Charakter eine Index Warnung, wenn der Charakter auf der nächsten BL stehen würde.',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 24
    ),
    //Erinnerung Charakter aktuell auf der Blacklist
    'activitytracker_bl_reminder_isonbl' => array(
      'title' => 'Blacklist - Hinweis',
      'description' => 'Bekommt der Charakter einen Hinweis auf dem Index, dass er gerade auf aktuell veröffentlichten Blacklist steht?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 25
    ),
    //Blacklist Benachrichtigungsart
    'activitytracker_bl_alert' => array(
      'title' => 'Blacklist - Benachrichtigung',
      'description' => 'Sollen User Informiert werden, wenn sie auf der BL stehen?',
      "optionscode" => "radio\nauto=automatisch beim erstellen der BL\nbutton=per Button\nnone=gar nicht",
      'value' => 'none', // Default
      'disporder' => 26
    ),
    'activitytracker_bl_alerttype' => array(
      'title' => 'Blacklist - Benachrichtigung',
      'description' => 'Wie sollen die User informiert werden?',
      "optionscode" => "radio\nmail=Per Mail\npm=Per PN",
      'value' => 'mail', // Default
      'disporder' => 27
    ),
    //Streichen lassen
    'activitytracker_bl_reminder_stroke' => array(
      'title' => 'Blacklist - Streichen',
      'description' => 'Dürfen sich User Streichen?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 28
    ),
    //wie oft dürfen Charaktere sich streichen lassen
    'activitytracker_bl_reminder_stroke_count' => array(
      'title' => 'Blacklist - Streichen Anzahl',
      'description' => 'Wir oft dürfen User ihren Charakter streichen? 0 für Unbegrenzt.',
      "optionscode" => "numeric",
      'value' => '1', // Default
      'disporder' => 29
    ),
    //wann werden die strokes zurückgesetzt
    'activitytracker_bl_reminder_stroke_reset' => array(
      'title' => 'Blacklist - Streichen Zeitraum',
      'description' => 'Nach wieviel Tagen wird ein Stroke zurückgesetzt',
      "optionscode" => "numeric",
      'value' => '365', // Default
      'disporder' => 30
    ),
    //Als zu Löschend markieren 
    'activitytracker_bl_reminder_todelete' => array(
      'title' => 'Blacklist - Als zu Löschend markieren',
      'description' => 'Dürfen User ihre Charaktere als zu Löschend markieren?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 31
    ),
    //Welcher Szenentracker wird benutzt
    'activitytracker_bl_tracker' => array(
      'title' => 'Blacklist - Szenentracker',
      'description' => 'Welcher Szenentracker wird benutzt?',
      "optionscode" => "radio\nkein=Kein Tracker\nrisu=Risuenas\nsparks2=Sparksflys 2.0\nsparks3=Sparksflys 3.0\nales=Ales 2.0,\nlara=Laras",
      'value' => 'kein', // Default
      'disporder' => 32
    ),
    //Postingreihenfolge
    'activitytracker_bl_tracker_order' => array(
      'title' => 'Blacklist - Szenentracker',
      'description' => 'Soll die Reihenfolge beachtet werden, wie Teilnehmer eingetragen wurden?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 33
    ),
    //ausgeschlossene Foren
    'activitytracker_fidexcluded' => array(
      'title' => 'ausgeschlossene Foren',
      'description' => 'Gibt es explizite Foren die ausgeschlossen werden soll? z.B. SMS oder Mail... ? ',
      'optionscode' => 'forumselect',
      'value' => '4', // Default
      'disporder' => 34
    ),
    //ausgeschlossene Threads
    'activitytracker_tidexcluded' => array(
      'title' => 'ausgeschlossene Threads',
      'description' => 'Gibt es einzelne Threads, die ausgeschlossen werden sollen (z.B. bestimmte Plotszenen?). Dann hier die tid als kommagetrennte Liste eintragen. ',
      'optionscode' => 'text',
      'value' => '', // Default
      'disporder' => 35
    ),
    /*******
     * Whitelist Einstellungen
     * *****/
    //Soll die Whitelist genutzt werden
    'activitytracker_wl' => array(
      'title' => 'Whitelist',
      'description' => 'Soll die Whitelist genutzt werden?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 36
    ),
    //Dürfen Gäste die Whielist sehen?
    'activitytracker_wl_guest' => array(
      'title' => 'Whitelist - Gäste',
      'description' => 'Dürfen Gäste die Whitelist sehen?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 37
    ),
    //Ist die Whitelist gerade aktiv
    'activitytracker_wl_activ' => array(
      'title' => 'Whitelist - Veröffentlich und aktiv',
      'description' => 'Ist die Whitelist gerade einsehbar und aktiv?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 38
    ),
    //Whitelist Zeitraum Rückmeldung
    'activitytracker_wl_deadline' => array(
      'title' => 'Whitelist - Zeitraum',
      'description' => 'Wie viele Tage haben die Mitglieder Zeit sich zurückzumelden?',
      'optionscode' => 'numeric',
      'value' => '7', // Default
      'disporder' => 39
    ),
    //An welchem Tag soll die Whitelist erscheinen?
    'activitytracker_wl_turnus_day' => array(
      'title' => 'Whitelist Erscheinen - Tag',
      'description' => 'An welchem Tag soll der Turnus für regelmäßige automatische Ausführung starten. Z.b. 1. (Monatlich: immer, am 1. Weekly 1 + 7 etc.) ',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 40
    ),
    //Wie oft soll die WL erscheinen?
    'activitytracker_wl_turnus' => array(
      'title' => 'Whitelist Erscheinen - Turnus',
      'description' => 'Wie oft soll die Whitelist erscheinen? Bei Manuell, muss der Task per Hand ausgeführt werden.',
      'optionscode' => "radio\nweekly=wöchentlich\n2weekly=alle 2 Wochen\nmonthly=monatlich\nmanuel=manuell",
      'value' => 'monthly', // Default
      'disporder' => 41
    ),
    //Automatische Deaktiverung
    'activitytracker_wl_autooff' => array(
      'title' => 'Whitelist - Deaktivierung',
      'description' => 'Soll die Whitelist automatisch nach der deadline wieder deaktiviert/unsichtbar gemacht werden?',
      'optionscode' => 'yesno',
      'value' => 'yes', // Default
      'disporder' => 42
    ),
    //Whitelist Regulierungen
    'activitytracker_wl_regulations' => array(
      'title' => 'Whitelist - Regulierungen',
      'description' => 'Gibt es Regulierungen ob man sich zurückmelden kann?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 43
    ),
    //Whitelist Posts
    'activitytracker_wl_regulations_type' => array(
      'title' => 'Whitelist - Regulierungen',
      'description' => 'Gibt es Regulierungen wann man sich zurückmelden kann?',
      'optionscode' => 'radio',
      'value' => "radio\nbl=gleiche Regeln wie bei der BL\nownpost=ein Post in den letzten X Tagen // Default\nownscene=keine Szenen die länger als X Tage warten",
      'disporder' => 44
    ),
    //Whitelist Posts
    'activitytracker_wl_regulations_days' => array(
      'title' => 'Whitelist - Regulierungen Tage',
      'description' => 'Wieviele Tage darf der letzte Post/Szene her sein?',
      'optionscode' => 'numeric',
      'value' => '30',
      'disporder' => 45
    ),
    //
    'activitytracker_wl_reminder' => array(
      'title' => 'Whitelist - Reminder',
      'description' => 'Soll auf der Index Seite eine Info angezeigt werden, wenn die Whitelist aktiv ist?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 46
    ),
    /*Eisliste Einstellungen*/
    //Laras Eisliste
    'activitytracker_ice_iceplugin' => array(
      'title' => 'Icelist Plugin von little-evil-genius',
      'description' => 'Ist das Icelistplugin von little.evil.genius installiert und soll berücksichtig werden?
      Wenn dies genutzt werden soll, bitte den nächsten Punkt auf No stellen!',
      "optionscode" => "yesno",
      'value' => '0', // Default
      'disporder' => 47
    ),
    //Eisliste ja oder nein?
    'activitytracker_ice' => array(
      'title' => 'Eisliste',
      'description' => 'Können einzelne Charaktere auf Eis gelegt werden?
          Anzeigbar im Profil mit der Variable {$iceMeldung}',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 48
    ),
    // 'activitytracker_ice_characterstatus' => array(
    //   'title' => 'Characterstatus von Risuena',
    //   'description' => 'Wird das Characterstatusplugin von Risuena verwenden?',
    //   "optionscode" => "yesno",
    //   'value' => '0', // Default
    //   'disporder' => 45
    // ),
    //Eisliste Zeitraum?
    'activitytracker_ice_duration' => array(
      'title' => 'Eisliste Zeitraum',
      'description' => 'Wie lange darf ein Charakter auf Eis gelegt sein?',
      "optionscode" => "numeric",
      'value' => '90', // Default
      'disporder' => 49
    ),
    //Eisliste Sperre
    'activitytracker_ice_lock' => array(
      'title' => 'Eisliste - Sperre',
      'description' => 'Gibt es eine Sperre? z.B Der Charakter darf nur einmal im Jahr auf Eis gelegt werden?',
      "optionscode" => "numeric",
      'value' => '365', // Default
      'disporder' => 50
    ),
    //Wie oft im Jahr?
    'activitytracker_ice_lock_days' => array(
      'title' => 'Eisliste - Zeitraum Sperre',
      'description' => 'Anzahl der Tage eintragen (z.B. 365 Tage = 1 Jahr).',
      "optionscode" => "numeric",
      'value' => '365', // Default
      'disporder' => 51
    ),
    //Wieviele Charaktere darf ein User auf Eis legen?
    'activitytracker_ice_type' => array(
      'title' => 'Eisliste - Beschänkungsart',
      'description' => 'Auf welche Art und Weise, soll die Anzahl der Charaktere, die man auf Eis legen kann eingeschränkt werden?',
      "optionscode" => "radio\nnone=gar nicht\nanzahl=eine konkrete Anzahl (z.B. 1 Charakter)\npercent_plus=Prozentangabe und aufrunden (bei 50% z.B 3 von 5)\npercent_minus=Prozentangabe und abrunden (bei 50% z.B 2 von 5)",
      'value' => '1', // Default
      'disporder' => 52
    ),
    /*Allgemein benutzergruppen*/
    //Gruppen für normale Blacklistregeln
    'activitytracker_groups' => array(
      'title' => 'Usergruppen Gruppen',
      'description' => 'Welche Gruppen sollen für die Blacklist/Whitelist/Eisliste berücksichtigt werden?',
      'optionscode' => 'groupselect',
      'value' => '1', // Default
      'disporder' => 53
    ),
    //Bewerbergruppe
    'activitytracker_applicationgroup' => array(
      'title' => 'Gruppe für Bewerber',
      'description' => 'Wähle die Gruppe für eure Bewerber aus.',
      'optionscode' => 'groupselect',
      'value' => '2', // Default
      'disporder' => 54
    ),
    //ausgeschlossene User z.B. Gastaccount / Admint
    'activitytracker_excludeduid' => array(
      'title' => 'Ausgeschlossene User',
      'description' => 'Gibt es user, die nicht berücksichtigt werden sollen? Z.B. Adminaccount / Gastaccount. Kommagetrennte Liste',
      'optionscode' => 'text',
      'value' => '0', // Default
      'disporder' => 55
    ),
    //IDs fürs Ingame?
    'activitytracker_ingame' => array(
      'title' => 'Berücksichtige Foren',
      'description' => 'Wähle die Foren aus, die berücksichtigt werden sollen. Elternforen reichen.',
      'optionscode' => 'forumselect',
      'value' => '0', // Default
      'disporder' => 56
    ),
    //IDs fürs Archiv?
    'activitytracker_archiv' => array(
      'title' => 'Wähle dein Archiv, elternforum reicht',
      'description' => 'Wähle die Foren aus, die berücksichtigt werden sollen. Elternforen reichen.',
      'optionscode' => 'forumselect',
      'value' => '0', // Default
      'disporder' => 57
    ),

    //Szenenreminder
    'activitytracker_scenereminder' => array(
      'title' => 'Szenenerinnerung',
      'description' => 'Soll der User erinnert werden, wenn er andere X Tage warten lässt? ',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 58
    ),
    //ausgeschlossene Foren
    'activitytracker_scenereminder_days' => array(
      'title' => 'Szenenerinnerung Tage',
      'description' => 'Nach wievielen Tagen soll man erinnert werden? ',
      'optionscode' => 'numeric',
      'value' => '0', // Default
      'disporder' => 59
    ),
    //Spielername
    'activitytracker_username' => array(
      'title' => 'Spielername',
      'description' => 'Wenn der Username im Profilfeld geppeichert wird nur die ID (Zahl) angeben. Wenn er über das Steckbriefplugin von Risuena angelegt ist, den Bezeichner angeben.',
      'optionscode' => 'text',
      'value' => '0', // Default
      'disporder' => 60
    ),
  );

  return $setting_array;
}

function activitytracker_add_templates($type = "install")
{
  global $db;

  if ($type == 'install') {
    $templategrouparray = array(
      'prefix' => 'activitytracker',
      'title'  => $db->escape_string('Aktivitätstracker'),
      'isdefault' => 1
    );
    $db->insert_query("templategroups", $templategrouparray);
  }
  $templates = activititracker_templatearray();
  foreach ($templates as $row) {
    $check = $db->num_rows($db->simple_select("templates", "title", "title LIKE '{$row['title']}'"));
    if ($check == 0) {
      $db->insert_query("templates", $row);
      echo "Neues Template {$row['title']} wurde hinzugefügt.<br>";
      if ($row['title' == 'application_ucp_profile_trigger']) {
        echo '<b>Achtung</b>: Variable {$application_ucp_profile_trigger} in member_profile an die Stelle einfügen, wo die Trigger Warnung angezeigt werden soll.</br>';
      }
    }
  }
}

function activititracker_templatearray()
{
  global $db;
  //templates anlegen
  $insert_array[] = array(
    'title'    => 'activitytracker_bl_index_reminder',
    'template'  => $db->escape_string('<div class="pm_alert bl-reminder">
	<div class="bl-reminder__content">
	{$activitytracker_bl_index_reminder_info}
	{$activitytracker_bl_index_reminder_bit}
	</div>
	<div class="bl-reminder__options">
		<a href="{$mybb->settings[\'bburl\']}/misc.php?action=blacklist_hide_info">[ausblenden]</a>
	</div>
</div>
    '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_index_reminder_is_bit',
    'template'  => $db->escape_string('<div class="bl-reminder__characterinfo">{$characters} {$blacklistwarning}</div>
    '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main',
    'template'  => $db->escape_string('<head>
	<title>Blacklist</title>
	{$headerinclude}
</head>
<body>
	{$header}
	<table width="100%" border="0" align="center">
		<tr>
			{$usercpnav}
			<td valign="top">
				<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
					<tr>
						<td class="thead" colspan="2"><strong>Blacklist</strong></td>
					</tr>
					<tr>
						<td class="trow2">
							<div class="at-blacklistinfo">Die Blacklist wird immer am {$frist_blacklistdaystart}. veröffentlicht. Du hast {$frist_bl_deadline_days_to_post} Tage Zeit bis zur Löschung.</div>
							<div class="at-blacklist">
								{$activitytracker_bl_show_main_userbit}         
							</div>
						</td>
					</tr>
				</table>
			</td>
		</tr>
	</table>
	{$footer}
</body>
</html>
     '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );


  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_charabit',
    'template'  => $db->escape_string('<div class="at-blacklist__chara">
	<div class="at-blacklist__charaname {$strokeinfoclass}">
		<h3>{$linked_charaname}</h3>
	</div>
	<div class="at-blacklist__charareason ">
		<span class="{$strokeinfoclass}">{$reason}</span>
		{$activitytracker_bl_show_main_charabit_threads}
		{$strokeinfo}
	</div>
	<div class="at-blacklist__options">{$activitytracker_bl_show_main_useroptions}{$activitytracker_bl_show_main_modoptions}</div>
</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_charabit_threads',
    'template'  => $db->escape_string('<div class="at-blacklist__threadlink">{$threadlink}</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_modoptions',
    'template'  => $db->escape_string('<div class="at-blacklist__modoptions"><b>Moderationsoptionen</b><br>{$strokelink_mod}
	{$delete}
</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_userbit',
    'template'  => $db->escape_string('<div class="at-blacklist__user">
	<div class="at-blacklist__userinfos">
		<h2>{$username}</h2>
		{$deletemarkinfo}
	</div>
	<div class="at-blacklist__charainfors">
		{$activitytracker_bl_show_main_charabit}
	</div>
</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_useroptions',
    'template'  => $db->escape_string('<div class="at-blacklist__useroptions">
	{$status} 
	{$strokelink}
	{$marktodel}
</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_show_main_modoptions',
    'template'  => $db->escape_string('<div class="at-blacklist__modoptions"><b>Moderationsoptionen</b><br>{$strokelink_mod}
	{$delete}
</div>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_ucp',
    'template'  => $db->escape_string('Die Blacklist ist gerade {$bl_status}. Sie erscheint {$bl_turnus}.
<table class="tborder">
	<tr>
		<td class="thead" colspan="2"><strong>Blacklistgefährdet</strong></td>
	</tr>
	{$activitytracker_bl_ucp_charabit}
</table>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_bl_ucp_charabit',
    'template'  => $db->escape_string('<tr>
	<td>{$charainfo[\'username\']}</td>
	<td>{$reason}</td>
</tr>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  $insert_array[] = array(
    'title'    => 'activitytracker_ucp',
    'template'  => $db->escape_string('<html>
	<head>
		<title>{$lang->user_cp} - Aktivitätstracker</title>
		{$headerinclude}
	</head>
	<body>
		{$header}
		<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
			<tr>
				<td valign="top" class="">

					<table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
						<tr>
							<td class="thead"><strong>Aktivitätstrackerübersicht von<br> {$username}</strong></td>
						</tr>
						<tr>

							<td class="trow2">
								<div class="usercp_content_text">
									<table width="100%" class="tborder" cellspacing="0" cellpadding="0">
										<tr><td class="thead"><strong>Aktivitätstracker</strong></td></tr>
										<tr><td class="trow2">Hier hast du eine Übersicht über deine Charaktere.</td></tr>
										
										<tr><td class="trow2">{$activitytracker_bl_ucp}</td></tr>
									</table>

								</div>

							</td>
						</tr>
					</table>
					{$latest_subscribed}
					{$latest_threads}
					{$latest_warnings}
					{$user_notepad}
				</td>
				{$usercpnav}
			</tr>
		</table>
		{$footer}
	</body>
</html>'),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );

  return $insert_array;
}

$plugins->add_hook('admin_config_settings_change_commit', 'activitytracker_editSettings');
function activitytracker_editSettings()
{
  global $mybb, $db;

  // Blacklist task
  $turnus_old = $mybb->settings['activitytracker_bl_turnus'];
  $turnus_new = $mybb->input['upsetting']['activitytracker_bl_turnus'];
  if ($turnus_old != $turnus_new) {
    // $mybb->input
    $particular = (int)$mybb->settings['activitytracker_bl_turnus_particular'];

    // Default Task - Immer am 1. des Monats ausführen (um 00:01)
    $task_update_bl = [
      'minute'   => '1',
      'hour'     => '0',
      'day'      => '1',
      'month'    => '*',
      'weekday'  => '*',
      'enabled'  => 1,
      'nextrun'  => TIME_NOW + 60,
    ];

    switch ($turnus_new) {
      case 'weekly':
        // einmal Wöchentlich, immer Montags
        $task_update_bl['weekday'] = 1; // Montag
        break;
      case '2weekly':
        // alle 2 Wochen, immer Montags, abfangen dafür im Task
        $task_update_bl['weekday'] = 1;
        break;

      case 'monthly':
        // einmal im Monat, je nach Tag 
        $task_update_bl['day'] = (int)$mybb->settings['activitytracker_bl_turnus_day'];
        if ($task_update_bl['day'] == 0) {
          $task_update_bl['day'] = 1; // Fallback auf den 1., falls 0 eingestellt ist
        }
        break;

      case 'particular':
        // nur in bestimmten Monaten
        $task_update_bl['day']   = (int)$mybb->settings['activitytracker_bl_turnus_day'];
        if ($task_update_bl['day'] == 0) {
          $task_update_bl['day'] = 1; // Fallback auf den 1., falls 0 eingestellt ist
        }

        $particular = $mybb->settings['activitytracker_bl_turnus_particular'];
        $task_update_bl['month'] = $particular;
        break;

      case 'manuel':
      default:
        // manuell → Task deaktivieren
        $task_update_bl['enabled'] = 0;
        break;
    }

    // Task aktualisieren
    $db->update_query('tasks', $task_update_bl, "file='at_blacklist'");

    $task_update_bl_autooff = [
      'minute'   => '59',
      'hour'     => '23',
      'day'      => '*',
      'month'    => '*',
      'weekday'  => '*',
      'enabled'  => 1,
      'nextrun'  => TIME_NOW + 60,
    ];
  }
  $autoof_old =  $mybb->settings['activitytracker_bl_autooff'];
  $autoof_new = $mybb->input['upsetting']['activitytracker_bl_autooff'];

  //Blacklist Status (automatisches deaktivieren)
  if ($autoof_old != $autoof_new) {
    // Task aktualisieren
    if ($autoof_new == 0) {
      $task_update_bl_autooff['enabled'] = 0;
    } else {
      $task_update_bl_autooff['enabled'] = 1;
    }
    $db->update_query('tasks', $task_update_bl, "file='at_blacklist_status'");
  }

  //Blacklist wurde deaktiviert -> View bei usern wieder auf 0 stellen
  $bl_activ_old =  $mybb->settings['activitytracker_bl_activ'];
  $bl_activ_new = $mybb->input['upsetting']['activitytracker_bl_activ'];
  if ($bl_activ_old != $bl_activ_new && $mybb->settings['activitytracker_bl_activ'] == 1) {
    //alle User die auf der Blacklist stehen, bekommen die View auf 0 gesetzt, damit sie die BL sehen können
    if ($bl_activ_new == 0) {
      $db->update_query("users", ["activitytracker_bl_view" => 0], "");
    }
  }
}

/**
 * Anzeige im Profil des Users, Status der Charaktere sind.
 */
$plugins->add_hook('usercp_start', 'activitytracker_usercp_main');
function activitytracker_usercp_main()
{
  global $db, $mybb, $templates, $header, $lang, $upload_data, $themes, $theme, $headerinclude, $footer, $usercpnav, $activitytracker_bl_ucp, $bl_turnus, $bl_status;
  if ($mybb->get_input('action') != "activitytracker_ucp") {
    return false;
  }
  $activitytracker_ucp = "";
  $lang->load("activitytracker");
  //alle Charaktere des Users bekommen:

  $thisuser = $mybb->user['uid'];
  $uidarray = activitytracker_get_allchars_as($thisuser);

  $userinfo = get_user($thisuser);
  $username = $userinfo['username'];

  if ($mybb->settings['activitytracker_bl_activ'] == 0) {
    $bl_status = $lang->blacklist_status_inactive;
  } else {
    $bl_status = $lang->blacklist_status_active;
  }

  //output für infos
  switch ($mybb->settings['activitytracker_bl_turnus']) {
    case 'weekly':
      $bl_turnus = $lang->blacklist_turnus_weekly;
      break;

    case '2weekly':
      $bl_turnus = $lang->blacklist_turnus_2weekly;
      break;

    case 'monthly':
      $month_info = $mybb->settings['activitytracker_bl_turnus_extrainfo_month'];
      if ($month_info) {
        $bl_turnus = $lang->blacklist_turnus_monthly;
      } else {
        $bl_turnus = $lang->blacklist_turnus_monthly_lastmonth;
      }
      break;

    case 'particular':
      $months = [
        1  => "Januar",
        2  => "Februar",
        3  => "März",
        4  => "April",
        5  => "Mai",
        6  => "Juni",
        7  => "Juli",
        8  => "August",
        9  => "September",
        10 => "Oktober",
        11 => "November",
        12 => "Dezember"
      ];

      $ids = explode(",", $mybb->settings['activitytracker_bl_turnus_particular']);

      // Monatsnamen rausholen
      $selectedMonths = array_map(fn($id) => $months[(int)$id], $ids);

      $month = implode(", ", $selectedMonths);
      $day = $mybb->settings['activitytracker_bl_turnus_day'];
      $bl_turnus = $lang->sprintf($lang->blacklist_turnus_particular, $month, $day);
      break;

    case 'manuel':
      $bl_turnus = $lang->blacklist_turnus_manuel;
      break;

    default:
      $bl_turnus = $lang->blacklist_turnus_unknown;
      break;
  }

  foreach ($uidarray as $uid) {
    //für jeden Charakter des Users Testen ob er auf der Blacklist stehen würde
    if ($mybb->settings['activitytracker_bl'] == 1) {
      $blacklist_array = activitytracker_check_blacklist($uid);

      if (!empty($blacklist_array)) {
        //Charakter würde auf der Blacklist stehen
        $charainfo = get_user($uid);
        $linked_charaname = build_profile_link($charainfo['username'], $charainfo['uid']);
        //var_dump($blacklist_array[$uid]);
        switch ($blacklist_array[$uid]['reason']) {
          case 'noingamestart':
            $reason = $lang->ucp_reason_noingamestart;
            break;

          case 'tooldscenes':
            $thread = get_thread($blacklist_array[$uid]['tid']);
            $threaddate = my_date($mybb->settings['dateformat'], $thread['dateline']);
            $reason = $lang->ucp_reason_tooldscenes . " ({$threaddate})";
            break;

          case 'noingamescene':

            $reason = $lang->ucp_reason_noingamescene;
            break;

          case 'noapplication':
            $reason = $lang->ucp_reason_noapplication;
            break;

          default:
            $reason = $lang->ucp_reason_default;
            break;
        }
        eval("\$activitytracker_bl_ucp_charabit .=\"" . $templates->get("activitytracker_bl_ucp_charabit") . "\";");
      }
    }
  }


  //Übersicht
  if ($mybb->settings['activitytracker_bl'] == 1) {
    eval("\$activitytracker_bl_ucp =\"" . $templates->get("activitytracker_bl_ucp") . "\";");
  }



  //Einstellungen für Reminder Options ETC. 
  //Speichern der einstellung


  //Blacklist
  //Soll die Blacklist verwendet werden
  //einstellungen User Reminder
  //speichern einstellungen user reminder


  //Soll die Whitelist verwendet werden
  //Whiteliste - Wenn aktiv - Welche Charaktere zurückgemeldet sind

  //Eisliste - Welche Charaktere liegen auf Eis / Seit wann

  eval("\$activitytracker_ucp =\"" . $templates->get("activitytracker_ucp") . "\";");
  output_page($activitytracker_ucp);
}

/**
 * Blacklist - Ausgabe
 * Erreichbar über forenadresse misc.php?action=blacklist_show 
 */
$plugins->add_hook("misc_start", "activitytracker_blacklist_show");
function activitytracker_blacklist_show()
{
  global $mybb, $db, $templates, $header, $footer, $theme, $headerinclude,  $lang, $activitytracker_bl_show_main;
  $lang->load('activitytracker');

  //variablen initialisieren
  $status =  $strokelink = "";

  $thisuser = $mybb->user['uid'];
  // if (!$mybb->get_input('action') == "blacklist_at") return;

  if ($mybb->get_input('action') == "blacklist_at") {
    //funktionen
    if ($mybb->get_input('stroke') == 1) {
      $strokeuid = $mybb->get_input('strokeuid');
      activitytracker_stroke($strokeuid, $thisuser);
    }
    if ($mybb->get_input('modstroke') == 1) {
      $strokeuid = $mybb->get_input('strokeuid');
      activitytracker_stroke($strokeuid, true);
    }

    if ($mybb->get_input('moddelete') == 1) {
      if ($mybb->usergroup['canmodcp'] == 1) {
        $deluid = $mybb->get_input('deluid');
        activitytracker_delete_bl($deluid);
      } else {
        error_no_permission();
      }
    }

    if ($mybb->get_input('markdelete')) {
      $markuid = $mybb->get_input('markuid');
      activitytracker_mark_to_delete($markuid);
    }

    //page bauen
    $page = "";
    add_breadcrumb("Blacklist", "misc.php?action=blacklist_at");

    // $frist_blacklistende = $mybb->settings['activitytracker_bl_deadline'];
    $frist_blacklistdaystart = $mybb->settings['activitytracker_bl_turnus_day'];
    $frist_bl_deadline_days_to_post = $mybb->settings['activitytracker_bl_deadline'];

    $activitytracker_bl_type = $mybb->settings['activitytracker_bl_type'];

    $activitytracker_excludeduid = $mybb->settings['activitytracker_excludeduid'];
    if ($activitytracker_excludeduid == "") {
      $activitytracker_excludeduid = 0;
    }

    //Gäste dürfen die BL nicht sehen
    if ($thisuser == 0 && $mybb->settings['activitytracker_bl_guest'] == 0) {
      error_no_permission();
    }
    $is_mod = $mybb->usergroup['canmodcp'];

    //Die Blacklist ist gerade nicht aktiv und der User ist kein Moderator
    if ($mybb->settings['activitytracker_bl_activ'] == 0 && $is_mod == 0) {
      error_no_permission();
    }

    //alle Hauptaccounts bekommen - die nicht abwesend sind
    $activitytracker_bl_show_main_userbit = "";
    $get_user = $db->simple_select("users", "uid", "as_uid = 0 and away = 0 AND uid not in ($activitytracker_excludeduid) AND away = 0", ["order_by" => "username", "order_dir" => "ASC"]);
    while ($hauptuser = $db->fetch_array($get_user)) {
      $activitytracker_bl_show_main_modoptions = "";

      //initial nicht auf BL
      $isonbl = 0;
      //infos Hauptuser
      $hauptuid = $hauptuser['uid'];
      $haupt = get_user($hauptuser['uid']);
      //alle uids des Users
      $uidarray = activitytracker_get_allchars_as($hauptuid);
      $usernameid = $mybb->settings['activitytracker_username'];

      //Spielername bekommen
      if (is_numeric($usernameid) && $usernameid != 0) {
        $usernamefid = "fid" . $usernameid;
        // $username = $haupt[$usernameid];
        $username = $db->fetch_field($db->simple_select("userfields", "{$usernamefid}", "ufid = '" . $hauptuid . "'"), "{$usernamefid}");

        if (empty($username)) {
          $username = $haupt['username'];
        }
      } else if ($usernameid == 0) {
        $username = $haupt['username'];
      } else {
        $usernamefid = $db->fetch_field($db->simple_select("application_ucp_fields", "id", "fieldname = '" . $usernameid . "'"), "id");
        $username = $db->fetch_field($db->simple_select("application_ucp_fields", "value", "uid = '$hauptuid' and fieldid = '" . $usernamefid . "'"), "value");
      }

      $activitytracker_bl_show_main_charabit = "";
      //Charas durchgehen und schauen ob einer auf der BL steht.
      foreach ($uidarray as $uid) {
        $strokeinfo = "";
        $strokeinfoclass = "";
        $deletemarkinfo = "";
        $deletemarkinfo_date = $db->fetch_field($db->simple_select("at_blacklist_deletelist", "markdate", "uid = '" . $uid . "'"), "markdate");
        if (!empty($deletemarkinfo_date)) {
          $deletemarkinfo_date = (new DateTime($deletemarkinfo_date))->format('d.m.Y');
          $deletemarkinfo = "<span class='at-blacklist__charadeletemark' title='Charakter ist zum Löschen vorgemerkt (vom User)'>[zum Löschen vorgemerkt ($deletemarkinfo_date)]</span>";
        }

        //anzeige auf der Blacklist, Charakter wird durchgestrichen, nachdem er gepostet, seinen Steckbrief abgegeben hat oder sich gestrichen hat.
        $check_stroke = activitytracker_check_strokeinfo($uid);
        $strokeinfoclass = $check_stroke['class'];
        $strokeinfo = $check_stroke['info'];

        // Mod/Useroptions - Streichen lassen - Mods dürfen immer
        if ($mybb->settings['activitytracker_bl_reminder_stroke'] == 1) {
          // Standard: kein Link
          $strokelink = "";

          //ist der Charakter von dem User der online ist
          if (activitytracker_is_in_switcher($thisuser)) {
            // UID ist im Array enthalten
            // Alle Strokes dieses Users laden
            $strokes = [];
            $query = $db->simple_select("at_blacklist_strokes", "*", "uid='{$hauptuser['uid']}'", ["order_by" => "strokedate", "order_dir" => "ASC"]);
            while ($stroke = $db->fetch_array($query)) {
              $strokes[] = $stroke;
            }
            $stroke_count_allowed = (int)$mybb->settings['activitytracker_bl_reminder_stroke_count'];
            // echo "$stroke_count_allowed von {$hauptuser['uid']} <br>";
            $stroke_reset_days    = (int)$mybb->settings['activitytracker_bl_reminder_stroke_reset'];

            $now = TIME_NOW;
            // Bedingung prüfen
            $can_stroke = true;
            if ($stroke_count_allowed > 0 && count($strokes) >= $stroke_count_allowed) {
              // Ältesten Stroke holen
              $first_stroke = reset($strokes);
              $first_time = strtotime($first_stroke['strokedate']);

              // prüfen, ob Resetzeitraum überschritten
              if (($now - $first_time) < ($stroke_reset_days * 86400)) {
                $can_stroke = false;
              }
            }
            // Sonderfall: erster Stroke -> immer erlauben
            if (count($strokes) == 0) {
              $can_stroke = true;
            }

            // Wenn erlaubt -> Link setzen
            if ($can_stroke) {
              $strokelink = "<a href=\"{$mybb->settings['bburl']}/misc.php?action=blacklist_at&stroke=1&strokeuid={$uid}\">streichen</a>";
            }
          } else {
            $strokelink = "";
          }
        }

        if ($mybb->settings['activitytracker_bl_reminder_stroke'] == 1) {
          $marktodel = "<a href=\"{$mybb->settings['bburl']}/misc.php?action=blacklist_at&markdelete=1&markuid={$uid}\">zum Löschen vormerken</a>";
        } else {
          $marktodel = "";
        }
        if ($mybb->settings['activitytracker_bl_reminder_stroke'] == 0 && $mybb->settings['activitytracker_bl_reminder_todelete'] == 0) {
          $activitytracker_bl_show_main_useroptions = "";
        } else {
          eval("\$activitytracker_bl_show_main_useroptions =\"" . $templates->get("activitytracker_bl_show_main_useroptions") . "\";");
        }

        if ($is_mod == 1) {
          // Mods haben immer zusätzlich diese Optionen
          if ($is_mod) {
            $strokelink_mod = "<a href=\"{$mybb->settings['bburl']}/misc.php?action=blacklist_at&modstroke=1&strokeuid={$uid}\">streichen(moderator)</a>";
            $delete = "<a href=\"{$mybb->settings['bburl']}/misc.php?action=blacklist_at&moddelete=1&deluid={$uid}\">aus Liste löschen</a>";
          }
          eval("\$activitytracker_bl_show_main_modoptions =\"" . $templates->get("activitytracker_bl_show_main_modoptions") . "\";");
        }

        //wenn setting = 1 dann gleicher Monat, wie gerade Also Blacklist 31. Juni -> dann Blacklist für Juni
        if ($mybb->settings['activitytracker_bl_turnus_extrainfo_month'] == "1") {
          $timestr = date('Y-m');
        } else {
          //wenn setting = 0 , dann immer der Vormmat (also Blacklist 31. August, dann für Juli)
          $timestr = date('Y-m', strtotime('first day of last month'));
        }

        $blquery = $db->simple_select("at_blacklist", "*", "uid = '$uid' AND bldate like '{$timestr}%' and away = '0'");
        if ($db->num_rows($blquery) > 0) {
          //User ist auf der BL
          $isonbl = 1;
          $blinfo = $db->fetch_array($blquery);
          $linked_charaname = build_profile_link($blinfo['username'], $blinfo['uid']);

          $status = $blinfo['status'];

          switch ($blinfo['reason']) {
            case 'noingamestart':
              $reason = $lang->ucp_reason_noingamestart;
              break;

            case 'tooldscenes':
              $reason = $lang->ucp_reason_tooldscenes;
              break;

            case 'noingamescene':
              $reason = "$lang->ucp_reason_noingamescene";
              break;

            case 'noapplication':
              $reason = $lang->ucp_reason_noapplication;
              break;

            default:
              $reason = $lang->ucp_reason_default;
              break;
          }
          $activitytracker_bl_show_main_charabit_threads = "";
          if ($blinfo['tids'] != "") {
            $threadlink = "";
            $threads = array_filter(explode(",", $blinfo['tids']), function ($tid) {
              return trim($tid) !== '';
            });

            // if (count($threads) > 1) {
            //   $threadlink = "die letzten Posts:<br>";
            // } elseif (count($threads) == 1) {
            //   $threadlink = "der letzte Post:<br>";
            // } else {
            //   $threadlink = "keine Posts";
            // }
            foreach ($threads as $tid) {
              $threadinfo = get_thread($tid);
              $postinfo = $db->fetch_array($db->simple_select("posts", "*", "tid = '$tid' AND uid = '$uid'", array('order_by' => 'dateline', 'order_dir' => 'desc', 'limit' => 1)));
              $readable_date = my_date('d.m.Y', $postinfo['dateline']);
              // array("limit" => 1)
              $threadlink = "<a href=\"showthread.php?tid=" . $tid . "\">" . htmlspecialchars_uni($threadinfo['subject']) . "</a> ({$readable_date})<br>";
              eval("\$activitytracker_bl_show_main_charabit_threads .=\"" . $templates->get("activitytracker_bl_show_main_charabit_threads") . "\";");
            }
          }
          //TODO ausgabe status
          eval("\$activitytracker_bl_show_main_charabit .=\"" . $templates->get("activitytracker_bl_show_main_charabit") . "\";");
        }
      }

      if ($isonbl) {
        eval("\$activitytracker_bl_show_main_userbit .=\"" . $templates->get("activitytracker_bl_show_main_userbit") . "\";");
      }
    }

    //abwesende User bekommen
    $get_user_away = $db->simple_select("users", "uid", "as_uid = 0 and away = 0 AND uid not in ($activitytracker_excludeduid) AND away = 1", ["order_by" => "username", "order_dir" => "ASC"]);
    // while ($hauptuser = $db->fetch_array($get_user)) {
    // }

    eval("\$activitytracker_bl_show_main =\"" . $templates->get("activitytracker_bl_show_main") . "\";");
    output_page($activitytracker_bl_show_main);
  }

  // blacklist - index meldung ausblenden
  if ($mybb->get_input('action') == "blacklist_hide_info") {

    // Aktuelle User-ID holen
    $uid = (int)$mybb->user['uid'];

    if ($uid > 0) {
      // Update der Spalte activitytracker_bl_view
      $db->update_query("users", ["activitytracker_bl_view" => 0], "uid='{$uid}'");
    }
    // Zurück zur Startseite
    redirect("index.php", "Blacklist-Info wurde ausgeblendet.");
  }
}

/**
 * Whitelist - Ausgabe
 * Erreichbar über forenadresse misc.php?action=whitelist_show 
 */
$plugins->add_hook("misc_start", "activitytracker_whitelist_show");
function activitytracker_whitelist_show()
{
  global $mybb, $db, $templates, $activitytracker_show_main, $active_yes, $active_no, $header, $footer, $theme, $headerinclude, $activitytracker_show_view, $lang, $activitytracker_show_userbitaway;
  //ausgabe Whitelist wenn aktiv
  //rückmeldung - wenn möglich

  //Mods immerzugriff

  //aktivieren der Whitelist // evt. ausführen des Tasks
}

/**
 * Anzeige auf dem Index, wenn activitytracker gerade aktiv ist 
 * inklusive ausblenden
 */
$plugins->add_hook("index_start", "activitytracker_index");
function activitytracker_index()
{
  global $mybb, $db, $templates, $blacklist_index, $lang, $activitytracker_bl_index_reminder_bit;

  $lang->load('activitytracker');
  //Reminder Blacklist - Blacklist ist aktiv dein Charakter würde drauf stehen.
  if ($mybb->settings['activitytracker_bl_reminder_isonbl'] && $mybb->settings['activitytracker_bl_activ']) {
    $allchars = activitytracker_get_allchars_as($mybb->user['uid']);
    $characters = "";
    $cnt = 0;
    foreach ($allchars as $uid) {
      $checkbl = $db->simple_select("at_blacklist", "*", "uid='{$uid}'");
      if ($db->num_rows($checkbl) > 0) {
        $cnt++;
        $blinfo = $db->fetch_array($checkbl);

        $activitytracker_bl_index_reminder_info = $lang->activitytracker_bl_index_reminder_info;
        if ($blinfo['status'] != 'stroke' && $blinfo['away'] == 0) {
          $userinfo = get_user($uid);
          $characters .= $userinfo['username'] . ", ";
        }
      }
    }

    //die letzten 2 Zeichen abschneiden (Komma und Leerzeichen)
    $characters = substr($characters, 0, -2);
    if ($cnt > 1) {
      $blacklistwarning = $lang->activitytracker_bl_index_reminder_warning_multi;
    } else {
      $blacklistwarning = $lang->activitytracker_bl_index_reminder_warning;
    }

    eval("\$activitytracker_bl_index_reminder_bit =\"" . $templates->get("activitytracker_bl_index_reminder_is_bit") . "\";");
  }
  eval("\$blacklist_index =\"" . $templates->get("activitytracker_bl_index_reminder") . "\";");
  //Reminder Whitelist

  //szenenerinnerung
}


//Anzeige wer ist online
$plugins->add_hook('fetch_wol_activity_end', 'activitytracker_user_activity');
$plugins->add_hook('build_friendly_wol_location_end', 'activitytracker_location_activity');

function activitytracker_user_activity($user_activity)
{
  global $user;

  //USER CP Aktivitätstracker

  if (my_strpos($user['location'], "misc.php") !== false) {
    $user_activity['activity'] = "misc.php";
  }

  //Whitelist
  //blacklist
  //icelist

  return $user_activity;
}

function activitytracker_location_activity($plugin_array)
{
  global $db, $mybb, $lang;
  $locationstring  = $plugin_array['user_activity']['location'];
  // echo  $locationstring;
  if (strpos($locationstring, "show_activitytracker") !== false) {

    // if ($plugin_array['user_activity']['location'] == "misc.php?action=activitytracker") {
    $plugin_array['location_name'] = "Sieht sich die <a href=\"misc.php?action=show_activitytracker\">activitytracker</a> an.";
  }
  return $plugin_array;
}

/**
 * Funktion um alle User der gewählten Usergruppen zu bekommen
 * @return array mit uids der User
 */
function activitytracker_get_users()
{
  global $db, $mybb;
  //welche Gruppen sollen berücksichtig werden
  $allgroups = $mybb->settings['activitytracker_groups'];
  //in ein Array speichern
  $allgroup_array = explode(",", $allgroups);
  //return array initialisieren
  $user_array = array();

  //jede Gruppe durchgehen
  foreach ($allgroup_array as $gid) {
    //Gruppenmitglieder (primäre und sekundäre)
    $arrayquery = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "users` WHERE usergroup = '{$gid}' or concat(',',additionalgroups,',') LIKE ',{$gid},'");

    //durchgehen und in array speichern 
    while ($user = $db->fetch_array($arrayquery)) {
      $user_array[] = $user['uid'];
    }
  }
  // Rückgabe
  return array_unique($user_array);
}

/**
 * Hilfsfunktion Accountswitcher
 * Gibt ein Array mit allen uids des Users zurück
 * @param int uid des Users 
 * @return Array mit uids
 */
function activitytracker_get_allchars_as($uid)
{
  global $mybb, $db;
  //array initialisieren und uid packen
  $alluids = array();
  //as uid bekommen
  $userinfo = get_user($uid);
  $as_uid = $userinfo['as_uid'];

  //Hauptaccount oder anderer 
  if ($as_uid == 0) {
    $getuid = $uid;
  } else if ($as_uid != 0) {
    $getuid = $as_uid;
  }

  $get_attached = $db->simple_select("users", "uid", "as_uid = '{$getuid}' OR uid = '{$getuid}'");
  //in array packen
  while ($attached = $db->fetch_array($get_attached)) {
    $alluids[] = $attached['uid'];
  }
  return $alluids;
}

/**
 * Hauptfunktion der Blacklist
 * Checkt ob ein Charakter auf der Blacklist stehen würde
 * @param array uids der Charaktere des users
 * @param string welchem datum ausgehend
 * @return array assoziatives array
 * Auflistung der Array einträge im Wiki
 */
function activitytracker_check_blacklist($uid, ?DateTime $checkdate = null)
{
  global $mybb, $db;
  //blacklist array initalisieren
  $blacklistarray = array();
  //für welches datum soll getestet werden, wenn keins übergeben wird, immer heute
  $checkdate = $checkdate ?? new DateTime('now');

  //get settings 
  $frist_blacklistende = $mybb->settings['activitytracker_bl_deadline'];
  $frist_blacklistdaystart = $mybb->settings['activitytracker_bl_turnus_day'];

  $setting_ingame_start = $mybb->settings['activitytracker_bl_ingamestart'];
  $wobdate = $mybb->settings['activitytracker_bl_wobdate'];
  //wieviele tage für post
  $ingame_days = $mybb->settings['activitytracker_bl_duration_days'];
  $ingame_days_startingame = $mybb->settings['activitytracker_bl_ingamestart_days'];
  //Soll es eine gesonderte Frist geben, wenn es keinen Post im Ingame gibt
  $bl_noscenes = $mybb->settings['activitytracker_bl_noscenes'];
  $noscenes_days = $mybb->settings['activitytracker_bl_noscenes_days'];
  $wobdays = $mybb->settings['activitytracker_bl_applicationduration'];
  $setting_checktype = $mybb->settings['activitytracker_bl_type'];

  $setting_reihenfolge = $mybb->settings['activitytracker_bl_tracker_order'];
  //aktueller monat wenn month = this 
  //sonst nächster Monat -> achtung mit Jahr!
  // $frist_date frist_blacklistdaystart + $month date + daysdeadline

  //Forenkram fids etc
  //Forenstring bauen
  $archive_fid = str_replace(" ", "", "," . $mybb->settings['activitytracker_archiv']);
  $ingame_fid  = str_replace(" ", "", "," . $mybb->settings['activitytracker_ingame']);

  //string für Archiv & Ingame, abfangen wenn kein Archiv angegeben ist
  if ($mybb->settings['activitytracker_archiv'] != "") {
    $ingame_archive_fid = $ingame_fid . "," . $archive_fid;
  } else {
    $ingame_archive_fid = $ingame_fid;
  }

  //Welche Gruppen gelten als angenommen
  $generalgroup = $mybb->settings['activitytracker_groups'];
  //Bewerbergruppe
  $applicantgroup = $mybb->settings['activitytracker_applicationgroup'];
  //ausgeschlossene User
  $excludeduser = $mybb->settings['activitytracker_excludeduid'];

  //art Zeitraum 
  $timeperiod = trim($mybb->settings['activitytracker_bl_duration']);
  $charinfo = array();

  //alle User durchgehen
  // foreach ($uids as $uid) {
  $scene_count_has_to = 0;
  $scene_count = 0;
  //Charkterinfos bekommen
  $charinfo = get_user($uid);
  //wob date des users
  $wob = activitytracker_get_wobdate($wobdate, $uid);
  //gibt es Posts im Ingame?
  $ingamepost = activitytracker_posts_check($uid, $ingame_fid);
  //gibt es Posts im Archiv
  $archivepost = activitytracker_posts_check($uid, $archive_fid);
  if (is_member($applicantgroup, $uid)) {
    //User ist Bewerber
    $regdate = intval($charinfo['regdate']);
    // $datetime = (new DateTime())->setTimestamp($regdate);
    $diff_days = activitytracker_get_days_diff($regdate, $checkdate);
    $formatted_date = (new DateTime())->setTimestamp($regdate)->format('d.m.Y');

    //Erst mal schauen ob die Frist schon abgelaufen ist, in der der User einen Steckbrief einreichen muss
    if ($diff_days > $wobdays) {
      //Dann schauen wir ob es eine Bewerbung gibt.
      $hasapplication = activitytracker_check_application($uid, $checkdate);
      if (!$hasapplication) {
        //keine Bewerbung eingereicht -> also blacklist
        $blacklistarray[$uid]['reason'] = "noapplication";
        $blacklistarray[$uid]['regdate'] = $formatted_date;
        $blacklistarray[$uid]['daysdiff'] = $diff_days;
      }
    }
  } else {
    //user is angenommen
    if (is_member($generalgroup, $uid)) {
      // es gibt keine posts (weder ingame noch archiv ) - also keinen ingameeinstieg (weil weder ein Post im Archiv noch im Ingame)
      if (!$ingamepost && !$archivepost) {
        // echo "Es gibt keine Posts im Ingame und Archiv activitytracker_check_blacklist<br>";
        //wir wollen herausfinden, seit wann der user angenommen ist
        //Settings für gesonderten Zeitraum, für einstieg ins Ingame checken
        if ($setting_ingame_start == 1) {
          //gesonderter Zeitraum für Ingameeinstieg
          $isintime = activitytracker_post_in_timeperiod($wob, 'days', $checkdate, $ingame_days_startingame);
        } else {
          // sonst wie bei posts
          $isintime = activitytracker_post_in_timeperiod($wob, $timeperiod, $checkdate);
        }
        //es gibt keinen Post in der Frist
        if (!$isintime) {
          //Umwandeln in DateTime Objekt - damit können wir besser rechnen.
          $wob_datetime = (new DateTime())->setTimestamp($wob);
          $formatted_date = $wob_datetime->format('d.m.Y');
          //berechnen der Tage seit wob
          $diff_days = activitytracker_get_days_diff($wob_datetime, $checkdate);
          $blacklistarray[$uid]['reason'] = "noingamestart";
          $blacklistarray[$uid]['wobdate'] = $formatted_date;
          $blacklistarray[$uid]['daysdiff'] = $diff_days;
        }
      }

      if ($ingamepost) {
        //Es gibt Ingameposts - wir prüfen ob der letzte post in der Frist liegt

        //setting holen, ob wir schauen ob wir pro szene schauen, wann gepostet wurde, oder einfach nur allgemein pro chara
        if ($setting_checktype === 'lastingame') {
          //Letzter Post des Charakters im Ingame
          //Soll die Reihenfolge beachtet werden?
          if ($setting_reihenfolge == 0) {
            //reihenfolge ist egal
            //einfach letzten post holen aus dem Ingame und schauen ob er im zeitraum ist
            $ingame_lastpost = activitytracker_get_lastpost($uid, $ingame_fid);
            //checken ob er im Zeitraum ist
            $isintime = activitytracker_post_in_timeperiod($ingame_lastpost['dateline'], $timeperiod, $checkdate);
            //TODO ? Checken ob einer im archiv ist? 
            //kein post gefunden der im Zeitraum ist
            if (!$isintime) {
              $blacklistarray[$uid]['reason'] = "tooldscenes";
              $blacklistarray[$uid]['tid'] = $ingame_lastpost['tid'];
            }
          } else {
            //Postreihenfolge soll beachtet werden
            $allposts_ingame = array();
            // erst einmal letzten posts des users aus dem Ingame in Zeitraum X (Blacklist settings) holen, 
            $ingame_lastpost = activitytracker_get_lastpost($uid, $ingame_fid);
            $isintime = false;
            $isintime = activitytracker_check_hastopost($ingame_lastpost['tid'], $uid);
            // es gibt zwar posts, aber keiner in der frist
            if (!$isintime) {
              //also muss der Charakter auf die Blacklist
              $blacklistarray[$uid]['reason'] = "tooldscenes";
              $blacklistarray[$uid]['tid'] = $ingame_lastpost['tid'];
            }
          }
        } else {
          //wir schauen pro Szene
          //wir holen uns alle Szenen des Charakters, wo er als Teilnehmer eingetragen ist
          //stattdessen alle posts aus dem ingame holen 
          $allposts_ingame = activitytracker_get_all_scenes_ingame($uid, $ingame_fid);

          //Soll die Reihenfolge beachtet werden?
          if ($setting_reihenfolge == 0) {
            //Postreihenfolge soll nicht beachtet werden
            $isintime = false;
            //reihenfolge ist egal
            //alle posts durchgehen 
            foreach ($allposts_ingame as $post) {
              $scene_count++; //wir zählen hier wieviele Szenen der User hat
              // hole die infos vom thread
              $get_thread = get_thread($post['tid']);
              if ($post['lastposteruid'] != $uid) {
                //Letzterpost nicht vom User, also wollen wir gucken, wie lange es her ist, seit dem der letzte post geschrieben wurden ist
                $lastpost_datetime = (new DateTime())->setTimestamp($get_thread['lastpost']);
                $tid = $get_thread['tid'];
                //wir haben eine szene, in der der User teilnehmer ist. 
                //Die Postingreihenfolge ist uns egal 
                //also schauen wir, von wann der letzte post in der Szene ist // bei 2er szenen sollte es sowieso ja immer der andere poster sein 
                //dafür checken wir ob der letzte post, im erlaubten Zeitraum ist
                $isintime = activitytracker_post_in_timeperiod($lastpost_datetime, $timeperiod, $checkdate);
                if (!$isintime) {
                  $diff_days = activitytracker_get_days_diff($lastpost_datetime, $checkdate);
                  $formatted_date = $lastpost_datetime->format('d.m.Y');
                  // Der letzte Post ist nicht im Zeitraum, also muss der Charakter/Szene auf die Blacklist
                  $blacklistarray[$uid]['reason'] = "tooldscenes";
                  $blacklistarray[$uid]['tid'] .= "{$tid},";
                }
                $hasttopost = activitytracker_check_hastopost($tid, $uid);
                if (!$hasttopost) {
                  //der User ist nicht dran, aber wir zählen hoch, wie viele Szenen in denen er nicht dran ist es gibt
                  $scene_count_has_to++;
                }
              }
            }
          } else {
            //wir wollen die Postreihenfolge beachten 

            //also gehen wir auch hier alle szenen durch
            foreach ($allposts_ingame as $post) {
              $scene_count++;
              $tid = $post['tid'];
              $get_thread = get_thread($tid);
              $hasttopost = activitytracker_check_hastopost($tid, $uid);
              //er ist dran
              if ($hasttopost) {
                //dann checken wir wie lange es her ist, seit dem der letzte post geschrieben wurden ist
                $lastpost_datetime = (new DateTime())->setTimestamp($get_thread['lastpost']);
                $formatted_date = $lastpost_datetime->format('d.m.Y');
                $isintime = activitytracker_post_in_timeperiod($lastpost_datetime, $timeperiod, $checkdate);
                //wenn der post nicht im zeitraum ist, dann muss der Charakter/Szene auf die Blacklist
                if (!$isintime) {
                  $blacklistarray[$uid]['reason'] = "tooldscenes";
                  $blacklistarray[$uid]['tid'] .= "{$tid},";
                }
              }
            }
          }
        }
      } else {
        // Es gibt keine Ingameszene
        // in diesem Fall schauen wir ob es eine Szene im Archiv gibt und ob der letzte Post des Users im Archiv im Zeitraum (evt. gesonderte frist) ist
        //wir holen uns den letzten Post des Users aus dem Archiv
        $ingame_archiv = activitytracker_get_lastpost($uid, $archive_fid);
        if (!empty($ingame_archiv)) {
          //Umwandeln in DateTime Objekt - damit können wir besser rechnen.
          //wir haben einen letzten Post im Archiv
          $lastpost_datetime = (new DateTime())->setTimestamp($ingame_archiv['dateline']);
          //Umwandeln in lesbares datum
          $formatted_date = $lastpost_datetime->format('d.m.Y');

          //Sonderbehandlung für keine offenen Szenen im Ingame - ob pro Charakter oder Szene ist hier egal, wir schauen uns sowieso nur den letzten Post des Charakters im Archiv an
          if ($bl_noscenes == 1) {
            //setting: gleicher Zeitraum wie post im ingame
            $isintime = activitytracker_post_in_timeperiod($ingame_archiv['dateline'], $timeperiod, $checkdate);
            if (!$isintime) {
              //berechnen der Tage seit wob
              $diff_days = activitytracker_get_days_diff($lastpost_datetime, $checkdate);
              $blacklistarray[$uid]['reason'] = "noingamescene";
              $blacklistarray[$uid]['lastpostdate'] = $formatted_date;
              $blacklistarray[$uid]['tid'] = $ingame_archiv['tid'];
              $blacklistarray[$uid]['daysdiff'] = $diff_days;
            }
          } else if ($bl_noscenes == 2) {
            //setting: gesonderter Zeitraum für keine offenen Szenen im Ingame
            $isintime = activitytracker_post_in_timeperiod($ingame_archiv['dateline'], $timeperiod, $checkdate, $noscenes_days);

            if (!$isintime) {
              //berechnen der Tage seit wob
              $diff_days = activitytracker_get_days_diff($lastpost_datetime, $checkdate);
              $blacklistarray[$uid]['reason'] = "noingamescene";
              $blacklistarray[$uid]['lastpostdate'] = $formatted_date;
              $blacklistarray[$uid]['tid'] = $ingame_archiv['tid'];

              $blacklistarray[$uid]['daysdiff'] = $diff_days;
            }
          } else {
            //setting: kein gesonderter Zeitraum für keine offenen Szenen im Ingame -> Es gibt keine Ingameszene, also muss der Chrakter auf die Blacklist 
            $ingame_archiv = activitytracker_get_lastpost($uid, $archive_fid);
            $lastpost_datetime = (new DateTime())->setTimestamp($ingame_archiv['dateline']);
            $formatted_date = $lastpost_datetime->format('d.m.Y');
            //berechnen der Tage seit wob
            $diff_days = activitytracker_get_days_diff($lastpost_datetime, $checkdate);
            $blacklistarray[$uid]['reason'] = "noingamescene";
            $blacklistarray[$uid]['lastpostdate'] = $formatted_date;
            $blacklistarray[$uid]['tid'] = $ingame_archiv['tid'];

            $blacklistarray[$uid]['daysdiff'] = $diff_days;
          }
        } else {
          //keine ingame / keine archiv szene - abfangen
          $blacklistarray[$uid]['reason'] = "noingamescene";
          $blacklistarray[$uid]['lastpostdate'] = $formatted_date;
        }
      }
    }
  }

  return $blacklistarray;
}

/**
 * Prüfen ob der User einen Steckbrief eingereicht hat
 * @param int uid des Users
 * @param DateTime checkdate - datum an dem geprüft werden soll
 * @return boolean
 */
function activitytracker_check_application($uid, $checkdate)
{
  global $db, $mybb;
  $type = $mybb->settings['activitytracker_bl_einreichung'];
  $daysforapplication = $mybb->settings['activitytracker_bl_applicationduration'];
  $applicationfid = $mybb->settings['activitytracker_bl_bewerberfid'];
  //prüfen ob der User einen Steckbrief eingereicht hat
  //wir schauen ob es einen Steckbrief gibt, der dem User zugeordnet ist
  if ($type == "stecki_anders") {
    //Check von Registrierungsdatum bis heute
    $regdate = get_user($uid)['regdate'];
    $diff_days = activitytracker_get_days_diff($regdate, $checkdate);
    if ($diff_days > $daysforapplication) {
      //es gibt keinen Steckbrief
      return false;
    } else {
      return true;
    }
  } else if ($type == "stecki_risu") {
    //application tabelle check, gibt es einen eintrag in der management tabelle
    $application_query = $db->simple_select("application_ucp_management", "*", "uid = '{$uid}'");
    if ($db->num_rows($application_query) > 0) {
      //es gibt einen Eintrag
      return true;
    }
  } else if ($type == "stecki_thread") {
    //schauen ob es einen thread in einer bestimmten area gibt
    $application = activitytracker_get_lastthread($uid, $applicationfid);
    if (empty($application)) {
      //es gibt keinen Thread
      return false;
    } else {
      return true;
    }
  }
  return false;
}

/**
 * Testet ob es einen Post des users in gegebener liste von Foren gibt
 * @param int - uid 
 * @param string - einzelne fid oder kommagetrennte liste von fids
 * @return boolean
 * */
function activitytracker_posts_check($uid, $fid)
{
  global $mybb, $db;

  //parentlist bekommen

  $fidlist = activitytracker_get_fids_string($fid);
  // ausgeschlossene foren
  $excluded = activitytracker_get_excluded_string();

  if ($fidlist != "") {
    if ($db->num_rows($db->simple_select("posts", "*", "uid = '{$uid}' AND fid in ({$fidlist}) {$excluded}")) > 0) {
      return true;
    }
  }
  return false;
}

/**
 * Checken ob User als Teilnehmer einer bestimmten Szene eingetragen ist
 * @param int threadid
 * @param int uid
 * @return boolean
 */
function activitytracker_check_teilnehmer_singlethread($tid, $uid)
{
  $teilnehmer_array = activitytracker_get_teilnehmer_singlethread($tid, $uid);
  $userdata = get_user($uid);
  return in_array($userdata['username'], $teilnehmer_array);
}

/**
 * Checken ob User als Teilnehmer einer bestimmten Szene in einer Area eingetragen ist
 * @param int threadid
 * @param int uid
 * @return boolean
 */
function activitytracker_check_teilnehmer_allthread($uid, $fid)
{
  global $mybb, $db;
  $trackertype = $mybb->settings['activitytracker_bl_tracker'];
  $fidlist = activitytracker_get_fids_string($fid);
  // ausgeschlossene foren
  $excluded = activitytracker_get_excluded_string();
  $user = get_user($uid);
  $username = $db->escape_string($user['username']);

  if ($trackertype == 'kein') {
    //dann schauen wir ob es irgendeinen post im ingame/archiv gib
    $teilnehmer_query = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "posts where uid = '{$uid}' AND fid in ({$fidlist}) {$excluded}");
    if ($db->num_rows($teilnehmer_query) > 0) {
      return true;
    }
  } elseif ($trackertype == 'risu') {
    //sichergehen dass der tracker installiert ist
    if (!$db->table_exists('scenetracker')) {
      if ($db->num_rows($db->simple_select("threads", "*", "scenetracker_user like '%{$username}%' AND fid in ({$fidlist} {$excluded}"))) {
        return true;
      }
    }
  } elseif ($trackertype == 'sparks2') {
    //sichergehen dass der tracker installiert ist
    if ($db->num_rows($db->simple_select("threads", "*", "partners like '%{$username}%' AND fid in ({$fidlist}) {$excluded}"))) {
      return true;
    }
  } elseif ($trackertype == 'sparks3') {
    //sichergehen dass der tracker installiert ist
    if (!$db->table_exists("ipt_scenes_partners")) {
      //Wir haben beim 3.0 keine gespeicherte reihenfolge, also gehen wir von der Reihenfolge aus, in der die Partner eingetragen wurden sind
      $ipt3 = $db->write_query("SELECT * FROM ipt_scenes_partners WHERE uid='{$uid}'");
      //gibt es überhaupt eine szene wo er eingetragen ist? 
      if ($db->num_rows($ipt3 > 0)) {
        //ist diese noch im ingame? 
        while ($teilnehmer_data = $db->fetch_array($ipt3)) {
          $tid = $teilnehmer_data['tid'];
          if ($db->num_rows($db->simple_select("threads", "*", "tid = '{$tid}' AND fid in ({$fidlist}"))) {
            return true;
          }
        }
      }
    }
  } elseif ($trackertype == 'ales') {
    //sichergehen dass der tracker installiert ist
    if (!$db->field_exists("charas", "threads")) {
      if ($db->simple_select("threads", "charas", "charas like '%{$username}%' AND fid in ({$fidlist}) {$excluded}") > 0) {
        return true;
      }
    }
  } elseif ($trackertype == 'laras') {
    //sichergehen dass der tracker installiert ist
    if (!$db->table_exists("inplayscenes")) {
      if ($db->simple_select("inplayscenes", "partners_username", "partners_username like '%{$username}%''") > 0) {
        return true;
      }
    }
  }
}


/**
 * Letzten (vom user erstellten) Thread eines Users aus einer Liste von Foren (+childs) bekommen
 * @param int uid des Users
 * @param string Kommagetrennte Liste von Foren
 * @return array Array mit Threaddaten
 */
function activitytracker_get_lastthread($uid, $fidlist)
{
  global $db, $mybb;
  //array initialisieren
  $thread = array();
  //liste aller foren bekommen
  $getchilds = activitytracker_get_fids_string($fidlist);
  $excluded = activitytracker_get_excluded_string();
  //den letzten Thread 
  $thread_query = $db->write_query("SELECT * from " . TABLE_PREFIX . "threads t
  WHERE uid = '{$uid}' 
  AND fid in ($getchilds) {$excluded} ORDER by dateline LIMIT 1");
  //post in array speichern
  $thread = $db->fetch_array($thread_query);
  return $thread;
}

/**
 * Letzten Post eines Users aus einem Forum (+childs) bekommen
 * @param int uid des Users
 * @param string Kommagetrennte Liste von Foren
 * @return array Array mit Postdaten
 */
function activitytracker_get_lastpost($uid, $fidlist)
{
  global $db, $mybb;
  //array initialisieren
  $post = array();
  //liste aller foren bekommen
  $getchilds = activitytracker_get_fids_string($fidlist);
  $excluded = activitytracker_get_excluded_string();
  //den letzten Post aus liste von foren bekommen
  $post_query = $db->write_query("SELECT * from " . TABLE_PREFIX . "posts p
  WHERE uid = '{$uid}' 
  AND fid in ($getchilds) {$excluded} ORDER by dateline desc LIMIT 1");
  //post in array speichern
  $post = $db->fetch_array($post_query);
  return $post;
}
/** Prüfen ob eine uid zum User gehört
 * @param int uid
 * @return boolean
 */
function activitytracker_is_in_switcher($uid, $tocheck = 0)
{
  global $mybb;
  //wird keine gesonderte tocheck uid übergeben, dann mit user checken der gerade online ist
  if ($tocheck == 0) {
    $tocheck = $mybb->user['uid'];
  }
  $uids = activitytracker_get_allchars_as($uid);
  return in_array($tocheck, $uids);
}

/** 
 * Streichen von Usern von der Blacklist
 * @param int uid
 * @param boolean mod - ob der user über den Modlink gestrichen wird
 */
function activitytracker_stroke($uid, $mod = false)
{
  global $db, $mybb;

  $today = (new DateTime())->format('Y-m-d');
  $stroke_array = array("uid" => $uid, "strokedate" => $today);

  if (!$mod) {
    if (!activitytracker_is_in_switcher($uid) && !$mod) {
      //ist nicht in switcher -> darf nicht gestrichen werden
      error_no_permission();
    }
    $db->insert_query("at_blacklist_strokes", $stroke_array);
  }

  if ($mod && $mybb->usergroup['canmodcp'] == 1) {
    $stroke_array['stroke_mod'] = 1;
    $db->insert_query("at_blacklist_strokes", $stroke_array);
  }
}

/** 
 * Löschen von Usern von der Blacklist
 * @param int uid
 */
function activitytracker_delete_bl($uid)
{
  global $db, $mybb;
  if ($mybb->usergroup['canmodcp'] == 1) {
    if ($mybb->settings['activitytracker_bl_turnus_extrainfo_month'] == "1") {
      $timestr = date('Y-m');
    } else {
      $timestr = date('Y-m', strtotime('first day of last month'));
    }
    $db->delete_query("at_blacklist", "uid = '{$uid}' AND bldate like '{$timestr}%'");
  } else {
    error_no_permission();
  }
}

/** 
 * User können den Charakter markieren, dass er gelöscht werden soll
 * @param int uid
 */
function activitytracker_mark_to_delete($uid)
{
  global $db, $mybb;
  if ($mybb->usergroup['canmodcp'] == 1 || activitytracker_is_in_switcher($uid)) {
    $insert = array(
      "uid" => "{$uid}",
      "markdate" => date('Y-m-d H:i:s'),
    );
    $db->insert_query("at_blacklist_deletelist", $insert);
  } else {
    error_no_permission();
  }
}

/**
 * Die Szenen(User ist als Teilnehmer eingetragen) sammeln, aus einer Liste von Foren (+Childs). 
 * @param int uid 
 * @return array mit thread oder postdaten
 */
function activitytracker_get_all_scenes_ingame($uid, $fidlist)
{
  global $db, $mybb;

  // Array initialisieren
  $posts = array();

  // Alle Foren (inkl. Child-Foren) zusammenstellen
  $getchilds = activitytracker_get_fids_string($fidlist);

  $excluded = activitytracker_get_excluded_string();

  $userdata = get_user($uid);
  $username = $db->escape_string($userdata['username']);
  $trackertype = $mybb->settings['activitytracker_bl_tracker'];
  $trackertype = trim($trackertype);

  if ($trackertype == 'kein') {
    // TODO Querie prüfen! 
    $post_query = $db->write_query("SELECT 
              p.*, 
              t.lastpost,
              t.lastposter,
              t.lastposteruid
          FROM " . TABLE_PREFIX . "posts p
          INNER JOIN (
              SELECT tid, MAX(dateline) AS max_date
              FROM " . TABLE_PREFIX . "posts
              WHERE uid = '{$uid}' 
                AND fid IN ($getchilds)
                {$excluded}
              GROUP BY tid
          ) last_posts 
              ON p.tid = last_posts.tid AND p.dateline = last_posts.max_date
          INNER JOIN " . TABLE_PREFIX . "threads t 
              ON t.tid = p.tid
          WHERE p.uid = '{$uid}' 
            AND p.fid IN ($getchilds)
            {$excluded}
          ORDER BY p.dateline DESC;
    ");
  } else if ($trackertype == 'risu') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists('scenetracker')) {
      $post_query = $db->simple_select("threads", "*", "scenetracker_user like '%$username%' AND fid IN ($getchilds) {$excluded}");
    }
  } else if ($trackertype == 'sparks2') {
    //sichergehen dass der tracker installiert ist
    //postorder wird wie gespeichert? 
    if ($db->field_exists("partners", "threads")) {
      $post_query = $db->simple_select("threads", "*", "partners like '%$username%' AND fid IN ($getchilds) {$excluded}");
    }
  } else if ($trackertype == 'sparks3') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists("ipt_scenes_partners")) {
      //Wir haben beim 3.0 keine gespeicherte reihenfolge, also gehen wir von der Reihenfolge aus, in der die Partner eingetragen wurden sind
      $post_query = $db->write_query("
            SELECT sp.*, t.*
            FROM " . TABLE_PREFIX . "ipt_scenes_partners sp
            INNER JOIN " . TABLE_PREFIX . "threads t ON t.tid = sp.tid
            WHERE sp.uid = '{$uid}'
            ORDER BY sp.spid DESC
        ");
    }
  } elseif ($trackertype == 'ales') {
    //sichergehen dass der tracker installiert ist
    if ($db->field_exists("charas", "threads")) {
      $post_query = $db->simple_select("threads", "charas", "* like '%$username%' AND fid IN ($getchilds) {$excluded}");
    }
  } elseif ($trackertype == 'laras') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists("inplayscenes")) {
      $post_query = $db->write_query("
            SELECT ip.*, t.*
            FROM " . TABLE_PREFIX . "inplayscenes ip
            INNER JOIN " . TABLE_PREFIX . "threads t ON t.tid = ip.tid
            WHERE ip.partners_username like '%$username%'
        ");
    }
  }
  //alle threads, wo der User als Teilnehmer eingetragen is durchgehen und speichern
  while ($post = $db->fetch_array($post_query)) {
    //daten des posts speichern
    $posts[] = $post;
  }
  //Array mit allen Szenen des Users zurückgeben
  return $posts;
}

/**
 * Alle Posts eines Users aus einem Forum (+childs) bekommen.
 * @param int uid des Users
 * @param string Kommagetrennte Liste von Foren
 * @return array Array mit Postdaten
 */
function activitytracker_get_all_posts_ingame($uid, $fidlist)
{
  global $db, $mybb;

  // Array initialisieren
  $posts = array();

  // Alle Foren (inkl. Child-Foren) zusammenstellen
  $getchilds = activitytracker_get_fids_string($fidlist);
  $excluded = activitytracker_get_excluded_string();

  $post_query = $db->write_query("SELECT * FROM " . TABLE_PREFIX . "posts
        WHERE uid = '{$uid}' 
        AND fid IN ($getchilds)
        {$excluded}
        ORDER BY dateline DESC");

  // 
  while ($post = $db->fetch_array($post_query)) {
    $posts[] = $post;
  }
  return $posts;
}

/**
 * Exclude String bauen für SQL Querys
 * @return string mit excluded foren für SQL String
 */
function activitytracker_get_excluded_string()
{
  global $mybb;
  $excluded = "";
  if ($mybb->settings['activitytracker_fidexcluded'] != "" && $mybb->settings['activitytracker_fidexcluded'] != 0) {
    $exluded_fidlist = activitytracker_get_fids_string($mybb->settings['activitytracker_fidexcluded']);
    $excluded .= " AND fid not in ({$exluded_fidlist})";
  }
  if ($mybb->settings['activitytracker_tidexcluded'] != "") {
    $excluded .= " AND tid not in ({$mybb->settings['activitytracker_tidexcluded']})";
  }
  return $excluded;
}


/**
 * Get days Diff
 * @param DateTime 
 * @return int 
 */
function activitytracker_get_days_diff($datetime, $checkdate = "today")
{
  // Wenn $checkdate "today" ist, dann verwenden wir das aktuelle Datum
  if ($checkdate === "today") {
    $tocheckDate = new DateTime();
  } elseif ($checkdate instanceof DateTime) {
    $tocheckDate = $checkdate;
  }

  //haben wir einen timestamp oder ein datetime objekt? Ist es int, müssen wir es noch umwandelnt
  if (is_numeric($datetime)) {
    $datetime = (new DateTime())->setTimestamp($datetime);
  }

  // Differenz berechnen 
  $diff = $tocheckDate->diff($datetime);
  return $diff->days;
}


/**
 * Benachrichtigungsfunktion
 * Sendet Mails an die User die auf der Blacklist stehen
 * Je nach Einstellung als PN oder als Mail
 */
function activitytracker_blacklist_alert()
{
  //hole einstellung
  //if (PN)
  //Set UP PN HANDLER
  //IF (MAIL)
  //SEND MAIL
}

/**
 * Forumids
 * Die Forenids bekommen, in denen Posts berücksichtig werden
 * Gibt einen String zurück mit der liste von zu testenden Foren, außer der ausgeschlossenen
 * @param string - fid oder , getrennte liste mit fids
 * @return string
 */
function activitytracker_get_fids_string($type)
{
  global $mybb;
  // fidsttring bereinigen, vorsichtshalber
  $fidsstr = str_replace(" ", "", "," . $type);

  $all_fids_str = array();
  //ausgeschlossene foren array - leerstellen rauswerfen falls vorhanden
  $excludedfids_array =  explode(",", str_replace(" ", "", $mybb->settings['activitytracker_fidexcluded']));

  //array basteln und leere einträge rauswerfen
  $fids_array = array_filter(explode(",", $fidsstr));
  //jedes durchgehen und childforen bekommen
  foreach ($fids_array as $fid) {
    $all_fids_str = array_merge($all_fids_str, get_child_list($fid));
  }

  //doppelte einträge rauswerfen
  $ingame_archiv_fids_array = array_unique($all_fids_str);
  //exluded rauswerfen
  $fids_array = array_diff($ingame_archiv_fids_array, $excludedfids_array);
  //zu string 
  $fids_str = implode(",", $fids_array);
  return $fids_str;
}

/**
 * Je nach Tracker prüfen, ob der User als nächstes dran ist.
 * @param int threadid
 * @return boolean  true: wenn dran, false wenn nicht dran
 */
function activitytracker_check_hastopost($tid, $uid)
{
  global $mybb, $db;

  $userdata = get_user($uid);
  $threaddata = get_thread($tid);

  // Teilnehmer holen
  $teilnehmer_array = activitytracker_get_teilnehmer_singlethread($tid, $uid);

  // 2-Teilnehmer-Fall: super easy
  if (count($teilnehmer_array) == 2) {
    return ($threaddata['lastposter'] == $userdata['username']) ? false : true;
  }

  // Mehr als 2 Teilnehmer → Rotation nötig
  $position = array_search($userdata['username'], $teilnehmer_array);

  // Falls User nicht in Teilnehmerliste -> kann nicht dran sein
  if ($position === false) {
    return false;
  }

  // Rotation erstellen: Alle hinter dem chara, dann alle davor, dann der chara selbst
  $before = array_slice($teilnehmer_array, $position + 1);
  $after = array_slice($teilnehmer_array, 0, $position);
  $teilnehmer_sorted = array_merge($before, $after, [$teilnehmer_array[$position]]);

  // Zeiger ans Ende setzen (aktueller Chara), einen Schritt zurück für den vorherigen chara
  end($teilnehmer_sorted);
  $lastposter_shouldbe = prev($teilnehmer_sorted);

  // to be save, gelöschte user überspringen
  while ($lastposter_shouldbe !== false && empty(get_user_by_username($lastposter_shouldbe))) {
    $lastposter_shouldbe = prev($teilnehmer_sorted);
  }

  // fallback
  if ($lastposter_shouldbe === false) {
    return false;
  }

  // hat der vorherige chara zuletzt gepostet?
  return ($lastposter_shouldbe == $threaddata['lastposter']);
}

/**
 * Die Teilnehmer einer bestimmten Szene bekommen
 * @param int threadid
 * @param int userid
 * @return array mit usernamen
 */
function activitytracker_get_teilnehmer_singlethread($tid, $uid)
{
  global $mybb, $db;
  $trackertype = trim($mybb->settings['activitytracker_bl_tracker']);
  // echo "activitytracker_get_teilnehmer_singlethread: tid: {$tid} - uid: {$uid} -trackertype '{$trackertype}' <br>";
  $teilnehmer = "";
  $teilnehmer_array = array();
  $threaddata = get_thread($tid);
  $userdata = get_user($uid);
  if ($trackertype == 'kein') {
    // echo "<br>activitytracker_get_teilnehmer_singlethread: trackertype ist kein <br>";
    //dann nehmen wir die reihenfolge in der bisher gepostet wurde - so gut das geht
    $teilnehmer_query = $db->write_query("SELECT DISTINCT(username) FROM " . TABLE_PREFIX . "posts where tid = '{$tid}' ORDER BY pid DESC");
    while ($teilnehmer_data = $db->fetch_array($teilnehmer_query)) {
      $teilnehmer_array = $teilnehmer_data['username'];
    }
  } else if ($trackertype == 'risu') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists('scenetracker')) {
      $teilnehmer = $db->fetch_field($db->simple_select("threads", "scenetracker_user", "tid='{$tid}'"), "scenetracker_user");
    }
    $teilnehmer_array = explode(",", trim($teilnehmer));
  } else if ($trackertype == 'sparks2') {
    //sichergehen dass der tracker installiert ist
    //postorder wird wie gespeichert? 
    if ($db->field_exists("partners", "threads")) {
      $teilnehmer = $db->fetch_field($db->simple_select("threads", "partners", "tid='{$tid}'"), "partners");
    }
    $teilnehmer_array = explode(",", trim($teilnehmer));
  } else if ($trackertype == 'sparks3') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists("ipt_scenes_partners")) {
      //Wir haben beim 3.0 keine gespeicherte reihenfolge, also gehen wir von der Reihenfolge aus, in der die Partner eingetragen wurden sind
      $teilnehmer_query = $db->write_query("SELECT * FROM ipt_scenes_partners WHERE tid='{$tid}' ORDER BY spid ASC");
      while ($teilnehmer_data = $db->fetch_array($teilnehmer_query)) {
        $get_user = get_user($teilnehmer_data['uid']); // Holt den User anhand der UID
        if (!empty($get_user)) {
          $teilnehmer_array[] = $get_user['username']; // füge Username ins Array ein
        }
      }
    }
  } else if ($trackertype == 'ales') {
    //sichergehen dass der tracker installiert ist
    if ($db->field_exists("charas", "threads")) {
      $teilnehmer = $db->fetch_field($db->simple_select("threads", "charas", "tid='{$tid}'"), "charas");
    }
    $teilnehmer_array = explode(",", trim($teilnehmer));
  } else if ($trackertype == 'laras') {
    //sichergehen dass der tracker installiert ist
    if ($db->table_exists("inplayscenes")) {
      $teilnehmer = $db->fetch_field($db->simple_select("inplayscenes", "partners_username", "tid='{$tid}'"), "partners_username");
      $teilnehmer_array = explode(",", trim($teilnehmer));
    }
  }
  // echo "activitytracker_get_teilnehmer_singlethread: Risu Szenentracker - teilnehmer: <br>";
  // var_dump($teilnehmer_array);
  // echo "<br>";
  // wir stellen sicher, dass das Array nur existierende user enthält
  $teilnehmer_array = array_filter($teilnehmer_array, function ($username) {
    //callback funktion, testet ob es den usernamen gibt
    $user = get_user_by_username(trim($username));
    return !empty($user); // true = behalten, false = entfernen
  });

  // Array neu indizieren, damit die Keys fortlaufend sind
  $teilnehmer_array = array_values($teilnehmer_array);

  return $teilnehmer_array;
}

/**
 * WOB Datum des Users holen
 * @param string - wobdate type
 * @param int - uid des Users
 * @return int - timestamp des wobdatums
 */

function activitytracker_get_wobdate($wobdate, $uid)
{
  global $db, $mybb;
  //wobdate/register/area
  if ($wobdate == 'ales_wob') {
    $wob = $db->fetch_field($db->simple_select("users", "wob_date", "uid = '{$uid}'"), "wob_date");
  } elseif ($wobdate == 'risu_reg') {
    $wob = $db->fetch_field($db->simple_select("users", "wob_date", "uid = '{$uid}'"), "wob_date");
  } elseif ($wobdate == 'thread') {
    //ID Steckiarea
    $wob_fid = $mybb->settings['activitytracker_bl_wobdate_thread'];
    $lastthread = activitytracker_get_lastthread($uid, $wob_fid);
    //Wir gehen davon aus dass das Postdate des letzten posts des Threads wobdate ist. (vermutlich letzter post des moderators)
    $wob = $lastthread['lastpost'];
  } else {
    //-> default registrierungsdatum von mybb
    $wob = $db->fetch_field($db->simple_select("users", "regdate", "uid = '{$uid}'"), "regdate");
  }
  //TODO LARAS Bewerbercheck
  return $wob;
}

/**
 * Die uid des Hauptusers(accountswitcher) des users
 * @param int uid eines users
 * @return int uid des Hauptaccounts 
 */
function activitytracker_get_as_uid($uid)
{
  global $db;
  $as_uid = $db->fetch_field($db->simple_select("users", "as_uid", "uid = '$uid'"), "as_uid");
  if ($as_uid == 0) {
    $as_uid = $uid;
  }
  return $as_uid;
}

/**
 * Checken ob User Teilnehmer einer bestimmten Szene ist
 * @param int threadid
 * @param int fid forenliste
 * @return boolean
 */
function activitytracker_check_is_teilnehmer($uid, $fid)
{
  global $mybb, $db;
  //parentlist bekommen
  $fidlist = activitytracker_get_fids_string($fid);
  $userdata = get_user($uid);
  $username = $db->escape_string($userdata['username']);
  $trackertype = $mybb->settings['activitytracker_bl_tracker'];
  $excluded = activitytracker_get_excluded_string();

  if ($fidlist != "") {
    if ($trackertype == 'kein') {
      //kein tracker, es gibt also kein teilnehmer feld, wir schauen also ob es irgendeinen post von dem user gibt
      if ($db->num_rows($db->simple_select("posts", "*", "uid = '{$uid}' AND fid in ({$fidlist}) {$excluded}")) > 0) {
        return true;
      }
    } elseif ($trackertype == 'risu') {
      //dann gibt es einen eintrag mit der uid in der szenentracker tabelle
      if (!$db->table_exists('scenetracker')) {
        if ($db->num_rows($db->simple_select("scenetracker", "*", "uid = '{$uid}'")) > 0) {
          return true;
        }
      }
    } elseif ($trackertype == 'sparks2') {
      if (!$db->field_exists("partners", "threads")) {
        if ($db->num_rows($db->simple_select("threads", "partners", "partners like '{$username}'")) > 0) {
          return true;
        }
      }
    } elseif ($trackertype == 'sparks3') {
      //sichergehen dass der tracker installiert ist
      if ($db->table_exists("ipt_scenes_partners")) {
        if ($db->num_rows($teilnehmer_query = $db->write_query("SELECT * FROM ipt_scenes_partners WHERE uid='{$uid}")) > 0) {
          return true;
        }
      }
    } elseif ($trackertype == 'ales') {
      //sichergehen dass der tracker installiert ist
      if ($db->field_exists("charas", "threads")) {
        if ($db->num_rows($db->simple_select("threads", "charas", "charas like '{$username}'")) > 0) {
          return true;
        }
      }
    } elseif ($trackertype == 'laras') {
      //sichergehen dass der tracker installiert ist
      if ($db->table_exists("inplayscenes")) {
        if ($db->num_rows($db->simple_select("inplayscenes", "partners_username", "partners_username='{$username}'")) > 0) {
          return true;
        }
      }
    }
  }
  return false;
}


/**
 * Testet ob ein Datum in einem gewissen Zeitraum liegt (z.b. ein geschriebener post)
 * @param int - timestamp 
 * @param string - timeperiod month, week, X days
 * @param DateTime - checkdate, wenn nicht angegeben dann heute
 * @param int - days, nur wenn timeperiod 'days' ist, sonst 0
 * @return boolean - wahr wenn in zeitraum
 * */
function activitytracker_post_in_timeperiod($timestamp, $timeperiod, ?DateTime $checkdate = null, int $days = 0)
{
  global $mybb;
  //datum des posts umwandeln wenn nötig
  //test if instance of DateTime or int
  if ($timestamp instanceof DateTime) {
    $postdate = $timestamp;
  } else {
    $postdate = (new DateTime())->setTimestamp($timestamp);
  }

  // ist ein datum übergeben dann das, sonst heute
  $checkdate = $checkdate ?? new DateTime();

  if ($timeperiod === 'month') {
    // Start: Erster Tag des letzten Monats
    if ($mybb->settings['activitytracker_bl_turnus_extrainfo_month'] == 0) {
      $start = (clone $checkdate)->modify('first day of last month')->setTime(0, 0, 0);
      $end = (clone $checkdate)->setTime(23, 59, 59); // Heute als Endpunkt
    } else {
      // Start: Erster Tag diesen Monats
      $start = (clone $checkdate)->modify('first day of this month')->setTime(0, 0, 0);
      $end = (clone $checkdate)->setTime(23, 59, 59); // Heute als Endpunkt
    }
  } elseif ($timeperiod === 'week') {
    // Start: Montag letzter Woche
    $start = (clone $checkdate)->modify('monday last week')->setTime(0, 0, 0);
    // Ende: Heute (Blacklist-Zeitpunkt)
    $end = (clone $checkdate)->setTime(23, 59, 59);
  } elseif ($timeperiod === 'days') {
    if ($days == 0) {
      $days = $mybb->settings['activitytracker_bl_duration_days'];
    } else {
      $days = $days;
    }
    $start = (clone $checkdate)->modify("-$days days")->setTime(0, 0, 0);
    $end = (clone $checkdate)->setTime(23, 59, 59);
  } else {
    // Ungültiger Zeitraum
    return false;
  }

  // Überprüfen, ob der Timestamp im Zeitraum liegt
  $check = $postdate >= $start && $postdate <= $end;

  if ($check === 1) {
    $check = true;
  }
  return $check;
}

/**
 * checkt ob der Name auf der Blacklist durchgestrichen werden muss
 * @param int uid
 * @return array mit Infos
 */
function activitytracker_check_strokeinfo($uid, ?DateTime $checkdate = null)
{
  $checkdate = $checkdate ?? new DateTime('now');
  $blacklistdate = (new DateTime())->format('Y-m');
  global $db, $lang, $mybb;
  $lang->load('activitytracker');
  $check_array = array();
  $check_array['class'] = "";
  $check_array['info'] = "";
  $ingame_fid = $mybb->settings['activitytracker_ingame'];

  //Hat der User sich gestrichen? 
  $query = $db->simple_select("at_blacklist_strokes", "*", "uid='{$uid}' and strokedate LIKE '{$blacklistdate}%'");
  if ($db->num_rows($query) > 0) {
    $check_array['class'] = "bl-strokeuser";
    $check_array['info'] = "<div>{$lang->blacklist_stroked}</div>";
  }
  //hat der User einen Steckbriefgepostet

  if (activitytracker_check_application($uid, $checkdate)) {
    $check_array['class'] = "bl-strokeuser";
    $check_array['info'] = "<div>{$lang->blacklist_application_in}</div>";
  }
  //hat der user gepostet
  $ingame_lastpost = activitytracker_get_lastpost($uid, $ingame_fid);
  //checken ob er im Zeitraum ist
  $timeperiod = trim($mybb->settings['activitytracker_bl_duration']);
  $post_isintime = activitytracker_post_in_timeperiod($ingame_lastpost['dateline'], $timeperiod, $checkdate);
  if ($post_isintime) {
    $check_array['class'] = "bl-strokeuser";
    $threadlink = "<a href=\"" . get_thread_link($ingame_lastpost['tid']) . "\">{$ingame_lastpost['subject']}</a>";
    $lang->blacklist_posted = $lang->sprintf($lang->blacklist_posted, $threadlink);
    $check_array['info'] = "<div>{$lang->blacklist_posted}</div>";
  }

  return $check_array;
}

// #####################################
// ### LARAS BIG MAGIC - RPG STUFF MODUL - THE FUNCTIONS ###
// #####################################

$plugins->add_hook("admin_rpgstuff_action_handler", "activitytracker_admin_rpgstuff_action_handler");
function activitytracker_admin_rpgstuff_action_handler(&$actions)
{
  $actions['activitytracker_manage'] = array('active' => 'activitytracker_manage', 'file' => 'activitytracker_manage');
  $actions['activitytracker_updates'] = array('active' => 'activitytracker_updates', 'file' => 'activitytracker_updates');
}

// Benutzergruppen-Berechtigungen im ACP
$plugins->add_hook("admin_rpgstuff_permissions", "activitytracker_admin_rpgstuff_permissions");
function activitytracker_admin_rpgstuff_permissions(&$admin_permissions)
{
  global $lang;
  // $lang->load('activitytracker');
  $admin_permissions['activitytracker'] = "Darf im ACP auf den Aktivitätsmanager zugreifen.";

  return $admin_permissions;
}

// im Menü einfügen
$plugins->add_hook("admin_rpgstuff_menu", "activitytracker_admin_rpgstuff_menu");
function activitytracker_admin_rpgstuff_menu(&$sub_menu)
{
  global $lang;
  // $lang->load('activitytracker');

  $sub_menu[] = [
    "id" => "activitytracker",
    "title" => "Verwaltung Activitytracker",
    "link" => "index.php?module=rpgstuff-activitytracker_transfer"
  ];
}

$plugins->add_hook("admin_load", "activitytracker_admin_manage");
function activitytracker_admin_manage()
{
  global $mybb, $db, $lang, $page, $run_module, $action_file, $cache;
  //verwaltung Blacklist
  // $page->add_breadcrumb_item("activitytracker", "index.php?module=rpgstuff-activitytracker_manage");
}
$plugins->add_hook('admin_rpgstuff_update_plugin', "activitytracker_admin_update_plugin");
// activitytracker_admin_update_plugin

function activitytracker_admin_update_plugin(&$table)
{
  global $db, $mybb, $lang;

  $lang->load('rpgstuff_plugin_updates');

  // UPDATE KRAM
  // Update durchführen
  if ($mybb->input['action'] == 'add_update' and $mybb->get_input('plugin') == "activitytracker") {

    //Settings updaten
    activitytracker_add_settings("update");
    rebuild_settings();

    //templates hinzufügen
    activitytracker_add_templates("update");

    //templates bearbeiten wenn nötig
    // activitytracker_replace_templates();

    //Datenbank updaten
    activitytracker_add_db("update");

    //Stylesheet hinzufügen wenn nötig:
    //array mit updates bekommen.
    $update_data_all = activitytracker_stylesheet_update();
    //alle Themes bekommen
    $theme_query = $db->simple_select('themes', 'tid, name');
    require_once MYBB_ADMIN_DIR . "inc/functions_themes.php";

    while ($theme = $db->fetch_array($theme_query)) {
      //wenn im style nicht vorhanden, dann gesamtes css hinzufügen
      $templatequery = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "themestylesheets` where tid = '{$theme['tid']}' and name ='activitytracker.css'");

      if ($db->num_rows($templatequery) == 0) {
        $css = activitytracker_stylesheet($theme['tid']);

        $sid = $db->insert_query("themestylesheets", $css);
        $db->update_query("themestylesheets", array("cachefile" => "activitytracker.css"), "sid = '" . $sid . "'", 1);
        update_theme_stylesheet_list($theme['tid']);
      }

      //testen ob updatestring vorhanden - sonst an css in theme hinzufügen
      $update_data_all = activitytracker_stylesheet_update();
      //array durchgehen mit eventuell hinzuzufügenden strings
      foreach ($update_data_all as $update_data) {
        //hinzuzufügegendes css
        $update_stylesheet = $update_data['stylesheet'];
        //String bei dem getestet wird ob er im alten css vorhanden ist
        $update_string = $update_data['update_string'];
        //updatestring darf nicht leer sein
        if (!empty($update_string)) {
          //checken ob updatestring in css vorhanden ist - dann muss nichts getan werden
          $test_ifin = $db->write_query("SELECT stylesheet FROM " . TABLE_PREFIX . "themestylesheets WHERE tid = '{$theme['tid']}' AND name = 'activitytracker.css' AND stylesheet LIKE '%" . $update_string . "%' ");
          //string war nicht vorhanden
          if ($db->num_rows($test_ifin) == 0) {
            //altes css holen
            $oldstylesheet = $db->fetch_field($db->write_query("SELECT stylesheet FROM " . TABLE_PREFIX . "themestylesheets WHERE tid = '{$theme['tid']}' AND name = 'activitytracker.css'"), "stylesheet");
            //Hier basteln wir unser neues array zum update und hängen das neue css hinten an das alte dran
            $updated_stylesheet = array(
              "cachefile" => $db->escape_string('activitytracker.css'),
              "stylesheet" => $db->escape_string($oldstylesheet . "\n\n" . $update_stylesheet),
              "lastmodified" => TIME_NOW
            );
            $db->update_query("themestylesheets", $updated_stylesheet, "name='activitytracker.css' AND tid = '{$theme['tid']}'");
            // echo "In Theme mit der ID {$theme['tid']} wurde CSS hinzugefügt -  $update_string <br>";
          }
        }
        update_theme_stylesheet_list($theme['tid']);
      }
    }
  }

  // Zelle mit dem Namen des Themes
  $table->construct_cell("<b>" . htmlspecialchars_uni("Activitytracker") . "</b>", array('width' => '70%'));

  // Überprüfen, ob Update nötig ist 
  $update_check = activitytracker_is_updated();
  if ($update_check) {
    $table->construct_cell($lang->plugins_actual, array('class' => 'align_center'));
  } else {
    $table->construct_cell("<a href=\"index.php?module=rpgstuff-plugin_updates&action=add_update&plugin=activitytracker\">" . $lang->plugins_update . "</a>", array('class' => 'align_center'));
  }

  $table->construct_row();
}

/**
 * fügt Stylesheet dem bestehenden CSS hinzu wenn nötig
 */
function activitytracker_stylesheet_update()
{
  // Update-Stylesheet
  // wird an bestehende Stylesheets immer ganz am ende hinzugefügt
  // array initialisieren
  $update_array_all = array();
  // $update_array_all[] = array(
  //   'stylesheet' => "
  //     /* update-userfilter - kommentar nicht entfernen */
  //       .scenefilteroptions__items.button {
  //           text-align: center;
  //           width: 100%;
  //       }
  //   ",
  //   'update_string' => 'update-userfilter'
  // );

  return $update_array_all;
}

/**
 * Funktion die das aktuelle CSS enhält
 * @return array css array
 */
function activitytracker_stylesheet($themeid = 1)
{
  global $db;
  $css = array(
    'name' => 'activitytracker.css',
    'tid' => $themeid,
    'attachedto' => '',
    "stylesheet" =>    '

    ',
    'cachefile' => $db->escape_string(str_replace('/', '', 'activitytracker.css')),
    'lastmodified' => time()
  );
  return $css;
}
/**
 * Funktion um alte Templates des Plugins bei Bedarf zu aktualisieren
 */
function activitytracker_replace_templates()
{
  global $db;
  //Wir wollen erst einmal die templates, die eventuellverändert werden müssen
  $update_template_all = activitytracker_updated_templates();
  if (!empty($update_template_all)) {
    //diese durchgehen
    foreach ($update_template_all as $update_template) {
      //anhand des templatenames holen
      $old_template_query = $db->simple_select("templates", "tid, template", "title = '" . $update_template['templatename'] . "'");
      //in old template speichern
      while ($old_template = $db->fetch_array($old_template_query)) {
        //was soll gefunden werden? das mit pattern ersetzen (wir schmeißen leertasten, tabs, etc raus)

        if ($update_template['action'] == 'replace') {
          $pattern = activitytracker_createRegexPattern($update_template['change_string']);
        } elseif ($update_template['action'] == 'add') {
          //bei add wird etwas zum template hinzugefügt, wir müssen also testen ob das schon geschehen ist
          $pattern = activitytracker_createRegexPattern($update_template['action_string']);
        } elseif ($update_template['action'] == 'overwrite') {
          $pattern = activitytracker_createRegexPattern($update_template['change_string']);
        }

        //was soll gemacht werden -> momentan nur replace 
        if ($update_template['action'] == 'replace') {
          //wir ersetzen wenn gefunden wird
          if (preg_match($pattern, $old_template['template'])) {
            $template = preg_replace($pattern, $update_template['action_string'], $old_template['template']);
            $update_query = array(
              "template" => $db->escape_string($template),
              "dateline" => TIME_NOW
            );
            $db->update_query("templates", $update_query, "tid='" . $old_template['tid'] . "'");
            // echo ("Template -replace- {$update_template['templatename']} in {$old_template['tid']} wurde aktualisiert <br>");
          }
        }
        if ($update_template['action'] == 'add') { //hinzufügen nicht ersetzen
          //ist es schon einmal hinzugefügt wurden? nur ausführen, wenn es noch nicht im template gefunden wird
          if (!preg_match($pattern, $old_template['template'])) {
            $pattern_rep = activitytracker_createRegexPattern($update_template['change_string']);
            $template = preg_replace($pattern_rep, $update_template['action_string'], $old_template['template']);
            $update_query = array(
              "template" => $db->escape_string($template),
              "dateline" => TIME_NOW
            );
            $db->update_query("templates", $update_query, "tid='" . $old_template['tid'] . "'");
            // echo ("Template -add- {$update_template['templatename']} in  {$old_template['tid']} wurde aktualisiert <br>");
          }
        }
        if ($update_template['action'] == 'overwrite') { //komplett ersetzen
          //checken ob das bei change string angegebene vorhanden ist - wenn ja wurde das template schon überschrieben, wenn nicht überschreiben wir das ganze template
          if (!preg_match($pattern, $old_template['template'])) {
            $template = $update_template['action_string'];
            $update_query = array(
              "template" => $db->escape_string($template),
              "dateline" => TIME_NOW
            );
            $db->update_query("templates", $update_query, "tid='" . $old_template['tid'] . "'");
            // echo ("Template -overwrite- {$update_template['templatename']} in  {$old_template['tid']} wurde aktualisiert <br>");
          }
        }
      }
    }
  }
}

/**
 * Hier werden Templates gespeichert, die im Laufe der Entwicklung aktualisiert wurden
 * @return array - template daten die geupdatet werden müssen
 * templatename: name des templates mit dem was passieren soll
 * change_string: nach welchem string soll im alten template gesucht werden
 * action: Was soll passieren - add: fügt hinzu, replace ersetzt (change)string, overwrite ersetzt gesamtes template
 * action_strin: Der string der eingefügt/mit dem ersetzt/mit dem überschrieben werden soll
 */
function activitytracker_updated_templates()
{
  global $db;

  //data array initialisieren 
  $update_template = array();

  // $update_template[] = array(
  //   "templatename" => 'activitytracker_ucp_main',
  //   "change_string" => '<form action="usercp.php?action=activitytracker" method="post">
  //     <div class="scene_ucp scenefilteroptions">
  //       <h2>Filteroptions</h2>
  //       <div class="scenefilteroptions__items">
  //         <label for="charakter">Szenen anzeigen von: </label>{$selectchara}
  //         <input type="hidden" value="{$thisuser}" name="uid" id="uid"/>
  //       </div>	
  //       <div class="scenefilteroptions__items">
  //         <label for="status">Status der Szene:  </label>
  //         <select name="status" id="status">
  //           <option value="both" {$sel_s[\'both\']}>beides</option>
  //             <option value="open" {$sel_s[\'open\']} >offen</option>
  //           <option value="closed" {$sel_s[\'closed\']}>geschlossen</option>
  //         </select>
  //       </div>
  //       <div class="scenefilteroptions__items">
  //           <label for="move">Du bist dran: </label>
  //           <select name="move" id="move">
  //           <option value="beides" {$sel_m[\'beides\']}>beides</option>
  //             <option value="ja" {$sel_m[\'ja\']}>ja</option>
  //           <option value="nein" {$sel_m[\'nein\']}>nein</option>

  //         </select>
  //       </div>
  //       <div class="scenefilteroptions__items button">
  //         <input type="submit" name="scenefilter" value="Szenen filtern" id="scenefilter" />
  //       </div>
  //     </div>
  //       </form>',
  //   "action" => 'replace',
  //   "action_string" => '{$activitytracker_ucp_filterscenes}'
  // );


  return $update_template;
}

/**
 * Update Check
 * @return boolean false wenn Plugin nicht aktuell ist
 * überprüft ob das Plugin auf der aktuellen Version ist
 */
function activitytracker_is_updated()
{
  global $db, $mybb;
  $needupdate = 0;
  // if (!$db->field_exists("activitytracker_date", "threads")) {
  //   echo ("In der Threadtabelle muss das Feld activitytracker_date  hinzugefügt werden <br>");
  //   return false;
  // }
  //Testen ob im CSS etwas fehlt
  $update_data_all = activitytracker_stylesheet_update();
  //alle Themes bekommen
  $theme_query = $db->simple_select('themes', 'tid, name');
  while ($theme = $db->fetch_array($theme_query)) {
    //wenn im style nicht vorhanden, dann gesamtes css hinzufügen
    $templatequery = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "themestylesheets` where tid = '{$theme['tid']}' and name ='activitytracker.css'");
    //activitytracker.css ist in keinem style nicht vorhanden
    if ($db->num_rows($templatequery) == 0) {
      echo ("Nicht im Theme mit der Id {$theme['tid']} vorhanden <br>");
      $needupdate = 1;
    } else {
      //activitytracker.css ist in einem style nicht vorhanden
      //css ist vorhanden, testen ob alle updatestrings vorhanden sind
      $update_data_all = activitytracker_stylesheet_update();
      //array durchgehen mit eventuell hinzuzufügenden strings
      foreach ($update_data_all as $update_data) {
        //String bei dem getestet wird ob er im alten css vorhanden ist
        $update_string = $update_data['update_string'];
        //updatestring darf nicht leer sein
        if (!empty($update_string)) {
          //checken ob updatestring in css vorhanden ist - dann muss nichts getan werden
          $test_ifin = $db->write_query("SELECT stylesheet FROM " . TABLE_PREFIX . "themestylesheets WHERE tid = '{$theme['tid']}' AND name = 'activitytracker.css' AND stylesheet LIKE '%" . $update_string . "%' ");
          //string war nicht vorhanden
          if ($db->num_rows($test_ifin) == 0) {
            echo ("Theme mit der id '{$theme['tid']}' muss aktualisiert werden <br>");
            $needupdate = 1;
          }
        }
      }
    }
  }

  //Testen ob eins der Templates aktualisiert werden muss
  //Wir wollen erst einmal die templates, die eventuellverändert werden müssen
  $update_template_all = activitytracker_updated_templates();
  //alle themes durchgehen
  foreach ($update_template_all as $update_template) {
    //entsprechendes Tamplate holen
    $old_template_query = $db->simple_select("templates", "tid, template, sid", "title = '" . $update_template['templatename'] . "'");
    while ($old_template = $db->fetch_array($old_template_query)) {
      //pattern bilden
      if ($update_template['action'] == 'replace') {
        $pattern = activitytracker_createRegexPattern($update_template['change_string']);
        $check = preg_match($pattern, $old_template['template']);
      } elseif ($update_template['action'] == 'add') {
        //bei add wird etwas zum template hinzugefügt, wir müssen also testen ob das schon geschehen ist
        $pattern = activitytracker_createRegexPattern($update_template['action_string']);
        $check = !preg_match($pattern, $old_template['template']);
      } elseif ($update_template['action'] == 'overwrite') {
        //checken ob das bei change string angegebene vorhanden ist - wenn ja wurde das template schon überschrieben
        $pattern = activitytracker_createRegexPattern($update_template['change_string']);
        $check = !preg_match($pattern, $old_template['template']);
      }
      //testen ob der zu ersetzende string vorhanden ist
      //wenn ja muss das template aktualisiert werden.
      if ($check) {
        $templateset = $db->fetch_field($db->simple_select("templatesets", "title", "sid = '{$old_template['sid']}'"), "title");
        echo ("Template {$update_template['templatename']} im Set {$templateset}'(SID: {$old_template['sid']}') muss aktualisiert werden.");
        $needupdate = 1;
      }
    }
  }
  $setting_array = activitytracker_settingarray();
  $gid = $db->fetch_field($db->simple_select("settinggroups", "gid", "name = 'activitytracker'"), "gid");

  foreach ($setting_array as $name => $setting) {
    $setting['name'] = $name;
    $setting['gid'] = $gid;
    $check2 = $db->write_query("SELECT * FROM `" . TABLE_PREFIX . "settings` WHERE name = '{$name}'");
    if ($db->num_rows($check2) > 0) {
      while ($setting_old = $db->fetch_array($check2)) {
        if (
          $setting_old['title'] != $setting['title'] ||
          stripslashes($setting_old['description']) != stripslashes($setting['description']) ||
          $setting_old['optionscode'] != $setting['optionscode'] ||
          $setting_old['disporder'] != $setting['disporder']
        ) {
          echo "Setting: {$name} muss aktualisiert werden.<br>";
          $needupdate = 1;
        }
      }
    } else {
      echo "Setting: {$name} muss hinzugefügt werden.<br>";
      $needupdate = 1;
    }
  }

  if ($needupdate == 1) {
    return false;
  }
  return true;
}

/**
 * Funktion um ein pattern für preg_replace zu erstellen
 * und so templates zu vergleichen.
 * @return string - pattern für preg_replace zum vergleich
 */
function activitytracker_createRegexPattern($html)
{
  // Entkomme alle Sonderzeichen und ersetze Leerzeichen mit flexiblen Platzhaltern
  $pattern = preg_quote($html, '/');

  // Ersetze Leerzeichen in `class`-Attributen mit `\s+` (flexible Leerzeichen)
  $pattern = preg_replace('/\s+/', '\\s+', $pattern);

  // Passe das Muster an, um Anfang und Ende zu markieren
  return '/' . $pattern . '/si';
}
/**
 * Reihenfolge beachten, je nach Tracker
 * @param int threadid
 * @param int uid
 * @return boolean  true: wenn dran, false wenn nicht dran
 */

//test function
$plugins->add_hook("misc_start", "activitytracker_test");
function activitytracker_test()
{
  global $mybb, $db;
  // echo "hallo";
  if (!($mybb->get_input('action') == "activitytracker_test")) {
    return;
  }
  // echo "<h1>in test funktion</h1>";

  $testarray = array(169);
  // activitytracker_check_blacklist($testarray);
  // $getchilds = activitytracker_get_fids_string(14);
  // echo "childlist" . $getchilds;
  // echo "ich bin ein test";
  $array = array();
  // $array = activitytracker_get_users();
  // var_dump(get_user_by_username('Zoey '));

  // activitytracker_get_lastpost(3, "14,20");
  // activitytracker_add_settings('update');
  // var_dump($array);
  // var_dump(activitytracker_check_blacklist($testarray));

  //   $ingamefids_str =   get_child_list(4);
  // $archivefidss_str =  get_child_list(29);
  // $excludedfids = str_replace(" ", "", "135 ,133 , 1999,19, 35,,");

  // $test = array_merge($ingamefids_str,$archivefidss_str);
  // var_dump($test);
  // $new = explode(",", $excludedfids);
  // echo "<br><br>";
  // var_dump(array_filter($new));
  // echo "<br><br>";
  // $result = array_diff($test, $new);
  $applicantgroup = $mybb->settings['activitytracker_applicationgroup'];
  //ausgeschlossene User
  // echo "applicantgroup - $applicantgroup";
  // var_dump(is_member($applicantgroup, 3));
  // if (is_member($applicantgroup, 213)) {
  //   echo "yes";
  // } else {
  //   echo "no";
  // }
  // include "./inc/tasks/at_blacklist.php";

  // task_at_blacklist($array);
  // $blarray = activitytracker_check_blacklist();
  // var_dump($blarray); 
}
