<?php

declare(strict_types=1);

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

use stdClass;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

/**
 * YouSign API Swagger documentation : https://swagger.yousign.com/
 * YouSign API Dev documentation : https://dev.yousign.com/
 * As per YouSign API doc, there's no native way to switch between prod & staging (sandbox), for further info :
 * https://dev.yousign.com/#environments
 * https://dev.yousign.com/#323c7029-0d92-46a7-b10e-3d409147831e
 * 
 * TODO: We need to find a way to use the events triggered via the YouSign WebHooks
 * and possibly use those instead of datereference as a trigger to read files data (& then download the PDF files)?
 * Or pass updatedAT property from the /files/{id} endpoint while actually targettting /files/{id}/download.
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
     * Therefore, the user only needs to choose whether they want to use the prod or the staging (sandbox) environment.
     *
     * @var string
     */
    protected $prodBaseUrl = 'https://api.yousign.com';
    protected $stagingBaseUrl = 'https://staging-api.yousign.com';

    protected $required_fields = ['default' => ['id', 'updatedAt', 'createdAt']];

    /**
     * Fields displayed on UI for user to fill in in order to be able to log in to the YouSign API.
     * The Sandbox field only accepts '0' and '1' and acts as a boolean to determine which base URL to use.
     *
     * @return array
     */
    public function getFieldsLogin()
    {
        return [
            [
                'name' => 'sandbox',
                'type' => TextType::class,
                'label' => 'solution.fields.sandbox',
            ],
            [
                'name' => 'apikey',
                'type' => PasswordType::class,
                'label' => 'solution.fields.apikey',
            ],
        ];
    }

    /**
     * Using the API key & the URL (staging sandbox or prod) provided by the user's input,
     * this method attempts to get a success response from the YouSign API via a GET request.
     * Since there's no actual endpoint to verify succesful authentication / login,
     * we currently make an API call to a 'random' endpoint which should be filled in from the get-go (organizations)
     * to test whether the API key provided by the user is correct.
     *
     * @param mixed $paramConnexion
     *
     * @return void
     */
    public function login($paramConnexion)
    {
        parent::login($paramConnexion);
        try {
            if ('1' === $this->paramConnexion['sandbox']) {
                $this->paramConnexion['url'] = $this->stagingBaseUrl;
            } else {
                $this->paramConnexion['url'] = $this->prodBaseUrl;
            }

            $apiKey = $this->paramConnexion['apikey'];
            $endpoint = 'organizations';
            $parameters['apikey'] = $apiKey;
            $parameters['endpoint'] = $endpoint;
            $result = $this->call($this->paramConnexion['url'], $parameters);
            if (!empty($result)) {
                $this->connexion_valide = true;
            } else {
                throw new \Exception('Failed to connect but no error returned by YouSign API.');
            }
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $this->logger->error($error);

            return ['error' => $error];
        }
    }

    /**
     * @param string $type
     *
     * @return array
     */
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

    /**
     * @param string $module
     * @param string $type
     * @param null   $param
     *
     * @return array|bool
     */
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

    /**
     * Get data from YouSign API via cURL call based on parameters (limit, date_ref),
     * Transform it to Myddleware-readable data,
     * Return the data.
     *
     * @param array $param
     *
     * @return array|bool
     */
    public function read($param)
    {
        try {
            $module = $param['module'];
            $endpoint = $module;
            if ('download_files' === $module) {
                $module = 'files';
                // TODO: find a way to pass the file ID as a GET parameter to our request in order to download the base64 document
                $endpoint = $module.'/{id}/download';
            }
            $param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
            $param['fields'] = $this->addRequiredField($param['fields'], $param['module'], $param['ruleParams']['mode']);
            if (empty($param['limit'])) {
                $param['limit'] = $this->callLimit;
            } else {
                if ($param['limit'] < $this->callLimit) {
                    $this->callLimit = $param['limit'];
                }
            }
            $dateRef = $this->removeTimeFromDateRef($param['date_ref']);
            $queryStringParams['date_ref'] = $dateRef;
            $moduleSpecificQueryString = $this->determineQueryStringBasedOnModule($module, $queryStringParams);
            $queryString = $this->cleanUpQueryStringFilter($moduleSpecificQueryString);
            $this->paramConnexion['endpoint'] = $endpoint.$queryString;
            $response = $this->call($this->paramConnexion['url'], $this->paramConnexion);
            $response = json_decode($response);
            if (!empty($response) && is_array($response)) {
                $result = $this->transformResponseToMyddlewareResultsFormat($response, $param);
            } elseif ($response instanceof stdClass) {
                $result['error'] = $response->error;
                $this->logger->error($response->error);
            } else {
                $result = [];
            }
        } catch (\Exception $e) {
            $error = $e->getMessage().' '.$e->getFile().' '.$e->getLine();
            $result['error'] = $error;
            $this->logger->error($error);

            return false;
        }

        return $result;
    }

    /**
     * Determines which query string to add to the GET request to the YouSign API
     * depending on the module (not all modules are born equal).
     *
     * @param string $module
     * @param array|null $queryStringParams
     * @return string
     */
    public function determineQueryStringBasedOnModule(string $module, ?array $queryStringParams) :string
    {
        $dateRef = $queryStringParams['date_ref'];
        switch ($module) {
            case 'users':
                $queryString = "";
                break;

            case 'files':
                $id= $queryStringParams['file_id'];
                $queryString = "?id=$id";
                break;

            case 'procedures':
                $queryString = "?updatedAt[strictly_after]=$dateRef";
                break;
            
            default:
            $queryString = "";
                break;
        }  
        return $queryString;
        
    }
    

    /**
     * Convert the result of the GET request to YouSign API to Myddleware-readable data.
     */
    public function transformResponseToMyddlewareResultsFormat(array $response, array $param): array
    {
        $result = [];
        foreach ($response as $record) {
            foreach ($param['fields'] as $field) {
                $result[$record->id][$field] = (!empty($record->$field) ? $record->$field : '');
            }
            $result[$record->id]['id'] = $record->id;
        }

        return $result;
    }

    /**
     * GET cURL call to YouSign API endpoints (modules).
     *
     * @param string $url
     * @param array  $parameters : the APIkey is required on ALL endpoints
     *
     * @return string|bool
     */
    protected function call($url, $parameters)
    {
        try {
            $url = $this->stagingBaseUrl;
            $endpoint = '';
            $apiKey = '';
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

    /**
     * Returns the reference date field name according to the module & rulemode.
     *
     * @param string $moduleSource
     * @param string $ruleMode
     *
     * @return string|null
     */
    public function getRefFieldName($moduleSource, $ruleMode)
    {
        if (in_array($ruleMode, ['0', 'S', 'C'])) {
            return 'updatedAt';
        } else {
            throw new \Exception("$ruleMode is not a correct Rule mode.");
        }

        return null;
    }

    /**
     * Convert date to Myddleware format.
     *
     * 2020-07-08T12:33:06+02:00 to 2020-07-08 10:33:06
     *
     * @param string $dateTime
     *
     * @return string
     */
    protected function dateTimeToMyddleware($dateTime)
    {
        $dto = new \DateTime($dateTime);
        // We save the UTC date in Myddleware
        $dto->setTimezone(new \DateTimeZone('UTC'));

        return $dto->format('Y-m-d H:i:s');
    }

    /**
     * Converts the date format to a YouSign format.
     *
     * @param string $dateTime
     *
     * @return string
     */
    protected function dateTimeFromMyddleware($dateTime)
    {
        $dto = new \DateTime($dateTime);
        // Return date to UTC timezone
        return $dto->format('Y-m-d\TH:i:s+00:00');
    }

    /**
     * We take off the 'time' part of the Myddleware 'date_ref' field
     * as the YouSign API query string only accepts a date format as a sorting filter for 'after'/'strictly_after'.
     *
     * @param \DateTime $dateRef
     */
    public function removeTimeFromDateRef(string $dateRef): string
    {
        $dto = new \DateTime($dateRef);

        return $dto->format('Y-m-d');
    }

    /**
     * URLEncode the query string filters to be added to the cURL API request.
     */
    public function cleanUpQueryStringFilter(string $queryString): string
    {
        return urlencode($queryString);
    }
}

class yousign extends yousigncore
{
}
