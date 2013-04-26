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
require_once('/var/www/bin/d_lib/Image_d/Image_d.php');

// 
$fullPath = $_SERVER['DOCUMENT_ROOT'];
// set some values
$uri = $_SERVER['REQUEST_URI'];

// if there are arguments in the query string its going to cause some issues when included .. so loose them for now
if (ereg("\?",$uri)) { 
	$uri = substr($uri,0,strpos($uri,"?"));
}

// get all the parts of the uri that we need
$filename = trim(strrchr($uri,"/"),"/");
$fileExt = strrchr($filename,".");

$action = $_REQUEST['a'];

$img  = New Image_d($fullPath.$uri,'jpg');

switch ($action) {
	case 'thumb1':
		$img->Resize('100','100');
		break;
	case 'thumb2':
		$img->Resize('200','200');
		break;
	case 'thumb3':
		$img->Resize('300','300');
		break;
	case 'tiny':
		$img->Resize('45','45');
		break;
	case 'resize':
		$x = $_REQUEST['x'];
		$y = $_REQUEST['y'];
		$img->Resize($x,$y);
		break;
	case 'flipx':
		$img->FlipX();
		break;
	case 'flipy':
		$img->FlipY();
		break;
}

$img->Display();

?>