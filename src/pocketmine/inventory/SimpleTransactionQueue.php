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
	
	/** @var \SplPriorityQueue */
	protected $queue;
	
	/** @var bool */
	protected $isExecuting = false;
	
	
	/**
	 * @param Player $player
	 */
	public function __construct(Player $player = null){
		$this->player = $player;
		$this->queue = new \SplPriorityQueue();
	}

	/**
	 * @return Player
	 */
	public function getPlayer(){
		return $this->player;
	}
	
	public function getCurrentTransactions(){
		return $this->queue;
	}
	
	/**
	 * @param Transaction $transaction
	 * @return bool
	 *
	 * Add a transaction to the queue
	 * Return true if the addition was successful, false if not.
	 */
	 
	public function addTransaction(Transaction $transaction){
		/* This will need some improvement so transaction duplication
		 * cannot happen. */
		 
		$change = $transaction->getChange();
		if($change === null){
			return false;
		}
		
		/* 2 is the priority of an "out" transaction.
		 * Assume "out" until/unless told otherwise. 
		 */
		$priority = 2;
		if($change["in"] instanceof Item){
			/* Priority order is: out, in/out, in.
			 * "in" must always be executed last. */
			
			if($change["out"] instanceof Item){
				/* "in/out" transaction*/
				$priority = 1;
			}elseif($change["out"] === null){
				/* "in" transaction */
				$priority = 0;
			}else{
				// "out" change was not of type Item
				// Invalid transaction. Should never happen, but just in case.
				return false;
			}
		}elseif($change["in"] !== null){
			// "in" change was not of type Item
			// Invalid transaction. Should never happen, but just in case.
			return false;
		}
		
		//Add the transaction to the queue
		$this->queue->insert($transaction, $priority);
		return true;
	}
}