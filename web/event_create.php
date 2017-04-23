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
 * @file $/web/event_create.php
 * @brief Create a new event.
 * @author Stephan Kreutzer
 * @since 2014-06-08
 */



require_once(dirname(__FILE__)."/libraries/https.inc.php");

require_once("./libraries/languagelib.inc.php");
require_once(getLanguageFile("event_create"));
require_once("./language_selector.inc.php");

echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n".
     "<!DOCTYPE html\n".
     "    PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\"\n".
     "    \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n".
     "<html xmlns=\"http://www.w3.org/1999/xhtml\" xml:lang=\"".getCurrentLanguage()."\" lang=\"".getCurrentLanguage()."\">\n".
     "    <head>\n".
     "        <title>".LANG_PAGETITLE."</title>\n".
     "        <link rel=\"stylesheet\" type=\"text/css\" href=\"mainstyle.css\"/>\n".
     "        <meta http-equiv=\"expires\" content=\"1296000\"/>\n".
     "        <meta http-equiv=\"content-type\" content=\"application/xhtml+xml; charset=UTF-8\"/>\n".
     "    </head>\n".
     "    <body>\n".
     getHTMLLanguageSelector("event_create.php").
     "        <div class=\"mainbox\">\n".
     "          <div class=\"mainbox_header\">\n".
     "            <h1 class=\"mainbox_header_h1\">".LANG_HEADER."</h1>\n".
     "          </div>\n".
     "          <div class=\"mainbox_body\">\n";

if (isset($_POST['event_type']) == false ||
    isset($_POST['asylum_seeker_family_name']) == false ||
    isset($_POST['asylum_seeker_given_name']) == false ||
    isset($_POST['asylum_seeker_address']) == false ||
    isset($_POST['asylum_seeker_zip_code']) == false ||
    isset($_POST['asylum_seeker_city']) == false ||
    isset($_POST['asylum_seeker_email']) == false ||
    isset($_POST['asylum_seeker_phone']) == false ||
    isset($_POST['date_received']) == false ||
    isset($_POST['details']) == false ||
    isset($_POST['date_deadline']) == false ||
    isset($_POST['creator_name']) == false ||
    isset($_POST['creator_email']) == false ||
    isset($_POST['creator_phone']) == false)
{
    echo "            <p>\n".
         "              ".LANG_DESCRIPTION."\n".
         "            </p>\n".
         "            <form action=\"event_create.php\" method=\"post\">\n".
         "              <fieldset>\n".
         "                <h2>".LANG_HEADER_ASYLUMSEEKER."</h2>\n".
         "                <select name=\"event_type\" size=\"1\">\n".
         "                  <option>".LANG_EVENTTYPE_ASYLUMINTERVIEWINVITATION."</option>\n".
         "                  <option>".LANG_EVENTTYPE_ASYLUMDECISION."</option>\n".
         "                  <option>".LANG_EVENTTYPE_COURTDECISION."</option>\n".
         "                  <option>".LANG_EVENTTYPE_OTHER."</option>\n".
         "                </select> ".LANG_EVENTTYPE_CAPTION."<br/>\n".
         "                <input name=\"asylum_seeker_family_name\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_FAMILYNAME."<br/>\n".
         "                <input name=\"asylum_seeker_given_name\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_GIVENNAME."<br/>\n".
         "                <input name=\"asylum_seeker_address\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_ADDRESS."<br/>\n".
         "                <input name=\"asylum_seeker_zip_code\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_ZIPCODE."<br/>\n".
         "                <input name=\"asylum_seeker_city\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_CITY."<br/>\n".
         "                <input name=\"asylum_seeker_email\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_EMAIL."<br/>\n".
         "                <input name=\"asylum_seeker_phone\" size=\"40\" maxlength=\"255\"/> ".LANG_ASYLUMSEEKER_PHONE."<br/>\n".
         "                <input name=\"date_received\" size=\"40\" maxlength=\"255\"/> ".LANG_DATERECEIVED."<br/>\n".
         "                <textarea name=\"details\" cols=\"80\" rows=\"12\">".LANG_DETAILS."</textarea><br/>\n".
         "                <input name=\"date_deadline\" size=\"40\" maxlength=\"255\"/> ".LANG_DATEDEADLINE."<br/>\n".
         "                <h2>".LANG_HEADER_VOLUNTEER."</h2>\n".
         "                <input type=\"text\" name=\"creator_name\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATOR_NAME."<br/>\n".
         "                <input type=\"text\" name=\"creator_email\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATOR_EMAIL."<br/>\n".
         "                <input type=\"text\" name=\"creator_phone\" value=\"\" size=\"40\" maxlength=\"255\"/> ".LANG_CREATOR_PHONE."<br/>\n".
         "                <input type=\"submit\" value=\"".LANG_EVENTCREATEBUTTON."\"/>\n".
         "              </fieldset>\n".
         "            </form>\n";

}
else
{
    require_once("./libraries/event_management.inc.php");

    $eventType = EVENTTYPE_UNKNOWN;

    switch ($_POST['event_type'])
    {
    case LANG_EVENTTYPE_OTHER:
        $eventType = EVENTTYPE_OTHER;
        break;
    case LANG_EVENTTYPE_ASYLUMINTERVIEWINVITATION:
        $eventType = EVENTTYPE_ASYLUMINTERVIEWINVITATION;
        break;
    case LANG_EVENTTYPE_ASYLUMDECISION:
        $eventType = EVENTTYPE_ASYLUMDECISION;
        break;
    case LANG_EVENTTYPE_COURTDECISION:
        $eventType = EVENTTYPE_COURTDECISION;
        break;
    default:
        $eventType = EVENTTYPE_UNKNOWN;
        break;
    }

    $result = AddNewEvent($eventType,
                          $_POST['asylum_seeker_family_name'],
                          $_POST['asylum_seeker_given_name'],
                          $_POST['asylum_seeker_address'],
                          $_POST['asylum_seeker_zip_code'],
                          $_POST['asylum_seeker_city'],
                          $_POST['asylum_seeker_email'],
                          $_POST['asylum_seeker_phone'],
                          $_POST['date_received'],
                          $_POST['details'],
                          $_POST['date_deadline'],
                          $_POST['creator_name'],
                          $_POST['creator_email'],
                          $_POST['creator_phone'],
                          LANG_NEWEVENTNOTIFICATIONTEXT);

    if (is_array($result) === true)
    {
        echo "            <p>\n".
             "              <span class=\"success\">".LANG_EVENTCREATEDSUCCESSFULLY."</span>\n".
             "            </p>\n".
             "            <form action=\"event_upload.php\" method=\"post\">\n".
             "              <fieldset>\n".
             "                <input type=\"hidden\" name=\"event_handle\" value=\"".$result['handle']."\"/>\n".
             "                <input type=\"submit\" value=\"".LANG_CONTINUE."\"/>\n".
             "              </fieldset>\n".
             "            </form>\n";
    }
    else
    {
        echo "            <p>\n".
             "              <span class=\"error\">".LANG_EVENTCREATEFAILED."</span>\n".
             "            </p>\n".
             "            <form action=\"event_create.php\" method=\"post\">\n".
             "              <fieldset>\n".
             "                <input type=\"submit\" value=\"".LANG_BACK."\"/>\n".
             "              </fieldset>\n".
             "            </form>\n";
    }
}

echo "          </div>\n".
     "        </div>\n".
     "        <div class=\"footerbox\">\n".
     "          <a href=\"license.php\" class=\"footerbox_link\">".LANG_LICENSE."</a>\n".
     "        </div>\n".
     "    </body>\n".
     "</html>\n";


?>
