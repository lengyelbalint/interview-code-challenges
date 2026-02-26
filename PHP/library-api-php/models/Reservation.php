<?php

class Reservation
{
    public $id;
    public $bookId;
    public $borrowerId;
    public $reservedAt;
    public $status;

    public function __construct($id, $bookId, $borrowerId, $reservedAt, $status = 'active')
    {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->borrowerId = $borrowerId;
        $this->reservedAt = $reservedAt;
        $this->status = $status;
    }
}