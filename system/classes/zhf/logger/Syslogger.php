<?php
class ZHF_Logger_Syslogger implements ZHF_Logger_Logger{
    const CONFIG_F_LOGGER='logger';
    const CONFIG_N_DEFAULT='default';
    private $priority;

    public function __construct(){
        $priority=ZHF::get_instance()->get_config(self::CONFIG_N_DEFAULT,self::CONFIG_F_LOGGER);
        $this->priority=$priority?$priority:LOG_WARNING;
    }

    public function debug(){
        $args=func_get_args();
        $args=array_merge(array(LOG_DEBUG),$args);
        return call_user_func_array(array($this,'log'), $args);
    }
    public function notice(){
        $args=func_get_args();
        $args=array_merge(array(LOG_NOTICE),$args);
        return call_user_func_array(array($this,'log'),$args);
    }
    public function warn(){
        $args=func_get_args();
        $args=array_merge(array(LOG_WARNING),$args);
        return call_user_func_array(array($this,'log'), $args);
    }

    public function fatal(){
        $args=func_get_args();
        $args=array_merge(array(LOG_CRIT),$args);
        return call_user_func_array(array($this,'log'), $args);
    }

    public function error(){
        $args=func_get_args();
        $args=array_merge(array(LOG_ERR),$args);
        return call_user_func_array(array($this,'log'), $args);
    }

    public function info(){
        $args=func_get_args();
        $args=array_merge(array(LOG_INFO),$args);
        return call_user_func_array(array($this,'log'), $args);
    }

    public function log(){
        $args_num=func_num_args();
        if($args_num<2){
            return FALSE;
        }
        $args=func_get_args();
        $priority=$args[0];
        $name=$args[1];
        $allow_priority=$this->priority;
        if($priority>$allow_priority){
            return FALSE;
        }
        $message="[{$priority}] [{$name}] ";
        for ($i=2;$i<$args_num;$i++){
            $message.=$args[$i];
        }
        openlog('ZHF',LOG_PID,LOG_USER);
        syslog($priority, $message);
        closelog();
    }
}
?>