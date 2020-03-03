<?php
/**
 * Created by PhpStorm.
 * User:
 * Date: 2020/2/22
 * Time: 4:53 下午
 */

namespace swo\console;


class Input
{
    public static function info($message,$desc = null){

        $return = '----->>> '.$desc ."start \r\n";
        if(is_array($message)){
            $return .= var_export($message,true);
        }else{
            $return .= $message ."\n\r";
        }
        $return .= '----->>> '.$desc ."end\n\r";
        echo $return;
    }
}