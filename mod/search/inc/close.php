<?php

/**
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @version $Id$
 */

\phpws\PHPWS_Core::initModClass('search', 'User.php');

Search_User::searchBox();

if (isset($_SESSION['Search_Admin'])) {
    \phpws\PHPWS_Core::initModClass('search', 'Admin.php');
    Search_Admin::miniAdmin();
}
