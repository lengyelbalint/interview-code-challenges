<?php

class Fine
{
    public $id;
    public $borrowerId;
    public $amount;
    public $details;
    public $createdAt;

    public function __construct($id, $borrowerId, $amount, $details = '', $createdAt = null)
    {
        $this->id = $id;
        $this->borrowerId = $borrowerId;
        $this->amount = $amount;
        $this->details = $details;
        $this->createdAt = $createdAt ?? date('c');
    }
}