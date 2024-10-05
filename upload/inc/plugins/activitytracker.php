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
    "description"  => "Aktivitätstracker für RPGS - Blacklist / Characterstatus / Eisliste / Whitelist in Kombination oder einzelnd.",
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

  activitytracker_add_db();
  activitytracker_add_settings("install");
  activitytracker_add_templates();
  /**
   * Tasks Anlegen
   */
  //Task, der per Default einmal im Monat ausgeführt wird.
  //TODO : Ändern wenn anders im ACP eingestellt wird 
  $db->insert_query('tasks', array(
    'title' => 'blacklist',
    'description' => 'Stellt die Blacklist zusammen.',
    'file' => 'activitytracker_blacklist',
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

  $db->insert_query('tasks', array(
    'title' => 'Blacklist Autooff',
    'description' => 'Deaktiviert/Versteckt die Blacklist wieder.',
    'file' => 'activitytracker_blacklist_autooff',
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

  $db->insert_query('tasks', array(
    'title' => 'Blacklist Benachrichtigung',
    'description' => 'Benachrichtigt User per Mail oder PN wenn sie auf der Blacklist stehen',
    'file' => 'activitytracker_blacklist_alert',
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


  $db->insert_query('tasks', array(
    'title' => 'Whitelist',
    'description' => 'Stellt die Whitelist zusammen.',
    'file' => 'activitytracker_whitelist',
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

  $db->insert_query('tasks', array(
    'title' => 'Whitelist Autooff',
    'description' => 'Deaktiviert/Versteckt die Whitelist wieder.',
    'file' => 'activitytracker_whitelist_autooff',
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

  $db->insert_query('tasks', array(
    'title' => 'Eisliste',
    'description' => 'Überprüft die Eisliste und trägt Charaktere eventuell aus.',
    'file' => 'activitytracker_icelist',
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
  $cache->update_tasks();
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
  if ($db->table_exists("at_whitelist")) {
    $db->drop_table("at_whitelist");
  }
  if ($db->table_exists("at_icelist")) {
    $db->drop_table("at_icelist");
  }
  if ($db->table_exists("at_scenereminder")) {
    $db->drop_table("at_scenereminder");
  }
  //templates entfernen
  $db->delete_query("templates", "title LIKE 'activitytracker_%'");
  // Einstellungen entfernen
  $db->delete_query('settinggroups', "name = 'activitytracker'");
  $db->delete_query('settings', "name like 'activitytracker%'");

  rebuild_settings();

  // Tasks löschen
  $db->delete_query("tasks", "file='activitytracker%'");
  $cache->update_tasks();

  if ($db->field_exists("activitytracker_bl_view", "users")) {
    $db->query("ALTER TABLE " . TABLE_PREFIX . "users DROP activitytracker_bl_view_warning");
  }
  if ($db->field_exists("activitytracker_bl_view", "users")) {
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
  find_replace_templatesets("usercp", "#" . preg_quote('{$latest_subscribed}') . "#i", '{$blacklist_ucp}{$latest_subscribed}');
  find_replace_templatesets("usercp_profile", "#" . preg_quote('{$contactfields}') . "#i", '{$contactfields}{$blacklist_ucp_edit}');

  //enable task
  $db->update_query('tasks', array('enabled' => 1), "file = 'blacklist'");
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
  $db->update_query('tasks', array('enabled' => 0), "file = 'blacklist'");
}

//ADMIN CP STUFF
$plugins->add_hook("admin_config_settings_change", "activitytracker_settings_change");
// Set peeker in ACP
function activitytracker_settings_change()
{
  global $db, $mybb, $activitytracker_settings_peeker;

  $result = $db->simple_select("settinggroups", "gid", "name='activitytracker'", array("limit" => 1));
  $group = $db->fetch_array($result);
  $activitytracker_settings_peeker = ($mybb->input['gid'] == $group['gid']) && ($mybb->request_method != 'post');
}

$plugins->add_hook("admin_settings_print_peekers", "activitytracker_settings_peek");
// Add peeker in ACP
function activitytracker_settings_peek(&$peekers)
{
  global $activitytracker_settings_peeker;

  if ($activitytracker_settings_peeker) {
    //Weitere Einstellungen im ACP anzeigen, je nach auswahl
    $peekers[] = 'new Peeker($(".activitytracker_bl_ingamestart"), $("#activitytracker_bl_ingamestart_days"),/1/,true)';
    $peekers[] = 'new Peeker($(".activitytracker_bl_noaway"), $("#activitytracker_bl_noaway_days"),/1/,true)';

    $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_iceduration"),/1/,true)';
    $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_icelock"),/1/,true)';
    $peekers[] = 'new Peeker($(".activitytracker_bl_icelock"), $("#activitytracker_bl_icelock_days"),/1/,true)';
    $peekers[] = 'new Peeker($(".activitytracker_bl_ice"), $("#activitytracker_bl_icenumber"),/1/,true)';
  }
}

function activitytracker_add_db()
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
    `strokes` int(1) NOT NULL,
    `strokedate_last` datetime NOT NULL,
    `bldate` datetime NOT NULL,
    `away` int(1) NOT NULL DEFAULT 0,
    `awaydate_last` datetime NOT NULL,
    PRIMARY KEY (`blid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }
  //Tabelle erstellen - für Whitelist
  if (!$db->table_exists("at_whitelist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_whitelist` (
      `wlid` int(10) NOT NULL AUTO_INCREMENT,
      `uid` int(10) NOT NULL,
      `username` varchar(50) NOT NULL DEFAULT '',
      `reported_back` int(0) NOT NULL DEFAULT 0,
      PRIMARY KEY (`wlid`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }
  //Tabelle erstellen - für Eislise
  if (!$db->table_exists("at_icelist")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_icelist` (
    `ilid` int(10) NOT NULL AUTO_INCREMENT,
    `uid` int(10) NOT NULL,
    `icedate` datetime,
    PRIMARY KEY (`ilid`)
    ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
  }

  //Tabelle erstellen - für Reminder
  if (!$db->table_exists("at_scenereminder")) {
    $db->write_query("CREATE TABLE `" . TABLE_PREFIX . "at_scenereminder` (
      `srid` int(10) NOT NULL AUTO_INCREMENT,
      `uid` int(10) NOT NULL,
      `tid` datetime,
      `ignore` int(10),
      PRIMARY KEY (`srid`)
      ) ENGINE=MyISAM CHARACTER SET utf8 COLLATE utf8_general_ci;");
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
    //An welchem Tag soll die Blacklist erscheinen?
    'activitytracker_bl_turnus_day' => array(
      'title' => 'Blacklist - Erscheinen Tag',
      'description' => 'An welchem Tag soll der Turnus für regelmäßige automatische Ausführung starten. Z.b. 1. (Monatlich: immer, am 1. Weekly 1 + 7 etc.) ',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 4
    ),
    //Wie oft soll die BL erscheinen?
    'activitytracker_bl_turnus' => array(
      'title' => 'Blacklist - Erscheinen Turnus',
      'description' => 'Wie oft soll die Blacklist erscheinen? Bei Manuell, muss der Task per Hand ausgeführt und die Blacklist anschließend aktiviert werden.',
      'optionscode' => 'radio
          weekly=wöchentlich
          2weekly=alle 2 Wochen
          monthly=monatlich
          particular=bestimmte Monate
          manuel=manuell',
      'value' => 'monthly', // Default
      'disporder' => 5
    ),
    //Wie oft soll die BL erscheinen?
    'activitytracker_bl_turnus_particular' => array(
      'title' => 'Blacklist - Bestimmte Monate',
      'description' => 'An welchen Monaten soll die Blacklist erscheinen',
      'optionscode' => 'select
              1=Januar
              2=Februar
              3=März
              4=April
              5=Mai
              5=Mai
              6=Juni
              7=Juli
              8=August
              9=September
              10=Oktober
              11=November
              12=Dezember',
      'value' => '1', // Default
      'disporder' => 5
    ),
    //Wie oft soll die BL erscheinen
    'activitytracker_bl_deadline' => array(
      'title' => 'Blacklist - Frist',
      'description' => 'Wie lange soll die Frist der Blacklist sein (Tage)? Für eine Woche z.B. 7 eintragen',
      'optionscode' => 'numeric',
      'value' => '7', // Default
      'disporder' => 6
    ),
    //Automatische Deaktiverung
    'activitytracker_bl_autooff' => array(
      'title' => 'Blacklist - Deaktivierung',
      'description' => 'Soll die Blacklist automatisch nach der deadline wieder deaktiviert/unsichtbar gemacht werden?',
      'optionscode' => 'yesno',
      'value' => 'yes', // Default
      'disporder' => 7
    ),
    //Zeitraum in Tagen - angenommene Charas
    'activitytracker_bl_duration' => array(
      'title' => 'Blacklist - Zeitraum?',
      'description' => 'Wie lang ist der Zeitraum, in der der Charakter in einer Szene seit dem letzten Post gepostet haben muss?',
      'optionscode' => 'numeric',
      'value' => '92', // Default
      'disporder' => 8
    ),
    //Typ der Berücksichtigung
    'activitytracker_bl_type_user' => array(
      'title' => 'Blacklist - Spieler oder Charakter?',
      'description' => 'Soll die Blacklist nach Spieler oder Charakter unterschieden werden?',
      'optionscode' => 'radio
          player=Spieler
          character=Character',
      'value' => 'character', // Default
      'disporder' => 9
    ),
    //Typ der Berücksichtigung
    'activitytracker_bl_type' => array(
      'title' => 'Blacklist - Berücksichtigungsart',
      'description' => 'Soll nur der allgemein letzte Post im Ingame des Charakters gezählt werden, oder gilt der erlaubte Zeitraum pro Szene?',
      'optionscode' => 'radio
      scene=pro Szene
      lastingame= letzter Post Ingame',
      'value' => 'scene', // Default
      'disporder' => 10
    ),
    //Keine Aktuelle Ingameszene
    'activitytracker_bl_noscenes' => array(
      'title' => 'Blacklist - Keine aktuelle Ingameszene',
      'description' => 'Soll es gesondert behandelt werden, wenn der Charakter gerade keine aktuelle Ingame Szene hat? Wenn Nein, landet der Charakter entsprechend nicht auf der BL.',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 11
    ),
    //Keine aktuelle Ingameszene Zeitraum
    'activitytracker_bl_noscenes_days' => array(
      'title' => 'Blacklist - Keine aktuelle Ingameszene - Zeitraum',
      'description' => 'Wieviele Tage hat ein Charakter Zeit eine neue Szene im Ingamezeitraum zu öffnen bevor er auf der Blacklist landet?',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 12
    ),
    //Zeitraum für Steckbriefe
    'activitytracker_bl_applicationduration' => array(
      'title' => 'Blacklist - Zeitraum Steckbriefe?',
      'description' => 'Wieviel Zeit(Tage) haben Bewerber einen Steckbrief zu posten?',
      'optionscode' => 'numeric',
      'value' => '21', // Default
      'disporder' => 13
    ),
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_bewerberfid' => array(
      'title' => 'Blacklist - Bewerbungsarea',
      'description' => 'In welches Forum posten eure Bewerber die Steckbriefe?',
      'optionscode' => 'forumselect',
      'value' => '16', // Default
      'disporder' => 14
    ),
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_ingamestart' => array(
      'title' => 'Blacklist - Ingameeinstieg',
      'description' => 'Soll der Ingameeinstieg eine gesonderte Frist haben?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 15
    ),
    //TODO vergleich mit registrierungsdatum //wobdate risus //stecki in area
    //TODO wenn steck in area ausgewaählt fid
    //Die fid (forenid) der Bewerbungsarea
    'activitytracker_bl_ingamestart_days' => array(
      'title' => 'Blacklist - Ingameeinstieg - Zeitraum',
      'description' => 'Wieviele Tage soll ein frisch angenommener Charakter haben einzusteigen?',
      'optionscode' => 'numeric',
      'value' => '14', // Default
      'disporder' => 16
    ),
    //Wie soll das wob date zugeordnet werden
    'activitytracker_bl_wobdate' => array(
      'title' => 'Blacklist - Berechnung WoB Date.',
      'description' => 'Wie soll das Datum des WoBs ermittelt werden?',
      "optionscode" => "radio
      reg=Registrierungsdatum
      risu_reg=WoB Risuenas Steckbriefplugin
      thread=Erstellung eines Threads (z.B. Steckbriefarea)
      ales_wob=WoB Bewerberchecklist von Ales",
      'value' => 'reg', // Default
      'disporder' => 17
    ),
    //Wenn Thread -> Welche Area
    'activitytracker_bl_wobdate_thread' => array(
      'title' => 'Blacklist - Berechnung WoB Date.',
      'description' => 'Wenn du Threads ausgewählt hast, in welchem Forum werden diese gepostet. Elternforum reicht.',
      "optionscode" => "forumselect",
      'value' => '0', // Default
      'disporder' => 18
    ),
    //Abwesenheit ja oder nein?
    'activitytracker_bl_away' => array(
      'title' => 'Blacklist - Abwesenheit beachten?',
      'description' => 'Soll beachtet werden, dass ein User abwesend gemeldet ist und er dann nicht auf die BL kommen?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 19
    ),
    //3 Monatsregel?
    'activitytracker_bl_noaway' => array(
      'title' => 'Blacklist - Abwesenheit Sonderregel?',
      'description' => 'Gibt es einen Zeitraum, bei dem der Charakter auf die BL kommt, auch wenn er abwesend ist?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 20
    ),
    //3 Monatsregel - Zeitraum?
    'activitytracker_bl_noaway_days' => array(
      'title' => 'Blacklist - Sonderregel - Zeitraum?',
      'description' => 'Nach wievielen Tagen soll die Abwesenheit nicht mehr als Schutz gelten? 0 Wenn es keine Begrenzung gibt.',
      "optionscode" => "numeric",
      'value' => '91', // Default
      'disporder' => 21
    ),
    //Der Charakter würde am ausgewählten Tag auf der Blacklist stehen
    'activitytracker_bl_reminder' => array(
      'title' => 'Blacklist - Warnung',
      'description' => 'Bekommt der Charakter eine Warnung, wenn der Charakter auf der nächsten BL stehen würde.',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 22
    ),
    //Erinnerung Charakter aktuell auf der Blacklist
    'activitytracker_bl_reminder_isonbl' => array(
      'title' => 'Blacklist - Hinweis',
      'description' => 'Bekommt der Charakter einen Hinweis, dass er gerade auf aktuell veröffentlichten Blacklist steht?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 23
    ),
    //Blacklist Benachrichtigungsart
    'activitytracker_bl_alert' => array(
      'title' => 'Blacklist - Benachrichtigung',
      'description' => 'Sollen User Informiert werden, wenn sie auf der BL stehen?',
      "optionscode" => "radio
      auto=automatisch beim erstellen der BL
      button=per Button
      none=gar nicht",
      'value' => 'none', // Default
      'disporder' => 23
    ),
    'activitytracker_bl_alerttype' => array(
      'title' => 'Blacklist - Benachrichtigung',
      'description' => 'Wie sollen die User informiert werden?',
      "optionscode" => "radio
      mail=Per Mail
      pm=Per PN",
      'value' => 'mail', // Default
      'disporder' => 23
    ),
    //Streichen lassen
    'activitytracker_bl_reminder_stroke' => array(
      'title' => 'Blacklist - Streichen',
      'description' => 'Dürfen sich Charaktere Streichen?',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 24
    ),
    //Dürfen Charaktere sicht streichen
    'activitytracker_bl_reminder_stroke_count' => array(
      'title' => 'Blacklist - Streichen Anzahl',
      'description' => 'Wir oft dürfen User ihren Charakter streichen? 0 für Unbegrenzt.',
      "optionscode" => "numeric",
      'value' => '1', // Default
      'disporder' => 25
    ),
    //Welcher Szenentracker wird benutzt
    'activitytracker_bl_tracker' => array(
      'title' => 'Blacklist - Szenentracker',
      'description' => 'Welcher Szenentracker wird benutzt?',
      "optionscode" => "radio
      risu=Risuenas
      spark2=Sparkflys 2.0
      spark3=Sparkflys 3.0
      ales=Ales 2.0",
      'value' => '1', // Default
      'disporder' => 26
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
      'disporder' => 27
    ),
    //Dürfen Gäste die Whielist sehen?
    'activitytracker_wl_guest' => array(
      'title' => 'Whitelist - Gäste',
      'description' => 'Dürfen Gäste die Whitelist sehen?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 28
    ),
    //Ist die Whitelist gerade aktiv
    'activitytracker_wl_activ' => array(
      'title' => 'Whitelist - Veröffentlich und aktiv',
      'description' => 'Ist die Whitelist gerade einsehbar und aktiv?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 29
    ),
    //Whitelist Zeitraum Rückmeldung
    'activitytracker_wl_deadline' => array(
      'title' => 'Whitelist - Zeitraum',
      'description' => 'Wie viele Tage haben die Mitglieder Zeit sich zurückzumelden?',
      'optionscode' => 'numeric',
      'value' => '7', // Default
      'disporder' => 30
    ),
    //An welchem Tag soll die Whitelist erscheinen?
    'activitytracker_wl_turnus_day' => array(
      'title' => 'Whitelist Erscheinen - Tag',
      'description' => 'An welchem Tag soll der Turnus für regelmäßige automatische Ausführung starten. Z.b. 1. (Monatlich: immer, am 1. Weekly 1 + 7 etc.) ',
      'optionscode' => 'numeric',
      'value' => '1', // Default
      'disporder' => 31
    ),
    //Wie oft soll die WL erscheinen?
    'activitytracker_wl_turnus' => array(
      'title' => 'Whitelist Erscheinen - Turnus',
      'description' => 'Wie oft soll die Whitelist erscheinen? Bei Manuell, muss der Task per Hand ausgeführt werden.',
      'optionscode' => 'radio
              weekly=wöchentlich
              2weekly=alle 2 Wochen
              monthly=monatlich
              manuel=manuell',
      'value' => 'monthly', // Default
      'disporder' => 32
    ),
    //Automatische Deaktiverung
    'activitytracker_wl_autooff' => array(
      'title' => 'Whitelist - Deaktivierung',
      'description' => 'Soll die Whitelist automatisch nach der deadline wieder deaktiviert/unsichtbar gemacht werden?',
      'optionscode' => 'yesno',
      'value' => 'yes', // Default
      'disporder' => 33
    ),
    //Whitelist Regulierungen
    'activitytracker_wl_regulations' => array(
      'title' => 'Whitelist - Regulierungen',
      'description' => 'Gibt es Regulierungen ob man sich zurückmelden kann?',
      'optionscode' => 'yesno',
      'value' => '0', // Default
      'disporder' => 34
    ),
    //Whitelist Posts
    'activitytracker_wl_regulations_type' => array(
      'title' => 'Whitelist - Regulierungen',
      'description' => 'Gibt es Regulierungen wann man sich zurückmelden kann?',
      'optionscode' => 'radio',
      'value' => 'radio
      bl=gleiche Regeln wie bei der BL
      ownpost=ein Post in den letzten X Tagen // Default
      ownscene=keine Szenen die länger als X Tage warten',
      'disporder' => 35
    ),
    //Whitelist Posts
    'activitytracker_wl_regulations_days' => array(
      'title' => 'Whitelist - Regulierungen Tage',
      'description' => 'Wieviele Tage darf der letzte Post/Szene her sein?',
      'optionscode' => 'numeric',
      'value' => '30',
      'disporder' => 36
    ),
    //
    'activitytracker_wl_reminder' => array(
      'title' => 'Whitelist - Reminder',
      'description' => 'Soll auf der Index Seite eine Info angezeigt werden, wenn die Whitelist aktiv ist?',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 37
    ),
    /*Eisliste Einstellungen*/
    //Laras Eisliste
    'activitytracker_bl_iceplugin' => array(
      'title' => 'Icelist Plugin von little-evil-genius',
      'description' => 'Ist das Icelistplugin von little.evil.genius installiert und soll berücksichtig werden?
      Wenn dies genutzt werden soll, bitte den nächsten Punkt auf No stellen!',
      "optionscode" => "yesno",
      'value' => '0', // Default
      'disporder' => 38
    ),
    //Eisliste ja oder nein?
    'activitytracker_ice' => array(
      'title' => 'Eisliste',
      'description' => 'Können einzelne Charaktere auf Eis gelegt werden?
          Anzeigbar im Profil mit der Variable {$iceMeldung}',
      "optionscode" => "yesno",
      'value' => '1', // Default
      'disporder' => 39
    ),
    //Eisliste Zeitraum?
    'activitytracker_ice_duration' => array(
      'title' => 'Eisliste Zeitraum',
      'description' => 'Wie lange darf ein Charakter auf Eis gelegt sein?',
      "optionscode" => "numeric",
      'value' => '90', // Default
      'disporder' => 40
    ),
    //Eisliste Sperre
    'activitytracker_ice_lock' => array(
      'title' => 'Eisliste - Sperre',
      'description' => 'Gibt es eine Sperre? z.B Der Charakter darf nur einmal im Jahr auf Eis gelegt werden?',
      "optionscode" => "numeric",
      'value' => '365', // Default
      'disporder' => 41
    ),
    //Wie oft im Jahr?
    'activitytracker_ice_lock_days' => array(
      'title' => 'Eisliste - Zeitraum Sperre',
      'description' => 'Anzahl der Tage eintragen (z.B. 365 Tage = 1 Jahr).',
      "optionscode" => "numeric",
      'value' => '365', // Default
      'disporder' => 42
    ),
    //Wieviele Charaktere darf ein User auf Eis legen?
    'activitytracker_ice_type' => array(
      'title' => 'Eisliste - Beschänkungsart',
      'description' => 'Auf welche Art und Weise, soll die Anzahl der Charaktere, die man auf Eis legen kann eingeschränkt werden?',
      "optionscode" => 'radio
      none=gar nicht
      anzahl=eine konkrete Anzahl (z.B. 1 Charakter) 
      percent_plus=Prozentangabe und aufrunden (bei 50% z.B 3 von 5)
      percent_minus=Prozentangabe und abrunden (bei 50% z.B 2 von 5)',
      'value' => '1', // Default
      'disporder' => 43
    ),
    /*Allgemein benutzergruppen*/
    //Gruppen für normale Blacklistregeln
    'activitytracker_groups' => array(
      'title' => 'Usergruppen Gruppen',
      'description' => 'Welche Gruppen sollen für die Blacklist/Whitelist/Eisliste berücksichtigt werden?',
      'optionscode' => 'groupselect',
      'value' => '1', // Default
      'disporder' => 44
    ),
    //Bewerbergruppe
    'activitytracker_applicationgroup' => array(
      'title' => 'Gruppe für Bewerber',
      'description' => 'Wähle die Gruppe für eure Bewerber aus.',
      'optionscode' => 'groupselect',
      'value' => '2', // Default
      'disporder' => 45
    ),
    //ausgeschlossene User z.B. Gastaccount / Admint
    'activitytracker_excludeduid' => array(
      'title' => 'Ausgeschlossene User',
      'description' => 'Gibt es user, die nicht berücksichtigt werden sollen? Z.B. Adminaccount / Gastaccount. Kommagetrennte Liste',
      'optionscode' => 'text',
      'value' => '0', // Default
      'disporder' => 46
    ),
    //IDs fürs Ingame?
    'activitytracker_ingame' => array(
      'title' => 'Berücksichtige Foren',
      'description' => 'Wähle die Foren aus, die berücksichtigt werden sollen. Elternforen reichen.',
      'optionscode' => 'forumselect',
      'value' => '0', // Default
      'disporder' => 47
    ),
    //IDs fürs Archiv?
    'activitytracker_archiv' => array(
      'title' => 'Wähle dein Archiv, elternforum reicht',
      'description' => 'Wähle die Foren aus, die berücksichtigt werden sollen. Elternforen reichen.',
      'optionscode' => 'forumselect',
      'value' => '0', // Default
      'disporder' => 47
    ),
    //ausgeschlossene Foren
    'activitytracker_fidexcluded' => array(
      'title' => 'ausgeschlossene Foren',
      'description' => 'Gibt es explizite Foren die ausgeschlossen werden soll? z.B. SMS oder Mail... ? ',
      'optionscode' => 'forumselect',
      'value' => '4', // Default
      'disporder' => 48
    ),
    //ausgeschlossene Foren
    'activitytracker_scenereminder' => array(
      'title' => 'Szenenerinnerung',
      'description' => 'Soll der User erinnert werden, wenn er andere X Tage warten lässt? ',
      'optionscode' => 'yesno',
      'value' => '1', // Default
      'disporder' => 49
    ),
    //ausgeschlossene Foren
    'activitytracker_scenereminder_days' => array(
      'title' => 'Szenenerinnerung Tage',
      'description' => 'Nach wievielen Tagen soll man erinnert werden? ',
      'optionscode' => 'numeric',
      'value' => '0', // Default
      'disporder' => 50
    ),
  );

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
            $setting_old['value'] != $setting['value'] ||
            $setting_old['disporder'] != $setting['disporder']
          ) {
            $db->update_query('settings', $setting, "name='{$name}'");
            echo "Setting: {$name} wurde aktualisiert.<br>";
          }
        }
      }
    }
    echo "<p>Einstellungen wurden aktualisiert</p>";
  }
  rebuild_settings();
}

function activitytracker_add_templates($type = "install")
{
  global $db;

  $templategrouparray = array(
    'prefix' => 'activitytracker',
    'title'  => $db->escape_string('Aktivitätstracker'),
    'isdefault' => 1
  );
  $db->insert_query("templategroups", $templategrouparray);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_show_main',
    'template'  => $db->escape_string('
        <head>
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
  $db->insert_query("templates", $insert_array);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_show_main_userbit',
    'template'  => $db->escape_string('
        <div class="at-blacklist__user">
          {$activitytracker_bl_show_main_charabit}         
        </div>
      '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_show_main_charabit',
    'template'  => $db->escape_string('
        <div class="at-blacklist__chara">
          <div class="at-blacklist__charaname">
          </div>
          <div class="at-blacklist__charareason">
          </div>
          <div class="at-blacklist__charaoptions">
          </div> 
        </div>
        '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_ucp_overview',
    'template'  => $db->escape_string('
         Übersicht - User 
         Am nächsten X. erscheint die BL - folgende Charas würden drauf stehen
         Übersicht Charas auf Eis 
      '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_index_reminder',
    'template'  => $db->escape_string('
      Dein Charakter würde auf der nächsten BL stehen -> wegdrücken
           
        '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);

  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_index_reminder_is_bit',
    'template'  => $db->escape_string('
      X steht auf der BL
           
        '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);
  //templates anlegen
  $insert_array = array(
    'title'    => 'activitytracker_bl_index_reminder_would_bit',
    'template'  => $db->escape_string('
        X steht auf der BL
             
          '),
    'sid'    => '-2',
    'version'  => '0',
    'dateline'  => TIME_NOW
  );
  $db->insert_query("templates", $insert_array);
}

//TODO edit Task
$plugins->add_hook("admin_config_settings_change_commit", "activitytracker_editSettings");
function activitytracker_editSettings()
{
  global $db, $mybb;

  //wurde etwas an den Taskseinstellungen geändert?
  //Blacklistask
  //Bliacklistask autooff
  //Blacklist Benachrichtigung

  //Whitelist
  //Whitelist autooff

  //Eisliste

  //vergleiche alte Settings mit Input
  //wenn ja -> ändern speichern
  //Tasks Cache Updaten

  // $var = $mybb->input['upsetting']['blacklist_days'];
  // $db->update_query('tasks', array('day' => $var), "file = 'blacklist'");
}

/**
 * Anzeige im Profil des Users, Status der Charaktere sind.
 */
$plugins->add_hook('usercp_start', 'activitytracker_usercp_main');
function activitytracker_usercp_main()
{
  global $db, $mybb, $templates, $activitytracker_bl_ucp;
  if ($mybb->get_input('action') != "activitytracker") {
    return false;
  }

  //Übersicht
  //Warnung welche Charaktere auf der BL stehen würden
  //Ausgabe: würden die Charaktere am Erscheinungsdatum der Blacklist drauf stehen?
  //Welcher Steht aktuell drauf + status (gestrichen / gepostet)
  //activitytracker_check_blacklist


  //Einstellungen für Reminder Options ETC. 
  //Speichern der einstellung


  //Blacklist
  //Soll die Blacklist verwendet werden
  //einstellungen User Reminder
  //speichern einstellungen user reminder


  //Soll die Whitelist verwendet werden
  //Whiteliste - Wenn aktiv - Welche Charaktere zurückgemeldet sind


  //Eisliste - Welche Charaktere liegen auf Eis / Seit wann

  eval("\$activitytracker_bl_ucp =\"" . $templates->get("activitytracker_bl_ucp") . "\";");
}

/**
 * Blacklist - Ausgabe
 * Erreichbar über forenadresse misc.php?action=blacklist_show 
 */
$plugins->add_hook("misc_start", "activitytracker_blacklist_show");
function activitytracker_blacklist_show()
{
  global $mybb, $db, $templates, $header, $footer, $theme, $headerinclude,  $lang, $activitytracker_bl_show_main;


  // if (!$mybb->get_input('action') == "blacklist_at") return;

  if ($mybb->get_input('action') == "blacklist_at") {

    echo "hallo";

    eval("\$activitytracker_bl_show_main =\"" . $templates->get("activitytracker_bl_show_main") . "\";");
    output_page($activitytracker_bl_show_main);
  }
  //Ausgabe der aktuellen Blacklist für User wenn aktiv

  //Mods Immer Zugriff 
  //AUswahl ob aktueller Monat (1.) oder  1. nächster Monat (wer würde nächsten Monat drauf steeh)
  //Button wenn Benachrichtigung per PN/Mail -> F

  //if alertbutton klick
  //PN oder Mail?
  // activitytracker_blacklist_alert($type)

  //aktivieren der Blacklist // evt. ausführen des Tasks
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
 * Anzeige auf dem Index, wenn die activitytracker gerade aktiv ist 
 * inklusive ausblenden
 */
$plugins->add_hook("index_start", "activitytracker_index");
function activitytracker_index()
{
  //Reminder Blacklist

  //Reminder Whitelist

  //szenenerinnerung
}

/**
 * Anzeige Profil: Charakter ist auf Eis
 */

$plugins->add_hook("member_profile_start", "activitytracker_viewOnIce");
function activitytracker_viewOnIce()
{
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
 * Alle angehangenen Charas
 * @param uid 
 * @return chars Array mit uids
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
 * @param string type von welchem datum ausgehend
 * @return array assoziatives array
 * array[uid]: [last-post, lastpostdate, fid, tid, pid, reason: (nopost | noactivescene | tooldscenes | noapplication | tooldapplication)
 * 
 * , [array[reason]: nopost | noactivescene | tooldscenes | noapplication | tooldapplication,] 
 * nopost: registered since 
 * noactivescene: lastposttid, lastpostdate
 * tooldscenes: lastposter, lastpostdate, tid
 * noapplication registered since
 * tooldapplication : registered since, postdatestecki
 */
function activitytracker_check_blacklist($uids, $month)
{
  global $mybb, $db;
  //get settings 
  //wie lange ist die Blacklist aktiv
  $frist_blacklistende = $mybb->settings['activitytracker_bl_deadline'];
  $frist_blacklistdaystart = $mybb->settings['activitytracker_bl_turnus_day'];

  $duration_post = $mybb->settings['activitytracker_bl_duration'];
  $bl_noscenes = $mybb->settings['activitytracker_bl_noscenes'];

  //aktueller monat wenn month = this 
  //sonst nächster Monat -> achtung mit Jahr!
  // $frist_date frist_blacklistdaystart + $month date + daysdeadline

  //Forenkram fids etc
  //Forenstring bauen
  $archive_fid = str_replace(" ", "", "," . $mybb->settings['activitytracker_archiv']);
  $ingame_fid  = str_replace(" ", "", "," . $mybb->settings['activitytracker_ingame']);


  //Welche Gruppen gelten als angenommen
  $generalgroup = $mybb->settings['activitytracker_groups'];
  //Bewerbergruppe
  $applicantgroup = $mybb->settings['activitytracker_applicationgroup'];
  //ausgeschlossene User
  $applicantgroup = $mybb->settings['activitytracker_excludeduid'];

  // Soll es eine gesonderteFrist für den Ingameeinstieg geben?
  if ($mybb->settings['activitytracker_bl_ingamestart'] == 1) {
    //wenn ja, holen wir uns den Zeitraum
    $einstieg_zeitraum = $mybb->settings['activitytracker_bl_ingamestart_days'];
  } else {
    //wenn nein verwenden wir den normalen Zeitraum der Posts
    $einstieg_zeitraum = $mybb->settings['activitytracker_bl_duration'];
  }

  //array mit usern durchgehen
  foreach ($uids as $uid) {
    //charakterinfos bekommen
    $charinfo = get_user($uid);
    //Usergruppe des Charakter

    //Mitglied ist angenommen
    if (is_member($generalgroup, $uid)) {
      //Post im Ingame
      $ingamepost = activitytracker_posts_check($uid, $ingame_fid);
      //Post im Archiv
      $archivepost = activitytracker_posts_check($uid, $archive_fid);

      if (!$ingamepost && !$archivepost) {
        //Es gibt gar keine Posts des Users --> kein Ingameeinstieg

        //wobdate/register/area
        if ($mybb->settings['activitytracker_bl_wobdate'] == 'ales_wob') {
          $wob = $db->fetch_field($db->simple_select("users", "wob_date", "uid = '{$uid}'"), "wob_date");
        } elseif ($mybb->settings['activitytracker_bl_wobdate'] == 'risu_reg') {
          $wob = $db->fetch_field($db->simple_select("users", "wobdate", "uid = '{$uid}'"), "wob_date");
        } elseif ($mybb->settings['activitytracker_bl_wobdate'] == 'thread') {
          //ID Steckiarea
          $wob_fid = $mybb->settings['activitytracker_bl_wobdate_thread'];
          $lastpost = activitytracker_get_lastpost($uid, $wob_fid);
          //Wir gehen davon aus dass das Postdate = wobdate ist.
          $wob = $lastpost['dateline'];
        } else {
          //-> default registrierungsdatum von mybb
          $wob = $db->fetch_field($db->simple_select("users", "regdate", "uid = '{$uid}'"), "regdate");
        }

        //Differenz von heute zu wob berechnen.
        //Umwandeln in DateTime Objekt - damit können wir besser rechnen.
        $wob_datetime = (new DateTime())->setTimestamp($wob);
        //heutiges Datum als DateTime Objekt 
        $currentDate = new DateTime();
        //berechnen der Tage
        $diff = $currentDate->diff($wob_datetime);
        $diff_days = $diff->days;
        
        //date entsprechend builden
        //if > als ingameeinstieg 
        //auf BL 

      } else {
        //es gibt posts im archiv oder im ingame


        //es soll eine Sonderbehandlung für keine aktuellen Ingameszenen geben
        if ($bl_noscenes) {
          //testen ob es Szene im ingame gibt
          if (!$ingamepost) {
            //frist bekommen
            $noscenes_days = $mybb->settings['activitytracker_bl_noscenes_days'];
            //keine Szene
            //activitytracker_get_last_scene_infos() of last scene
            //get days 
            //vergleich mit frist
            //if last days > frist 
            // array['noactivescene'] = array(since => lastpostdate)
            // array[$uid] = array[noactivescene]
          } else {
          }
        } else {
          // keine sonderbehandlung
          // get last post of user (egal ob ingame oder archiv)
          // 
        }
      }
    } elseif (is_member($applicantgroup, $uid)) {
      //Mitglied ist Bewerber

      //Mitglied hat seinen Steckbrief gepostet
      //In Bewerbung

      //Mitglied hat noch keinen Steckbrief gepostet
    }
  }
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

  if ($fidlist != "") {
    if ($db->num_rows($db->simple_select("posts", "*", "uid = '{$uid}' AND fid in ({$fidlist});")) > 0) {
      return true;
    }
  }
  return false;
}

/**
 * Letzten Post eines Users aus einem Forum (+childs) bekommen
 * @param int uid 
 * @param string forenliste
 * @return array mit postdaten
 */
function activitytracker_get_lastpost($uid, $fidlist)
{
  global $db;
  //array initialisieren
  $post = array();
  //liste aller foren bekommen
  $getchilds = activitytracker_get_fids_string($fidlist);

  //den letzten Post aus liste von foren bekommen
  $post_query = $db->write_query("SELECT * from " . TABLE_PREFIX . "posts p
  WHERE uid = '{$uid}' 
  AND fid in ($getchilds) ORDER by dateline LIMIT 1");
  //post in array speichern
  while ($post = $db->fetch_array($post_query)) {
    $post[] = $post;
  }
  return $post;
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

  //zu array und doppelte rauswerfen
  $ingame_archiv_fids_array = array_unique($all_fids_str);

  //exluded rauswerfen
  $fids_array = array_diff($ingame_archiv_fids_array, $excludedfids_array);
  //zu string 
  $fids_str = implode(",", $fids_array);
  return $fids_str;
}


//test function

$plugins->add_hook("misc_start", "activitytracker_test");
function activitytracker_test()
{

  // echo "ich bin ein test";
  // $array = array();
  // $array = activitytracker_get_users();

  // activitytracker_get_lastpost(3, "14,20");
  activitytracker_add_settings('update');
  // var_dump($array);
  // var_dump(activitytracker_check_blacklist("this"));

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
}
