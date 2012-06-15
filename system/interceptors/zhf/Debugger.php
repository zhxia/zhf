<?php
class ZHF_DebuggerInterceptor extends ZHF_Interceptor{
    const TRIGGER_PARAMETER_NAME='debug';
    const TRIGGER_COOKIE_NAME='debug';
    public function before(){
        if(!$this->is_allow_debug()){
            return self::STEP_CONTINUE;
        }
        $request=$this->zhf->get_request();
        $response=$this->zhf->get_response();
        $debug_param_value=$request->get_parameter(self::TRIGGER_PARAMETER_NAME);
        $debug_cookie_value=$request->get_cookie(self::TRIGGER_COOKIE_NAME);
        if(isset($debug_param_value)&&$debug_param_value==0){
            if(isset($debug_cookie_value)){
                $response->remove_cookie(self::TRIGGER_COOKIE_NAME);
            }
            return self::STEP_CONTINUE;
        }
        if(isset($debug_param_value)&&$debug_param_value>0){
            $this->zhf->set_debugger($this->create_debugger());
            $response->set_cookie(self::TRIGGER_COOKIE_NAME,$debug_param_value);
            return self::STEP_CONTINUE;
        }
        if(isset($debug_cookie_value)&&$debug_cookie_value>0){
            $this->zhf->set_debugger($this->create_debugger());
        }
        else{
            if(isset($debug_cookie_value)){
                $response->remove_cookie(self::TRIGGER_COOKIE_NAME);
            }
        }
        return self::STEP_CONTINUE;
    }

    protected function create_debugger(){
        return new ZHF_Debugger();
    }

    protected function is_allow_debug(){
        $zhf=ZHF::get_instance();
        $request=$zhf->get_request();
        $client_ip=$request->get_client_ip();
        $is_enable_debug=$zhf->get_config('enable_debug');
        return $is_enable_debug;
    }
}