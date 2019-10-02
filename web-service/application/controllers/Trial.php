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
* | Trial.php                                                                 |
* +---------------------------------------------------------------------------+
* | Author: Jort de Vreeze <j.devreeze@iwm-tuebingen.de>                      |
* +---------------------------------------------------------------------------+
*/

/*
 * Session controller
 */
class Controller_Trial extends Base_Controller
{

    /**
     * Default action
     * 
     * @return void 
     */
    public function index()
    {   
        
    }
    
	/**
     * Create a new trial
     * 
     * @return void 
     */
    public function start()
    {
        $this->doNotRender();
		
		if (false == $this->isPost()) {
            $this->redirect('index', 'trial');
        } else {            

			$key = filter_input(INPUT_POST, 'key');  
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				$this->redirect('index', 'trial', array('error' => 1));
			}
			   
			$trialModel = new Model_Trial($this->getConfiguration('model'));
			$subjectModel = new Model_Subject($this->getConfiguration('model'));
			
			$trialModel->closeActiveTrials();
			$trialModel->addNewTrial(); 
        }
        
		$this->redirect('index', 'trial');  
    }
	
    /**
     * Add a subject to the trial (AJAX)
     * 
     * @return void 
     */
    public function add()
    {
        $this->doNotRender();

		if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				echo json_encode(array('status' => 1));
				
			} else {
			
				$name = filter_input(INPUT_POST, 'name');
				
				if (null === $name) {
					$name = '';
				}
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));
				$subjectModel = new Model_Subject($this->getConfiguration('model'));
				
				$trial = $trialModel->getActiveTrials();

				if (count($trial) != 1) {
					
					http_response_code(404);
					echo json_encode(array('status' => 1));
					
				} else {
					
					$subjectModel->addSubject(current($trial), $name);
					
					http_response_code(200);
					echo json_encode(array('status' => 0, 'subject' => $subjectModel->getLastId()));
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');        
		}		
    }
	
	/**
     * Check how long ago the trial was activated (AJAX)
     * 
     * @return void 
     */
    public function age()
    {
        $this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				echo json_encode(array('status' => 1));
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));
				$trial = $trialModel->getTimestampForActiveTrials();
				
				if (count((array)$trial) != 1) {
					
					http_response_code(404);
					echo json_encode(array('status' => 1));
					
				} else {
					
					$dateFrom = new DateTime($trial->timestamp);
					$dateTo = new DateTime();					
					$minutes = round(($dateTo->getTimestamp() - $dateFrom->getTimestamp()) / 60);
			
					http_response_code(200);
					echo json_encode(array('status' => 0, 'start' => $dateFrom->format('Y-m-d H:i:s'), 'current' => $dateTo->format('Y-m-d H:i:s'), 'difference' => $minutes));
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}
		        
    }
	
	/**
     * Check if all subjects are assigned (AJAX)
     * 
     * @return void 
     */
    public function assigned()
    {
        $this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));
				$trial = $trialModel->getActiveTrials();
				
				if (count($trial) != 1) {
					
					http_response_code(404);
					$code = 1;
					
				} else {
					
					$subjectModel = new Model_Subject($this->getConfiguration('model'));
					$notAssigned = $subjectModel->getSubjectCount(1);		
					
					$ready = ($notAssigned > 1) ? 0 : 1;
					
					http_response_code(200);
					$code = 0;
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}
		
		echo json_encode(array('status' => $code, 'assigned' => $ready));
        
    }

    /**
     * Get the number of participants in the current trial (AJAX)
     * 
     * @return void 
     */
    public function number()
    {
		$this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));
				$trial = $trialModel->getActiveTrials();
				
				if (count($trial) != 1) {
					
					http_response_code(404);
					$code = 1;
					
				} else {
					
					$subjectModel = new Model_Subject($this->getConfiguration('model'));
					$assigned = $subjectModel->getSubjectCount(0);
					$total = $assigned + $subjectModel->getSubjectCount(1);
					
					http_response_code(200);
					$code = 0;
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}
		
		echo json_encode(array('status' => $code, 'total' => $total, 'assigned' => $assigned));           
    }	
		
	/**
     * Set the value for a participant (AJAX)
     * 
     * @return void 
     */
    public function value()
    {
        $this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$subject = filter_input(INPUT_POST, 'subject');
				$value = filter_input(INPUT_POST, 'value');
				
				if (null !== $subject && null !== $value) {		
				
					$subjectModel = new Model_Subject($this->getConfiguration('model'));
					$subjectModel->addValueToSubject($subject, $value);
					
					http_response_code(200);
					$code = 0;
					
				} else {
					
					http_response_code(404);
					$code = 1;
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}
		
		echo json_encode(array('status' => $code));
    }
	
	/**
     * Return the matching subject (AJAX)
     * 
     * @return void 
     */
    public function partner()
    {
        $this->doNotRender();
		
		if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));				
				$trial = $trialModel->getActiveTrials();
				
				if (count($trial) != 1) {
					
					http_response_code(404);
					$code = 1;
					
				} else {
				
					$subject = filter_input(INPUT_POST, 'subject');
					
					if (null !== $subject) {	
					
						$dyadModel = new Model_Dyad($this->getConfiguration('model'));
						$partner = $dyadModel->getPartner(current($trial), $subject);
						
						if (false == $partner) {
							
							http_response_code(404);
							$code = 1;
							
						}
						
						$partnerId = ($partner->l == $subject) ? $partner->r : $partner->l;
						$partnerSession = $partner->session;
						
						http_response_code(200);
						$code = 0;
						
					} else {
						
						http_response_code(404);
						$code = 1;
						
					}
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}		
		
        echo json_encode(array('status' => $code, 'partner' => $partnerId, 'session' => $partnerSession)); 
               
    }
	
	/**
     * Check if a subject is ready (AJAX)
     * 
     * @return void 
     */
    public function ready()
    {
        $this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');           
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$method = filter_input(INPUT_POST, 'method');
				$subject = filter_input(INPUT_POST, 'subject');
					
				if (null !== $subject) {
					
					$subjectModel = new Model_Subject($this->getConfiguration('model'));
					
					if ($method == 'set') {
						$subjectModel->setSubjectAsReady($subject);
					}
					
					$ready = $subjectModel->isSubjectReady($subject);
					
					http_response_code(200);
					$code = 0;
					
				} else {
					
					http_response_code(404);
					$code = 1;
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}
		
		echo json_encode(array('status' => $code, 'ready' => $ready));
        
    }
	
	/**
     * Match all participants together
     * 
     * @return void 
     */
	public function match()
    {
        $this->doNotRender();
		
        if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));
				$subjectModel = new Model_Subject($this->getConfiguration('model'));
				$dyadModel = new Model_Dyad($this->getConfiguration('model'));
				
				$trial = $trialModel->getActiveTrials();
				
				if (count($trial) != 1) {
					
					http_response_code(404);
					$code = 1;
					
				} else {
					
					$trialId = current($trial);
					
					/* 
					 * Check if matching took place already took place 
					 */
					if (0 == $trialModel->isActiveTrialReady()) { 
					
						$subjects = $subjectModel->getUnassignedSubjects($trialId);
						$distribution = $subjectModel->getConditionCount();
						
						$conditions = range(1, $this->getConfiguration('conditions')['number']);
						$conditions = array_fill_keys($conditions, 0);
						
						$left = $right = array();
						$split = $this->getConfiguration('conditions')['range'] / 2;

						while ($row = $subjects->fetch_object()){
							if ($row->value > $split) {
								$right[$row->id] = $row->value;
							} else {
								$left[$row->id] = $row->value;
							}
						}
						
						$size = count($left) + count($right);
						
						if ($size > 1) {		

							if (!empty($distribution)) {
								while ($row = $distribution->fetch_object()){
									$conditions[$row->c] = $row->frequency;
								}						
							}
												
							$i = 0;
							while ($i < $size) {
								
								$flag = false;
								
								$leftCount = count($left);
								$rightCount = count($right);
								
								if ($rightCount > 1) {
									
									if ($conditions[4] <= ($conditions[2] + $conditions[3]) || $leftCount == 0) {

										$subject = array_keys($right);
										
										$subjectModel->assignSubject($subject[0], 4); 
										$subjectModel->assignSubject($subject[1], 4); 
										
										$dyadModel->createDyad($trialId, $subject[0], $subject[1]);
										
										$conditions[4] += 2;
										$right = array_slice($right, 2, count($right), true); 
										
									} else {								
										$flag = true;															
									}
									
								} else if ($rightCount == 1 && $leftCount >= 1) {
									$flag = true;
								} else if ($leftCount > 1) {
									
									$subject = array_keys($left);
										
									$subjectModel->assignSubject($subject[0], 1);
									$subjectModel->assignSubject($subject[1], 1);
									
									$dyadModel->createDyad($trialId, $subject[0], $subject[1]);
									
									$conditions[1] += 2;
									$left = array_slice($left, 2, count($left), true);
									
								}
								
								if (true == $flag) {
									
									if ($conditions[2] < $conditions[3]) {
										$number = 2;								
										$conditions[$number] += 2;
									} else {
										$number = 3;	
										$conditions[$number] += 2;
									}
									
									$subject = [array_keys($left)[0], array_keys($right)[0]];
									
									$subjectModel->assignSubject($subject[0], $number);
									$subjectModel->assignSubject($subject[1], $number);
									
									$dyadModel->createDyad($trialId, $subject[0], $subject[1]);

									$left = array_slice($left, 1, count($left), true); 								
									$right = array_slice($right, 1, count($right), true);
									
								}
							
								$i += 2;

							}							
							
						}
						
						/* 
						 * Update the model that matching took place 
						 */
						$trialModel->setActiveTrialAsReady();
					
					}
					
					http_response_code(200);
					$code = 0;
					
				}
				
			}
			
		} else {
			$this->redirect('index', 'trial');  
		}
        
        echo json_encode(array('status' => $code));      
    }

	/**
     * Return all the details of the trial, subject and dyad (AJAX)
     * 
     * @return void 
     */
    public function details()
    {
        $this->doNotRender();
		
		if (true === $this->isAjaxRequest() || true === $this->isPost()) {
			
			header("Access-Control-Allow-Origin: *");
			header("Content-Type: application/json; charset=UTF-8");
			
			$key = filter_input(INPUT_POST, 'key');
			
			if ($this->getConfiguration('secret_keys')['trial'] !== $key) {
				
				http_response_code(401);
				$code = 1;
				
			} else {
				
				$trialModel = new Model_Trial($this->getConfiguration('model'));				
				$trial = $trialModel->getActiveTrials();

				if (count($trial) != 1) {
					
					http_response_code(404);
					$code = 1;
					
				} else {
					
					$trialId = current($trial);
					$activated = $trialModel->getTimestampForActiveTrials();				
					
					$subjectModel = new Model_Subject($this->getConfiguration('model'));
					$dyadModel = new Model_Dyad($this->getConfiguration('model'));
					
					$subjectId = filter_input(INPUT_POST, 'subject');
					
					if (null === $subjectId) {
					
						$subjects = $subjectModel->getAllSubjects($trialId);

						if ($subjects->num_rows > 0) {
							
							while ($row = $subjects->fetch_object()){
								
								$partner = $dyadModel->getPartner($trialId, $row->id);
																
								$subject['TrialId'] = $trialId;
								$subject['Activated'] = $activated->timestamp;
								
								$subject['SubjectId'] = $row->id;
								$subject['Name'] = $row->name;
								$subject['Timestamp'] = $row->timestamp;
								$subject['Value'] = $row->value;
								$subject['Assigned'] = $row->assigned;
								$subject['Condition'] = $row->c;
								$subject['Ready'] = $row->ready;

								$subject['PartnerId'] = ($partner->l == $row->id) ? $partner->r : $partner->l;
								$subject['Session'] = $partner->session;

								$subject['Threshold'] = $subjectModel->getNumberOfSubjectsInCondition($row->c);
								
								$details[] = $subject;
								
							}
							
						}
						
					} else {
						
						$subject = $subjectModel->getSubject($subjectId);
						$partner = $dyadModel->getPartner($trialId, $subjectId);
								
						$details['TrialId'] = $trialId;
						$details['Activated'] = $activated->timestamp;
						
						$details['SubjectId'] = $subjectId;
						$details['Name'] = $subject->name;
						$details['Timestamp'] = $subject->timestamp;
						$details['Value'] = $subject->value;
						$details['Assigned'] = $subject->assigned;
						$details['Condition'] = $subject->c;
						$details['Ready'] = $subject->ready;

						$details['PartnerId'] = ($partner->l == $subjectId) ? $partner->r : $partner->l;
						$details['Session'] = $partner->session;

						$details['Threshold'] = $subjectModel->getNumberOfSubjectsInCondition($subject->c);
						
					}
					
					http_response_code(200);
					$code = 0;
					
				}
			}
			
		} else {
			$this->redirect('index', 'trial');
		}		
		
        echo json_encode(array('status' => $code, 'details' => $details));
               
    }	
    
}
