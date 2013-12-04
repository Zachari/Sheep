<?php

/*
__PocketMine Plugin__
name=Sheep
author=KnownUnown
version=2.0
apiversion=9,10,11
class=Sheep
*/

/*
Full list of contributors:
KnownUnown (project creator)
sekjun9878
*/

class Sheep implements Plugin {
    
    private $api;
    private $server;
    private $config;
    private $confirm;
    private $nOfPlugins;
	private $questionableFunctionsList;
    private $w;
    
    public function __construct(ServerAPI $api, $server = false){
        $this->api = $api;
        $this->server = ServerAPI::request();
        
        $this->config = new Config($this->api->plugin->configPath($this) . "sheep.yml", CONFIG_YAML, array(
        "api-url" => "http://forums.pocketmine.net/api.php",
        "dl-url" => "http://forums.pocketmine.net/index.php?plugins/{$this->w["title"]}.{$this->w["description_id"]}/download&version={$this->w["version_id"]}",
        "player-install" => false,
        "auto-update" => true,
        "spapi-url" => null,
        "spapi-enabled" => false,
        "bad-functions" => array(
		"passthru",
		"exec",
		"pnctl_exec",
		"proc_open",
		"popen",
		"system",
		"shell_exec",
		"register_shutdown_function",
		"register_tick_function",
		"dl",
		"eval",
		"expect_popen",
		"apache_child_terminate",
		"link",
		"posix_kill",
		"posix_mkfifo",
		"posix_setpgid",
		"posix_setsid",
		"posix_setuid",
		"proc_close",
		"proc_get_status",
		"proc_nice",
		"proc_terminate",
		"putenv",
		"touch",
		"alter_ini",
		"highlight_file",
		"show_source",
		"ini_alter",
		"fgetcsv",
		"fputcsv",
		"fpassthru",
		"ini_get_all",
		"openlog",
		"syslog",
		"rename",
		"copy",
		"parse_ini_file",
		"ftp_connect",
		"ftp_ssl_connect",
		"fsockopen",
		"pfsockopen",
		"socket_bind",
		"socket_connect",
		"socket_listen",
		"socket_create_listen",
		"socket_accept",
		"socket_getpeername",
		"socket_send",
		"apache_get_modules",
		"apache_get_version",
		"apache_getenc",
		"apache_note",
		"apache_setenv",
		"apache_request_headers",
		"diskfreespace",
		"disk_free_space",
		"get_current_user",
		"getmypid",
		"getmyuid",
		"getrusage",
		"set_time_limit",
		"show_source",
		"symlink",
		"tmpfile",
		"virtual",
		"phpinfo",
		"max_execution_time",
		"set_include_path",
		"escapeshellcmd",
		"escapeshellarg",
		"basename",
		"chgrp",
		"chmod",
		"chown",
		"clearstatcache",
		"copy",
		"delete",
		"dirname",
		"disk_free_space",
		"disk_total_space",
		"diskfreespace",
		"fclose",
		"feof",
		"fflush",
		"fgetc",
		"fgetcsv",
		"fgets",
		"fgetss",
		"file_exists",
		"file_get_contents",
		"file_put_contents",
		"file",
		"fileatime",
		"filectime",
		"filegroup",
		"fileinode",
		"filemtime",
		"fileowner",
		"fileperms",
		"filesize",
		"filetype",
		"flock",
		"fnmatch",
		"fopen",
		"fpassthru",
		"fputcsv",
		"fputs",
		"fread",
		"fscanf",
		"fseek",
		"fstat",
		"ftell",
		"ftruncate",
		"fwrite",
		"glob",
		"is_dir",
		"is_executable",
		"is_file",
		"is_link",
		"is_readable",
		"is_uploaded_file",
		"is_writable",
		"is_writeable",
		"lchgrp",
		"lchown",
		"link",
		"linkinfo",
		"lstat",
		"mkdir",
		"move_uploaded_file",
		"parse_ini_file",
		"parse_ini_string",
		"pathinfo",
		"pclose",
		"popen",
		"readfile",
		"readlink",
		"realpath_cache_get",
		"realpath_cache_size",
		"realpath",
		"rename",
		"rewind",
		"rmdir",
		"set_file_buffer",
		"stat",
		"symlink",
		"tempnam",
		"tmpfile",
		"touch",
		"umask",
		"unlink",
	),
        ));
        if($this->config->get("spapi-enabled")){
            if($this->config->get("spapi-url") == (null || "")){
                if(!Utils::curl_post($this->config->get("spapi-url"), array($ip = $_SERVER["SERVER_ADDR"]))){
                    console('[Sheep] ERROR: Unable to connect to remote SPanel API.');
                } else {
                console('[Sheep] SPanel has been enabled!');
                }
            } else {
                console('[Sheep] SPanel is disabled.');
            }
        }
        $this->questionableFunctionsList = $this->api->get("bad-functions");
        $this->nOfPlugins = json_decode(file_get_contents($this->config->get("api-url")))['count'];
        console("[Sheep] Loaded Sheep! Current count of plugins on PocketMine Forums: {$this->nOfPlugins}");
    }
    
    public function init(){
        $this->api->console->register("sheep", "Sheep version 2.0", array($this, "cmdHandle"));
    }
    
    public function cmdHandle($cmd, $params, $issuer){
	    if($issuer instanceof Player)
	    {
            if(!$this->config->get("player-install")){
		    return "[Sheep] You are not allowed to use this command. Consider asking your administrator to enable player-install in sheep.yml.";
            }
        }

        $output = "";
        switch($cmd){
            case "sheep":
                switch($params[0]){
                    case "install":
						console("[Sheep] Installing...\n");
                        if(!isset($params[1]) or $params[1] == ""){
                            return "[Sheep] No plugin specified to install.";
                        }
                        if(!$this->derpUrl($params[1])){
                            console("[Sheep] Error: Unknown error.");
                        } else {
                            $state = $this->derpUrl($params[1])["state"];
                            $name = $this->derpUrl($params[1])["name"];
                            $author = $this->derpUrl($params[1])["author"];
                            $dl = $this->derpUrl($params[1])["dl"];
                            if($state !== "visible"){
                                return false;
                            }
	                        console("[Sheep] Downloading plugin {$name} by {$author}...\n");
	                        $plugin = file_get_contents($dl);
	                        console("[Sheep] Checking for malware...\n");
	                        foreach($this->questionableFunctionsList as $q)
	                        {
		                        if (strpos($plugin, $q) !== false) {
			                        return "[Sheep] Plugin contains file system function(s). This plugin can be planning to do something nasty! To install, do /sheep confirm.";
			                        $this->confirm = $dl;
		                        } else {
		                            file_put_contents(DATA_PATH."/plugins/".$name.".php", $plugin);
		                        }
	                        }
                            if(!$this->api->plugin->load($name.".php")){
                                $output = "[Sheep] An internal error has occured.";
                            } else {
                            $output = "[Sheep] Successfully downloaded and installed plugin " . $name . " .";
                            }
                        }
	                    console("[Sheep] Installed plugin.\n");
		                break;
	                case "uninstall":
	                case "remove":
	                    switch($params[1]){
	                        case '':
	                            $output = "[Sheep] Plugin name cannot be blank!";
                                break;
	                        default:
	                            unlink(DATA_PATH . DIRECTORY_SEPERATOR . "plugins" . DIRECTORY_SEPERATOR . $params[1]);
	                            $output = "[Sheep] Successfully removed plugin named" . $params[1];
	                    }
		                break;
                }
        }
        return $output;
    }

    public function derpUrl($name){
        $api = $this->config->get("api-url");
        $parsedjson = json_decode(file_get_contents($api));
        foreach($parsedjson as $hmm => $idk){ //TODO: what was I thinking here again?
            $this->w = $idk;
            foreach($idk["title"] as $meh){
                if($meh = $name){
                    return array(
                        "name" => $this->w["title"],
                        "state" => $this->w["state"],
                        "author" => $this->w["author"],
                        "dl" => $this->config->get("dl-url"),
                    );
                } else {
                    return false;
                }
            }
        }
    }
    
    public function __destruct(){
        $this->config->save();
        console('[Sheep] Sheep exiting.');
    }
}
?>
