<?php 
/*
    wobble - another php web framework... 
    Copyright (C) 2013  David Lenwell

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

session_start();

// load config
include_once "../config.php";
$config = new config();

//Load the dependant classes 
include_once $config->LIB_PATH."d_lib/Template_d/Template_d.php";
include_once $config->LIB_PATH."d_lib/Uri_d/Uri_d.php";
include_once $config->LIB_PATH."d_lib/Auth_d/Auth_d.php";

//load auth
$AUTH = new Auth_d();
// parse uri 
$URI = new Uri_d();

// load module
if ($URI->slot[1] != '')
{
	$requested_module = $URI->slot[1];
} else { 
	$requested_module = $config->DEFAULT_MODULE ;
	$URI->slot[2] = 'home';
}

// module level 404 handler .. if the user requests a module that isn't in the config they should be directed to a friendly error message
if (! in_array($requested_module, $config->MODULES))
{
	$requested_module = 'fourofour';
}

// include the parent module 
include_once $config->LIB_PATH.'modules/module.php';
// include the correct module files
include_once $config->LIB_PATH.'modules/'.$requested_module."/".$requested_module.".php";

$module = new $requested_module();

// final output 
$template = new Template_d($module->master_page, $module->master_page_path);	

// have to call the content output first because it can effect the other values 
$template->Set('content',$module->OutputSecurityWrapper());

if ($requested_module == 'admin')
{
	$admin_module = $module ;
} else { 
	include_once $config->LIB_PATH.'modules/admin/admin.php';
	$admin_module = new admin();
}

//$main_navigation = $admin_module->OutputNavigation();

//$template->Set('main_navigation',$main_navigation);
$template->Set('current_page',$URI->slot[2]);
$template->Set('form_action',$module->form_action);
$template->Set('keywords',$module->meta_keywords);
$template->Set('description',$module->meta_description);
$template->Set('page_title',$config->SITE_NAME.' - '.$module->page_title);

// output to buffer
echo $template->Output();
?>
