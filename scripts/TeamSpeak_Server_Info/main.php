<?php

namespace TS_Scripts\TeamSpeak_Server_Info;

use TeamSpeak3;
use TeamSpeak3_Exception;
use TS_Scripts\Config;

//import config
require_once __DIR__ ."/../../config/config.php";

$config_class = new Config();

$config = $config_class->getConfig();

//cache 30 secs
if(!file_exists($config['Cache_file_url']."/ts_server_info.json") OR (time() - filemtime($config['Cache_file_url']."/ts_server_info.json")) > (1 * 30))
{

    //import libs
    require_once __DIR__ ."/../../vendor/autoload.php";

    try
    {

        //login
        $ts3 = TeamSpeak3::factory("serverquery://".$config['TS_Username'].":".$config['TS_Password']."@".$config['TS_Host'].":".$config['TS_Server_Query_Port']."/?server_port=".$config['TS_Server_Port']);


        try
        {

            //get online status/name/players
            if($ts3->getProperty("virtualserver_status")->toString() == "online")
            {

                $ts_info_online = "Online";

                if(!empty($ts3->__get("virtualserver_name")))
                {

                    $ts_info_name = $ts3->__get("virtualserver_name");

                }
                else
                {

                    $ts_info_name = "Couldn't fetch server's name!";

                }

                if(!empty($ts3->__get("virtualserver_clientsonline")) AND !empty($ts3->__get("virtualserver_maxclients")))
                {

                    $ts_info_players = $ts3->__get("virtualserver_clientsonline") . " / " . $ts3->__get("virtualserver_maxclients");

                }
                else
                {

                    $ts_info_players = "Couldn't fetch client count!";

                }


            }
            elseif($ts3->getProperty("virtualserver_status")->toString() == "offline")
            {

                $ts_info_online = "Offline";
                $ts_info_name = "Server Offline";
                $ts_info_players = "0/0";

            }
            else
            {

                $ts_info_online = "Server not responding";
                $ts_info_name = "Can't connect to the server";
                $ts_info_players = "0/0";

            }

        }
        catch(TeamSpeak3_Exception $error)
        {

            $ts_info_online = "Server Error";
            $ts_info_name = "Couldn't fetch online status";
            $ts_info_players = "0/0";

        }

    }
    catch(TeamSpeak3_Exception $error)
    {

        $ts_info_online = "Server Offline";
        $ts_info_name = "Can't connect to the server";
        $ts_info_players = "0/0";

    }

    //create json
    $ts_info = array
    (

        "TS3 Main" => array
        (

            "Status" => $ts_info_online,
            "Name" => $ts_info_name,
            "Players" => $ts_info_players

        ),
        "Updated" => date("H:i:s d.m.Y")

    );

    //make json
    $json_ts_info = json_encode($ts_info);

    //caching
    file_put_contents($config['Cache_file_url']."/ts_server_info.json", $json_ts_info);

}
