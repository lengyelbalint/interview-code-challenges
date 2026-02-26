<?php

class BookStock
{
    public $id;
    public $bookId;
    public $isOnLoan;
    public $loanEndDate;
    public $borrowerId;

    public function __construct($id, $bookId, $isOnLoan = false, $loanEndDate = null, $borrowerId = null)
    {
        $this->id = $id;
        $this->bookId = $bookId;
        $this->isOnLoan = $isOnLoan;
        $this->loanEndDate = $loanEndDate;
        $this->borrowerId = $borrowerId;
    }
}
