<?php

namespace ifteam\RankManager\rank;

use pocketmine\utils\Config;

class RankData {
	private $userName;
	private $dataFolder;
	private $data;
	private $nowSpecialPrefix = null;
	private $specialPrefixList = [ ];
	public function __construct($userName, $dataFolder) {
		$userName = strtolower ( $userName );
		
		$this->userName = $userName;
		$this->dataFolder = $dataFolder . substr ( $userName, 0, 1 ) . "/";
		
		if (! file_exists ( $this->dataFolder ))
			@mkdir ( $this->dataFolder );
		
		$this->load ();
	}
	public function load() {
		$this->data = (new Config ( $this->dataFolder . $this->userName . ".json", Config::JSON, [ 
				"nowPrefix" => null,
				"prefixList" => [ ] ] ))->getAll ();
	}
	public function save($async = false) {
		$data = new Config ( $this->dataFolder . $this->userName . ".json", Config::JSON );
		$data->setAll ( $this->data );
		$data->save ( $async );
	}
	public function addPrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			$this->data ["prefixList"] [$prefix] = true;
	}
	public function addSpecialPrefixs(array $prefixs) {
		foreach ( $prefixs as $prefix )
			$this->specialPrefixList [$prefix] = true;
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
	public function isExistPrefixToIndex($index) {
		return ($this->getPrefixToIndex ( $index ) !== null) ? true : false;
	}
	public function setPrefix($prefix) {
		$this->data ["nowPrefix"] = $this->getIndexToPrefix ( $prefix );
		return true;
	}
	public function setSpecialPrefix($prefix) {
		$this->nowSpecialPrefix = $prefix;
	}
	public function getPrefix() {
		return $this->getPrefixToIndex ( $this->data ["nowPrefix"] );
	}
	public function getSpecialPrefix() {
		return $this->nowSpecialPrefix;
	}
	public function getPrefixList() {
		return $this->data ["prefixList"];
	}
	public function getSpecialPrefixList() {
		return $this->specialPrefixList;
	}
	public function getIndexToPrefix($requestKey) {
		$index = 0;
		foreach ( $this->data ["prefixList"] as $key => $bool ) {
			if ($requestKey == $key)
				return $index;
			$index ++;
		}
		return null;
	}
	public function getPrefixToIndex($requestIndex) {
		$index = 0;
		foreach ( $this->data ["prefixList"] as $key => $bool ) {
			if ($index == $requestIndex)
				return $key;
			$index ++;
		}
		return null;
	}
}

?>