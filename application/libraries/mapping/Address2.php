<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Address2 extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }
}
