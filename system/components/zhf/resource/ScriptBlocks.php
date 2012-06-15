<?php
class ZHF_Resource_ScriptBlocksComponent extends ZHF_Resource_JavascriptsAndStylesComponent{
    public function get_view(){
        return $this->get_param('head')?'ScriptBlocksHead':'ScriptBlocks';
    }

    public function get_script_blocks(){
        return ZHF::get_instance()->get_script_blocks();
    }
}