<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Phone2 extends ColumnValidation_Phone {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

    }
}
