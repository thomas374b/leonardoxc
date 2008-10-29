<?
/************************************************************************/
/* Leonardo: Gliding XC Server					                        */
/* ============================================                         */
/*                                                                      */
/* Copyright (c) 2004-5 by Andreadakis Manolis                          */
/* http://leonardo.thenet.gr                                            */
/* http://sourceforge.net/projects/leonardoserver                       */
/*                                                                      */
/* This program is free software. You can redistribute it and/or modify */
/* it under the terms of the GNU General Public License as published by */
/* the Free Software Foundation; either version 2 of the License.       */
/************************************************************************/

// Specific settigns for phpBB module operation ($opMode=2)

// Path settings
$CONF['path']['direct_call']=0;

function moduleRelPath($forUtilityFiles=0){
	global $module_name,$CONF;

	if ( $CONF['links']['type']==3 ) {
		return $CONF['links']['baseURL'];
	} else  {
		if ($forUtilityFiles) // for EXT_ files
			return "./";
		else 
			return "modules/$module_name";	
	}	
	

}

// bridge to the users table of different forum/portal/cms systems
$CONF['userdb']['users_table']='phpbb_users';
$CONF['userdb']['user_id_field']='user_id';
$CONF['userdb']['username_field']='username';
$CONF['userdb']['password_field']='user_password';

$CONF['userdb']['use_leonardo_real_names']=1;

// if  $CONF['userdb']['use_leonardo_real_names']=0;
// then the following will be used to extract real names from the 'aplication' DB
$CONF['userdb']['has_realnames']=1;
$CONF['userdb']['user_real_name_field']='username';
// if you are running phpbb2 with the realanme mod , uncomment this instead
// $CONF['userdb']['user_real_name_field']='user_realname';

$CONF['userdb']['has_seperate_last_first_name']=0;
$CONF['userdb']['user_last_name_field']='';
$CONF['userdb']['user_first_name_field']='';


// bridge to the login system of different forum/portal/cms systems
$CONF['bridge']['login_url']="?name=%module_name%&op=login";
$CONF['bridge']['logout_url']="?name=%module_name%&op=login&logout=true";
// $CONF['bridge']['register_url']="?name=%module_name%&op=users&page=index&act=register";
$CONF['bridge']['register_url']="profile.php?mode=register";
$CONF['bridge']['forgot_password_url']='';
$CONF['bridge']['edit_profile_url']='';


// various settings that depend on $opMode !
$CONF_mainfile="modules.php";
$CONF_arg_name="name";

function setModuleArg() {
	global $CONF_arg_name,$module_name;
	define('CONF_MODULE_ARG',"?$CONF_arg_name=$module_name");
}

?>