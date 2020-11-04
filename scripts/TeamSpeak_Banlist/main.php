<?php

namespace TS_Scripts\TeamSpeak_Banlist;

use TeamSpeak3;
use TeamSpeak3_Exception;
use TS_Scripts\Config;

//import config
require_once __DIR__ ."/../../config/config.php";

$config_class = new Config();

$config = $config_class->getConfig();

//check cache 60 mins = 1 hour
if(!file_exists($config['Cache_file_url']."/banlist.json") OR (time() - filemtime($config['Cache_file_url']."/banlist.json")) > (60 * 60))
{

    //import libs
    require_once __DIR__ ."/../../vendor/autoload.php";

    try
    {

        //login
        $ts3 = TeamSpeak3::factory("serverquery://".$config['TS_Username'].":".$config['TS_Password']."@".$config['TS_Host'].":".$config['TS_Server_Query_Port']."/?server_port=".$config['TS_Server_Port']);

        try
        {

            //fetch banlist
            $banlist = $ts3->banlist();

            if($banlist !== null)
            {

                //epoch time to mysql date
                foreach($banlist as $key => $value)
                {

                    if(isset($value["created"]))
                    {

                        $banlist[$key]["created"] = date("Y-m-d H:i:s", $value["created"]);

                    }

                }

                //create json and save banlist
                $json_banlist = json_encode($banlist);

            }
            else
            {

                $json_banlist = "Couldn't fetch banlist - server responded badly.";

            }

        }
        catch(TeamSpeak3_Exception $error)
        {

            $json_banlist =  "Error: Can't connect to the server ".$config['TS_Host'].":".$config['TS_Server_Port']." ".$error->getMessage();

        }

    }
    catch(TeamSpeak3_Exception $error)
    {

        $json_banlist = "Error: Couldn't fetch the banlist at ".$config['TS_Host'].":".$config['TS_Server_Port']." ".$error->getMessage();

    }

    //putting it to the cache file
    file_put_contents($config['Cache_file_url']."/banlist.json", $json_banlist);

}

