<?php
/*
 * Gallery - a web based photo album viewer and editor
 * Copyright (C) 2000-2007 Bharat Mediratta
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or (at
 * your option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street - Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Mambo Open Source CMS integration written by Beckett Madden-Woods
 * <beckett@beckettmw.com> First version January 2004.
 *
 * $Id: User.php 16440 2007-05-23 14:49:06Z jenst $
*/
?>
<?php
class Mambo_User extends Abstract_User {
	var $db;
	var $prefix;
	var $fields;
	var $gid;

	function Mambo_User() {
		global $gallery;

		$this->db = $gallery->database{'mambo'};
		$this->prefix = $gallery->database{'user_prefix'};
		$this->fields = $gallery->database{'fields'};
		$this->gid = '';
		$this->isAdmin = false;
	}

	function loadByUid($uid) {
		$results = $this->db->query('SELECT ' . $this->fields{'uname'} . ', ' .
												$this->fields{'name'} . ', ' .
												$this->fields{'email'} . ', ' .
												$this->fields{'gid'} .
									' FROM ' . $this->prefix . 'users' .
									' WHERE ' . $this->fields{'uid'} . "='$uid'");

		$row = $this->db->fetch_row($results);
		$this->username = $row[0];
		$this->fullname = $row[1];
		$this->email = $row[2];
		$this->gid = $row[3];
		$this->isAdmin = $this->isGalleryAdmin();
		$this->canCreateAlbums = ($this->isAdmin || $this->canCreateRootAlbum());
		$this->uid = $uid;
	}

	function loadByUserName($uname) {
		$results = $this->db->query('SELECT ' . $this->fields{'uid'} . ', ' .
												$this->fields{'name'} . ', ' .
												$this->fields{'email'} . ', ' .
												$this->fields{'gid'} .
									' FROM ' . $this->prefix . 'users' .
									' WHERE ' . $this->fields{'uname'} . "='$uname'");

		$row = $this->db->fetch_row($results);
		$this->uid = $row[0];
		$this->fullname = $row[1];
		$this->email = $row[2];
		$this->gid = $row[3];
		$this->isAdmin = $this->isGalleryAdmin();
		$this->canCreateAlbums = ($this->isAdmin || $this->canCreateRootAlbum());
		$this->username = $uname;
	}

	/*
	 * Determine whether the Joomla/Mambo user has Gallery admin privileges
	 * based on the user's Joomla/Mambo authorization level
	 */
	function isGalleryAdmin() {
		global $MOS_GALLERY_PARAMS;

		/* Select minimum authorization level (set in component admin interface).
		 * Current choices are:
		 *
		 *	'Super Administrator'
		 *	'Publisher'
		 *	'Administrator'
		 *	'Editor'
		 *	'Manager'
		 *	'Author'
		 *	'Registered'
		*/

		$minAuthType = $MOS_GALLERY_PARAMS['minAuthType'];

		$results = $this->db->query('SELECT lft FROM ' . $this->prefix . "core_acl_aro_groups WHERE group_id='$minAuthType'");
		$row = $this->db->fetch_row($results);
		$minAuthLevel = $row[0];

		$results = $this->db->query('SELECT lft FROM ' . $this->prefix . "core_acl_aro_groups WHERE group_id='{$this->gid}'");
		$row = $this->db->fetch_row($results);
		$myAuthLevel = $row[0];

		return $myAuthLevel >= $minAuthLevel;
	}

		/*
	 * Determine whether the Mambo user has Gallery admin privileges
	 * based on the user's Mambo authorization level
	 */
	function canCreateRootAlbum() {
		global $MOS_GALLERY_PARAMS;

		/* Select minimum authorization level (set in component admin interface).
		 * Current choices are:
		 *
		 *	'Super Administrator'
		 *	'Publisher'
		 *	'Administrator'
		 *	'Editor'
		 *	'Manager'
		 *	'Author'
		 *	'Registered'
		*/

		$minAuthAlbums = $MOS_GALLERY_PARAMS['minAuthAlbums'];

		$results = $this->db->query('SELECT lft FROM ' . $this->prefix . "core_acl_aro_groups WHERE group_id='$minAuthAlbums'");
		$row = $this->db->fetch_row($results);
		$minAuthLevel = $row[0];

		$results = $this->db->query('SELECT lft FROM ' . $this->prefix . "core_acl_aro_groups WHERE group_id='{$this->gid}'");
		$row = $this->db->fetch_row($results);
		$myAuthLevel = $row[0];

		return $myAuthLevel >= $minAuthLevel;
	}
}

?>