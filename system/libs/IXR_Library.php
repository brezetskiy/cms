<?php

/**
 * IXR - The Incutio XML-RPC Library
 *
 * Copyright (c) 2010, Incutio Ltd. // Отредактировано phpdreamer 25.04.2011
 */
class IXR_Value
{

    public $data;
    public $type;

    function __construct($data, $type = false)
    {
        $this->data = $data;
        if (!$type)
        {
            $type = $this->calculateType();
        }
        $this->type = $type;
        if ($type == 'struct')
        {
            // Turn all the values in the array in to new IXR_Value objects
            foreach ($this->data as $key => $value)
            {
                $this->data[$key] = new IXR_Value($value);
            }
        }
        if ($type == 'array')
        {
            for ($i = 0, $j = count($this->data); $i < $j; $i++)
            {
                $this->data[$i] = new IXR_Value($this->data[$i]);
            }
        }
    }

    function calculateType()
    {
        if ($this->data === true || $this->data === false)
        {
            return 'boolean';
        }
        if (is_integer($this->data))
        {
            return 'int';
        }
        if (is_double($this->data))
        {
            return 'double';
        }

        // Deal with IXR object types base64 and date
        if (is_object($this->data) && is_a($this->data, 'IXR_Date'))
        {
            return 'date';
        }
        if (is_object($this->data) && is_a($this->data, 'IXR_Base64'))
        {
            return 'base64';
        }

        // If it is a normal PHP object convert it in to a struct
        if (is_object($this->data))
        {
            $this->data = get_object_vars($this->data);
            return 'struct';
        }
        if (!is_array($this->data))
        {
            return 'string';
        }

        // We have an array - is it an array or a struct?
        if ($this->isStruct($this->data))
        {
            return 'struct';
        } else
        {
            return 'array';
        }
    }

    function getXml()
    {
        // Return XML for this value
        switch ($this->type)
        {
            case 'boolean':
                return '<boolean>' . (($this->data) ? '1' : '0') . '</boolean>';
                break;
            case 'int':
                return '<int>' . $this->data . '</int>';
                break;
            case 'double':
                return '<double>' . $this->data . '</double>';
                break;
            case 'string':
                return '<string>' . htmlspecialchars($this->data) . '</string>';
                break;
            case 'array':
                $return = '<array><data>' . "\n";
                foreach ($this->data as $item)
                {
                    $return .= '  <value>' . $item->getXml() . "</value>\n";
                }
                $return .= '</data></array>';
                return $return;
                break;
            case 'struct':
                $return = '<struct>' . "\n";
                foreach ($this->data as $name => $value)
                {
                    $return .= "  <member><name>$name</name><value>";
                    $return .= $value->getXml() . "</value></member>\n";
                }
                $return .= '</struct>';
                return $return;
                break;
            case 'date':
            case 'base64':
                return $this->data->getXml();
                break;
        }
        return false;
    }

    /**
     * Checks whether or not the supplied array is a struct or not
     *
     * @param unknown_type $array
     * @return boolean
     */
    function isStruct($array)
    {
        $expected = 0;
        foreach ($array as $key => $value)
        {
            if ((string) $key != (string) $expected)
            {
                return true;
            }
            $expected++;
        }
        return false;
    }

}

/**
 * IXR_MESSAGE
 *
 * @package IXR
 * @since 1.5
 *
 */
class IXR_Message
{

    public $message;
    public $messageType;  // methodCall / methodResponse / fault
    public $faultCode;
    public $faultString;
    public $methodName;
    public $params;
    // Current variable stacks
    public $_arraystructs = array();   // The stack used to keep track of the current array/struct
    public $_arraystructstypes = array(); // Stack keeping track of if things are structs or array
    public $_currentStructName = array();  // A stack as well
//    public $_param;
//    public $_value;
//    public $_currentTag;
//    public $_currentTagContents;
    // The XML parser
    public $_parser;

    function __construct($message)
    {
        $this->message = & $message;
    }

    function parse()
    {
        // first remove the XML declaration
        // merged from WP #10698 - this method avoids the RAM usage of preg_replace on very large messages
        $header = preg_replace('/<\?xml.*?\?' . '>/', '', substr($this->message, 0, 100), 1);
        $this->message = substr_replace($this->message, $header, 0, 100);
        if (trim($this->message) == '')
        {
            return false;
        }
        $this->_parser = xml_parser_create();
        // Set XML parser to take the case of tags in to account
        xml_parser_set_option($this->_parser, XML_OPTION_CASE_FOLDING, false);
        // Set XML parser callback functions
        xml_set_object($this->_parser, $this);
        xml_set_element_handler($this->_parser, 'tag_open', 'tag_close');
        xml_set_character_data_handler($this->_parser, 'cdata');
        $chunk_size = 262144; // 256Kb, parse in chunks to avoid the RAM usage on very large messages
        $final = FALSE;
        do
        {
            if (strlen($this->message) <= $chunk_size)
            {
                $final = true;
            }
            $part = substr($this->message, 0, $chunk_size);
            $this->message = substr($this->message, $chunk_size);
            if (!xml_parse($this->_parser, $part, $final))
            {
                return false;
            }
            if ($final)
            {
                break;
            }
        } while (true);
        xml_parser_free($this->_parser);

        // Grab the error messages, if any
        if ($this->messageType == 'fault')
        {
            $this->faultCode = $this->params[0]['faultCode'];
            $this->faultString = $this->params[0]['faultString'];
        }
        return true;
    }

    function tag_open($parser, $tag, $attr)
    {
        $this->_currentTagContents = '';
        $this->currentTag = $tag;
        switch ($tag)
        {
            case 'methodCall':
            case 'methodResponse':
            case 'fault':
                $this->messageType = $tag;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':    // data is to all intents and puposes more interesting than array
                $this->_arraystructstypes[] = 'array';
                $this->_arraystructs[] = array();
                break;
            case 'struct':
                $this->_arraystructstypes[] = 'struct';
                $this->_arraystructs[] = array();
                break;
        }
    }

    function cdata($parser, $cdata)
    {
        $this->_currentTagContents .= $cdata;
    }

    function tag_close($parser, $tag)
    {
        $valueFlag = false;
        switch ($tag)
        {
            case 'int':
            case 'i4':
                $value = (int) trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'double':
                $value = (double) trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'string':
                $value = (string) trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'dateTime.iso8601':
                $value = new IXR_Date(trim($this->_currentTagContents));
                $valueFlag = true;
                break;
            case 'value':
                // "If no type is indicated, the type is string."
                if (trim($this->_currentTagContents) != '')
                {
                    $value = (string) $this->_currentTagContents;
                    $valueFlag = true;
                }
                break;
            case 'boolean':
                $value = (boolean) trim($this->_currentTagContents);
                $valueFlag = true;
                break;
            case 'base64':
                $value = base64_decode($this->_currentTagContents);
                $valueFlag = true;
                break;
            /* Deal with stacks of arrays and structs */
            case 'data':
            case 'struct':
                $value = array_pop($this->_arraystructs);
                array_pop($this->_arraystructstypes);
                $valueFlag = true;
                break;
            case 'member':
                array_pop($this->_currentStructName);
                break;
            case 'name':
                $this->_currentStructName[] = trim($this->_currentTagContents);
                break;
            case 'methodName':
                $this->methodName = trim($this->_currentTagContents);
                break;
        }

        if ($valueFlag)
        {
            if (count($this->_arraystructs) > 0)
            {
                // Add value to struct or array
                if ($this->_arraystructstypes[count($this->_arraystructstypes) - 1] == 'struct')
                {
                    // Add to struct
                    $this->_arraystructs[count($this->_arraystructs) - 1][$this->_currentStructName[count($this->_currentStructName) - 1]] = $value;
                } else
                {
                    // Add to array
                    $this->_arraystructs[count($this->_arraystructs) - 1][] = $value;
                }
            } else
            {
                // Just add as a paramater
                $this->params[] = $value;
            }
        }
        $this->_currentTagContents = '';
    }

}

/**
 * IXR_Request
 *
 * @package IXR
 * @since 1.5
 */
class IXR_Request
{

    public $method;
    public $args;
    public $xml;

    function __construct($method, $args)
    {
        $this->method = $method;
        $this->args = $args;
        $this->xml = <<<EOD
<?xml version="1.0"?>
<methodCall>
<methodName>{$this->method}</methodName>
<params>

EOD;
        foreach ($this->args as $arg)
        {
            $this->xml .= '<param><value>';
            $v = new IXR_Value($arg);
            $this->xml .= $v->getXml();
            $this->xml .= "</value></param>\n";
        }
        $this->xml .= '</params></methodCall>';
    }

    function getLength()
    {
        return strlen($this->xml);
    }

    function getXml()
    {
        return $this->xml;
    }

}

/**
 * IXR_Client
 *
 * @package IXR
 * @since 1.5
 *
 */
class IXR_Client
{

    public $server;
    public $port;
    public $path;
    public $useragent;
    public $response;
    public $message = false;
    public $debug = false;
    public $timeout;
    public $cookiePath;
    // Storage place for an error message
    public $error = false;

    function __construct($server, $path = false, $port = 80, $timeout = 15)
    {
        if (!$path)
        {
            // Assume we have been given a URL instead
            $bits = parse_url($server);
            $this->server = $bits['host'];
            $this->port = isset($bits['port']) ? $bits['port'] : 80;
            $this->path = isset($bits['path']) ? $bits['path'] : '/';

            // Make absolutely sure we have a path
            if (!$this->path)
            {
                $this->path = '/';
            }
        } else
        {
            $this->server = $server;
            $this->path = $path;
            $this->port = $port;
        }
        $this->useragent = 'The Incutio XML-RPC PHP Library';
        $this->timeout = $timeout;
    }

    function query($args)
    {
        //$args = func_get_args();
        
        $method = array_shift($args);
        $request = new IXR_Request($method, $args);
        $length = $request->getLength();
        $xml = $request->getXml();

        if ($this->debug)
        {
            echo '<pre>' . htmlspecialchars($xml) . "\n</pre>\n\n";
        }

        $curl = curl_init('http://' . $this->server . ':' . $this->port . $this->path);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        //Since 23Jun2004 (0.1.2) - Made timeout a class field
        curl_setopt($curl, CURLOPT_TIMEOUT, $this->timeout);

        if ($this->debug)
        {
            curl_setopt($curl, CURLOPT_VERBOSE, 1);
        }

        curl_setopt($curl, CURLOPT_COOKIEFILE, $this->cookiePath);
        curl_setopt($curl, CURLOPT_COOKIEJAR, $this->cookiePath);

        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($curl, CURLOPT_PORT, $this->port);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array(
            "Content-Type: text/xml",
            "Content-length: {$length}"));

        // Call cURL to do it's stuff and return us the content
        $contents = curl_exec($curl);
        curl_close($curl);

        // Check for 200 Code in $contents
        if (!strstr($contents, '200 OK'))
        {
            //There was no "200 OK" returned - we failed
            $this->error = new IXR_Error(-32300, 'transport error - HTTP status code was not 200');
            return false;
        }

        if ($this->debug)
        {
            echo '<pre>' . htmlspecialchars($contents) . "\n</pre>\n\n";
        }
        // Now parse what we've got back
        // Since 20Jun2004 (0.1.1) - We need to remove the headers first
        // Why I have only just found this, I will never know...
        // So, remove everything before the first <
        $contents = substr($contents, strpos($contents, '<'));

        $this->message = new IXR_Message($contents);
        if (!$this->message->parse())
        {
            // XML error
            $this->error = new IXR_Error(-32700, 'parse error. not well formed');
            return false;
        }
        // Is the message a fault?
        if ($this->message->messageType == 'fault')
        {
            $this->error = new IXR_Error($this->message->faultCode, $this->message->faultString);
            return false;
        }

        // Message must be OK
        return true;
    }

    function getResponse()
    {
        // methodResponses can only have one param - return that
        return $this->message->params[0];
    }

    function isError()
    {
        return (is_object($this->error));
    }

    function getErrorCode()
    {
        return $this->error->code;
    }

    function getErrorMessage()
    {
        return $this->error->message;
    }

}

/**
 * IXR_Error
 *
 * @package IXR
 * @since 1.5
 */
class IXR_Error
{

    public $code;
    public $message;

    function __construct($code, $message)
    {
        $this->code = $code;
        $this->message = htmlspecialchars($message);
    }

    function getXml()
    {
        $xml = <<<EOD
<methodResponse>
  <fault>
    <value>
      <struct>
        <member>
          <name>faultCode</name>
          <value><int>{$this->code}</int></value>
        </member>
        <member>
          <name>faultString</name>
          <value><string>{$this->message}</string></value>
        </member>
      </struct>
    </value>
  </fault>
</methodResponse>

EOD;
        return $xml;
    }

}

/**
 * IXR_Date
 *
 * @package IXR
 * @since 1.5
 */
class IXR_Date
{

    public $year;
    public $month;
    public $day;
    public $hour;
    public $minute;
    public $second;
    public $timezone;

    function __construct($time)
    {
        // $time can be a PHP timestamp or an ISO one
        if (is_numeric($time))
        {
            $this->parseTimestamp($time);
        } else
        {
            $this->parseIso($time);
        }
    }

    function parseTimestamp($timestamp)
    {
        $this->year = date('Y', $timestamp);
        $this->month = date('m', $timestamp);
        $this->day = date('d', $timestamp);
        $this->hour = date('H', $timestamp);
        $this->minute = date('i', $timestamp);
        $this->second = date('s', $timestamp);
        $this->timezone = '';
    }

    function parseIso($iso)
    {
        $this->year = substr($iso, 0, 4);
        $this->month = substr($iso, 4, 2);
        $this->day = substr($iso, 6, 2);
        $this->hour = substr($iso, 9, 2);
        $this->minute = substr($iso, 12, 2);
        $this->second = substr($iso, 15, 2);
        $this->timezone = substr($iso, 17);
    }

    function getIso()
    {
        return $this->year . $this->month . $this->day . 'T' . $this->hour . ':' . $this->minute . ':' . $this->second . $this->timezone;
    }

    function getXml()
    {
        return '<dateTime.iso8601>' . $this->getIso() . '</dateTime.iso8601>';
    }

    function getTimestamp()
    {
        return mktime($this->hour, $this->minute, $this->second, $this->month, $this->day, $this->year);
    }

}

///**
// * IXR_Base64
// *
// * @package IXR
// * @since 1.5
// */
//class IXR_Base64
//{
//    public $data;
//
//    function __construct($data)
//    {
//        $this->data = $data;
//    }
//
//    function getXml()
//    {
//        return '<base64>'.base64_encode($this->data).'</base64>';
//    }
//}
//
