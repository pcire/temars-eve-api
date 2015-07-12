<?php

if (!defined('SMF'))
	die('Hacking attempt...');

Global $teats, $db_prefix, $sourcedir, $modSettings, $user_info, $context, $txt, $smcFunc, $settings;
loadLanguage('TEA');

require_once($sourcedir.'/TEAC.php');

class TEA_Jabber_DB extends TEAC
{
	function __construct(&$db_prefix, &$sourcedir, &$modSettings, &$user_info, &$context, &$txt, &$smcFunc, &$settings)
	{
		//	$this -> db_prefix = &$db_prefix;
		$this -> sourcedir = &$sourcedir;
		$this -> modSettings = &$modSettings;
		$this -> user_info = &$user_info;
		$this -> context = &$context;
		$this -> txt = &$txt;
		$this -> smcFunc = &$smcFunc;
		$this -> settings = &$settings;
	}


	function get_groups()
	{
		$ret='';
		$secret = $this -> modSettings['tea_jabber_secret'];
		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/groups';
		$groups = $this -> get_rest_site($url, $secret, null, 'GET');
		if(is_object($groups))
		{
			foreach($groups->{'group'} as $group)
			{
				$ret[$group->{'name'}] = $group->{'name'};
			}
		}
		return $ret;
	}

	function get_user_groups($name)
	{
		$ret='';
		$secret = $this -> modSettings['tea_jabber_secret'];
		$name = str_replace("'", "_", $name);
		$name = str_replace(" ", "_", $name);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($name).'/groups';
		$groups = $this -> get_rest_site($url, $secret, null, 'GET');
		if(is_object($groups))
		{
			if (is_array($groups->{'groupname'})) {
				foreach($groups->{'groupname'} as $group)
				{
					$ret[$group] = $group;
				}
			}
			else {
				$ret[$groups->{'groupname'}] = $groups->{'groupname'};
			}

		}
		return $ret;
	}

	function add_user($uname, $pw, $name, $email, $groups)
	{
		$secret = $this -> modSettings['tea_jabber_secret'];
		$uname = str_replace("'", "_", $uname);
		$uname = str_replace(" ", "_", $uname);

		$postData = array(
			'username' => $uname,
			'password' => $pw,
			'name' => $name,
			'email' => $email
		);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users';
		$response = 'Create user... ' . $this -> get_rest_site($url, $secret, $postData);

		$postData = array(
			'groupname' => $this -> safe_array_keys($groups)
		);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname).'/groups';
		$response .=  'Add groups... ' .  $this -> get_rest_site($url, $secret, $postData);

		return $response;
	}

	function get_user($name)
	{
		$secret = $this -> modSettings['tea_jabber_secret'];
		$name = str_replace("'", "_", $name);
		$name = str_replace(" ", "_", $name);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($name);
		$user = $this -> get_rest_site($url, $secret, null, 'GET');
		if(is_object($user))
		{
			Return TRUE;
		}
		else
		{
			Return FALSE;
		}
	}

	function del_user($uname, $kick=true)
	{
		$secret = $this -> modSettings['tea_jabber_secret'];
		$uname = str_replace("'", "_", $uname);
		$uname = str_replace(" ", "_", $uname);

		if ($kick) {
			$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/sessions/'.urlencode($uname);
			$this -> get_rest_site($url, $secret, null, 'DELETE');
		}

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname);
		$this -> get_rest_site($url, $secret, null, 'DELETE');
		return;
	}

	function update_user($uname, $pw, $name, $email, $groups, $removeGroups=null)
	{
		$secret = $this -> modSettings['tea_jabber_secret'];
		$uname = str_replace("'", "_", $uname);
		$uname = str_replace(" ", "_", $uname);

		$postData = array(
			'username' => $uname,
			'name' => $name,
			'email' => $email
		);

		if ($pw)
			$postData['password'] = $pw;

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname);
		$this -> get_rest_site($url, $secret, $postData, 'PUT');


		$postData = array(
			'groupname' => $this -> safe_array_keys($groups)
		);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname).'/groups';
		$this -> get_rest_site($url, $secret, $postData);


		if (isset($removeGroups) && count($removeGroups) > 0) {
			$postData = array(
				'groupname' => $this -> safe_array_keys($removeGroups)
			);

			$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname).'/groups';
			$this -> get_rest_site($url, $secret, $postData, 'DELETE');
		}
		return;
	}

	function add_to_group($uname, $group)
	{
		$secret = $this -> modSettings['tea_jabber_secret'];
		$uname = str_replace("'", "_", $uname);
		$uname = str_replace(" ", "_", $uname);

		$postData = array(
			'groupname' => $this -> safe_array_keys($group)
		);

		$url = $this -> modSettings['tea_jabber_admin_url'].'/plugins/restapi/v1/users/'.urlencode($uname).'/groups';
		$this -> get_rest_site($url, $secret, $postData);
		return TRUE;
	}

	function safe_array_keys($mixed) {
		if (!isset($mixed) || !is_array($mixed) || count($mixed) == 0 || array_keys($mixed)[0] === 0)
			return $mixed;
		else
			return array_keys($mixed);
	}
}

$jabber_db = new TEA_Jabber_DB($db_prefix, $sourcedir, $modSettings, $user_info, $context, $txt, $smcFunc, $settings);

?>
