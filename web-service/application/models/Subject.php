<?php
/**
* +---------------------------------------------------------------------------+
* | Copyright (c) 2019, Jort de Vreeze                                        |
* | All rights reserved.                                                      |
* |                                                                           |
* | Redistribution and use in source and binary forms, with or without        |
* | modification, are not permitted.                                          |
* +---------------------------------------------------------------------------+
* | jService 1.0                                                              |
* +---------------------------------------------------------------------------+
* | Subject.php                                                               |
* +---------------------------------------------------------------------------+
* | Author: Jort de Vreeze <j.devreeze@iwm-tuebingen.de>                      |
* +---------------------------------------------------------------------------+
*/

class Model_Subject extends Base_Model
{

    public function addSubject($trial, $name)
    {   
        
        $date = new DateTime();
        $timestamp = $date->format('Y-m-d H:i:s');         

        $result = $this->query(
            sprintf(
                "INSERT INTO %s (trial_id, name, timestamp, assigned) VALUES (%d, '%s', '%s', '0')", 
                $this->getTableName(), $trial, $name, $timestamp
            )
        );

        return $result;
    }
	
	public function addValueToSubject($id, $value)
    {     
        
        $result = $this->query(
            sprintf(
                "UPDATE %s SET value = %s WHERE id = %d", 
                $this->getTableName(), $value, $id
            )
        );

        return $result;
    }
    
    public function assignSubject($subject, $condition)
    {   
        
        $result = $this->query(
            sprintf(
				"UPDATE %s SET assigned = 1, c = %d WHERE id = %d", 
				$this->getTableName(), $condition, $subject
			)
        );

        return $result;
    }
	
	public function getSubject($subject)
    {   
        
        $result = $this->query(
            sprintf("SELECT * FROM %s WHERE id = %d", 
            $this->getTableName(), $subject)
        );

        if (1 != $result->num_rows) {
			return false;
		}
		
		return $result->fetch_object();
    }
	
	public function getSubjectName($subject)
    {   
        
        $result = $this->query(
            sprintf("SELECT name FROM %s WHERE id = %d", 
            $this->getTableName(), $subject)
        );

		if (false != $result && $result->num_rows > 0) {
			while ($row = $result->fetch_object()){
				return $row->name;
			}
		}
		
		return '';

    }
	
	public function getAllSubjects($trial)
    {   
        
        $result = $this->query(
            sprintf("SELECT * FROM %s WHERE trial_id = %d ORDER BY id", 
            $this->getTableName(), $trial)
        );

        return $result;
    }
    
    public function getUnassignedSubjects($trial)
    {   
        
        $result = $this->query(
            sprintf("SELECT * FROM %s WHERE trial_id = %d AND assigned = 0 ORDER BY id", 
            $this->getTableName(), $trial)
        );

        return $result;
    }
	
	public function hasUnassignedSubjects($trial)
    {   
        
		$subjects = $this->getUnassignedSubjects($trial);
        
		if ($subjects->num_rows == 0 || null == $subjects) {
			return false;
		}
		
		return true;
    }
	
	public function getConditionCount()
    {   
        
        $result = $this->query(
            sprintf("SELECT c, COUNT(*) AS frequency FROM %s WHERE c != 0 GROUP BY c", 
            $this->getTableName())
        );
        
        return $result;
    }
	
	public function getNumberOfSubjectsInCondition($condition)
    {   
        
        $result = $this->query(
            sprintf("SELECT COUNT(*) AS frequency FROM %s WHERE c = %d", 
            $this->getTableName(), $condition)
        );
        
	while ($row = $result->fetch_object()){
		return $row->frequency;
	}

    }
	
	public function getSubjectCount($assigned)
    {   
        
        $result = $this->query(
            sprintf("SELECT id, COUNT(*) AS frequency FROM %s WHERE assigned != %d", 
            $this->getTableName(), $assigned)
        );
        
		while ($row = $result->fetch_object()){
			return $row->frequency;
		}	

    }
	
	public function setSubjectAsReady($subject)
    {   
        
        $result = $this->query(
            sprintf(
				"UPDATE %s SET ready = 1 WHERE id = %d", 
				$this->getTableName(), $subject
			)
        );

        return $result;

    }
	
	public function isSubjectReady($subject)
    {   
        
        $result = $this->query(
            sprintf("SELECT ready FROM %s WHERE id = %d", 
            $this->getTableName(), $subject)
        );

		if (false != $result && $result->num_rows > 0) {
			while ($row = $result->fetch_object()){
				return $row->ready;
			}
		}
		
		return 0;

    }

    
}
