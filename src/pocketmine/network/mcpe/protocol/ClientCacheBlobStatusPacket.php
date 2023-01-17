<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace pocketmine\network\mcpe\protocol;

use pocketmine\utils\Binary;

use pocketmine\network\mcpe\NetworkSession;
use function count;

class ClientCacheBlobStatusPacket extends DataPacket/* implements ServerboundPacket*/{
	public const NETWORK_ID = ProtocolInfo::CLIENT_CACHE_BLOB_STATUS_PACKET;

	/** @var int[] xxHash64 subchunk data hashes */
	private $hitHashes = [];
	/** @var int[] xxHash64 subchunk data hashes */
	private $missHashes = [];

	/**
	 * @param int[] $hitHashes
	 * @param int[] $missHashes
	 */
	public static function create(array $hitHashes, array $missHashes) : self{
		//type checks
		(static function(int ...$hashes) : void{})(...$hitHashes);
		(static function(int ...$hashes) : void{})(...$missHashes);

		$result = new self;
		$result->hitHashes = $hitHashes;
		$result->missHashes = $missHashes;
		return $result;
	}

	/**
	 * @return int[]
	 */
	public function getHitHashes() : array{
		return $this->hitHashes;
	}

	/**
	 * @return int[]
	 */
	public function getMissHashes() : array{
		return $this->missHashes;
	}

	protected function decodePayload() : void{
		$hitCount = $this->getUnsignedVarInt();
		$missCount = $this->getUnsignedVarInt();
		for($i = 0; $i < $hitCount; ++$i){
			$this->hitHashes[] = (Binary::readLLong($this->get(8)));
		}
		for($i = 0; $i < $missCount; ++$i){
			$this->missHashes[] = (Binary::readLLong($this->get(8)));
		}
	}

	protected function encodePayload() : void{
		$this->putUnsignedVarInt(count($this->hitHashes));
		$this->putUnsignedVarInt(count($this->missHashes));
		foreach($this->hitHashes as $hash){
			($this->buffer .= (\pack("VV", $hash & 0xFFFFFFFF, $hash >> 32)));
		}
		foreach($this->missHashes as $hash){
			($this->buffer .= (\pack("VV", $hash & 0xFFFFFFFF, $hash >> 32)));
		}
	}

	public function handle(NetworkSession $handler) : bool{
		return $handler->handleClientCacheBlobStatus($this);
	}
}
