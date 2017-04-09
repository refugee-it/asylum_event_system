<?php
/* Copyright (C) 2014-2017  Stephan Kreutzer
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
 * @file $/web/event_upload.php
 * @brief Upload files for an event.
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



require_once(dirname(__FILE__)."/libraries/https.inc.php");

session_start();

$eventHandle = null;

if (isset($_GET['handle']) === true)
{
    $eventHandle = $_GET['handle'];
}

if (isset($_POST['event_handle']) === true)
{
    $eventHandle = $_POST['event_handle'];
}

if ($eventHandle == null)
{
    http_response_code(412);
    exit(1);
}

require_once("./libraries/event_management.inc.php");

$event = GetEventByHandle($eventHandle);

if (is_array($event) != true)
{
    http_response_code(404);
    exit(1);
}

if ((int)$event['status'] !== EVENT_STATUS_NEW)
{
    http_response_code(403);
    exit(1);
}

$files = $event['files'];

require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("event_upload"));

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"mainstyle.css\"/>\n".
     "    </head>\n".
     "    <body>\n".
     "        <div class=\"mainbox\">\n".
     "          <div class=\"mainbox_header\">\n".
     "            <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
     "          </div>\n".
     "          <div class=\"mainbox_body\">\n";

if (isset($_POST['upload_done']) === true)
{
    if (EventCheckin($eventHandle) === 0)
    {
        echo "            <p>\n".
             "              ".LANG_UPLOAD_THANK_YOU."\n".
             "            </p>\n".
             "            <form action=\"index.php\" method=\"post\">\n".
             "              <fieldset>\n".
             "                <input type=\"submit\" value=\"".LANG_UPLOAD_DONE."\"/>\n".
             "              </fieldset>\n".
             "            </form>\n".
             "          </div>\n".
             "        </div>\n".
             "        <div class=\"footerbox\">\n".
             "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
             "        </div>\n".
             "    </body>\n".
             "</html>\n";

        exit(0);
    }
    else
    {
        echo "            <p>\n".
             "              <span class=\"error\">".LANG_UPLOAD_GENERAL_ERROR."</span>\n".
             "            </p>\n";
    }
}

echo "            <p>\n".
     "              ".LANG_UPLOAD_DESCRIPTION."\n".
     "            </p>\n";

if (isset($_POST['upload']) === true)
{
    $success = true;

    if (isset($_FILES['file']) !== true)
    {
        echo "            <p>\n".
             "              <span class=\"error\">".LANG_UPLOAD_GENERAL_ERROR."</span>\n".
             "            </p>\n";

        $success = false;
    }

    if ($success === true)
    {
        if ($_FILES['file']['error'] != 0)
        {
            echo "            <p>\n".
                 "              <span class=\"error\">".LANG_UPLOAD_SPECIFIC_ERROR_PRE.htmlspecialchars($_FILES['file']['error'], ENT_COMPAT | ENT_HTML401, "UTF-8").LANG_UPLOAD_SPECIFIC_ERROR_POST."</span>\n".
                 "            </p>\n";

            $success = false;
        }
    }

    if ($success === true)
    {
        if ($_FILES['file']['size'] > 5242880)
        {
            echo "            <p>\n".
                 "              <span class=\"error\">".LANG_UPLOAD_FILESIZE_ERROR_PRE."5242880".LANG_UPLOAD_FILESIZE_ERROR_POST."</span>\n".
                 "            </p>\n";

            $success = false;
        }
    }

    $id = null;
    $internalName = md5(uniqid(rand(), true));

    if ($success === true)
    {
        if (move_uploaded_file($_FILES['file']['tmp_name'], "./uploads/".$internalName) !== true)
        {
            echo "            <p>\n".
                 "              <span class=\"error\">".LANG_UPLOAD_CANT_SAVE."</span>\n".
                 "            </p>\n";

            $success = false;
        }
    }

    if ($success === true)
    {
        $result = AttachUpload($event['id'], $_FILES['file']['name'], $internalName);

        if (is_array($result) !== true)
        {
            $success = false;
        }
    }

    if ($success === true)
    {
        $files[] = array('internal_name' => $internalName, 'display_name' => $_FILES['file']['name']);

        echo "            <p>\n".
             "              <span class=\"success\">".LANG_UPLOAD_SUCCESS."</span>\n".
             "            </p>\n";
    }
    else
    {
        echo "            <p>\n".
             "              <span class=\"success\">".LANG_UPLOAD_ERROR."</span>\n".
             "            </p>\n";
    }
}

echo "            <form enctype=\"multipart/form-data\" action=\"event_upload.php\" method=\"post\">\n".
     "              <fieldset>\n".
     "                <input type=\"file\" name=\"file\"/><br/>\n".
     "                <input type=\"hidden\" name=\"event_handle\" value=\"".htmlspecialchars($eventHandle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
     "                <input type=\"submit\" name=\"upload\" value=\"".LANG_UPLOAD_SUBMIT."\"/><br/>\n".
     "              </fieldset>\n".
     "            </form>\n";

if (count($files) > 0)
{
    echo "            <ul>\n";

    foreach ($files as $file)
    {
        echo "              <li>\n".
             "                ".htmlspecialchars($file['display_name'], ENT_COMPAT | ENT_HTML401, "UTF-8").".\n".
             "              </li>\n";
    }

    echo "            </ul>\n";
}

echo "            <form action=\"event_upload.php\" method=\"post\">\n".
     "              <fieldset>\n".
     "                <input type=\"submit\" name=\"upload_done\" value=\"".LANG_UPLOAD_DONE."\"/>\n".
     "                <input type=\"hidden\" name=\"event_handle\" value=\"".htmlspecialchars($eventHandle, ENT_COMPAT | ENT_HTML401, "UTF-8")."\"/>\n".
     "              </fieldset>\n".
     "            </form>\n".
     "          </div>\n".
     "        </div>\n".
     "        <div class=\"footerbox\">\n".
     "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n";



?>
