<?php
/**
* +---------------------------------------------------------------------------+
* | Copyright (c) 2015, Jort de Vreeze                                        |
* | All rights reserved.                                                      |
* |                                                                           |
* | Redistribution and use in source and binary forms, with or without        |
* | modification, are not permitted.                                          |
* +---------------------------------------------------------------------------+
* | jService 1.0                                                              |
* +---------------------------------------------------------------------------+
* | Trial.php                                                                 |
* +---------------------------------------------------------------------------+
* | Author: Jort de Vreeze <j.devreeze@iwm-tuebingen.de>                      |
* +---------------------------------------------------------------------------+
*/

class Model_Trial extends Base_Model
{
	
    public function addNewTrial()
    {   

        $date = new DateTime();
        $timestamp = $date->format('Y-m-d H:i:s');

        $result = $this->query(
            sprintf(
                "INSERT INTO %s (timestamp, active) VALUES ('%s', 1)", 
                $this->getTableName(), $timestamp
            )
         );

        return $result;
    }
	
	public function closeActiveTrials()
    {
		$result = $this->query(
            sprintf("UPDATE %s SET active = 0 WHERE active = 1", $this->getTableName())
         );
        
        return $result;
		
	}

    public function getActiveTrials()
    {
        $result = $this->query(
            sprintf("SELECT id FROM %s WHERE active = 1", $this->getTableName())
         );
        
		return (array) $result->fetch_object();
    }
    
    public function getTimestampForActiveTrials()
    {
        $result = $this->query(
            sprintf("SELECT timestamp FROM %s WHERE active = 1", $this->getTableName())
         );
        
        return $result->fetch_object();
    }

}
