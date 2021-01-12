<?php

namespace TS_Scripts\TeamSpeak_Bots_Info;

use Exception;
use SinusBot;
use TS_Scripts\Config;

//import config
require_once __DIR__ ."/../../config/config.php";

$config_class = new Config();

$config = $config_class->getConfig();

//cache 12 hours
if(!file_exists($config['Cache_file_url']."/ranks.json") OR (time() - filemtime($config['Cache_file_url']."/ranks.json")) > (12 * 60 * 60))
{

    //import libs
    require_once __DIR__ ."/../../vendor/autoload.php";

    //login
    $sinusbot = new SinusBot\API($config['Bot_Host']);

    try
    {

        $sinusbot->login($config['Bot_Username'], $config['Bot_Password']);

        //ranks data
        $ranks = $sinusbot->getRanks($config['Bot_Rank_Instance'])['0']['data'];

        if($ranks == null)
        {

            $ranks = "Error getting ranks data";

        }

    }
    catch(Exception $error)
    {

        if($error->getMessage() === "Error: 404 (Not Found)")
        {

            $ranks = "Instance rank UUID is wrong in config or rank plugin is not installed!";

        }
        else
        {

            $ranks = $error->getMessage();

        }

    }

    //save banlist
    $json_ranks = json_encode($ranks);
    file_put_contents($config['Cache_file_url']."/ranks.json", $json_ranks);

}
