<?php
/* Copyright (C) 2012-2017  Stephan Kreutzer
 *
 * This file is part of asylum event system by refugee-it.de.
 *
 * asylum event system by refugee-it.de is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License version 3 or any later version,
 * as published by the Free Software Foundation.
 *
 * asylum event system by refugee-it.de is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License 3 for more details.
 *
 * You should have received a copy of the GNU Affero General Public License 3
 * along with asylum event system by refugee-it.de. If not, see <http://www.gnu.org/licenses/>.
 */
/**
 * @file $/web/libraries/event_management.inc.php
 * @author Stephan Kreutzer
 * @since 2016-07-25
 */



require_once(dirname(__FILE__)."/database.inc.php");



define("EVENTTYPE_UNKNOWN", 0);
define("EVENTTYPE_OTHER", 1);
define("EVENTTYPE_ASYLUMINTERVIEWINVITATION", 2);
define("EVENTTYPE_ASYLUMDECISION", 3);
define("EVENTTYPE_COURTDECISION", 4);

define("EVENT_STATUS_UNKNOWN", 0);
define("EVENT_STATUS_NEW", 1);
define("EVENT_STATUS_CHECKEDIN", 2);

define("EVENT_UPLOAD_STATUS_UNKNOWN", 0);



function AddNewEvent($eventType,
                     $asylumSeekerFamilyName,
                     $asylumSeekerGivenName,
                     $asylumSeekerAddress,
                     $asylumSeekerZipCode,
                     $asylumSeekerCity,
                     $asylumSeekerEmail,
                     $asylumSeekerPhone,
                     $dateReceived,
                     $details,
                     $dateDeadline,
                     $creatorName,
                     $creatorEmail,
                     $creatorPhone,
                     $notificationText)
{
    /** @todo Check for empty parameters. Check, if $title exists already in the database. */

    $eventType = (int)$eventType;

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    if (Database::Get()->BeginTransaction() !== true)
    {
        return -2;
    }

    $handle = md5(uniqid(rand(), true));

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."events` (`id`,\n".
                                  "    `event_type`,\n".
                                  "    `asylum_seeker_family_name`,\n".
                                  "    `asylum_seeker_given_name`,\n".
                                  "    `asylum_seeker_address`,\n".
                                  "    `asylum_seeker_zip_code`,\n".
                                  "    `asylum_seeker_city`,\n".
                                  "    `asylum_seeker_email`,\n".
                                  "    `asylum_seeker_phone`,\n".
                                  "    `date_received`,\n".
                                  "    `details`,\n".
                                  "    `date_deadline`,\n".
                                  "    `creator_name`,\n".
                                  "    `creator_email`,\n".
                                  "    `creator_phone`,\n".
                                  "    `status`,\n".
                                  "    `datetime_created`,\n".
                                  "    `handle`,\n".
                                  "    `id_user`)\n".
                                  "VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?)\n",
                                  array(NULL, $eventType, $asylumSeekerFamilyName, $asylumSeekerGivenName, $asylumSeekerAddress, $asylumSeekerZipCode, $asylumSeekerCity, $asylumSeekerEmail, $asylumSeekerPhone, $dateReceived, $details, $dateDeadline, $creatorName, $creatorEmail, $creatorPhone, EVENT_STATUS_NEW, $handle, NULL),
                                  array(Database::TYPE_NULL, Database::TYPE_INT, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_STRING, Database::TYPE_NULL));

    if ($id <= 0)
    {
        Database::Get()->RollbackTransaction();
        return -3;
    }

    if (Database::Get()->CommitTransaction() === true)
    {
        $user = Database::Get()->Query("SELECT `e_mail`\n".
                                       "FROM `".Database::Get()->GetPrefix()."users`\n".
                                       "WHERE `id`=?\n",
                                       array(1),
                                       array(Database::TYPE_INT));

        if (is_array($user) === true)
        {
            if (count($user) > 0)
            {
                $user = $user[0];

                $message = "Time: ".date("c")."\n";
                $message = "Event Type: ".$eventType."\n".
                $message .= "Message: ".htmlspecialchars($notificationText, ENT_COMPAT | ENT_HTML401, "UTF-8")."\n";

                @mail($user['e_mail'],
                      "Asylum Event Notification System",
                      $message,
                      "From: noreply@example.org\n".
                      "MIME-Version: 1.0\n".
                      "Content-type: text/plain; charset=UTF-8\n");
            }
        }

        return array("id" => $id, "handle" => $handle);
    }

    Database::Get()->RollbackTransaction();
    return -4;
}

function RemoveEventHandle($eventHandle)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $result = Database::Get()->Execute("UPDATE `".Database::Get()->GetPrefix()."events`\n".
                                       "SET `handle`=?\n".
                                       "WHERE `handle` LIKE ?",
                                       array(NULL, $eventHandle),
                                       array(Database::TYPE_NULL, Database::TYPE_STRING));

    if ($result === true)
    {
        return 0;
    }
    else
    {
        return -1;
    }
}

function EventCheckin($eventHandle)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $result = Database::Get()->Execute("UPDATE `".Database::Get()->GetPrefix()."events`\n".
                                       "SET `status`=?\n".
                                       "WHERE `handle` LIKE ?",
                                       array(EVENT_STATUS_CHECKEDIN, $eventHandle),
                                       array(Database::TYPE_INT, Database::TYPE_STRING));

    if ($result === true)
    {
        return 0;
    }
    else
    {
        return -1;
    }
}

function AttachUpload($eventId, $displayName, $internalName)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    if (Database::Get()->BeginTransaction() !== true)
    {
        return -2;
    }

    $id = Database::Get()->Insert("INSERT INTO `".Database::Get()->GetPrefix()."uploaded_files` (`id`,\n".
                                  "    `display_name`,\n".
                                  "    `internal_name`,\n".
                                  "    `status`,\n".
                                  "    `event_id`)\n".
                                  "VALUES (?, ?, ?, ?, ?)\n",
                                  array(NULL, $displayName, $internalName, EVENT_UPLOAD_STATUS_UNKNOWN, $eventId),
                                  array(Database::TYPE_NULL, Database::TYPE_STRING, Database::TYPE_STRING, Database::TYPE_INT, Database::TYPE_INT));

    if ($id <= 0)
    {
        Database::Get()->RollbackTransaction();
        return -3;
    }

    if (Database::Get()->CommitTransaction() === true)
    {
        return array("id" => $id);
    }

    Database::Get()->RollbackTransaction();
    return -4;
}

function GetEventById($id)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $event = Database::Get()->Query("SELECT `id`,\n".
                                    "    `event_type`,\n".
                                    "    `asylum_seeker_family_name`,\n".
                                    "    `asylum_seeker_given_name`,\n".
                                    "    `asylum_seeker_address`,\n".
                                    "    `asylum_seeker_zip_code`,\n".
                                    "    `asylum_seeker_city`,\n".
                                    "    `asylum_seeker_email`,\n".
                                    "    `asylum_seeker_phone`,\n".
                                    "    `date_received`,\n".
                                    "    `details`,\n".
                                    "    `date_deadline`,\n".
                                    "    `creator_name`,\n".
                                    "    `creator_email`,\n".
                                    "    `creator_phone`,\n".
                                    "    `status`,\n".
                                    "    `datetime_created`,\n".
                                    "    `handle`,\n".
                                    "    `id_user`\n".
                                    "FROM `".Database::Get()->GetPrefix()."events`\n".
                                    "WHERE `id`=?\n",
                                    array($id),
                                    array(Database::TYPE_INT));

    if (is_array($event) !== true)
    {
        return null;
    }

    if (count($event) <= 0)
    {
        return null;
    }

    $event = $event[0];

    $files = Database::Get()->Query("SELECT `display_name`,\n".
                                    "    `internal_name`,\n".
                                    "    `status`\n".
                                    "FROM `".Database::Get()->GetPrefix()."uploaded_files`\n".
                                    "WHERE `event_id`=?\n",
                                    array($id),
                                    array(Database::TYPE_INT));

    if (is_array($files) === true)
    {
        if (count($files) > 0)
        {
            $event['files'] = $files;
        }
        else
        {
            $event['files'] = null;
        }
    }
    else
    {
        $event['files'] = null;
    }

    return $event;
}

function GetEventByHandle($eventHandle)
{
    /** @todo Check for empty parameters. */

    if (Database::Get()->IsConnected() !== true)
    {
        return -1;
    }

    $event = Database::Get()->Query("SELECT `id`,\n".
                                    "    `event_type`,\n".
                                    "    `asylum_seeker_family_name`,\n".
                                    "    `asylum_seeker_given_name`,\n".
                                    "    `asylum_seeker_address`,\n".
                                    "    `asylum_seeker_zip_code`,\n".
                                    "    `asylum_seeker_city`,\n".
                                    "    `asylum_seeker_email`,\n".
                                    "    `asylum_seeker_phone`,\n".
                                    "    `date_received`,\n".
                                    "    `details`,\n".
                                    "    `date_deadline`,\n".
                                    "    `creator_name`,\n".
                                    "    `creator_email`,\n".
                                    "    `creator_phone`,\n".
                                    "    `status`,\n".
                                    "    `datetime_created`,\n".
                                    "    `handle`,\n".
                                    "    `id_user`\n".
                                    "FROM `".Database::Get()->GetPrefix()."events`\n".
                                    "WHERE `handle` LIKE ?\n",
                                    array($eventHandle),
                                    array(Database::TYPE_STRING));

    if (is_array($event) !== true)
    {
        return null;
    }

    if (count($event) <= 0)
    {
        return null;
    }

    $event = $event[0];

    $files = Database::Get()->Query("SELECT `display_name`,\n".
                                    "    `internal_name`,\n".
                                    "    `status`\n".
                                    "FROM `".Database::Get()->GetPrefix()."uploaded_files`\n".
                                    "WHERE `event_id`=?\n",
                                    array($event['id']),
                                    array(Database::TYPE_INT));

    if (is_array($files) === true)
    {
        if (count($files) > 0)
        {
            $event['files'] = $files;
        }
        else
        {
            $event['files'] = null;
        }
    }
    else
    {
        $event['files'] = null;
    }

    return $event;
}




?>
