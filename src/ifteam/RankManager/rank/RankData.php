<?php

namespace ifteam\RankManager\rank;

use pocketmine\utils\Config;

class RankData {
	//
	private $userName;
	private $dataFolder;
	private $data;
	public function __construct($userName, $dataFolder) {
		$userName = strtolower ( $userName );
		
		$this->userName = $userName;
		$this->dataFolder = $dataFolder . substr ( $userName, 0, 1 ) . "/";
		
		$this->load ();
	}
	public function load() {
		$this->data = (new Config ( $this->dataFolder . $this->userName . ".yml", Config::YAML, [ 
				"nowPrefix" => "",
				"nowSpecialPrefix" => "",
				"prefixList" => [ ],
				"specialPrefixList" => [ ] 
		] ))->getAll ();
	}
	public function save($async = false) {
		(new Config ( $this->dataFolder . $this->userName . ".yml", $this->data ))->save ( $async );
	}
	public function addPrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			$this->data ["prefixList"] [$prefix] = true;
	}
	public function addSpecialPrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			$this->data ["specialPrefixList"] [$prefix] = true;
	}
	public function deletePrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			if (isset ( $this->data ["prefixList"] [$prefix] ))
				unset ( $this->data ["prefixList"] [$prefix] );
	}
	public function deleteSpecialPrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			if (isset ( $this->data ["prefixList"] [$prefix] ))
				unset ( $this->data ["prefixList"] [$prefix] );
	}
	public function isExistPrefix($prefix) {
		return isset ( $this->data ["prefixList"] [$prefix] ) ? true : false;
	}
	public function setPrefix($prefix) {
		$this->data ["nowPrefix"] = $prefix;
	}
	public function setSpecialPrefix($prefix) {
		$this->data ["nowSpecialPrefix"] = $prefix;
	}
	public function getPrefix() {
		return $this->data ["nowPrefix"];
	}
	public function getSpecialPrefix() {
		return $this->data ["nowSpecialPrefix"];
	}
	public function getPrefixList() {
		return $this->data ["prefixList"];
	}
	public function getSpecialPrefixList() {
		return $this->data ["specialPrefixList"];
	}
}

?>