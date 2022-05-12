<?php
/*********************************************************************************
 * This file is part of Myddleware.

 * @package Myddleware
 * @copyright Copyright (C) 2013 - 2015  Stéphane Faure - CRMconsult EURL
 * @copyright Copyright (C) 2015 - 2016  Stéphane Faure - Myddleware ltd - contact@myddleware.com
 * @link http://www.myddleware.com

 This file is part of Myddleware.

 Myddleware is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.

 Myddleware is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Myddleware.  If not, see <http://www.gnu.org/licenses/>.
*********************************************************************************/

namespace App\Solutions;

use Exception;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

/**
 * TODO: We need to find a way to use the events triggered via the YouSign WebHooks
 * and possibly use those instead of datereference as a trigger to read files data (& then download the PDF files)?
 * Or pass updatedAT property from the /files/{id} endpoint while actually targettting /files/{id}/download
 */
class yousigncore extends solution
{
    protected $callLimit = 20;
    // Enable to read deletion and to delete data
    protected $readDeletion = true;
    protected $sendDeletion = true;
    /**
     * All YouSign API calls go through the same URL, it is the API Key only which determines which API we're using
     * and serves as auth credentials.
     * Therefore, the user only needs to choose whether they want to use the prod or the staging environment.
     * A possible enhancement could be to display a select button only (instead of a string input for now) in which
     * the user simply chooses between the prod or the staging URL.
     * This could be achieved by copying the bheaviour in Salesforce connector (with sandbox checkbox)
     *
     * @var string
     */
    protected $prodBaseUrl = 'https://api.yousign.com';
    protected $stagingBaseUrl = 'https://staging-api.yousign.com';

    protected $required_fields = ['default' => ['id', 'updatedAt', 'createdAt']];

    public function getFieldsLogin()
    {
        return [
            [
                'name' => 'url',
                'type' => TextType::class,
                'label' => 'solution.fields.url',
            ],
            [
                'name' => 'apikey',
                'type' => PasswordType::class,
                'label' => 'solution.fields.apikey',
            ],
        ];
    }

    public function login($paramConnexion)
    {
        parent::login($paramConnexion);
        try {
            if (empty($this->paramConnexion['url'])) {
                $this->paramConnexion['url'] = $this->stagingBaseUrl;
            }
            $apiKey = $this->paramConnexion['apikey'];
            // For now, in order to test we're correctly logged in to the API, we call a random endpoint since there's no 'login_check' endpoint
            $endpoint = 'organizations';
            $parameters['apikey'] = $apiKey;
            $parameters['endpoint'] = $endpoint;
            $result = $this->call($this->paramConnexion['url'], $parameters);
            if (!empty($result)) {
                $this->connexion_valide = true;
            } elseif (empty($result)) {
                throw new \Exception('Failed to connect but no error returned by YouSign. ');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $this->logger->error($error);

            return ['error' => $error];
        }
    }

    public function get_modules($type = 'source')
    {
        return [
            'users' => 'Users',
            'files' => 'Files',
            'download_files' => 'Download Files',
            'procedures' => 'Procedures',
            'members' => 'Members',
        ];
    }

    public function get_module_fields($module, $type = 'source', $param = null)
    {
        parent::get_module_fields($module, $type);
        try {
            //Use yousign metadata !just for user and procedure! to review
            require 'lib/yousign/metadata.php';
            if (!empty($moduleFields[$module])) {
                $this->moduleFields = array_merge($this->moduleFields, $moduleFields[$module]);
            }

            if (!empty($fieldsRelate[$module])) {
                $this->fieldsRelate = $fieldsRelate[$module];
            }

            if (!empty($this->fieldsRelate)) {
                $this->moduleFields = array_merge($this->moduleFields, $this->fieldsRelate);
            }

            return $this->moduleFields;
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $this->logger->error($error);
            return false;
        }
    }

    public function read($param)
    {
        try {
            $result = [];
            $module = $param['module'];
            $param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
            $param['fields'] = $this->addRequiredField($param['fields'], $param['module'], $param['ruleParams']['mode']);
            if (empty($param['limit'])) {
                $param['limit'] = $this->callLimit;
            } else {
                if ($param['limit'] < $this->callLimit) {
                    $this->callLimit = $param['limit'];
                }
            }
            $stop = false;
            $count = 0;
            $page = 1;
            $content = [];
            $endpoint = $module;
            if ($module === 'download_files'){
                $module = 'files';
                $endpoint = $module.'/{id}/download';
            } 
            $this->paramConnexion['endpoint'] = $endpoint;
            try {
                $response = $this->call($this->paramConnexion['url'], $this->paramConnexion);
                $response = json_decode($response);
                if (!empty($response)){
                    $result['values'] = [];
                    $currentCount = 0;
                    foreach($response as $record){
                        ++$currentCount;
                        foreach ($param['fields'] as $field) {
                            // dd($param);
                            // $result['values'][$record->id]['date_modified'] = date('Y-m-d H:i:s');
                            $result['values'][$record->$field] = (!empty($record->$field) ? $record->$field : '');
                            $result['date_modified'] = (!empty($record->$field) ? $record->$field : '');
                        }
                        $result['values'][$record['id']]['id'] = $record['id'];
                        ++$result['count'];
                        ++$count;
                    }
                }
            } catch(Exception $e){
                $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
                $result['error'] = $error;
                $this->logger->error($error);
            }
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $result['error'] = $error;
            $this->logger->error($error);
            return false;
        }
    }

    protected function call($url, $parameters)
    {
        try {
            $endpoint = "";
            $url = $this->stagingBaseUrl;
            $apiKey = "";
            if (!empty($parameters['url'])) {
                $url = $parameters['url'];
            }
            if (!empty($parameters['endpoint'])) {
                $endpoint = $parameters['endpoint'];
            }
            if (!empty($parameters['apikey'])) {
                $apiKey = $parameters['apikey'];
            }

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $url.'/'.$endpoint,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer '.$apiKey,
                    'Content-Type: application/json',
                ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
                $this->logger->error($err);
                throw new \Exception('cURL Error #: '.$err);
            }
            return $response;
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $this->logger->error($error);
            return false;
        }
    }

    	// Renvoie le nom du champ de la date de référence en fonction du module et du mode de la règle
	public function getRefFieldName($moduleSource, $ruleMode) {
		if(in_array($ruleMode,array("0","S"))) {
			return "updatedAt";
		} else if ($ruleMode == "C"){
			return "createdAt";
		} else {
			throw new \Exception ("$ruleMode is not a correct Rule mode.");
		}
		return null;
	}
}

class yousign extends yousigncore
{
}
