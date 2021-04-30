<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class A2P_PDF extends TCPDF {

    // A2P_PDF
    //
    // Custom extensions to the TCPDF class
    // ---------------------------------------------------------

    public $footer_html_view;
    public $footer_html_view_array;

    public function Footer() {

        // Footer
        //
        // Create an HTML footer rather than using the default footer built
        // into TCPDF.
        // -------------------------------------------------------------------

        // Render the footer HTML, if we have a few specified on the class.
        if ( getStringValue($this->footer_html_view) != "" )
        {
            // Always pass in the page number and the total pages so the view
            // can use them if they want.
            $page = getStringValue($this->getAliasNumPage());
            $total = getStringValue($this->getAliasNbPages());

            // Use the footer view array if specified.
            $view_array = array();
            $view_array = array_merge($view_array, array("page" => $page));
            $view_array = array_merge($view_array, array("total" => $total));
            if ( ! empty($this->footer_html_view_array) ) $view_array = array_merge($view_array, $this->footer_html_view_array);

            // Render then write the footer HTML.
            $html = RenderViewAsString($this->footer_html_view, $view_array);
            $this->writeHTML($html);
        }


    }
}
