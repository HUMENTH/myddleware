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

use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;

class yousigncore extends solution
{
    protected $limitCall = 100;
    // Enable to read deletion and to delete data
    protected $readDeletion = true;
    protected $sendDeletion = true;

    protected $required_fields = ['default' => ['id', 'date_modified', 'date_entered']];

    public function getFieldsLogin()
    {
        return [
            [
                'name' => 'login',
                'type' => TextType::class,
                'label' => 'solution.fields.login',
            ],
            [
                'name' => 'password',
                'type' => PasswordType::class,
                'label' => 'solution.fields.password',
            ],
            [
                'name' => 'url',
                'type' => TextType::class,
                'label' => 'solution.fields.url',
            ],
            [
                'name' => 'token',
                'type' => PasswordType::class,
                'label' => 'solution.fields.token',
            ],
        ];
    }

    public function login($paramConnexion)
    {
        parent::login($paramConnexion);
        try {
            $result = $this->call($this->paramConnexion['url'], $this->paramConnexion['token']);
            //dd($result);
            if (!empty($result)) {
                $this->connexion_valide = true;
            } elseif (empty($result)) {
                throw new \Exception('Failed to connect but no error returned by YouSign. ');
            }
            
        } catch (\Exception $e) {
            $error = $e->getMessage();
            $this->logger->error($error);
            return ['error' => $error];
        }
    }

     //Get module list
     public function get_modules($type = 'source')
    {
        if ('source' == $type) {
            return [
               'users' => 'Users',
               'files' => 'Files',
               'procedures' => 'Procedures',
               'members' => 'Members'
            ];
        }
        return [
            'users' => 'Users',
            'files' => 'Files',
            'procedures' => 'Procedures',
            'members' => 'Members'
        ];
    }

    //Returns the fields of the module passed in parameter
    public function get_module_fields($module, $type = 'source', $param = null)
    {
        parent::get_module_fields($module, $type);
        try {

        //Use yousign metadata !just for user and procedure! to review
        require 'lib/yousign/metadata.php';
        switch ($module) {
            case 'users':
                if (!empty($moduleFields['users'])) {
                    $this->moduleFields = $moduleFields['users'];        
                   return $this->moduleFields;
                 }
                break;
            case 'procedures':
                if (!empty($moduleFields['procedures'])) {
                    $this->moduleFields = $moduleFields['procedures'];        
                    return $this->moduleFields;
                    }
                break;
             
        }
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return false;
        }
    }

    // Read all fields
    public function read($param)
    {
        try {

            //'https://staging-api.yousign.com/procedures'; <- url ok with postman 

            $content = [];
            $module = $param['module'];

            if (!empty($param)) {
                $params = $this->call($this->paramConnexion['url'], $this->paramConnexion['token']);
            }        
            
            // Remove Myddleware's system fields (useful?)
            $param['fields'] = $this->cleanMyddlewareElementId($param['fields']);
             // Add required fields
             $param['fields'] = $this->addRequiredField($param['fields'], $param['module'], $param['ruleParams']['mode']);


            
            // $client = HttpClient::create();
        

            
        } catch (\Exception $e) {
            $error = $e->getMessage();
            return false;
        }
    }

    public function logout()
    {
        try {
            $logout_parameters = ['session' => $this->session];
            $this->call('logout', $logout_parameters, $this->paramConnexion['url']);

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Error logout REST '.$e->getMessage());

            return false;
        }
    } 
    
     //function to make cURL request
     protected function call($url, $parameters)
     {
        try {
        $curl = curl_init();  
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "Authorization: Bearer ".$parameters,
                "Content-Type: application/json"
            ),
        ));
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        if ($err) {
            return "cURL Error #:" . $err;
        } else {
            return $response;
        }       
        } catch (\Exception $e) {
            return false;
        }
     }

      //convert from Myddleware format to Woocommerce format
    protected function dateTimeFromMyddleware($dateTime)
    {
        $dto = new \DateTime($dateTime);
        // Return date to UTC timezone
        return $dto->format('Y-m-d\TH:i:s');
    }
}

class yousign extends yousigncore
{
}
