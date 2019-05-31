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
* | Dyad.php                                                                  |
* +---------------------------------------------------------------------------+
* | Author: Jort de Vreeze <j.devreeze@iwm-tuebingen.de>                      |
* +---------------------------------------------------------------------------+
*/

class Model_Dyad extends Base_Model
{
	
    public function createDyad($trial, $left, $right)
    {   
	
        $result = $this->query(
            sprintf(
                "INSERT INTO %s (trial_id, session, l, r) VALUES (%d, '%s', %d, %d)", 
                $this->getTableName(), $trial, uniqid(),  $left, $right
            )
        );
		
		print_r($this->error());

        return $result;
    }

    public function getPartner($trial, $subject)
    {	
		
		$result = $this->query(
            sprintf(
				"SELECT * FROM %s WHERE trial_id = %d AND (l = %d OR r = %d)", 
				$this->getTableName(), $trial, $subject, $subject 
			)
        );
		
		if (1 != $result->num_rows) {
			return false;
		}
		
		return $result->fetch_object();		

    }


}
