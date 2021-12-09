<?php

/*********************************************************************************
 * This file is part of Myddleware.
 * @package Myddleware
 * @copyright Copyright (C) 2013 - 2015  Stéphane Faure - CRMconsult EURL
 * @copyright Copyright (C) 2015 - 2022  Stéphane Faure - Myddleware ltd - contact@myddleware.com
 * @link http://www.myddleware.com
 *
 * This file is part of Myddleware.
 *
 * Myddleware is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Myddleware is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Myddleware.  If not, see <http://www.gnu.org/licenses/>.
 *********************************************************************************/

namespace Myddleware\RegleBundle\Solutions;

use DateTime;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
class airtablecore extends solution {

	protected $sendDeletion = true;	
	
    protected $airtableURL = 'https://api.airtable.com/v0/';
    protected $metadataApiEndpoint = 'https://api.airtable.com/v0/meta/bases/';

    /**
     * Airtable base
     *
     * @var string
     */
    protected $projectID; 
    /**
     * API key (provided by Airtable)
     *
     * @var string
     */
    protected $token;
    protected $delaySearch = '-1 month';
    /**
     * Name of the table / module that will be used as the default table to access the login() method
     * This is initialised to 'Contacts' by default as I've assumed that would be the most common possible value.
     * However, this can of course be changed to any table value already present in your Airtable base
     * @var string
     */
    protected $tableName = 'Contacts';

    /**
     * From AirTable API doc : 
     * pageSize = 100 
     * maxRecords => if higher than pageSize, multiple pages must be loaded in request
     * @var integer
     */
    protected $defaultLimit = 100; 
	
    /**
     * Max number of records posted by call
     *
     * @var string
     */
    protected $callPostLimit = 10; 

    //Log in form parameters
    public function getFieldsLogin()
    {
        // QUESTION: could we possibly pass a MODULE here ? 
        // This would allow us to then only resort to variable in login etc
        // However it will obviously mean 1 connector per module/table, which is of course not ideal.
        return array(
            array(
                'name' => 'projectid',
                'type' => TextType::class,
                'label' => 'solution.fields.projectid'
            ),
            array(
                'name' => 'apikey',
                'type' => PasswordType::class,
                'label' => 'solution.fields.apikey'
            )
        );
    }

    /**
     * Request to attempt to log in to Airtable
     *
     * @param array $paramConnexion
     * @return void
     */
    public function login($paramConnexion) {
        parent::login($paramConnexion);
        try {
            $this->projectID = $this->paramConnexion['projectid'];
            $this->token =  $this->paramConnexion['apikey'];
            // We test the connection to the API with a request on Module/Table (change the value of tableName to fit your needs)
            $client = HttpClient::create();
            $options = ['auth_bearer' => $this->token];
            $response = $client->request('GET', $this->airtableURL.$this->projectID.'/'.$this->tableName, $options);
            $statusCode = $response->getStatusCode();
            $contentType = $response->getHeaders()['content-type'][0];
            $content = $response->getContent();
            $content = $response->toArray();
            if(!empty($content) && $statusCode === 200){
                $this->connexion_valide = true;
            }
        }catch(\Exception $e){
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $this->logger->error($error);
            return array('error' => $error);
        }
    }

    /**
     * Retrieve the list of modules available to be read or sent
     *
     * @param string $type source|target
     * @return array
     */
    public function get_modules($type = 'source') {
        /**
         * These modules MUST BE HARDCODED in order for the connector to work as 
         * Airtable modules are 100% custom
         * e.g. return array('Contacts' => 'Contacts');
         */
        return array();
    }

    /**
     * Retrieve the list of fields (metadata) associated to each module
     *
     * @param string $module
     * @param string $type
     * @return array
     */
    public function get_module_fields($module, $type = 'source') {
        require_once('lib/airtable/metadata.php');
        parent::get_module_fields($module, $type);
        try {
            if(!empty($moduleFields[$module])){
                $this->moduleFields = $moduleFields[$module];
            }

            if (!empty($fieldsRelate[$module])) {
				$this->fieldsRelate = $fieldsRelate[$module]; 
			}	

            if (!empty($this->fieldsRelate)) {
				$this->moduleFields = array_merge($this->moduleFields, $this->fieldsRelate);
			}

            return $this->moduleFields;
        }catch(\Exception $e){
            $this->logger->error($e->getMessage().' '.$e->getFile().' '.$e->getLine());		
			return false;
        }
    }

    /**
     * Read records in source application & transform them to fit standard Myddleware format
     *
     * @param array $param
     * @return array
     */
    public function read($param){
        try {			
            $baseID = $this->paramConnexion['projectid'];
            $module = $param['module'];
            $result = [];
            $result['count'] = 0;
            $result['date_ref'] = $param['ruleParams']['datereference'];
            if(empty($param['limit'])){
                $param['limit'] = $this->defaultLimit;
            }
            // Remove Myddleware's system fields
			$param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
            // Add required fields
			$param['fields'] = $this->addRequiredField($param['fields'],$module);
            $stop = false;
            $count = 0;
            $page = 1;
            do {
                $client = HttpClient::create();
                $options = ['auth_bearer' => $this->token];
                //specific record requested
                if(!empty($param['query'])){
                    if (!empty($param['query']['id'])) {
                        $id = $param['query']['id'];			
                        $response = $client->request('GET', $this->airtableURL.$baseID.'/'.$module.'/'.$id, $options);
                        $statusCode = $response->getStatusCode();
                        $contentType = $response->getHeaders()['content-type'][0];
                        $content2 = $response->getContent();
                        $content2 = $response->toArray();
                        // Add a dimension to fit with the rest of the method
                        $content['records'][] = $content2;
                    } else {
                        // Filter by specific field (for example to avoid duplicate records)
                        foreach($param['query'] as $key => $queryParam){
                            // TODO: improve this, for now we can only filter with ONE key, 
                            // we should be able to add a variety (but this would need probably a series of 'AND() / OR() query params)
                            $response = $client->request('GET', $this->airtableURL.$baseID.'/'.$module.'?filterByFormula={'.$key.'}="'.$queryParam.'"', $options);
                            $statusCode = $response->getStatusCode();
                            $contentType = $response->getHeaders()['content-type'][0];
                            $content = $response->getContent();
                            $content = $response->toArray();        				
                        }
                    }
                } else {
                    // all records
                    $response = $client->request('GET', $this->airtableURL.$baseID.'/'.$module, $options);
                    $statusCode = $response->getStatusCode();
                    $contentType = $response->getHeaders()['content-type'][0];
                    $content = $response->getContent();
                    $content = $response->toArray();
                }							
                if(!empty($content['records'])){
                    $currentCount = 0;
                    //used for complex fields that contain arrays
                    $content = $this->convertResponse($param, $content['records']);  
                    foreach($content as $record){
                        $currentCount++;
                        foreach($param['fields'] as $field){
							if (!empty($record['fields'][$field])) {
								// Depending on the field type, the result can be an array, in this case we take the first result
								if (is_array($record['fields'][$field])) {
									$result['values'][$record['id']][$field] = current($record['fields'][$field]);
								} else {
									$result['values'][$record['id']][$field] = $record['fields'][$field];
								}
							}
                        }
                        // TODO: FIND AN ALTERNATIVE TO THIS => records DO NOT HAVE A DATE MODIFIED ATTRIBUTE for now if date_modified doesn't exist, we set it to NOW (which ofc isn't viable)
                        // $dateTime = new DateTime();
                        $dateModif = (!empty($record['fields']['date_modified'])) ? $record['fields']['date_modified'] : $record['createdTime'];
                        $result['values'][$record['id']]['date_modified'] = $this->dateTimeToMyddleware($dateModif);
                        $result['values'][$record['id']]['id'] = $record['id'];
                    }
                    $result['count']++;
                    $count++;
                } else {
                    $stop = true;
                }
                $page++;
            } while(!$stop && $currentCount === $this->defaultLimit) ;
        } catch (\Exception $e){
            $result['error'] = 'Error : '.$e->getMessage().' '.$e->getFile().' Line : ( '.$e->getLine().' )';	  
            $this->logger->error($e->getMessage().' '.$e->getFile().' '.$e->getLine());
        }			
        return $result;
    }

    /**
     * Reads the last inserted record (latest) from the source application
     *
     * @param array $param
     * @return array
     */
    public function read_last($param){
        $result = array();
        try{
            //for simulation purposes, we create a new date_ref in the past
            $param['ruleParams']['datereference'] = date('Y-m-d H:i:s', strtotime($this->delaySearch));
            //get all instances of the module
            $read = $this->read($param);
            if (!empty($read['error'])) {
                $result['error'] = $read['error'];
            } else {
                if (!empty($read['values'])) {
                    $result['done'] = true;
                    // Get only one record (the API sorts them by date by default, first one is therefore latest modified)
                    $result['values'] = $read['values'][array_key_first($read['values'])]; 
                } else {
                    $result['done'] = false;
                }
            }
        } catch (\Exception $e) {
            $result['error'] = 'Error : '.$e->getMessage().' '.$e->getFile().' Line : ( '.$e->getLine().' )';
			$result['done'] = -1;
            $this->logger->error($e->getMessage().' '.$e->getFile().' '.$e->getLine());	
        }
        return $result;
    }

    /**
     * Create data into target app
     *
     * @param array $param
     * @return void
     */
    public function create($param){
        return $this->upsert('create', $param);
    }

    /**
     * Update existing data into target app
     *
     * @param [type] $param
     * @return void
     */
    public function update($param){
        return $this->upsert('update', $param);
    }

    /**
     * Insert or update data depending on method's value
     *
     * @param string $method create|update
     * @param array $param
     * @return void
     */ 
    public function upsert($method, $param){		
		// Init parameters
		$baseID = $this->paramConnexion['projectid'];
		$result= array();
		$param['method'] = $method;
		$module = ucfirst($param['module']);
		
		// trigger to add custom code if needed
		$data = $this->checkDataBeforeCreate($param, $param['data']);
		
        /**
         * In order to load relationships, we MUST first load all fields
         */
        $allFields = $this->get_module_fields($param['module'], 'source');
        $relationships = $this->get_module_fields_relate($param['module'], 'source');
		
		
		// Group records for each calls
		// Split the data into several array using the limite size
		$recordsArray = array_chunk($param['data'], $this->callPostLimit, true);	
		foreach($recordsArray as $records) {			
			// Airtable expects data to come in a 'records' array
			$body = [];
			$body['typecast'] = true;
			$body['records']= array();
			$i = 0;
			try{
				foreach($records as $idDoc => $data){
					if($method === 'create'){
						unset($data['target_id']);
					}
					// Myddleware_element_id is a field only used by Myddleware. Not sent to the target application
					if (!empty($data['Myddleware_element_id'])) {
						unset($data['Myddleware_element_id']);
					}
					$body['records'][$i]['fields'] = $data;
					/**
					 * Add dimensional array for relationships fields as Airtable expects arrays of IDs
					 */
					foreach($body['records'][$i]['fields'] as $fieldName => $fieldVal){
						if(array_key_exists($fieldName, $relationships)){
							$arrayVal = [];
							$arrayVal[] = $fieldVal;
							$body['records'][$i]['fields'][$fieldName] = $arrayVal;
						}
					}
					// Add the record id in the body if update 
					if($method === 'update'){
						$body['records'][$i]['id'] = $data['target_id'];
						unset($body['records'][$i]['fields']['target_id']);
					}
					$i++;
				}			
				// Send records to Airtable
				$client = HttpClient::create();
				$options = [
					'auth_bearer' => $this->token,
					'json' => $body,
					'headers' => ['Content-Type' => 'application/json']
				];					
				// POST or PATCH depending on the method
				if($method === 'create'){
					$response = $client->request('POST', $this->airtableURL.$baseID.'/'.$module, $options);
				} else {
					$response = $client->request('PATCH', $this->airtableURL.$baseID.'/'.$module, $options);
				}				
				$statusCode = $response->getStatusCode();
				$contentType = $response->getHeaders()['content-type'][0];
				$content = $response->getContent();
				$content = $response->toArray();										
				if(!empty($content)){
					$i = 0;
					foreach($records as $idDoc => $data){
						$record = $content['records'][$i];
						if(!empty($record['id'])){
							$result[$idDoc] = array(
													'id' => $record['id'],
													'error' => false
											);
						} else {
							$result[$idDoc] = array(
								'id' => '-1',
								'error' => 'Failed to send data. Message from Airtable : '.print_r($content['records'][$i],true)
							);
						}
						$i++;
						// Modification du statut du flux
						$this->updateDocumentStatus($idDoc,$result[$idDoc],$param);
					}
				} else {
					throw new \Exception('Failed to send the record but no error returned by Airtable. ');
				}
				
			}catch(\Exception $e){
				$error = $e->getMessage();
				foreach($records as $idDoc => $data){
					$result[$idDoc] = array(
						'id' => '-1',
						'error' => $error
					);
					$this->updateDocumentStatus($idDoc,$result[$idDoc],$param);
				}
				$this->logger->error($e->getMessage().' '.$e->getFile().' '.$e->getLine());
			} 
		}			
        return $result;
    }


    protected function convertResponse($param, $response) {

        return $response;
    }

    // Convert date to Myddleware format 
	// 2020-07-08T12:33:06 to 2020-07-08 12:33:06
	protected function dateTimeToMyddleware($dateTime) {
		$dto = new \DateTime($dateTime);
		return $dto->format("Y-m-d H:i:s");  //TODO: FIND THE EXACT FORMAT : 2015-08-29T07:00:00.000Z
	}
	
    //convert from Myddleware format to Woocommerce format
	protected function dateTimeFromMyddleware($dateTime) {
		$dto = new \DateTime($dateTime);
		return $dto->format('Y-m-d\TH:i:s');
	}

}

// Include custom file if it exists : used to redefine Myddleware standard core
$file = __DIR__. '/../Custom/Solutions/airtable.php';
if(file_exists($file)){
    require_once($file);
} else { 
    class airtable extends airtablecore {

    }
}