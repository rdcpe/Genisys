<?php

/*
 *
 *  _____   _____   __   _   _   _____  __    __  _____
 * /  ___| | ____| |  \ | | | | /  ___/ \ \  / / /  ___/
 * | |     | |__   |   \| | | | | |___   \ \/ /  | |___
 * | |  _  |  __|  | |\   | | | \___  \   \  /   \___  \
 * | |_| | | |___  | | \  | | |  ___| |   / /     ___| |
 * \_____/ |_____| |_|  \_| |_| /_____/  /_/     /_____/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author iTX Technologies
 * @link https://itxtech.org
 *
 */

namespace pocketmine\inventory;

use pocketmine\Player;
use pocketmine\item\Item;

class SimpleTransactionQueue implements TransactionQueue{
	
	/** @var Player[] */
	protected $player = null;
	
	/** @var Transaction[] */
	protected $in = [];
	
	/** @var Transaction[] */
	protected $out = [];	
	
	/**
	 * @param Player $player
	 */
	public function __construct(Player $player = null){
		$this->player = $player;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	public function getCurrentTransactions(){
		return array_merge($this->out, $this->in);
	}
	
	//I don't like this function. TODO: Find a better way to do this.
	public function compactTransactions(&$queue){
		foreach($queue as $hash => $transaction){
			foreach($queue as $hash2 => $transactionToCompare){
				if($hash === $hash2){
					//Comparing the object to itself
					continue;
				}elseif($transaction === $transactionToCompare){
					//Two references to the same transaction object
					unset($queue[$hash2]);
					continue;
				}elseif($transaction->getInventory() === $transactionToCompare->getInventory()
					and $transaction->getSlot() === $transactionToCompare->getSlot()){
					//Found a transaction that refers to the same slot in the same inventory
					
					//Take the source item from the older transaction
					$sourceItem = ($transaction->getCreationTime() < $transactionToCompare->getCreationTime() ? $transaction->getSourceItem(): $transactionToCompare->getSourceItem());
					//and the target from the newer one
					$targetItem = ($transaction->getCreationTime() > $transactionToCompare->getCreationTime() ? $transaction->getTargetItem(): $transactionToCompare->getTargetItem());
					
					$compactTransaction = new BaseTransaction($transaction->getInventory(), $transaction->getSlot(), $sourceItem, $targetItem);
					
					unset($queue[$hash]);
					unset($queue[$hash2]);
					
					$queue[spl_object_hash($compactTransaction)] = $compactTransaction;
				}
			}
		}
	}
	
	/**
	 * @param Transaction $transaction
	 * @return bool
	 *
	 * Add a transaction to the queue
	 * Return true if the addition was successful, false if not.
	 */
	public function addTransaction(Transaction $transaction){
		
		$change = $transaction->getChange();
		if($change === null){
			return false;
		}
		
		if($change["in"] instanceof Item){
			$this->in[spl_object_hash($transaction)] = $transaction;
		}
		if($change["out"] instanceof Item){
			$this->out[spl_object_hash($transaction)] = $transaction;
		}
		
		return true;
	}
	
	/**
	 * This function will be called at regular intervals
	 * to allow transactions to stack and then be cleared.
	 */
	public function execute(){
		if(count($this->in) === 0 and count($this->out) === 0){
			//No waiting transactions, return
			return false;
		}
		//TODO: finish
	}
}