<?php
interface ZHF_Logger_Logger {
    function log();
    function debug();
    function warn();
    function info();
    function error();
    function fatal();
}
?>