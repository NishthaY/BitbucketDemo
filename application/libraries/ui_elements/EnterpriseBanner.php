<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class EnterpriseBanner
{
    protected $is_hidden;
    protected $description;
    protected $closable;

    function __construct($form_type=null )
    {
        $this->is_hidden = true;
        $this->description = "";
        $this->closable = false;
    }

    /**
     * Place the class generated in this function on dom objects that must
     * have different properties to compensate for when the banner is open or
     * closed.
     *
     * @return string
     */
    public function getPaddingClass()
    {
        // If we are in production, do not show padding.
        if ( LevelTag() === 'PROD' ) return "enterprise-banner-no-padding";

        $show = true;
        if ( GetSessionValue('show_enterprise_banner') === 'FALSE' ) $show = false;

        if ( $show ) return "enterprise-banner-padding";
        return "enterprise-banner-no-padding";
    }


    /**
     * render
     *
     * Return the HTML for this html element.
     *
     * @return string|void
     */
    public function render()
    {
        // Configure what we say based on the release level.
        $this->_set_description();

        // Never show the banner if the user has turned it off in the session.
        if (GetSessionValue('show_enterprise_banner') === 'FALSE') {
            $this->is_hidden = true;
        }

        // Do not show the close button if the user is not logged in.
        if (GetSessionValue('is_logged') === 'TRUE')
        {
            $this->closable = true;
        }

        $view_array = array();
        $view_array['description'] = $this->description;
        $view_array['is_hidden'] = $this->is_hidden;
        $view_array['closable'] = $this->closable;

        return RenderViewAsString("templates/enterprise_banner", $view_array);
    }

    /**
     * Return the TEXT that we will show on the banner based on the
     * current release level.
     */
    private function _set_description()
    {
        if ( LevelTag() === 'DEV' )
        {
            $this->description = "DEVELOPMENT";
            $this->is_hidden = false;
        }
        else if ( LevelTag() === 'UAT' )
        {
            $this->description = "USER ACCEPTANCE TEST";
            $this->is_hidden = false;
        }
        else if ( LevelTag() === 'SBOX' )
        {
            $this->description = "CUSTOMER SANDBOX";
            $this->is_hidden = false;
        }
        else if ( LevelTag() === 'DEMO' )
        {
            $this->description = "DEMO";
            $this->is_hidden = false;
        }
        else if ( LevelTag() === 'QA' )
        {
            $this->description = "Quality Assurance";
            $this->is_hidden = false;
        }
    }
}
