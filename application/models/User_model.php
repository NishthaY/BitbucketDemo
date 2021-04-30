<?php

class User_model extends CI_Model {

    public function __construct()
    {
        parent::__construct();

        $this->db = $this->load->database('default', TRUE);

    }
    public function super_user_email_address_list()
    {
        $file = "database/sql/user/UserEmailByPermissionAllSELECT.sql";
        $results = GetDBResults( $this->db, $file, [] );
        if ( count($results) === 0 ) return array();

        $list = array();
        foreach($results as $row)
        {
            if ( GetArrayStringValue('EmailAddress', $row) !== '' ) $list[] = GetArrayStringValue('EmailAddress', $row);
        }
        return $list;
    }
    public function is_super_user( $user_id )
    {
        $file = "database/sql/acls/IsSuperUser.sql";
        $vars = array(
            getIntValue($user_id)
        );
        return GetDBExists( $this->db, $file, $vars );
    }
    public function is_authenticated ( $acls=array(), $actions=array() )
    {
        $acl_list = "";
        foreach($acls as $acl)
        {
            // Only review ACLS that do not have a target.
            if ( strpos($acl, ":") === FALSE )
            {
                $acl_list .= "'{$acl}',";
            }

        }
        $acl_list= fLeftBack($acl_list, ",");

        $action_list = "";
        foreach($actions as $action)
        {
            $action_list .= "'{$action}',";
        }
        $action_list= fLeftBack($action_list, ",");

        $replacefor = array();
        $replacefor["{ACL_LIST}"] = $acl_list;
        $replacefor["{ACTION_LIST}"] = $action_list;

        $file = "database/sql/acls/IsAuthenticated.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars, $replacefor );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            $value = getArrayStringValue("IsAuthenticated", $results);
            if ( $value === 't' ) return TRUE;
            if ( $value === 'f' ) return FALSE;
        }
        throw new Exception("Unexpected situation.");
    }
    public function is_authenticated_by_target ( $acls, $actions, $target, $target_id, $user_id )
    {
        if ( ! is_array($acls) ) throw new Exception("Missing required input array: acls");
        if ( ! is_array($actions) ) throw new Exception("Missing required input array: actions");
        if ( ! GetStringValue($target) === '' ) throw new Exception("Missing required input array: target");
        if ( ! GetStringValue($target_id) === '' ) throw new Exception("Missing required input array: target_id");
        if ( ! GetStringValue($user_id) === '' ) throw new Exception("Missing required input array: user_id");

        $acl_list = "";
        foreach($acls as $acl)
        {
            if ( strpos($acl, ":") !== FALSE)
            {
                // Only accept an ACL as valid if the ACL in the session has a matching
                // target and target_id.
                $acl_name = fLeft($acl, ":");
                $acl_target_id = fRightBack($acl, ":");
                $acl_target = strtolower(fBetween($acl, ":", ":"));
                if ( strtolower($target) === $acl_target && $acl_target_id === $target_id )
                {
                    $acl_list .= "'{$acl_name}',";
                }

            }

        }
        $acl_list= fLeftBack($acl_list, ",");
        if ( $acl_list === '' ) return FALSE;

        $action_list = "";
        foreach($actions as $action)
        {
            $action_list .= "'{$action}',";
        }
        $action_list= fLeftBack($action_list, ",");

        $replacefor = array();
        $replacefor["{ACL_LIST}"] = $acl_list;
        $replacefor["{ACTION_LIST}"] = $action_list;

        $file = "database/sql/acls/IsAuthenticatedByTarget.sql";
        $vars = array(
            GetIntValue($user_id)
            , GetStringValue($target)
            , GetIntValue($target_id)
        );
        $results = GetDBResults( $this->db, $file, $vars, $replacefor );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            $value = getArrayStringValue("IsAuthenticated", $results);
            if ( $value === 't' ) return TRUE;
            if ( $value === 'f' ) return FALSE;
        }
        throw new Exception("Unexpected situation.");
    }
    public function delete_user( $user_id ) {
        $file = "database/sql/user/UserUPDATE_LogicalDeleteById.sql";
        $vars = array(
            getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $payload = array();
        $payload = array_merge($payload, array('Id'=>$user_id));
        $payload = array_merge($payload, array('FirstName'=>GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName'=>GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress'=>GetArrayStringValue('email_address', $user)));
        AuditIt("Logically deleted user.", $payload);
    }
    public function count_users() {
        $file = "database/sql/user/UserCOUNT.sql";
        $vars = array();
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 1 )
        {
            $results = $results[0];
            return getArrayStringValue("Count", $results);
        }
        throw new Exception("Unexpected situation.");
    }
    public function get_user_preference( $user_id, $group, $group_code ) {
        $file = "database/sql/user/SelectUserPreferenceByGroupAndGroupCode.sql";
        $vars = array(
            getIntValue($user_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();
        if ( count($results) > 1 ) throw new Exception("Found too many user preferences.  Expected one or none.");
        return $results[0];
    }
    public function save_user_preference(  $user_id, $group, $group_code, $value  ) {
        $pref = $this->get_user_preference($user_id, $group, $group_code);
        if ( empty($pref) ) {
            $this->insert_user_preference($user_id, $group, $group_code, $value);
        }else{
            $this->update_user_preference($user_id, $group, $group_code, $value);
        }
    }
    public function insert_user_preference( $user_id, $group, $group_code, $value ) {
        $file = "database/sql/user/InsertUserPreference.sql";
        $vars = array(
            getIntValue($user_id)
            , ( $group == null ? null : getStringValue($group) )
            , ( $group_code == null ? null : getStringValue($group_code) )
            , ( $value == null ? null : getStringValue($value) )
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function update_user_preference( $user_id, $group, $group_code, $value ) {
        $file = "database/sql/user/UpdateUserPreference.sql";
        $vars = array(
            getStringValue($value)
            , getIntValue($user_id)
            , getStringValue($group)
            , getStringValue($group_code)
        );
        ExecuteSQL( $this->db, $file, $vars );
    }
    public function enable_user( $user_id ) {
        $file = "database/sql/user/EnableUser.sql";
        $vars = array(
            getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>$user_id));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        AuditIt("User enabled", $payload);
    }
    public function disable_user( $user_id ) {
        $file = "database/sql/user/DisableUser.sql";
        $vars = array(
            getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>$user_id));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        AuditIt("User disabled.", $payload);
    }
    public function get_all_users( $company_id )
    {
        $file = "database/sql/user/UserSELECT_ByCompanyId_ALL.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $users = GetDBResults($this->db, $file, $vars);
        if (count($users) == 0) return array();
        return $users;
    }
    public function get_all_active_users( $company_id )
    {
        $file = "database/sql/user/UserSELECT_ByCompanyId_Active.sql";
        $vars = array(
            getIntValue($company_id)
        );
        $users = GetDBResults($this->db, $file, $vars);
        if (count($users) == 0) return array();

        $updated_users = array();
        foreach ($users as $user)
        {
            $user_id = GetArrayStringValue('user_id', $user);
            $file = "database/sql/user/UserAclSELECT_IsManager.sql";
            $vars = array(
                getIntValue($user_id)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( count($results) != 1 ) throw new Exception("Unexpected results from db.");
            $results = $results[0];

            $user['is_manager'] = $results['is_manager'];
            $updated_users[] = $user;
        }

        return $updated_users;
    }
    public function get_all_users_for_parent( $company_parent_id )
    {
        $file = "database/sql/user/UserSELECT_ByCompanyParentId.sql";
        $vars = array(
            getIntValue($company_parent_id)
        );
        $users = GetDBResults( $this->db, $file, $vars );
        if ( count($users) == 0) return array();

        $updated_users = array();
        foreach ($users as $user)
        {
            $user_id = GetArrayStringValue('user_id', $user);
            $file = "database/sql/user/UserAclSELECT_IsManager.sql";
            $vars = array(
                getIntValue($user_id)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( count($results) != 1 ) throw new Exception("Unexpected results from db.");
            $results = $results[0];

            $user['is_manager'] = $results['is_manager'];
            $updated_users[] = $user;
        }

        return $updated_users;

    }
    public function create_user( $email, $first_name, $last_name, $password ) {

        // Hash the password.
        $hashed_password = A2PHashClearText($password);

        $file = "database/sql/user/CreateUser.sql";
        $vars = array(
            getStringValue($first_name)
            , getStringValue($last_name)
            , getStringValue($email)
            , getStringValue($hashed_password)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user($email);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        AuditIt("User created.", $payload);
    }
    public function update_user_password_by_id( $user_id, $password ) {

        $hashed_password = A2PHashClearText($password);

        $file = "database/sql/user/UpdateUserPasswordById.sql";
        $vars = array(
            getStringValue($hashed_password)
            , getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        AuditIt("User password updated.", $payload);
    }
    public function update_user_by_id( $user_id, $email, $firstname, $lastname ) {
        $file = "database/sql/user/UserUPDATE_ById.sql";
        $vars = array(
            getStringValue($firstname)
            , getStringValue($lastname)
            , getStringValue($email)
            , getIntValue($user_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        AuditIt("User profile updated.", $payload);
    }

    public function get_user( $email ) {

        $file = "database/sql/user/UserSELECT_ByEmail.sql";
        $vars = array(
            getStringValue($email)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1 ) throw new Exception("Found too many result for user [{$email}]");
        if ( count($results) == 0) return array();
        return $results[0];

    }
    public function get_user_by_id( $user_id ) {

        // Look up the object.
        $file = "database/sql/user/UserSELECT_ById.sql";
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) > 1 ) throw new Exception("Found too many result for user with id [{$user_id}]");
        if ( count($results) == 0) return array();
        $user = $results[0];

        // Add to the user object a flag indicating if they are in a management role.
        $file = "database/sql/user/UserAclSELECT_IsManager.sql";
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1 ) throw new Exception("Found too many result for user with id [{$user_id}]");
        $results = $results[0];
        $user['is_manager'] = $results['is_manager'];

        return $user;

    }
    public function get_user_acls_by_id( $user_id ) {

        $file = "database/sql/user/SelectUserAclsById.sql";
        $vars = array(
            getIntValue($user_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) == 0) return array();


        // Turn out output from the DB into a flat list of ACLs
        $out = array();
        foreach($results as $row)
        {
            $name = GetArrayStringValue("Name", $row);
            $target = GetArrayStringValue("Target", $row);
            $target_id = GetArrayStringValue("TargetId", $row);

            $item = "";
            if ( $target === '' && $target_id === '' )
            {
                $item = $name;
            }
            if ( $target !== '' && $target_id !== '' )
            {
                $item = "{$name}:{$target}:{$target_id}";
            }

            if ( $item !== '' )
            {
                $out[] = $item;
            }

        }
        return $out;

    }
    public function link_user_to_company( $user_id, $company_id ) {

        $file = "database/sql/user/LinkUserToCompany.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


        $user = $this->get_user_by_id($user_id);
        $company = $this->Company_model->get_company($company_id);

    }
    public function unlink_user_to_company( $user_id, $company_id ) {

        $file = "database/sql/user/UnlinkUserToCompany.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


    }
    public function is_user_linked_to_company( $user_id, $company_id ) {

        $file = "database/sql/user/IsUserLinkedToCompany.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $results = $results[0];
        if ( getArrayStringValue("linked", $results) == "1" ) return true;
        return false;

    }
    public function link_user_to_parent( $user_id, $company_parent_id ) {

        $file = "database/sql/user/LinkUserToParent.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_parent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function unlink_user_to_parent( $user_id, $company_parent_id ) {

        $file = "database/sql/user/UnlinkUserToParent.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_parent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

    }
    public function is_user_linked_to_parent( $user_id, $company_parent_id ) {

        $file = "database/sql/user/IsUserLinkedToParent.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_parent_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $results = $results[0];
        if ( getArrayStringValue("linked", $results) == "1" ) return true;
        return false;

    }
    public function grant_user_acl ( $user_id, $acl_name, $target=null, $target_id=null ) {

        // Grab the ACL they want to add.
        $file = "database/sql/acls/AclSELECT_PermissionByName.sql";
        $vars = array(
            getStringValue($acl_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $acl = $results[0];
        if ( empty($acl) ) throw new Exception("Could not find permission for assignment.");
        $acl_id = getArrayIntValue("Id", $acl);

        // Check to see if the user already has this permission.
        $file = "database/sql/user/DoesUserHavePermission.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($acl_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $has_permission = getArrayStringValue("has_permission", $results[0]);

        // Check to see if the user already has this permission specific to the target.
        if ( $has_permission !== 'f' && GetStringValue($target) !== "" && GetStringValue($target_id) !== '' )
        {
            $file = "database/sql/user/DoesUserHavePermissionForTarget.sql";
            $vars = array(
                getIntValue($user_id)
                , getIntValue($acl_id)
                , GetStringValue($target)
                , GetIntValue($target_id)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
            $has_permission = getArrayStringValue("has_permission", $results[0]);
        }


        // add permission if needed.
        if ( $has_permission != "t" )
        {
            $file = "database/sql/acls/UserAclINSERT_UserPermission.sql";
            $vars = array(
                getIntValue($user_id)
                , getIntValue($acl_id)
                , GetStringValue($target) === '' ? null : GetStringValue($target)
                , GetStringValue($target_id) === '' ? null : GetIntValue($target_id)
            );
            ExecuteSQL( $this->db, $file, $vars );

            $payload = array();
            $payload["acl_name"] = $acl_name;
            if ( GetStringValue($target) !== '' )
            {
                $payload["target"] = strtoupper($target);
                $payload["target_id"] = strtoupper($target_id);
            }
            $user = $this->get_user_by_id($user_id);
            $payload = array_merge($payload, $user);

            // Audit this transaction.
            $user = $this->User_model->get_user_by_id($user_id);
            $payload = array();
            $payload = array_merge($payload, array('AclName' => $acl_name));
            if ( GetStringValue($target) !== '' ) $payload = array_merge($payload, array('Target' => GetStringValue($target)));
            if ( GetStringValue($target) !== '' ) $payload = array_merge($payload, array('TargetId' => GetStringValue($target_id)));
            $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
            $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
            $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
            AuditIt("User granted permission.", $payload);

        }

    }

    public function deny_user_acl ( $user_id, $acl_name, $target=null, $target_id=null ) {

        // Grab the ACL they want to change.
        $file = "database/sql/acls/AclSELECT_PermissionByName.sql";
        $vars = array(
            getStringValue($acl_name)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( empty($results) ) return array();
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $acl = $results[0];
        if ( empty($acl) ) throw new Exception("Could not find permission for assignment.");
        $acl_id = getArrayIntValue("Id", $acl);

        // Does this user already have this permission?
        $file = "database/sql/user/DoesUserHavePermission.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($acl_id)
        );
        $results = GetDBResults( $this->db, $file, $vars );
        if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
        $has_permission = getArrayStringValue("has_permission", $results[0]);

        // Check to see if the user already has this permission specific to the target.
        if ( $has_permission !== 'f' && GetStringValue($target) !== "" && GetStringValue($target_id) !== '' )
        {
            $file = "database/sql/user/DoesUserHavePermissionForTarget.sql";
            $vars = array(
                getIntValue($user_id)
            , getIntValue($acl_id)
            , GetStringValue($target)
            , GetIntValue($target_id)
            );
            $results = GetDBResults( $this->db, $file, $vars );
            if ( count($results) != 1) throw new Exception("Unexpected results from the database.");
            $has_permission = getArrayStringValue("has_permission", $results[0]);
        }

        // Yes, they do have the permission.  Remove it.
        if ( $has_permission == "t" )
        {
            if ( GetStringValue($target) !== '' )
            {
                $file = "database/sql/acls/UserAclDELETE_TargetedUserPermission.sql";
                $vars = array(
                    getIntValue($user_id),
                    getIntValue($acl_id),
                    GetStringValue($target) === '' ? null : GetStringValue($target),
                    GetStringValue($target_id) === '' ? null : GetIntValue($target_id)
                );
            }
            else
            {
                $file = "database/sql/acls/UserAclDELETE_UserPermission.sql";
                $vars = array(
                    getIntValue($user_id),
                    getIntValue($acl_id)
                );
            }
            ExecuteSQL( $this->db, $file, $vars );

            $payload = array();
            $payload["acl_name"] = $acl_name;
            if ( GetStringValue($target) !== '' )
            {
                $payload["target"] = strtoupper($target);
                $payload["target_id"] = strtoupper($target_id);
            }

            // Audit this transaction.
            $user = $this->User_model->get_user_by_id($user_id);
            $payload = array();
            $payload = array_merge($payload, array('AclName' => $acl_name));
            if ( GetStringValue($target) !== '' ) $payload = array_merge($payload, array('Target' => GetStringValue($target)));
            if ( GetStringValue($target) !== '' ) $payload = array_merge($payload, array('TargetId' => GetStringValue($target_id)));
            $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
            $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
            $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
            AuditIt("User permission removed.", $payload);

        }

    }

    /**
     * get_users_responsible_for
     *
     * This function will return a list of all users for the specified
     * parent company.  Those users will have both the IsManager and
     * ResponsibleFor boolean attributes set.  The ResponsibleFor attribute
     * reflects if the user is in a responsible for relationship with the
     * specified company.  The IsManager attribute will denotes if the user
     * is a manager for the parent company.
     *
     * @param $company_parent_id
     * @param $company_id
     * @return array
     * @throws Exception
     */
    public function get_users_responsible_for($company_parent_id, $company_id )
    {

        $file = "database/sql/user/UserSELECT_ResponsibleFor.sql";
        $vars = array(
            getIntValue($company_id)
            , getIntValue($company_parent_id)
        );
        $users = GetDBResults( $this->db, $file, $vars );

        $updated_users = array();
        foreach ($users as $user)
        {
            $user_id = GetArrayStringValue('Id', $user);
            $file = "database/sql/user/UserAclSELECT_IsManager.sql";
            $vars = array(
                getIntValue($user_id)
            );
            $results = GetDBResults($this->db, $file, $vars);
            if ( count($results) != 1 ) throw new Exception("Unexpected results from db.");
            $results = $results[0];

            $user['IsManager'] = $results['is_manager'];
            $updated_users[] = $user;
        }

        return $updated_users;

    }
    public function insert_user_is_responsible_for_company( $user_id, $company_id, $company_parent_id )
    {
        $file = "database/sql/user/UserINSERT_ResponsibleFor.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_id)
            , getIntValue($company_parent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );


        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $company = $this->Company_model->get_company($company_id);
        $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("User assigned responsibility for company.", $payload);

    }
    public function delete_user_is_responsible_for_company( $user_id, $company_id, $company_parent_id )
    {
        $file = "database/sql/user/UserDELETE_ResponsibleFor.sql";
        $vars = array(
            getIntValue($user_id)
            , getIntValue($company_id)
            , getIntValue($company_parent_id)
        );
        ExecuteSQL( $this->db, $file, $vars );

        // Audit this transaction.
        $user = $this->User_model->get_user_by_id($user_id);
        $company = $this->Company_model->get_company($company_id);
        $parent = $this->CompanyParent_model->get_companyparent($company_parent_id);
        $payload = array();
        $payload = array_merge($payload, array('UserId'=>GetArrayStringValue('user_id', $user)));
        $payload = array_merge($payload, array('FirstName' => GetArrayStringValue('first_name', $user)));
        $payload = array_merge($payload, array('LastName' => GetArrayStringValue('last_name', $user)));
        $payload = array_merge($payload, array('EmailAddress' => GetArrayStringValue('email_address', $user)));
        $payload = array_merge($payload, array('CompanyId'=>GetArrayStringValue('company_id', $company)));
        $payload = array_merge($payload, array('CompanyName' => GetArrayStringValue('company_name', $company)));
        $payload = array_merge($payload, array('CompanyParentId'=>GetArrayStringValue('Id', $parent)));
        $payload = array_merge($payload, array('CompanyParentName' => GetArrayStringValue('Name', $parent)));
        AuditIt("User unassigned responsibility for company.", $payload);

    }


    public function hard_delete_user( $user_id, $authenticated_user_id, $verbose=false )
    {
        if ( getStringValue($user_id) === "" ) throw new Exception("Missing required input user_id");

        $user = $this->get_user_by_id($user_id);

        $tables = array();
        $tables[] = "Login";
        $tables[] = "HistoryFailedJob";
        $tables[] = "UserAcl";
        $tables[] = "UserCompanyParentRelationship";
        $tables[] = "UserPreference";

        $template = 'delete from "{TABLE}" where "UserId" = ?';
        $vars = array(
            getIntValue($user_id)
        );

        foreach( $tables as $table )
        {
            if ( $verbose ) print "Removing user data from table {$table}.\n";
            $replacefor = array();
            $replacefor["{TABLE}"] = $table;
            ExecuteSQL( $this->db, $template, $vars, $replacefor );
        }

        if ( $verbose ) print "Removing user data from table User.\n";
        $sql = 'delete from "User" where "Id" = ?';
        ExecuteSQL( $this->db, $sql, $vars );

        // Audit this action has completed.
        AuditIt("Delete user and user data.", $user, $authenticated_user_id, A2P_COMPANY_ID);

    }

}


/* End of file User_model.php */
/* Location: ./system/application/models/User_model.php */
