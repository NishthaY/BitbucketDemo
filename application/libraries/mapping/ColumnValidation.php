<?php
if (!defined('BASEPATH')) exit('No direct script access allowed');

class ColumnValidation {

    // Variables

    public $encryption_key;
    public $validation_error_type;
    public $debug;

    protected $identifier;
    protected $identifier_type;
    protected $company_id;
    protected $companyparent_id;
    protected $column_no;
    protected $column_name;
    protected $column_display;
    protected $validation_required;
    protected $validation_error_count;
    protected $normalization_regex;

    private $upload_key;
    private $fh_lookup_info;


    function __construct( $identifier=null, $identifier_type=null, $column_no=null )
    {
        $CI = & get_instance();
        $CI->load->model('Wizard_model');
        $CI->load->helper('s3');


        if ( GetStringValue($identifier) == '' ) return;

        // CompanyId
        // CompanyParentId
        if ( $identifier_type === 'company' )
        {
            $this->company_id = $identifier;
            $this->companyparent_id = GetCompanyParentId($this->company_id);
        }
        else if ( $identifier_type === 'companyparent' )
        {
            $this->company_id = null;
            $this->companyparent_id = $identifier;
        }




        $this->identifier = $identifier;
        $this->identifier_type = $identifier_type;
        $this->debug = false;
        $this->upload_key = null;
        $this->fh = null;
        $this->error_count = 0;
        $this->column_no = $column_no;
        $this->column_name = null;
        $this->column_display = null;
        $this->validation_error_type = "string";
        $this->validation_error_count = $CI->Validation_model->count_validation_errors($identifier, $identifier_type);
        $this->normalization_regex = array();
        $this->fh_lookup_info = array();

        // Developer Note:
        // Please keep in mind, CodeIgniter requires that the variables passed into
        // the constructor must default to NULL.  To actually use this class and get the
        // functionality you want out of it, you need to provide as much detail as possible.
        // For example, if you do not provide a company_id, then you may not know if validation
        // is truly required or not.  Provide the inputs!  If you don't, take the time to
        // understand what will the side effects are and decide if you can live with it.

        if ( strpos(strtoupper(getStringValue($column_no)), "COL") !== FALSE )
        {
            $this->column_no = strtoupper(getStringValue($column_no));
            $this->column_no = getIntValue(replaceFor($this->column_no, "COL", ""));
        }

        // Calculate the column name given our inputs.
        if ( getStringValue($this->identifier) != "" && GetStringValue($this->identifier_type) !== '' && getStringValue($this->column_no) != "")
        {
            $this->column_name =  $CI->Wizard_model->get_mapping_for_upload_column($identifier, $identifier_type, "col{$this->column_no}");
        }

        // Calculate the column display given our inputs.
        $this->column_display = $this->get_column_display($this->column_name);

        // Look at the databse settings for this column.  Is this a required field?
        // If so, then every field must have content.
        $mapping = $CI->Mapping_model->get_mapping_column_by_name($this->column_name, $this->company_id, $this->companyparent_id);
        if ( getArrayStringValue("required", $mapping) == "t" ) $this->validation_required = true;

        // The column may contain data in the normalization_regex column.  This data is
        // a json string that represents a collection of regex commands we need to run on
        // this data as we normalize it.  If we have a json object in this mapping field
        // decode it and save it on the object.
        $regex_data = getArrayStringValue('normalization_regex', $mapping);
        if ( GetStringValue($regex_data) !== '' )
        {
            $this->normalization_regex = json_decode($regex_data, true);
        }

    }

    function validate( $has_headers ) {

        // validate
        //
        // This function can be instructed to validate a column of data in several different
        // ways.  It can be told to "require" input on every line.  It can be told to, if data exists,
        // normalize it and then validate it.  It can be told to do both too.  Errors will be written
        // to the validation errors table if there are any problems.
        // ------------------------------------------------------------------

        $CI = & get_instance();

        $fh = null;
        $error_count = 0;


        try{

            if ( getStringValue($this->identifier) == "" ) throw new Exception("Missing required input identifier");
            if ( getStringValue($this->identifier_type) == "" ) throw new Exception("Missing required input identifier_type");
            if ( getStringValue($this->column_name) == "" ) throw new Exception("Missing required input column_name");

            // Does our column file exist?
            S3GetClient();
            $url = $this->get_validation_filename($this->identifier, $this->identifier_type, $this->column_name);
            if ( ! file_exists($url) ) throw new Exception("Missing secured uploaded file for column [{$this->column_name}] for " . get_class() );

            // Create a collection of info files.
            $this->fh_lookup_info = OpenSeekableInfoFiles($this->identifier, $this->identifier_type);

            // Loop the column file line by line and evaluate each row.
            // Write an error message to the database for every row we find
            // that is invalid.
            $fh = fopen($url, "r");
            if ($fh) {
                $index = 0;
                while (($line = fgets($fh)) !== false)
                {
                    $index++;

                    // If we had too many errors, just stop.
                    if ( MAX_VALIDATION_ERRORS != 0 && $this->validation_error_count > MAX_VALIDATION_ERRORS ) break;

                    // If there are headers, skip line one.
                    if ( $has_headers && $index == 1 ) continue;

                    // Do not validate ignored lines.
                    if ( IsIgnoredImportLine($index, $this->fh_lookup_info, $this->column_name) ) continue;

                    // Grab the piece of data.
                    $line = trim($line);
                    $line = getStringValue($line);
                    $line = A2PDecryptString($line, $this->encryption_key);
                    $line = $this->apply_default_value($line);

                    // Required.
                    if ( $this->validation_required )
                    {
                        // if the field is required, then record an error if we
                        // found the empty string.
                        if ( $line == "" )
                        {
                            $error_count++;
                            $message = "Column ".strtolower($this->column_display)." is required.  No data was found for this column in row {$index}.";
                            $this->save_upload_error( $index, "required", $message );
                            if ( $this->debug ) print "e";
                            continue;
                        }
                    }

                    // Normalize - If we have data, it must be normalized.  If we can't it's an error.
                    if ( $this->normalize($line) === FALSE )
                    {
                        $error_count++;
                        $message = "The ".strtolower($this->column_display)." column on row {$index} could not be validated.";
                        $this->save_upload_error( $index, $this->validation_error_type, $message );
                        if ( $this->debug ) print "e";
                    }
                    if ( $this->debug ) print ".";
                }
                fclose($fh);
                CloseSeekableInfoFiles($this->fh_lookup_info);
            }else{
                throw new Exception("Unable to validate the upload file.  We could not read the {$this->column_name} file.");
            }
        }catch(Exception $e){
            if ( $fh != null ) fclose($fh);
            CloseSeekableInfoFiles($this->fh_lookup_info);
            throw $e;
        }

        if ( $error_count != 0 ) return false;
        return true;

    }

    /**
     * looks_like
     *
     * Does the input to this function "look like" data for this column?
     * This class will be extended by a "type" class where this function
     * will implement rules to decide if the data looks like something
     * we can consume into our database.
     *
     * By default, return FALSE so we are required to implement this
     * in each case.
     *
     * @param $input
     * @return bool
     */
    function looks_like($input)
    {
        return false;
    }

    /**
     * column_data_match
     *
     * Returns true if we want to auto map this column for and end user
     * to one of their data sets.  Really only useful if you have one
     * column with a specific type.  ( Like, only one SSN )
     *
     * @param $input
     * @return bool
     */
    function column_data_match( $input )
    {
        return false;
    }

    /**
     * column_header_match
     *
     * This function assumes that the input is a "header" for a CSV file
     * and will return TRUE if the value matches one of our pre-determined
     * column header matches for this type of object.
     *
     * @param $input
     * @return bool
     */
    function column_header_match($input) {

        // column_header_match
        //
        // This function assumes that the input is a "header" for a CSV file
        // and will return TRUE if the value matches one of our pre-determined
        // column header matches for this type of object.
        // ------------------------------------------------------------------

        $CI = & get_instance();
        $input = strtolower(getStringValue($input));
        $headers = $CI->Mapping_model->get_mapping_column_headers(strtolower(get_called_class()));
        foreach($headers as $header)
        {
            $header = strtolower(getStringValue($header));
            if ( $input == $header ) return true;
            if ( $input == replaceFor($header, " ", "_") ) return true;
            if ( $input == replaceFor($header, " ", "") ) return true;
        }
        return false;

    }

    /**
     * normalize
     *
     * Every input needs to be normalized before we put it into our
     * database.  This function will make any changes to the data so that
     * the data, given it's type, will become expected formats.
     *
     * @param $input
     * @return string
     */
    function normalize($input)
    {
        $input = $this->apply_default_value( $input );

        if ( ! empty($this->normalization_regex) )
        {
            foreach($this->normalization_regex as $regex )
            {
                $pattern = GetArrayStringValue('pattern', $regex);
                $replacement = GetArrayStringValue('replacement', $regex);

                if ( $pattern !== '' )
                {
                    $input = preg_replace($pattern, $replacement, $input);
                }
            }
        }

        return getStringValue($input);
    }

    /**
     * apply_default_value
     *
     * If the input is the empty string, this function will turn that
     * into a default value, if desired given the object type.
     *
     * @param $input
     * @return string
     */
    function apply_default_value( $input )
    {
        return getStringValue($input);
    }

    /**
     * get_validation_filename
     *
     * This function will return the S3 URL for the column data that needs to
     * be validated.
     *
     * @param $identifier
     * @param $identifier_type
     * @param $column_name
     * @return string
     */
    function get_validation_filename($identifier, $identifier_type, $column_name)
    {
        if ($this->debug ) print "get_validation_filename: identifier[" . $identifier . "]\n";
        if ($this->debug ) print "get_validation_filename: identifier_type[" . $identifier_type . "]\n";
        if ($this->debug ) print "get_validation_filename: column_name[" . $column_name . "]\n";
        if ($this->debug ) print "get_validation_filename: company_id[" . $this->company_id . "]\n";
        if ($this->debug ) print "get_validation_filename: companyparent_id[" . $this->companyparent_id . "]\n";


        // Pull the column number off the mapped record for the given name.
        $CI = & get_instance();
        $column_no = FALSE;

        // TODO: the line below is not returning the column no for the given column name for parents.
        $column_no = $CI->Mapping_model->get_mapped_column_no( $this->company_id, $this->companyparent_id, $column_name );
        if ($this->debug ) print "get_validation_filename: column_no[" . $column_no . "]\n";
        if ( $column_no === FALSE ) return "";

        // Build the URL given the specific column no.
        $prefix = GetS3Prefix('parsed', $identifier, $identifier_type);
        if ($this->debug ) print "get_validation_filename: prefix[" . $prefix . "]\n";
        $url = "s3://".S3_BUCKET."/".$prefix."/col{$column_no}.txt";
        if ($this->debug ) print "get_validation_filename: url[" . $url . "]\n";
        return $url;
    }

    /**
     * save_upload_error
     *
     * Write a validation error to the database.
     *
     * @param $row_no
     * @param $short_code
     * @param $message
     */
    function save_upload_error( $row_no, $short_code, $message )
    {
        $CI = &get_instance();
        if (GetStringValue($this->upload_key) === '')
        {
            $this->upload_key = GetUploadKey($this->identifier, $this->identifier_type);
        }
        $CI->load->model('Validation_model');
        $CI->Validation_model->write_validation_error( $row_no, $short_code, $message, $this->column_name, $this->column_no, $this->upload_key, $this->identifier, $this->identifier_type );
        $this->validation_error_count++;
    }

    /**
     * get_column_display
     *
     * Calculate the column display given our inputs.
     *
     * @param $column_name
     * @return string
     */
    protected function get_column_display($column_name)
    {
        $CI = & get_instance();

        $out = "";
        if ( getStringValue($column_name) != "" )
        {
            $mapping_column = $CI->Mapping_model->get_mapping_column_by_name( $column_name, $this->company_id, $this->companyparent_id );
            $out = getArrayStringValue("display", $mapping_column);
        }
        return $out;
    }


}
