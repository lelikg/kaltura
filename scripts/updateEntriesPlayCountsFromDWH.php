<?php

ini_set("memory_limit","1024M");

define('ROOT_DIR', realpath(dirname(__FILE__) . '/../../'));
require_once(ROOT_DIR . '/infra/bootstrap_base.php');
require_once(ROOT_DIR . '/infra/KAutoloader.php');

KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "vendor", "propel", "*"));
KAutoloader::addClassPath(KAutoloader::buildPath(KALTURA_ROOT_PATH, "plugins", "*"));
KAutoloader::setClassMapFilePath('../cache/classMap.cache');
KAutoloader::register();

error_reporting(E_ALL);
KalturaLog::setLogger(new KalturaStdoutLogger());

$dbConf = kConf::getDB();
DbManager::setConfig($dbConf);
DbManager::initialize();



$connection = Propel::getConnection();

$query = "select entry_id, sum(count_plays) as plays, sum(count_loads) views from kalturadw.dwh_hourly_events_entry group by entry_id ";
$statement = $connection->prepare($query);

$statement->execute();
while($resultset = $statement->fetch(PDO::FETCH_OBJ))
{
        if($resultset->entry_id)
        {
                $entry = entryPeer::retrieveByPK($resultset->entry_id);
                if($entry)
                {
                        if($resultset->plays)
                        {
                                $entry->setPlays($resultset->plays);
                        }

                        if($resultset->views)
                        {
                                $entry->setViews($resultset->views);
                        }

                        $entry->save();
                }
        }
}



