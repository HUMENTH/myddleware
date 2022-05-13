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

$moduleFields = [
    'users' => [
        'firstname' => ['label' => 'firstname', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'lastname' => ['label' => 'lastname', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'email' => ['label' => 'email', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'title' => ['label' => 'title', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'phone' => ['label' => 'phone', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'status' => ['label' => 'status', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'organization' => ['label' => 'organization', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        //'workspaces'        => ['label' => 'workspaces', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'permission' => ['label' => 'permission', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        //'group'             => ['label' => 'group', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'createdAt' => ['label' => 'createdAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'updatedAt' => ['label' => 'updatedAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'deleted' => ['label' => 'deleted', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'deletedAt' => ['label' => 'deletedAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'config' => ['label' => 'config', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'inweboUserRequest' => ['label' => 'inweboUserRequest', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'samlNameId' => ['label' => 'samlNameId', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'defaultSignImage' => ['label' => 'defaultSignImage', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        //'notifications'     => ['label' => 'notifications', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'fastSign' => ['label' => 'fastSign', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'fullName' => ['label' => 'fullName', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
    ],

    'procedures' => [
        'name' => ['label' => 'name', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'description' => ['label' => 'description', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'createdAt' => ['label' => 'createdAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'updatedAt' => ['label' => 'updatedAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'finishedAt' => ['label' => 'finishedAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'expiresAt' => ['label' => 'expiresAt', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'status' => ['label' => 'status', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'creator' => ['label' => 'creator', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'creatorFirstName' => ['label' => 'creatorFirstName', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'creatorLastName' => ['label' => 'creatorLastName', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'workspace' => ['label' => 'workspace', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'template' => ['label' => 'template', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'ordered' => ['label' => 'ordered', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'parent' => ['label' => 'parent', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'metadata' => ['label' => 'metadata', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'config' => ['label' => 'config', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        //'members'     => ['label' => 'members', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'subscribers' => ['label' => 'subscribers', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        //'files'          => ['label' => 'files', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'relatedFilesEnable' => ['label' => 'relatedFilesEnable', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'archive' => ['label' => 'archive', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'archiveMetadata' => ['label' => 'archiveMetadata', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'fields' => ['label' => 'fields', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'permissions' => ['label' => 'permissions', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
    ],
    'files' => [
        'id' => ['label' => 'ID', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'name' => ['label' => 'Name', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'type' => ['label' => 'Type', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'contentType' => ['label' => 'Content Type', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'description' => ['label' => 'Description', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'createdAt' => ['label' => 'Created At', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'updatedAt' => ['label' => 'Updated At', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'sha256' => ['label' => 'sha256', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        // 'metadata'=> ['label' => 'metadata', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'workspace' => ['label' => 'Workspace', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'creator' => ['label' => 'Creator', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'protected' => ['label' => 'Protected', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'position' => ['label' => 'Position', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        'parent' => ['label' => 'Parent', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        // 'companies'=> ['label' => 'Companies', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
        // 'fileObjects'=> ['label' => 'File Objects', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
    ],
    'download_files' => [
        'base64' => ['label' => 'base64', 'type' => 'varchar(255)', 'type_bdd' => 'varchar(255)', 'required' => 0, 'required_relationship' => 0, 'relate' => 0],
    ],
];
