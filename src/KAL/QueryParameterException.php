<?php

class KAL_QueryParameterException extends Exception
{
    private $query;

    public function __construct($query, $message)
    {
        parent::__construct($message." Query: ".$query);
        $this->query = $query;
    }

    public function getQuery()
    {
        return $this->query;
    }

}

?>

