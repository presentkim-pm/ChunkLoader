<?php

/*
 *
 *  ____                           _   _  ___
 * |  _ \ _ __ ___  ___  ___ _ __ | |_| |/ (_)_ __ ___
 * | |_) | '__/ _ \/ __|/ _ \ '_ \| __| ' /| | '_ ` _ \
 * |  __/| | |  __/\__ \  __/ | | | |_| . \| | | | | | |
 * |_|   |_|  \___||___/\___|_| |_|\__|_|\_\_|_| |_| |_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author  PresentKim (debe3721@gmail.com)
 * @link    https://github.com/PresentKim
 * @license https://www.gnu.org/licenses/agpl-3.0.html AGPL-3.0.0
 *
 *   (\ /)
 *  ( . .) ♥
 *  c(")(")
 */

declare(strict_types=1);

namespace kim\present\chunkloader\task;

use kim\present\chunkloader\ChunkLoader;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class CheckUpdateAsyncTask extends AsyncTask{
	private const RELEASE_URL = "https://api.github.com/repos/PresentKim/ChunkLoader-PMMP/releases/latest";
	private const CONTEXT_OPTION = ["http" => ["header" => "User-Agent: true"]];

	/**
	 * @var string|null Latest version of plugin
	 */
	private $latestVersion = null;

	/**
	 * @var string|null File-name and Download-url of latest release
	 */
	private $fileName, $downloadURL;

	/**
	 * Actions to execute when run
	 *
	 * Get latest version for comparing with plugin version, Store to $latestVersion
	 * Get file-name and download-url of latest release, Store to $fileName, $downloadURL
	 */
	public function onRun() : void{
		if(ini_get("allow_url_fopen")){
			$latestRelease = file_get_contents(self::RELEASE_URL, false, stream_context_create(self::CONTEXT_OPTION));
			if($latestRelease !== false){
				$jsonData = json_decode($latestRelease, true);
				$this->latestVersion = $jsonData["tag_name"];
				foreach($jsonData["assets"] as $key => $assetData){
					if(substr_compare($assetData["name"], ".phar", -strlen(".phar")) === 0){ //ends with ".phar"
						$this->fileName = $assetData["name"];
						$this->downloadURL = $assetData["browser_download_url"];
					}
				}
			}
		}
	}

	/**
	 * Actions to execute when completed (on main thread)
	 * Implement this if you want to handle the data in your AsyncTask after it has been processed
	 *
	 * @param Server $server
	 */
	public function onCompletion(Server $server) : void{
		$plugin = ChunkLoader::getInstance();
		if($this->latestVersion === null){
			$plugin->getLogger()->critical("Update check failed : Connection to release server failed");
		}elseif(version_compare($plugin->getDescription()->getVersion(), $this->latestVersion) >= 0){
			$plugin->getLogger()->notice("The plugin is latest version or higher (Latest version: {$this->latestVersion})");
		}else{
			$plugin->getLogger()->warning("The plugin is not up to date. We recommend that you update your plugin. (Latest : {$this->latestVersion})");

			//Shorten download url of latest release
			$plugin->getServer()->getAsyncPool()->submitTask(new ShortenDownloadURLAsyncTask($this->fileName, $this->downloadURL));
		}
	}
}