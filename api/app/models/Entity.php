<?php

class Entity extends Eloquent {

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'entity';

	/**
	 * Simple primary key
	 * @var int
	 */
	private $id;


	/**
	 * Body is a zlib compressed json string.
	 * @var string
	 */
	private $body;


	public function customer() {
		return $this->belongsTo('Customer');
	}

	
	/**
	 *
	 * @param $body 
	 * @return
	 */
	public function setBody($body) {
		//check if json is valid
		
		
		$compressed_body = gzencode($body, 9);
		if(!$compressed_body){
			throw new Exception("An error occured while compressing body.");
		}else{
			$this->body = $compressed_body;
		}

	}

	/**
	 *
	 * @return $body
	 */
	public function getBody() {
		return gzdecode($this->body);
	}
}


?>