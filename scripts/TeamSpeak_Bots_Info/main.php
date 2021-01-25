<?php

namespace TS_Scripts\TeamSpeak_Bots_Info;

use Exception;
use SinusBot;
use TS_Scripts\Config;

//import config
require_once __DIR__ ."/../../config/config.php";

$config_class = new Config();

$config = $config_class->getConfig();

//check cache 30 secs
if(!file_exists($config['Cache_file_url']."/bots.json") OR (time() - filemtime($config['Cache_file_url']."/bots.json")) > (1 * 30))
{

    //import libs
    require_once __DIR__ ."/../../vendor/autoload.php";

    //login
    $sinusbot = new SinusBot\API($config['Bot_Host']);

    try
    {

        $sinusbot->login($config['Bot_Username'], $config['Bot_Password']);

        $instances = $sinusbot->getInstances();

        //make main json
        $bot_info = array("Bot" => array(), "Updated" => date("H:i:s d.m.Y"));

        foreach($instances as $instance)
        {

            $object_vars = get_object_vars($instance);

            if (!isset($object_vars['uuid']))
            {

                $object_vars['uuid'] = NULL;

            }

            $bot_nick = $instance->getName();

            $is_running = $instance->isRunning();

            if($is_running == true)
            {

                $bot_status = "Online";

                $is_playing = $instance->isPlaying();

                if($is_playing == true)
                {

                    $bot_data = $instance->getStatus();

                    if(!isset($bot_data['currentTrack']['title']) && !isset($bot_data['currentTrack']['tempArtist']))
                    {
                        //delete .mp3
                        $bot_playing = preg_replace('/\\.[^.\\s]{3,4}$/', '', $bot_data['currentTrack']['filename']);

                    }
                    elseif(isset($bot_data['currentTrack']['tempArtist']) && isset($bot_data['currentTrack']['tempTitle']))
                    {

                        $bot_playing = $bot_data['currentTrack']['tempArtist']." - ".$bot_data['currentTrack']['tempTitle'];

                    }
                    else
                    {

                        $bot_playing = $bot_data['currentTrack']['title'];

                    }

                }
                else
                {

                    $bot_playing = "Currently not playing.";

                }

            }
            else
            {

                $bot_status = "Offline";
                $bot_playing = "Bot is not running";

            }

            //create and add to main json
            $bot_info['Bot'][$object_vars['uuid']] = array(
                "Name"  => (string)$bot_nick,
                "Status" => (string)$bot_status,
                "Playing" => (string)$bot_playing
            );

        }

    }
    catch(Exception $error)
    {

        $bot_info = $error->getMessage();

    }

    //create json and save it
    $json_bot_info = json_encode($bot_info);

    file_put_contents($config['Cache_file_url']."/bots.json", $json_bot_info);

}
