<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Ssn extends ColumnValidation_SSN {
//class Ssn extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);
    }

}
