<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class Plan extends ColumnValidation {

    function __construct( $identifier=null, $identifier_type=null, $column_no=null ) {
        parent::__construct($identifier, $identifier_type, $column_no);

        // This column is not required in the UI.  That means the constructor
        // will set validation_required to false.  However, we will pull data
        // from a required column if missing, so it really is required.
        $this->validation_required = true;
    }

    function validate( $has_headers ) {

        // validate
        //
        // This column is special.  We are telling the user that it's not required
        // in the UI.  However, it is required.  If we are missing data in this column
        // we will pull that data from a differnet column.  This version of the validate
        // function will open both the column file and the parent column file and walk
        // them together.  If we are missing data in the column file, we will use the
        // data from the parent.
        // ------------------------------------------------------------------

        $fh                 = null;
        $p_fh               = null;
        $parent_column_name = "plan_type";
        $parent_display     = $this->get_column_display($parent_column_name);

        try{

            // If there are no validation specifications, return true.  success!
            if ( ! $this->validation_required && ! $this->normalization_required ) return true;

            if ( getStringValue($this->company_id) == "" ) throw new Exception("Missing required input company_id");
            if ( getStringValue($this->column_name) == "" ) throw new Exception("Missing required input column_name");

            // Does our column file exist?
            $url = $this->get_validation_filename($this->identifier, $this->identifier_type, $this->column_name);
            if ( ! file_exists($url) ) throw new Exception("Missing secured uploaded file for column [{$this->column_name}] for " . get_class() );
            $p_url = $this->get_validation_filename($this->identifier, $this->identifier_type, $parent_column_name);    // Grab the paren file, if we can.

            // Loop the column file line by line and evaluate each row.
            // Write an error message to the database for every row we find
            // that is invalid.
            $fh = fopen($url, "r");
            if ($fh) {

                // PARENT: Open the parent file if it exists.
                if ( file_exists($p_url) ) $p_fh = fopen($p_url, "r");

                $index = 0;
                while (($line = fgets($fh)) !== false)
                {

                    // If we had too many errors, just stop.
                    if ( MAX_VALIDATION_ERRORS != 0 && $this->validation_error_count > MAX_VALIDATION_ERRORS ) break;

                    // PARENT: Read the cooresponding parent line too.
                    $p_line = "";
                    if ( $p_fh ) {
                        $p_line = fgets($p_fh);
                        $p_line = trim($p_line);
                        $p_line = getStringValue($p_line);
                    }

                    $index++;

                    // If there are headers, skip line one.
                    if ( $has_headers && $index == 1 ) continue;

                    // Grab the piece of data.
                    $line = trim($line);
                    $line = getStringValue($line);

                    // PARENT: for go the standard default value and default to the parent value.
                    if ( $line == "" ) $line = getStringValue($p_line);


                    // Required.
                    if ( $this->validation_required )
                    {
                        // if the field is required, then record an error if we
                        // found the empty string.
                        if ( $line == "" )
                        {
                            $message = "Row {$index} for the ".strtolower($this->column_display)." column is invalid.  This field was empty and we tried to infer it from the {$parent_display} column, but that ws empty too.";
                            $this->save_upload_error( $index, "required", $message );
                            continue;
                        }
                    }

                    // Normalize - If we have data, it must be normalized.  If we can't it's an error.
                    if ( $this->normalize($line) === FALSE )
                    {;
                        $message = "The ".strtolower($this->column_display)." column on row {$index} could not be validated.";
                        $this->save_upload_error( $index, $this->validation_error_type, $message );
                    }

                }
                if ( $fh != null ) fclose($fh);
                if ( $p_fh != null ) fclose($p_fh);
            }else{
                throw new Exception("Unable to validate the upload file.  We could not read the {$this->column_name} ({$this->column_no}) file.");
            }
        }catch(Exception $e){
            if ( $fh != null ) fclose($fh);

            // PARENT: close the parent file.
            if ( $p_fh != null ) fclose($fh);

            throw $e;
        }

        if ( $this->validation_error_count != 0 ) return false;
        return true;

    }


}
