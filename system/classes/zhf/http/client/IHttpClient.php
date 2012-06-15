<?php
/**
 *
 * HttpClient Interface
 * @author zhxia84
 *
 */
interface ZHF_Http_Client_IHttpClient {
     function set_option($name,$value);
     function execute();

}