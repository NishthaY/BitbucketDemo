<?php

class Menu_model extends CI_Model {

    function __construct()
    {
        parent::__construct();

    }

    public function sidebar( ) {

        $acls = GetSessionObject('acls');

        if ( IsAuthenticated() ) return $this->everything_sidebar();
        if ( in_array('Manager', $acls) ) return $this->company_sidebar();
        if ( in_array('Parent Manager', $acls) || in_array('Parent User', $acls) ) return $this->company_parent_sidebar();
        if ( in_array('Staff', $acls) ) return $this->staff_sidebar();

        return $this->standard_sidebar();

    }
    public function staff_sidebar() {
        $selected_parent = $this->get_parent_token();
        $selected_child = $this->get_child_token();

        $menu = new Menu();
        $this->_add_welcome_menu($menu, $selected_parent, $selected_child);
        $this->_add_company_parent_menu($menu, $selected_parent, $selected_child);
        $this->_add_companies_menu($menu, $selected_parent, $selected_child);
        $this->_add_users_menu($menu, $selected_parent, $selected_child);
        $this->_add_support_menu($menu, $selected_parent, $selected_child);
        return $menu;
    }
    public function everything_sidebar( ) {

        $selected_parent = $this->get_parent_token();
        $selected_child = $this->get_child_token();


        $menu = new Menu();
        $this->_add_welcome_menu($menu, $selected_parent, $selected_child);
        $this->_add_company_parent_menu($menu, $selected_parent, $selected_child);
        $this->_add_companies_menu($menu, $selected_parent, $selected_child);
        $this->_add_users_menu($menu, $selected_parent, $selected_child);
        $this->_add_support_menu($menu, $selected_parent, $selected_child);
        return $menu;

    }
    public function company_sidebar( ) {

        $selected_parent = $this->get_parent_token();
        $selected_child = $this->get_child_token();

        $menu = new Menu();
        $this->_add_welcome_menu($menu, $selected_parent, $selected_child);
        $this->_add_reports_menu($menu, $selected_parent, $selected_child);
        $this->_add_users_menu($menu, $selected_parent, $selected_child);

        return $menu;
    }
    public function company_parent_sidebar( ) {

        $selected_parent = $this->get_parent_token();
        $selected_child = $this->get_child_token();

        $menu = new Menu();
        $this->_add_welcome_menu($menu, $selected_parent, $selected_child);
        $this->_add_companies_menu($menu, $selected_parent, $selected_child);
        if( IsAuthenticated("parent_company_write") )
        {
            $this->_add_users_menu($menu, $selected_parent, $selected_child);
        }

        return $menu;
    }
    public function standard_sidebar( ) {

        $selected_parent = $this->get_parent_token();
        $selected_child = $this->get_child_token();

        $menu = new Menu();
        $this->_add_welcome_menu($menu, $selected_parent, $selected_child);
        $this->_add_reports_menu($menu, $selected_parent, $selected_child);
        return $menu;
    }
    private function _add_welcome_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "dashboard" ) $selected = true;
        $disabled = false; if ( $selected_parent == "dashboard" ) $disabled = true;
        $description = ""; if ( $selected_parent == "dashboard" && $selected_child != "" ) $description = "Welcome to Advice2Pay!";
        $menu->add("Dashboard", $description, base_url("dashboard"), false, $selected, $disabled, "ion-home");

    }
    private function _add_reports_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "reports" ) $selected = true;
        $disabled = false; if ( $selected_parent == "reports" ) $disabled = true;
        $description = ""; if ( $selected_parent == "reports" && $selected_child != "" ) $description = "Reports";
        $menu->add("Reports", $description, base_url("reports"), false, $selected, $disabled, "ion-document-text");

    }
    private function _add_company_parent_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "parent" ) $selected = true;
        $disabled = false; if ( $selected_parent == "parent" ) $disabled = true;
        $description = ""; if ( $selected_parent == "parent" && $selected_child != "" ) $description = "Manage application parents.";
        $menu->add("Parents", $description, base_url("parents/manage"), false, $selected, $disabled, "ion-ios7-box"); //ion-link
    }
    private function _add_companies_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "companies" ) $selected = true;
        $disabled = false; if ( $selected_parent == "companies" ) $disabled = true;
        $description = ""; if ( $selected_parent == "companies" && $selected_child != "" ) $description = "Manage application companies.";
        $menu->add("Companies", $description, base_url("companies/manage"), false, $selected, $disabled, "ion-briefcase");
    }
    private function _add_company_parent_companies_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "companies" ) $selected = true;
        $disabled = false; if ( $selected_parent == "companies" ) $disabled = true;
        $description = ""; if ( $selected_parent == "companies" && $selected_child != "" ) $description = "Manage application companies.";
        $menu->add("Companies", $description, base_url("parents/companies"), false, $selected, $disabled);
    }
    private function _add_users_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "users"  ) $selected = true;
        $disabled = false; if ( $selected_parent == "users" ) $disabled = true;
        $description = ""; if ( $selected_parent == "users" && $selected_child != "" ) $description = "Manage company users.";
        $menu->add("Users", $description, base_url("users/manage"), false, $selected, $disabled, "ion-person-stalker");
    }
    private function _add_support_menu( $menu, $selected_parent, $selected_child )
    {
        $selected = false; if ( $selected_parent == "support"  ) $selected = true;
        $disabled = false; if ( $selected_parent == "support" ) $disabled = true;
        $description = ""; if ( $selected_parent == "support" && $selected_child != "" ) $description = "Support Tools.";
        $menu->add("Support", $description, base_url("support/manage"), false, $selected, $disabled, "fa fa-life-ring");
    }


    private function get_parent_token() {
        $parent_token = fRight(replaceFor(current_url(), base_url(), ""), "/");
        if ( strpos($parent_token, "/") !== FALSE ) $parent_token = fLeftBack($parent_token, "/");
        return $parent_token;
    }
    private function get_child_token() {
        $child_token = "";
        $parent_token = fRight(replaceFor(current_url(), base_url(), ""), "/");
        if ( strpos($parent_token, "/") !== FALSE ) $child_token = fRight($parent_token, "/");
        if ( strpos($child_token, "/") !== FALSE ) $child_token = fLeftBack($child_token, "/");
        return $child_token;
    }



}


/* End of file menu_model.php */
/* Location: ./system/application/models/menu_model.php */
