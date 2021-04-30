/**
 * strrev
 * Take in a string and reverse it.
 *
 * @param str
 * @returns {string}
 */
function strrev(str)
{
    str = getStringValue(str);
    if ( str === '' ) return "";

    var reversed_string = "";
    for (var i = (str.length - 1); i >= 0; i--)
    {
        reversed_string += str[i];
    }
    return getStringValue(reversed_string);
}

/**
 * explode
 *
 * Create an array from string by splitting the
 * string by the delimiter.  Each chuck will be
 * returned in the resulting array.
 *
 * May return false if the inputs cannot be processed.
 *
 * @param delim
 * @param str
 * @returns {boolean|[]}
 */
function explode(delim, str)
{
    str = getStringValue(str);
    if ( str === '' ) return false;

    var strArray = str.split(delim);

    // Display array values on page
    var out = [];
    for(var i = 0; i < strArray.length; i++){
        out.push(strArray[i]);
    }
    return out;
}

/**
 * fLeft
 * Returns everything to the left of delimiter sent in, starting from the
 * front. If the delimiter cannot be found then an empty string is
 * returned.
 *
 * @param haystack
 * @param needle
 * @returns {string}
 */
function fLeft(haystack, needle)
{
    haystack = getStringValue(haystack);
    needle = getStringValue(needle);

    var index = haystack.indexOf(needle);
    if( index > -1 )
    {
        index = getIntValue(index);
        var out = haystack.substring(0,index);
        return getStringValue(out);
    }
    return "";
}



/**
 * fLeftBack
 *
 * Returns everything to the left of delimiter sent in, starting from the
 * rear. If the delimiter cannot be found then an empty string is returned.
 *
 * @param inText
 * @param delim
 * @returns {string}
 */
function fLeftBack(haystack, needle)
{
    haystack = strrev(getStringValue(haystack));
    needle = strrev(getStringValue(needle));

    var out = strrev(fRight(haystack, needle));
    return getStringValue(out);
}


/**
 * fRight
 *
 * Returns everything to the right of delimeter sent in, starting from
 * the front. If the delimeter cannot be found then an empty string is
 * returned.
 *
 * @param haystack
 * @param needle
 * @returns {string}
 */
function fRight(haystack, needle)
{
    haystack = getStringValue(haystack);
    needle = getStringValue(needle);

    var index = haystack.indexOf(needle);
    if( index > -1 )
    {
        index = getIntValue(index);
        index = index + needle.length;

        var out = haystack.substring(index);
        return getStringValue(out);
    }
    return "";
}


/**
 * fRightBack
 *
 * Returns everything to the right of delimeter sent in, starting from
 * the back. If the delimeter cannot be found then an empty string is
 * returned.
 * @param inText
 * @param delim
 * @returns {string}
 */
function fRightBack(haystack, needle)
{
    haystack   = strrev(getStringValue(haystack));
    needle     = strrev(getStringValue(needle));

    return strrev(fLeft(haystack, needle));
}

/**
 * getStringValue
 *
 * Take in a string and make it a safe string.
 *
 * @param inString
 * @returns {string}
 */
function getStringValue(str)
{
    if( str == null ) return "";
    if( str == "undefined" ) return "";
    if( str == "null" ) return "";
    if( str == "[object]" ) return "";
    if( str == "[object NodeList]" ) return "";

    try
    {
        var out	= new String(str);
        return out.toString();
    }
    catch(e)
    {
        // Ignore errors, we will return the empty string
        // in those cases.
    }
    return "";
}
function getIntValue(str)
{
    str = getStringValue(str);
    if ( str == "" ) return parseInt("0");

    try
    {
        return parseInt(str);
    }
    catch(e){
        // Ignore errors, we will return zero in those cases.
    }
    return parseInt("0");
}

/**
 * replaceFor
 *
 * Take in a string and look for a substring and replace it with the desired text.
 *
 * @param inT
 * @param lookFor
 * @param replaceWith
 * @returns {String}
 */
function replaceFor(haystack, search, replace)
{
    if( getStringValue(search) === '' ) return "";

    var out = haystack.replace(search, replace);
    return getStringValue(out);
}


/**
 * now
 *
 * Get a string in MM/DD/CCYY format that matches the current date.
 *
 * @returns {string}
 */
function now()
{
    var currentTime = new Date();

    var month = parseInt(currentTime.getMonth() + 1);
    var day = parseInt(currentTime.getDate());
    var year = parseInt(currentTime.getFullYear());

    month = "0" + month;
    month = month.substr(-2, 2);

    day = "0" + day;
    day = day.substr(-2, 2);

    return month + "/" + day + "/" + year;
}

/**
 * pprint_r
 *
 * Pretty print out an object vai an alert box.
 *
 * @param arr
 * @param level
 */
function pprint_r(arr, level)
{
    alert( print_r(arr, level) );
}

/**
 * print_r
 * Return a 'human readable' string representing the thing passed in.
 *
 * @param thing
 * @param depth
 * @returns {string}
 */
function print_r( thing, depth )
{
    if (typeof (thing) != 'object') {
        var literal = thing;
        return "-->" + literal + "<--( " + typeof (literal) + " )";
    }

    // If we got this far, then we have an object that might
    // have some depth to it.  That means we will now start collecting
    // the details and recursively calling ourselves until we
    // have the full depth of the object collected.

    if (!depth) depth = 0;
    var obj = thing;
    var output = "";

    // Add our padding to make things more readable based on our
    // current depth in the object.
    var padding = "";
    for (var j = 0; j < depth + 1; j++) padding += "    ";

    // Collect our output.  If we are at a leaf in the object collect
    // that value, else keep calling ourselves.
    for (var item in obj)
    {
        var value = obj[item];
        if (typeof (value) == 'object')
        {
            // Keep going!  The value is also an object.
            output += padding + "'" + item + "' ...\n";
            output += print_r(value, depth + 1);
        } else {
            output += padding + "'" + item + "' -> \"" + value + "\"\n";
        }
    }
    return output;
}

/**
 * executeFunctionByName
 * Given the name of a JS function in string format, this function will execute
 * that function.  Context is "window" or maybe the name of an iframe which tells
 * the function where it should look for the function in question.  Args is a
 * collection of data that will be passed to the function as parameters.
 *
 * @param {string} functionName - Javascript function to execute.
 * @param {string} context - Where does the function live, like on the "window".
 * @param {array} args - list of parameters to pass functionName when called.
 * @returns {void}
 */
function executeFunctionByName(functionName, context, args)
{
    try
    {
        if ( getStringValue(functionName) == "" ) return;

        // Collect all of the arguments that were passed in as the
        // function arguments AFTER the frist two ( functionName and context )
        var args = [].slice.call(arguments);
        args = args.splice(2);


        // If the function has namespaces, find the actual function name.
        // and then store the namespaces on the context.
        var namespaces = functionName.split(".");
        var func = namespaces.pop();
        for(var i = 0; i < namespaces.length; i++) {
            context = context[namespaces[i]];
        }

        // Run the function and apply the supplied context and args.
        // Return the results of the function.
        return context[func].apply(context, args);

    }catch( e ){
        // Suppress failures.  In development, you can turn on
        // the console line below to research.
        // console.log(e);
    }
}