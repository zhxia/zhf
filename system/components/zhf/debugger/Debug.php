<?php
class ZHF_Debugger_DebugComponent extends ZHF_Component{
    public static function use_styles(){
        $path=zhf_classname_to_path(__CLASS__);
        return array_merge(
            array($path.'Debug.css')
        );
    }

    public function get_view(){
        return 'Debug';
    }

    public function get_benchmarks(){
        return ZHF::get_instance()->get_debugger()->get_benchmarks();
    }

    public function get_messages(){
        return ZHF::get_instance()->get_debugger()->get_messages();
    }
}