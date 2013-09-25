<?php
/*
	PHP LDAP CLASS FOR MANIPULATING ACTIVE DIRECTORY
	Version 1.5
	
	Written by Scott Barnett
	email: scott@wiggumworld.com
	http://adldap.sourceforge.net/
	
	Copyright (C) 2006 Scott Barnett
	
	I'd appreciate any improvements or additions to be submitted back
	to benefit the entire community :)
	
	Works with both PHP 4 and PHP 5
	
	The examples should be pretty self explanatory. If you require more
	information, please visit http://adldap.sourceforge.net/
	
	
	This library is free software; you can redistribute it and/or
	modify it under the terms of the GNU Lesser General Public
	License as published by the Free Software Foundation; either
	version 2.1 of the License, or (at your option) any later version.
	
	This library is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
	Lesser General Public License for more details.

	********************************************************************
	Something to keep in mind is that Active Directory is a permissions
	based directory. If you bind as a domain user, you can't fetch as
	much information on other users as you could as a domain admin.
	********************************************************************

	FUNCTIONS:
		
	authenticate($username,$password)
	Authenticate to the directory with a specific username and password
	
	user_info($user,$fields=NULL)
	Returns an array of information for a specific user

	user_groups($user,$recursive=NULL)
	Returns an array of groups that a user is a member off

	user_ingroup($user,$group,$recursive=NULL)
	Returns true if the user is a member of the group
	
	group_info($group)
	Returns an array of information for a specific group
	
	all_users($include_desc = false, $search = "*", $sorted = true)
	Returns all AD users (expensive on resources)
	
	all_groups($include_desc = false, $search = "*", $sorted = true)
	Returns all AD groups (expensive on resources)

*/

// Different type of accounts in AD
define ('ADLDAP_NORMAL_ACCOUNT', 805306368);
define ('ADLDAP_WORKSTATION_TRUST', 805306369);
define ('ADLDAP_INTERDOMAIN_TRUST', 805306370);
define ('ADLDAP_SECURITY_GLOBAL_GROUP', 268435456);
define ('ADLDAP_DISTRIBUTION_GROUP', 268435457);
define ('ADLDAP_SECURITY_LOCAL_GROUP', 536870912);
define ('ADLDAP_DISTRIBUTION_LOCAL_GROUP', 536870913);

class adLDAP {
	// BEFORE YOU ASK A QUESTION, PLEASE READ THE FAQ
	// http://adldap.sourceforge.net/faq.php

	// You will need to edit these variables to suit your installation
	var $_account_suffix="@mydcomain.local";
	var $_base_dn = "DC=mydomain,DC=local"; 
	
	// An array of domain controllers. Specify multiple controllers if you 
	// would like the class to balance the LDAP queries amongst multiple servers
	var $_domain_controllers = array ("dc01.mydomain.local");
	
	// optional account with higher privileges for searching
	var $_ad_username=NULL;
	var $_ad_password=NULL;
	
	// AD does not return the primary group. http://support.microsoft.com/?kbid=321360
	// This tweak will resolve the real primary group, but may be resource intensive. 
	// Setting to false will fudge "Domain Users" and is much faster. Keep in mind though that if
	// someone's primary group is NOT domain users, this is obviously going to bollocks the results
	var $_real_primarygroup=true;
	
	// When querying group memberships, do it recursively
	// eg. User Fred is a member of Group A, which is a member of Group B, which is a member of Group C
	// user_ingroup("Fred","C") will returns true with this option turned on, false if turned off
	var $_recursive_groups=true;
	
	// You should not need to edit anything below this line
	//******************************************************************************************
	
	//other variables
	var $_user_dn;
	var $_user_pass;
	var $_conn;
	var $_bind;

	// default constructor
	function adLDAP(){
		//connect to the LDAP server as the username/password
		$this->_conn = ldap_connect($this->random_controller());
		ldap_set_option($this->_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
		ldap_set_option($this->_conn, LDAP_OPT_REFERRALS, 0); //disable plain text passwords
		return true;
	}

	// default destructor
	function __destruct(){ ldap_close ($this->_conn); }

	function random_controller(){
		//select a random domain controller
		mt_srand(doubleval(microtime()) * 100000000);
		return ($this->_domain_controllers[array_rand($this->_domain_controllers)]);
	}

	// authenticate($username,$password)
	//	Authenticate to the directory with a specific username and password
	//	Extremely useful for validating login credentials
	function authenticate($username,$password){
		//validate a users login credentials
		$returnval=false;
		
		if ($username!=NULL && $password!=NULL){ //prevent null bind
			$this->_user_dn=$username.$this->_account_suffix;
			$this->_user_pass=$password;
			
			$this->_bind = @ldap_bind($this->_conn,$this->_user_dn,$this->_user_pass);
			if ($this->_bind){ $returnval=true; }
		}
		return ($returnval);
	}
	
	// rebind()
	//	Binds to the directory with the default search username and password
	//	specified above.
	function rebind(){
		//connect with another account to search with if necessary
		$ad_dn=$this->_ad_username.$this->_account_suffix;
		$this->_bind = @ldap_bind($this->_conn,$ad_dn,$this->_ad_password);
		if ($this->_bind){ return (true); }
		return (false);
	}

	// user_info($user,$fields)
	//	Returns an array of information for a specific user
	function user_info($user,$fields=NULL){
		if ($user!=NULL){
			if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
			
			if ($this->_bind){ //perform the search and grab all their details
				$filter="samaccountname=".$user;
				if ($fields==NULL){
					$fields=array("samaccountname","mail","memberof","department","displayname","telephonenumber","primarygroupid");
					//$fields=array("*");
				}
				$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
				$entries = ldap_get_entries($this->_conn, $sr);
				
				// AD does not return the primary group in the ldap query, we may need to fudge it
				if ($this->_real_primarygroup){
					$entries[0]["memberof"][]=$this->group_cn($entries[0]["primarygroupid"][0]);
				} else {
					$entries[0]["memberof"][]="CN=Domain Users,CN=Users,".$this->_base_dn;
				}
				
				//echo ("<pre>"); print_r($entries);

				$entries[0]["memberof"]["count"]++;
				return ($entries);
			}
		}

		return (false);
	}
	
	// user_groups($user)
	//	Returns an array of groups that a user is a member off
	function user_groups($user,$recursive=NULL){
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		if ($recursive==NULL){ $recursive=$this->_recursive_groups; }
		
		if ($this->_bind){
			//search the directory for their information
			$info=@$this->user_info($user,array("memberof"));
			//echo ("<pre>"); print_r($info);
			$groups=$info[0]["memberof"]; //presuming the entry returned is our guy (unique usernames)
			
			$group_array=$this->nice_names($groups);

			if ($recursive){
				foreach ($group_array as $id => $group_name){
					$extra_groups=$this->recursive_groups($group_name);
					$group_array=array_merge($group_array,$extra_groups);
				}
			}
			
			return ($group_array);
		}
		return (false);	
	}
	
	// user_ingroup($user,$group)
	//	Returns true if the user is a member of the group
	function user_ingroup($user,$group,$recursive=NULL){
		if ($recursive==NULL){ $recursive=$this->_recursive_groups; }
		
		if (($user!=NULL) && ($group!=NULL)){
			if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary

			if ($this->_bind){
				$groups=$this->user_groups($user,array("memberof"),$recursive);
				if (in_array($group,$groups)){ return (true); }
			}
		}
		return (false);
	}
	
	function recursive_groups($group){
		$ret_groups=array();
		
		$groups=$this->group_info($group,array("memberof"));
		$groups=$groups[0]["memberof"];

		if ($groups){
			$group_names=$this->nice_names($groups);
			$ret_groups=array_merge($ret_groups,$group_names); //final groups to return
			
			foreach ($group_names as $id => $group_name){
				$child_groups=$this->recursive_groups($group_name);
				$ret_groups=array_merge($ret_groups,$child_groups);
			}

		}

		return ($ret_groups);
	}

	// take an ldap query and return the nice names, without all the LDAP prefixes (eg. CN, DN)
	function nice_names($groups){

		$group_array=array();
		for ($i=0; $i<$groups["count"]; $i++){ //for each group
			$line=$groups[$i];
			
			if (strlen($line)>0){ 
				//more presumptions, they're all prefixed with CN=
				//so we ditch the first three characters and the group
				//name goes up to the first comma
				$bits=explode(",",$line);
				$group_array[]=substr($bits[0],3,(strlen($bits[0])-3));
			}
		}
		return ($group_array);	
	}



	
	function group_cn($gid){
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		// coping with AD not returning the primary group
		// http://support.microsoft.com/?kbid=321360
		// for some reason it's not possible to search on primarygrouptoken=XXX
		// if someone can show otherwise, I'd like to know about it :)
		// this way is resource intensive and generally a pain in the @#%^
		
		$r=false;
		
		if ($this->_bind){
			$filter="(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP ."))";
			$fields=array("primarygrouptoken","samaccountname","distinguishedname");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
			
			for ($i=0; $i<$entries["count"]; $i++){
				if ($entries[$i]["primarygrouptoken"][0]==$gid){
					$r=$entries[$i]["distinguishedname"][0];
					$i=$entries["count"];
				}
			}
		}
		return ($r);
	}
	
	// group_info($group_name,$fields=NULL)
	// Returns an array of information for a specified group
	function group_info($group_name,$fields=NULL){
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary

		if ($this->_bind){
			//escape brackets
			$group_name=str_replace("(","\(",$group_name);
			$group_name=str_replace(")","\)",$group_name);
			
			$filter="(&(objectCategory=group)(name=".$group_name."))";
			//echo ($filter."<br>");
			if ($fields==NULL){ $fields=array("member","memberof","cn","description","distinguishedname","objectcategory","samaccountname"); }
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
			//print_r($entries);
			return ($entries);
		}
		return (false);
	}

	function all_users($include_desc = false, $search = "*", $sorted = true){
		// Returns all AD users
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			$users_array = array();
		
			//perform the search and grab all their details
			$filter = "(&(objectClass=user)(samaccounttype=". ADLDAP_NORMAL_ACCOUNT .")(objectCategory=person)(cn=$search))";
			$fields=array("samaccountname","displayname");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
		
			for ($i=0; $i<$entries["count"]; $i++){
				if( $include_desc && strlen($entries[$i]["displayname"][0]) > 0 )
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["displayname"][0];
				else if( $include_desc )
					$users_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
				else
					array_push($users_array, $entries[$i]["samaccountname"][0]);
			}
			if( $sorted ){ asort($users_array); }
			return ($users_array);
		}
		return (false);
	}
	
	function all_groups($include_desc = false, $search = "*", $sorted = true){
		// Returns all AD groups
		if ($this->_ad_username!=NULL){ $this->rebind(); } //bind as a another account if necessary
		
		if ($this->_bind){
			$groups_array = array();
		
			//perform the search and grab all their details
			$filter = "(&(objectCategory=group)(samaccounttype=". ADLDAP_SECURITY_GLOBAL_GROUP .")(cn=$search))";
			$fields=array("samaccountname","description");
			$sr=ldap_search($this->_conn,$this->_base_dn,$filter,$fields);
			$entries = ldap_get_entries($this->_conn, $sr);
			
			for ($i=0; $i<$entries["count"]; $i++){
				if( $include_desc && strlen($entries[$i]["description"][0]) > 0 )
					$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["description"][0];
				else if( $include_desc )
					$groups_array[ $entries[$i]["samaccountname"][0] ] = $entries[$i]["samaccountname"][0];
				else

					array_push($groups_array, $entries[$i]["samaccountname"][0]);
			}
			if( $sorted ){ asort($groups_array); }
			return ($groups_array);
		}
		return (false);
	}
} // End class

?>
