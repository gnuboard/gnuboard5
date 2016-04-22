<?php

//
// +----------------------------------------------------------------------+
// | <phpXML/> version 1.0                                                |
// | Copyright (c) 2001 Michael P. Mehl. All rights reserved.             |
// +----------------------------------------------------------------------+
// | Latest releases are available at http://phpxml.org/. For feedback or |
// | bug reports, please contact the author at mpm@phpxml.org. Thanks!    |
// +----------------------------------------------------------------------+
// | The contents of this file are subject to the Mozilla Public License  |
// | Version 1.1 (the "License"); you may not use this file except in     |
// | compliance with the License. You may obtain a copy of the License at |
// | http://www.mozilla.org/MPL/                                          |
// |                                                                      |
// | Software distributed under the License is distributed on an "AS IS"  |
// | basis, WITHOUT WARRANTY OF ANY KIND, either express or implied. See  |
// | the License for the specific language governing rights and           |
// | limitations under the License.                                       |
// |                                                                      |
// | The Original Code is <phpXML/>.                                      |
// |                                                                      |
// | The Initial Developer of the Original Code is Michael P. Mehl.       |
// | Portions created by Michael P. Mehl are Copyright (C) 2001 Michael   |
// | P. Mehl. All Rights Reserved.                                        |
// +----------------------------------------------------------------------+
// | Authors:                                                             |
// |   Michael P. Mehl <mpm@phpxml.org>                                   |
// +----------------------------------------------------------------------+
//

/**
 * Class for accessing XML data through the XPath language.
 *
 * This class offers methods for accessing the nodes of a XML document using 
 * the XPath language. You can add or remove nodes, set or modify their 
 * content and their attributes. No additional PHP extensions like DOM XML 
 * or something similar are required to use these features.
 *
 * @link      http://www.phpxml.org/ Latest release of this class
 * @link      http://www.w3.org/TR/xpath W3C XPath Recommendation
 * @copyright Copyright (c) 2001 Michael P. Mehl. All rights reserved.
 * @author    Michael P. Mehl <mpm@phpxml.org>
 * @version   1.0 (2001-03-08)
 * @access    public
 */

/**
 *
 * 해당 라이브러리는 절대 수정되어서는 안됩니다.
 * 임의로 수정된 코드에 대한 책임은 전적으로 수정자에게 있음을 알려드립니다.
 *
 */
class XML {

    /**
     * List of all document nodes.
     *
     * This array contains a list of all document nodes saved as an
     * associative array.
     *
     * @access private
     * @var    array
     */
    var $nodes = array();

    /**
     * List of document node IDs.
     *
     * This array contains a list of all IDs of all document nodes that
     * are used for counting when adding a new node.
     *
     * @access private
     * @var    array
     */
    var $ids = array();

    /**
     * Current document path.
     *
     * This variable saves the current path while parsing a XML file and adding
     * the nodes being read from the file.
     *
     * @access private
     * @var    string
     */
    var $path = "";

    /**
     * Current document position.
     *
     * This variable counts the current document position while parsing a XML
     * file and adding the nodes being read from the file.
     *
     * @access private
     * @var    int
     */
    var $position = 0;

    /**
     * Path of the document root.
     *
     * This string contains the full path to the node that acts as the root
     * node of the whole document.
     *
     * @access private
     * @var    string
     */
    var $root = "";

    /**
     * Current XPath expression.
     *
     * This string contains the full XPath expression being parsed currently.
     *
     * @access private
     * @var    string
     */
    var $xpath = "";

    /**
     * List of entities to be converted.
     *
     * This array contains a list of entities to be converted when an XPath
     * expression is evaluated.
     *
     * @access private
     * @var    array
     */
    var $entities = array("&" => "&amp;", "<" => "&lt;", ">" => "&gt;",
        "'" => "&apos", '"' => "&quot;");

    /**
     * List of supported XPath axes.
     *
     * This array contains a list of all valid axes that can be evaluated in an
     * XPath expression.
     *
     * @access private
     * @var    array
     */
    var $axes = array("child", "descendant", "parent", "ancestor",
        "following-sibling", "preceding-sibling", "following", "preceding",
        "attribute", "namespace", "self", "descendant-or-self",
        "ancestor-or-self");

    /**
     * List of supported XPath functions.
     *
     * This array contains a list of all valid functions that can be evaluated
     * in an XPath expression.
     *
     * @access private
     * @var    array
     */
    var $functions = array("last", "position", "count", "id", "name",
        "string", "concat", "starts-with", "contains", "substring-before",
        "substring-after", "substring", "string-length", "translate",
        "boolean", "not", "true", "false", "lang", "number", "sum", "floor",
        "ceiling", "round", "text");

    /**
     * List of supported XPath operators.
     *
     * This array contains a list of all valid operators that can be evaluated
     * in a predicate of an XPath expression. The list is ordered by the
     * precedence of the operators (lowest precedence first).
     *
     * @access private
     * @var    array
     */
    var $operators = array(" or ", " and ", "=", "!=", "<=", "<", ">=", ">",
        "+", "-", "*", " div ", " mod ");
    var $xml_node = array();

    /**
     * Constructor of the class.
     *
     * This constructor initializes the class and, when a filename is given,
     * tries to read and parse the given file.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $file Path and name of the file to read and parsed.
     * @see       load_xml()
     */
    //modify by ddaemiri, 2007.05.28
    //load_file -> load_xml로 파일 및 string 으로 모두 입력받을 수 있음.
    function XML($file = "") {
        // Check whether a file was given.
        if (!empty($file)) {
            // Load the XML file.
            return $this->load_xml($file, "");
        }
    }

    /**
     * Reads a file and parses the XML data.
     *
     * This method reads the content of a XML file, tries to parse its
     * content and upon success stores the information retrieved from
     * the file into an array.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $file Path and name of the file to be read and parsed.
     * @see       handle_start_element(), handle_end_element(),
     *            handle_character_data()
     */
    //modify by ddaemiri, 2007.05.28
    //load_file -> load_xml로 파일 및 string 으로 모두 입력받을 수 있음.
    function load_xml($file, $str) {
        // Check whether the file exists and is readable.
        if ((file_exists($file) && is_readable($file)) || $str != "") {
            // Read the content of the file.
            if ($str == "")
                $content = implode("", file($file));
            else
                $content = $str;

            // Check whether content has been read.
            if (!empty($content)) {
                // Create an XML parser.
                $parser = xml_parser_create();

                // Set the options for parsing the XML data.
                xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
                xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);

                // Set the object for the parser.
                xml_set_object($parser, $this);

                // Set the element handlers for the parser.
                xml_set_element_handler($parser, "handle_start_element", "handle_end_element");
                xml_set_character_data_handler($parser, "handle_character_data");

                // Parse the XML file.
                if (!xml_parse($parser, $content, true)) {
                    // Display an error message.
                    $this->display_error("XML error in file %s, line %d: %s", $file, xml_get_current_line_number($parser), xml_error_string(xml_get_error_code($parser)));
                }

                // Free the parser.
                xml_parser_free($parser);

                return OK;
            }
        } else {
            // Display an error message.
            //$this->display_error("File %s could not be found or read.", $file);
            return RESULT_MSG_FORMAT_ERR;
        }
    }

    //modify by ddaemiri, 2007.05.28
    //charset 추가( header 생성 )
    function make_xml($highlight = array(), $root = "", $level = 0, $charset = "UTF-8") {
        // header 추가
        $header = "<?xml version=\"1.0\" encoding=\"" . $charset . "\"?>" . "\n";
        $body = $this->get_xml($highlight, $root, $level);
        return $header . $body;
    }

    /**
     * Generates a XML file with the content of the current document.
     *
     * This method creates a string containing the XML data being read
     * and modified by this class before. This string can be used to save
     * a modified document back to a file or doing other nice things with
     * it.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $highlight Array containing a list of full document
     *            paths of nodes to be highlighted by <font>...</font> tags
     *            in the generated XML string.
     * @param     string $root While doing a recursion with this method, this
     *            parameter is used for internal purpose.
     * @param     int $level While doing a recursion with this method, this
     *            parameter is used for internal purpose.
     * @return    string The returned string contains well-formed XML data
     *            representing the content of this document.
     * @see       load_xml(), evaluate(), get_content()
     */
    //modify by ddaemiri, 2007.05.28
    //get_file -> get_xml 로 함수이름 변경. 
    function get_xml($highlight = array(), $root = "", $level = 0) {

        // Create a string to save the generated XML data.
        $xml = "";

        // Create two strings containing the tags for highlighting a node.
        $highlight_start = "<font color=\"#FF0000\"><b>";
        $highlight_end = "</b></font>";

        // Generate a string to be displayed before the tags.
        $before = "";

        // Calculate the amount of whitespaces to display.
        for ($i = 0; $i < ( $level * 2 ); $i++) {
            // Add a whitespaces to the string.
            $before .= " ";
        }

        // Check whether a root node is given.
        if (empty($root)) {
            // Set it to the document root.
            $root = $this->root;
        }

        // Check whether the node is selected.
        $selected = in_array($root, $highlight);

        // Now add the whitespaces to the XML data.
        $xml .= $before;

        // Check whether the node is selected.
        if ($selected) {
            // Add the highlight code to the XML data.
            $xml .= $highlight_start;
        }

        // Now open the tag.
        $xml .= "<" . $this->nodes[$root]["name"];

        // Check whether there are attributes for this node.
        if (count($this->nodes[$root]["attributes"]) > 0) {
            // Run through all attributes.
            foreach ($this->nodes[$root]["attributes"] as $key => $value) {
                // Check whether this attribute is highlighted.
                if (in_array($root . "/attribute::" . $key, $highlight)) {
                    // Add the highlight code to the XML data.
                    $xml .= $highlight_start;
                }

                // Add the attribute to the XML data.
                $xml .= " " . $key . "=\"" . trim(stripslashes($value)) . "\"";

                // Check whether this attribute is highlighted.
                if (in_array($root . "/attribute::" . $key, $highlight)) {
                    // Add the highlight code to the XML data.
                    $xml .= $highlight_end;
                }
            }
        }

        // Check whether the node contains character data or has children.
        if ($this->nodes[$root]["text"] == "" &&
                !isset($this->nodes[$root]["children"])) {
            // Add the end to the tag.
            $xml .= "/";
        }

        // Close the tag.
        //$xml .= ">\n";
        $xml .= ">";

        // Check whether the node is selected.
        if ($selected) {
            // Add the highlight code to the XML data.
            $xml .= $highlight_end;
        }

        // Check whether the node contains character data.
        if ($this->nodes[$root]["text"] != "") {
            // Add the character data to the XML data.
            //$xml .= $before."  ".$this->nodes[$root]["text"]."\n";
            //$xml .= $before.$this->nodes[$root]["text"];
            $xml .= $this->nodes[$root]["text"];
        }

        // Check whether the node has children.
        if (isset($this->nodes[$root]["children"])) {
            // Run through all children with different names.
            foreach ($this->nodes[$root]["children"] as $child => $pos) {
                // Run through all children with the same name.
                for ($i = 1; $i <= $pos; $i++) {
                    // Generate the full path of the child.
                    $fullchild = $root . "/" . $child . "[" . $i . "]";

                    // Add the child's XML data to the existing data.
                    $xml .= "\n\t" . $this->get_xml($highlight, $fullchild, $level + 1);
                }
            }
        }

        // Check whether there are attributes for this node.
        if ($this->nodes[$root]["text"] != "" ||
                isset($this->nodes[$root]["children"])) {
            // Add the whitespaces to the XML data.
            //$xml .= $before;
            // Check whether the node is selected.
            if ($selected) {
                // Add the highlight code to the XML data.
                $xml .= $highlight_start;
            }

            // Add the closing tag.
            $xml .= "</" . $this->nodes[$root]["name"] . ">";

            // Check whether the node is selected.
            if ($selected) {
                // Add the highlight code to the XML data.
                $xml .= $highlight_end;
            }

            // Add a linebreak.
            //$xml .= "\n";
        }

        // Return the XML data.
        return $xml;
    }

    /**
     * Adds a new node to the XML document.
     *
     * This method adds a new node to the tree of nodes of the XML document
     * being handled by this class. The new node is created according to the
     * parameters passed to this method.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $content Full path of the parent, to which the new
     *            node should be added as a child.
     * @param     string $name Name of the new node.
     * @return    string The string returned by this method will contain the
     *            full document path of the created node.
     * @see       remove_node(), evaluate()
     */
    function add_node($context, $name, $value = "", $attr_arr = NULL) {
        // Check whether a name for this element is already set.
        if (empty($this->root)) {
            // Use this tag as the root element.
            $this->root = "/" . $name . "[1]";
        }

        // Calculate the full path for this element.
        $path = $context . "/" . $name;

        // Set the relative context and the position.
        $position = ++$this->ids[$path];
        $relative = $name . "[" . $position . "]";

        // Calculate the full path.
        $fullpath = $context . "/" . $relative;

        // Calculate the context position, which is the position of this
        // element within elements of the same name in the parent node.
        $this->nodes[$fullpath]["context-position"] = $position;

        // Calculate the position for the following and preceding axis
        // detection.
        $this->nodes[$fullpath]["document-position"] = $this->nodes[$context]["document-position"] + 1;

        // Save the information about the node.
        $this->nodes[$fullpath]["name"] = $name;
        $this->nodes[$fullpath]["text"] = "";
        $this->nodes[$fullpath]["parent"] = $context;

        // Add this element to the element count array.
        if (!$this->nodes[$context]["children"][$name]) {
            // Set the default name.
            $this->nodes[$context]["children"][$name] = 1;
        } else {
            // Calculate the name.
            $this->nodes[$context]["children"][$name] = $this->nodes[$context]["children"][$name] + 1;
        }

        if ($value != "" && is_array($attr_arr)) {
            $this->set_attributes($fullpath, $attr_arr);
            if ($attr_arr["urlencode"] == "1")
                $value = urlencode($value);
        }
        if ($value != "") {
            $this->set_content($fullpath, $value);
        }

        // Return the path of the new node.
        return $fullpath;
    }

    /**
     * Removes a node from the XML document.
     *
     * This method removes a node from the tree of nodes of the XML document.
     * If the node is a document node, all children of the node and its
     * character data will be removed. If the node is an attribute node,
     * only this attribute will be removed, the node to which the attribute
     * belongs as well as its children will remain unmodified.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node to be removed.
     * @see       add_node(), evaluate()
     */
    function remove_node($node) {
        // Check whether the node is an attribute node.
        if (preg_match("/attribute::/", $node)) {
            // Get the path to the attribute node's parent.
            $parent = $this->prestr($node, "/attribute::");

            // Get the name of the attribute.
            $attribute = $this->afterstr($node, "/attribute::");

            // Check whether the attribute exists.
            if (isset($this->nodes[$parent]["attributes"][$attribute])) {
                // Create a new array.
                $new = array();

                // Run through the existing attributes.
                foreach ($this->nodes[$parent]["attributes"]
                as $key => $value) {
                    // Check whether it's the attribute to remove.
                    if ($key != $attribute) {
                        // Add it to the new array again.
                        $new[$key] = $value;
                    }
                }

                // Save the new attributes.
                $this->nodes[$parent]["attributes"] = $new;
            }
        } else {
            // Create an associative array, which contains information about
            // all nodes that required to be renamed.
            $rename = array();

            // Get the name, the parent and the siblings of current node.
            $name = $this->nodes[$node]["name"];
            $parent = $this->nodes[$node]["parent"];
            $siblings = $this->nodes[$parent]["children"][$name];

            // Decrease the number of children.
            $this->nodes[$parent]["children"][$name] --;

            // Create a counter for renumbering the siblings.
            $counter = 1;

            // Now run through the siblings.
            for ($i = 1; $i <= $siblings; $i++) {
                // Create the name of the sibling.
                $sibling = $parent . "/" . $name . "[" . $i . "]";

                // Check whether it's the name of the current node.
                if ($sibling != $node) {
                    // Create the new name for the sibling.
                    $new = $parent . "/" . $name . "[" . $counter . "]";

                    // Increase the counter.
                    $counter++;

                    // Add the old and the new name to the list of nodes
                    // to be renamed.
                    $rename[$sibling] = $new;
                }
            }

            // Create an array for saving the new node-list.
            $nodes = array();

            // Now run through through the existing nodes.
            foreach ($this->nodes as $name => $values) {
                // Check the position of the path of the node to be deleted
                // in the path of the current node.
                $position = strpos($name, $node);

                // Check whether it's not the node to be deleted.
                if ($position === false) {
                    // Run through the array of nodes to be renamed.
                    foreach ($rename as $old => $new) {
                        // Check whether this node and it's parent requires to
                        // be renamed.
                        $name = str_replace($old, $new, $name);
                        $values["parent"] = str_replace($old, $new, $values["parent"]);
                    }

                    // Add the node to the list of nodes.
                    $nodes[$name] = $values;
                }
            }

            // Save the new array of nodes.
            $this->nodes = $nodes;
        }
    }

    /**
     * Add content to a node.
     *
     * This method adds content to a node. If it's an attribute node, then
     * the value of the attribute will be set, otherwise the character data of
     * the node will be set. The content is appended to existing content,
     * so nothing will be overwritten.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node.
     * @param     string $value String containing the content to be added.
     * @see       get_content(), evaluate()
     */
    function add_content($path, $value) {
        // Check whether it's an attribute node.
        if (preg_match("/attribute::/", $path)) {
            // Get the path to the attribute node's parent.
            $parent = $this->prestr($path, "/attribute::");

            // Get the parent node.
            $parent = $this->nodes[$parent];

            // Get the name of the attribute.
            $attribute = $this->afterstr($path, "/attribute::");

            // Set the attribute.
            $parent["attributes"][$attribute] .= $value;
        } else {
            // Set the character data of the node.
            $this->nodes[$path]["text"] .= $value;
        }
    }

    /**
     * Set the content of a node.
     *
     * This method sets the content of a node. If it's an attribute node, then
     * the value of the attribute will be set, otherwise the character data of
     * the node will be set. Existing content will be overwritten.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node.
     * @param     string $value String containing the content to be set.
     * @see       get_content(), evaluate()
     */
    function set_content($path, $value) {
        // Check whether it's an attribute node.
        if (preg_match("/attribute::/", $path)) {
            // Get the path to the attribute node's parent.
            $parent = $this->prestr($path, "/attribute::");

            // Get the parent node.
            $parent = $this->nodes[$parent];

            // Get the name of the attribute.
            $attribute = $this->afterstr($path, "/attribute::");

            // Set the attribute.
            $parent["attributes"][$attribute] = $value;
        } else {
            // Set the character data of the node.
            $this->nodes[$path]["text"] = strtr($value, $this->entities);
        }
    }

    /**
     * Retrieves the content of a node.
     *
     * This method retrieves the content of a node. If it's an attribute
     * node, then the value of the attribute will be retrieved, otherwise
     * it'll be the character data of the node.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node, from which the
     *            content should be retrieved.
     * @return    string The returned string contains either the value or the
     *            character data of the node.
     * @see       set_content(), evaluate()
     */
    function get_content($path) {
        // Check whether it's an attribute node.
        if (preg_match("/attribute::/", $path)) {
            // Get the path to the attribute node's parent.
            $parent = $this->prestr($path, "/attribute::");

            // Get the parent node.
            $parent = $this->nodes[$parent];

            // Get the name of the attribute.
            $attribute = $this->afterstr($path, "/attribute::");

            // Get the attribute.
            $attribute = $parent["attributes"][$attribute];

            // Return the value of the attribute.
            return $attribute;
        } else {
            // Return the cdata of the node.
            return stripslashes($this->nodes[$path]["text"]);
        }
    }

    /**
     * Add attributes to a node.
     *
     * This method adds attributes to a node. Existing attributes will not be
     * overwritten.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node, the attributes
     *            should be added to.
     * @param     array $attributes Associative array containing the new
     *            attributes for the node.
     * @see       set_content(), get_content()
     */
    function add_attributes($path, $attributes) {
        // Add the attributes to the node.
        $this->nodes[$path]["attributes"] = array_merge($attributes, $this->nodes[$path]["attributes"]);
    }

    /**
     * Sets the attributes of a node.
     *
     * This method sets the attributes of a node and overwrites all existing
     * attributes by doing this.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node, the attributes
     *            of which should be set.
     * @param     array $attributes Associative array containing the new
     *            attributes for the node.
     * @see       set_content(), get_content()
     */
    function set_attributes($path, $attributes) {
        // Set the attributes of the node.
        $this->nodes[$path]["attributes"] = $attributes;
    }

    /**
     * Retrieves a list of all attributes of a node.
     *
     * This method retrieves a list of all attributes of the node specified in
     * the argument.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node, from which the
     *            list of attributes should be retrieved.
     * @return    array The returned associative array contains the all
     *            attributes of the specified node.
     * @see       get_content(), $nodes, $ids
     */
    function get_attributes($path) {
        // Return the attributes of the node.
        return $this->nodes[$path]["attributes"];
    }

    /**
     * Retrieves the name of a document node.
     *
     * This method retrieves the name of document node specified in the
     * argument.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path Full document path of the node, from which the
     *            name should be retrieved.
     * @return    string The returned array contains the name of the specified
     *            node.
     * @see       get_content(), $nodes, $ids
     */
    function get_name($path) {
        // Return the name of the node.
        return $this->nodes[$path]["name"];
    }

    /**
     * Evaluates an XPath expression.
     *
     * This method tries to evaluate an XPath expression by parsing it. A
     * XML document has to be read before this method is able to work.
     *
     * @access    public
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $path XPath expression to be evaluated.
     * @param     string $context Full path of a document node, starting
     *            from which the XPath expression should be evaluated.
     * @return    array The returned array contains a list of the full
     *            document paths of all nodes that match the evaluated
     *            XPath expression.
     * @see       $nodes, $ids
     */
    function evaluate($path, $context = "") {
        // Remove slashes and quote signs.
        $path = stripslashes($path);
        $path = str_replace("\"", "", $path);
        $path = str_replace("'", "", $path);

        // Split the paths into different paths.
        $paths = $this->split_paths($path);

        // Create an empty set to save the result.
        $result = array();

        // Run through all paths.
        foreach ($paths as $path) {
            // Trim the path.
            $path = trim($path);

            // Save the current path.
            $this->xpath = $path;

            // Convert all entities.
            $path = strtr($path, array_flip($this->entities));

            // Split the path at every slash.
            $steps = $this->split_steps($path);

            // Check whether the first element is empty.
            if (empty($steps[0])) {
                // Remove the first and empty element.
                array_shift($steps);
            }

            // Start to evaluate the steps.
            $nodes = $this->evaluate_step($context, $steps);

            // Remove duplicated nodes.
            $nodes = array_unique($nodes);

            // Add the nodes to the result set.
            $result = array_merge($result, $nodes);
        }

        // Return the result.
        return $result;
    }

    /**
     * Handles opening XML tags while parsing.
     *
     * While parsing a XML document for each opening tag this method is
     * called. It'll add the tag found to the tree of document nodes.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     int $parser Handler for accessing the current XML parser.
     * @param     string $name Name of the opening tag found in the document.
     * @param     array $attributes Associative array containing a list of
     *            all attributes of the tag found in the document.
     * @see       handle_end_element(), handle_character_data(), $nodes, $ids
     */
    function handle_start_element($parser, $name, $attributes) {
        // Add a node.
        $this->path = $this->add_node($this->path, $name);

        // Set the attributes.
        // Xpath로 안가져온다. 한달을 헛지랄 했다!!
        // modifyed by ddaemiri, 2007.09.03
        // $this->set_attributes($this->path, $attributes);
        // add array, added by ddaemiri, 2007.09.03
        $arr = preg_split("/[\/]+/", $this->path, -1, PREG_SPLIT_NO_EMPTY);
        $this->xml_node[$arr[count($arr) - 1]]["attr"] = $attributes;
    }

    /**
     * Handles closing XML tags while parsing.
     *
     * While parsing a XML document for each closing tag this method is
     * called.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     int $parser Handler for accessing the current XML parser.
     * @param     string $name Name of the closing tag found in the document.
     * @see       handle_start_element(), handle_character_data(), $nodes, $ids
     */
    function handle_end_element($parser, $name) {
        // Jump back to the parent element.
        $this->path = substr($this->path, 0, strrpos($this->path, "/"));
    }

    /**
     * Handles character data while parsing.
     *
     * While parsing a XML document for each character data this method
     * is called. It'll add the character data to the document tree.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     int $parser Handler for accessing the current XML parser.
     * @param     string $text Character data found in the document.
     * @see       handle_start_element(), handle_end_element(), $nodes, $ids
     */
    function handle_character_data($parser, $text) {
        // Replace entities.
        $text = strtr($text, $this->entities);

        // Save the text.
        // Xpath로 안가져온다. 한달을 헛지랄 했다!!
        // modifyed by ddaemiri, 2007.09.03
        //$this->add_content($this->path, addslashes(trim($text)));
        // add array, added by ddaemiri, 2007.09.03
        $arr = preg_split("/[\/]+/", $this->path, -1, PREG_SPLIT_NO_EMPTY);
        //edited by ddaemiri. libexpat은 \n을 분리자로 인식
        //$this->xml_node[$arr[count($arr)-1]]["text"] = addslashes(trim($text));
        $this->xml_node[$arr[count($arr) - 1]]["text"] = $this->xml_node[$arr[count($arr) - 1]]["text"] . addslashes(trim($text));
    }

    /**
     * Splits an XPath expression into its different expressions.
     *
     * This method splits an XPath expression. Each expression can consists of
     * list of expression being separated from each other by a | character.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $expression The complete expression to be splitted
     *            into its different expressions.
     * @return    array The array returned from this method contains a list
     *            of all expressions found in the expression passed to this
     *            method as a parameter.
     * @see       evalute()
     */
    function split_paths($expression) {
        // Create an empty array.
        $paths = array();

        // Save the position of the slash.
        $position = -1;

        // Run through the expression.
        do {
            // Search for a slash.
            $position = $this->search_string($expression, "|");

            // Check whether a | was found.
            if ($position >= 0) {
                // Get the left part of the expression.
                $left = substr($expression, 0, $position);
                $right = substr($expression, $position + 1);

                // Add the left value to the steps.
                $paths[] = $left;

                // Reduce the expression to the right part.
                $expression = $right;
            }
        } while ($position > -1);

        // Add the remaing expression to the list of steps.
        $paths[] = $expression;

        // Return the steps.
        return $paths;
    }

    /**
     * Splits an XPath expression into its different steps.
     *
     * This method splits an XPath expression. Each expression can consists of
     * list of steps being separated from each other by a / character.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $expression The complete expression to be splitted
     *            into its different steps.
     * @return    array The array returned from this method contains a list
     *            of all steps found in the expression passed to this
     *            method as a parameter.
     * @see       evalute()
     */
    function split_steps($expression) {
        // Create an empty array.
        $steps = array();

        // Replace a double slashes, because they'll cause problems otherwise.
        $expression = str_replace("//@", "/descendant::*/@", $expression);
        $expression = str_replace("//", "/descendant::", $expression);

        // Save the position of the slash.
        $position = -1;

        // Run through the expression.
        do {
            // Search for a slash.
            $position = $this->search_string($expression, "/");

            // Check whether a slash was found.
            if ($position >= 0) {
                // Get the left part of the expression.
                $left = substr($expression, 0, $position);
                $right = substr($expression, $position + 1);

                // Add the left value to the steps.
                $steps[] = $left;

                // Reduce the expression to the right part.
                $expression = $right;
            }
        } while ($position > -1);

        // Add the remaing expression to the list of steps.
        $steps[] = $expression;

        // Return the steps.
        return $steps;
    }

    /**
     * Retrieves axis information from an XPath expression step.
     *
     * This method tries to extract the name of the axis and its node-test
     * from a given step of an XPath expression at a given node.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $step String containing a step of an XPath expression.
     * @param     string $node Full document path of the node on which the
     *            step is executed.
     * @return    array This method returns an array containing information
     *            about the axis found in the step.
     * @see       evaluate_step()
     */
    function get_axis($step, $node) {
        // Create an array to save the axis information.
        $axis = array(
            "axis" => "",
            "node-test" => "",
            "predicate" => array()
        );

        // Check whether there are predicates.
        if (preg_match("/\[/", $step)) {
            // Get the predicates.
            $predicates = substr($step, strpos($step, "["));

            // Reduce the step.
            $step = $this->prestr($step, "[");

            // Try to split the predicates.
            $predicates = str_replace("][", "]|[", $predicates);
            $predicates = explode("|", $predicates);

            // Run through all predicates.
            foreach ($predicates as $predicate) {
                // Remove the brackets.
                $predicate = substr($predicate, 1, strlen($predicate) - 2);

                // Add the predicate to the list of predicates.
                $axis["predicate"][] = $predicate;
            }
        }

        // Check whether the axis is given in plain text.
        if ($this->search_string($step, "::") > -1) {
            // Split the step to extract axis and node-test.
            $axis["axis"] = $this->prestr($step, "::");
            $axis["node-test"] = $this->afterstr($step, "::");
        } else {
            // Check whether the step is empty.
            if (empty($step)) {
                // Set it to the default value.
                $step = ".";
            }

            // Check whether is an abbreviated syntax.
            if ($step == "*") {
                // Use the child axis and select all children.
                $axis["axis"] = "child";
                $axis["node-test"] = "*";
            }
            //elseif ( ereg("\(", $step) )
            //elseif ( preg_match("\(", $step) )
            elseif (preg_match("/\(/", $step)) {
                // Check whether it's a function.
                if ($this->is_function($this->prestr($step, "("))) {
                    // Get the position of the first bracket.
                    $start = strpos($step, "(");
                    $end = strpos($step, ")", $start);

                    // Get everything before, between and after the brackets.
                    $before = substr($step, 0, $start);
                    $between = substr($step, $start + 1, $end - $start - 1);
                    $after = substr($step, $end + 1);

                    // Trim each string.
                    $before = trim($before);
                    $between = trim($between);
                    $after = trim($after);

                    // Save the evaluated function.
                    $axis["axis"] = "function";
                    $axis["node-test"] = $this->evaluate_function($before, $between, $node);
                } else {
                    // Use the child axis and a function.
                    $axis["axis"] = "child";
                    $axis["node-test"] = $step;
                }
            }
            //elseif ( eregi("^@", $step) )
            //elseif ( preg_match("^@/i", $step) )
            elseif (preg_match("/^@/i", $step)) {
                // Use the attribute axis and select the attribute.
                $axis["axis"] = "attribute";
                $axis["node-test"] = substr($step, 1);
            }
            //elseif ( eregi("\]$", $step) )
            //elseif ( preg_match("\]$/i", $step) )
            elseif (preg_match("/\]$/i", $step)) {
                // Use the child axis and select a position.
                $axis["axis"] = "child";
                $axis["node-test"] = substr($step, strpos($step, "["));
            } elseif ($step == ".") {
                // Select the self axis.
                $axis["axis"] = "self";
                $axis["node-test"] = "*";
            } elseif ($step == "..") {
                // Select the parent axis.
                $axis["axis"] = "parent";
                $axis["node-test"] = "*";
            }
            //elseif ( ereg("^[a-zA-Z0-9\-_]+$", $step) )
            //elseif ( preg_match("^[a-zA-Z0-9\-_]+$", $step) )
            elseif (preg_match("/^[a-zA-Z0-9\-_]+$/", $step)) {
                // Select the child axis and the child.
                $axis["axis"] = "child";
                $axis["node-test"] = $step;
            } else {
                // Use the child axis and a name.
                $axis["axis"] = "child";
                $axis["node-test"] = $step;
            }
        }

        // Check whether it's a valid axis.
        if (!in_array($axis["axis"], array_merge($this->axes, array("function")))) {
            // Display an error message.
            $this->display_error("While parsing an XPath expression, in " .
                    "the step \"%s\" the invalid axis \"%s\" was found.", str_replace($step, "<b>" . $step . "</b>", $this->xpath), #
                    $axis["axis"]);
        }

        // Return the axis information.
        return $axis;
    }

    /**
     * Looks for a string within another string.
     *
     * This method looks for a string within another string. Brackets in the
     * string the method is looking through will be respected, which means that
     * only if the string the method is looking for is located outside of
     * brackets, the search will be successful.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $term String in which the search shall take place.
     * @param     string $expression String that should be searched.
     * @return    int This method returns -1 if no string was found, otherwise
     *            the offset at which the string was found.
     * @see       evaluate_step()
     */
    function search_string($term, $expression) {
        // Create a new counter for the brackets.
        $brackets = 0;

        // Run through the string.
        for ($i = 0; $i < strlen($term); $i++) {
            // Get the character at the position of the string.
            $character = substr($term, $i, 1);

            // Check whether it's a breacket.
            if (( $character == "(" ) || ( $character == "[" )) {
                // Increase the number of brackets.
                $brackets++;
            } elseif (( $character == ")" ) || ( $character == "]" )) {
                // Decrease the number of brackets.
                $brackets--;
            } elseif ($brackets == 0) {
                // Check whether we can find the expression at this index.
                if (substr($term, $i, strlen($expression)) == $expression) {
                    // Return the current index.
                    return $i;
                }
            }
        }

        // Check whether we had a valid number of brackets.
        if ($brackets != 0) {
            // Display an error message.
            $this->display_error("While parsing an XPath expression, in the " .
                    "predicate \"%s\", there was an invalid number of brackets.", str_replace($term, "<b>" . $term . "</b>", $this->xpath));
        }

        // Nothing was found.
        return (-1);
    }

    /**
     * Checks for a valid function name.
     *
     * This method check whether an expression contains a valid name of an
     * XPath function.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $expression Name of the function to be checked.
     * @return    boolean This method returns true if the given name is a valid
     *            XPath function name, otherwise false.
     * @see       evaluate()
     */
    function is_function($expression) {
        // Check whether it's in the list of supported functions.
        if (in_array($expression, $this->functions)) {
            // It's a function.
            return true;
        } else {
            // It's not a function.
            return false;
        }
    }

    /**
     * Evaluates a step of an XPath expression.
     *
     * This method tries to evaluate a step from an XPath expression at a
     * specific context.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $context Full document path of the context from
     *            which starting the step should be evaluated.
     * @param     array $steps Array containing the remaining steps of the
     *            current XPath expression.
     * @return    array This method returns an array containing all nodes
     *            that are the result of evaluating the given XPath step.
     * @see       evaluate()
     */
    function evaluate_step($context, $steps) {
        // Create an empty array for saving the nodes found.
        $nodes = array();

        // Check whether the context is an array of contexts.
        if (is_array($context)) {
            // Run through the array.
            foreach ($context as $path) {
                // Call this method for this single path.
                $nodes = array_merge($nodes, $this->evaluate_step($path, $steps));
            }
        } else {
            // Get this step.
            $step = array_shift($steps);

            // Create an array to save the new contexts.
            $contexts = array();

            // Get the axis of the current step.
            $axis = $this->get_axis($step, $context);

            // Check whether it's a function.
            if ($axis["axis"] == "function") {
                // Check whether an array was return by the function.
                if (is_array($axis["node-test"])) {
                    // Add the results to the list of contexts.
                    $contexts = array_merge($contexts, $axis["node-test"]);
                } else {
                    // Add the result to the list of contexts.
                    $contexts[] = $axis["node-test"];
                }
            } else {
                // Create the name of the method.
                $method = "handle_axis_" . str_replace("-", "_", $axis["axis"]);

                // Check whether the axis handler is defined.
                if (!method_exists($this, $method)) {
                    // Display an error message.
                    $this->display_error("While parsing an XPath expression, " .
                            "the axis \"%s\" could not be handled, because this " .
                            "version does not support this axis.", $axis["axis"]);
                }

                // Perform an axis action.
                $contexts = call_user_method($method, $this, $axis, $context);

                // Check whether there are predicates.
                if (count($axis["predicate"]) > 0) {
                    // Check whether each node fits the predicates.
                    $contexts = $this->check_predicates($contexts, $axis["predicate"]);
                }
            }

            // Check whether there are more steps left.
            if (count($steps) > 0) {
                // Continue the evaluation of the next steps.
                $nodes = $this->evaluate_step($contexts, $steps);
            } else {
                // Save the found contexts.
                $nodes = $contexts;
            }
        }

        // Return the nodes found.
        return $nodes;
    }

    /**
     * Evaluates an XPath function
     *
     * This method evaluates a given XPath function with its arguments on a
     * specific node of the document.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $function Name of the function to be evaluated.
     * @param     string $arguments String containing the arguments being
     *            passed to the function.
     * @param     string $node Full path to the document node on which the
     *            function should be evaluated.
     * @return    mixed This method returns the result of the evaluation of
     *            the function. Depending on the function the type of the 
     *            return value can be different.
     * @see       evaluate()
     */
    function evaluate_function($function, $arguments, $node) {
        // Remove whitespaces.
        $function = trim($function);
        $arguments = trim($arguments);

        // Create the name of the function handling function.
        $method = "handle_function_" . str_replace("-", "_", $function);

        // Check whether the function handling function is available.
        if (!method_exists($this, $method)) {
            // Display an error message.
            $this->display_error("While parsing an XPath expression, " .
                    "the function \"%s\" could not be handled, because this " .
                    "version does not support this function.", $function);
        }

        // Return the result of the function.
        return call_user_method($method, $this, $node, $arguments);
    }

    /**
     * Evaluates a predicate on a node.
     *
     * This method tries to evaluate a predicate on a given node.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the predicate
     *            should be evaluated.
     * @param     string $predicate String containing the predicate expression
     *            to be evaluated.
     * @return    mixed This method is called recursively. The first call should
     *            return a boolean value, whether the node matches the predicate
     *            or not. Any call to the method being made during the recursion
     *            may also return other types for further processing.
     * @see       evaluate()
     */
    function evaluate_predicate($node, $predicate) {
        // Set the default position and the type of the operator.
        $position = 0;
        $operator = "";

        // Run through all operators and try to find them.
        foreach ($this->operators as $expression) {
            // Check whether a position was already found.
            if ($position <= 0) {
                // Try to find the operator.
                $position = $this->search_string($predicate, $expression);

                // Check whether a operator was found.
                if ($position > 0) {
                    // Save the operator.
                    $operator = $expression;

                    // Check whether it's the equal operator.
                    if ($operator == "=") {
                        // Also look for other operators containing the
                        // equal sign.
                        if ($this->search_string($predicate, "!=") ==
                                ( $position - 1 )) {
                            // Get the new position.
                            $position = $this->search_string($predicate, "!=");

                            // Save the new operator.
                            $operator = "!=";
                        }
                        if ($this->search_string($predicate, "<=") ==
                                ( $position - 1 )) {
                            // Get the new position.
                            $position = $this->search_string($predicate, "<=");

                            // Save the new operator.
                            $operator = "<=";
                        }
                        if ($this->search_string($predicate, ">=") ==
                                ( $position - 1 )) {
                            // Get the new position.
                            $position = $this->search_string($predicate, ">=");

                            // Save the new operator.
                            $operator = ">=";
                        }
                    }
                }
            }
        }

        // Check whether the operator is a - sign.
        if ($operator == "-") {
            // Check whether it's not a function containing a - in its name.
            foreach ($this->functions as $function) {
                // Check whether there's a - sign in the function name.
                //if ( ereg("-", $function) )
                //if ( preg_match("-", $function) )
                if (preg_match("/-/", $function)) {
                    // Get the position of the - in the function name.
                    $sign = strpos($function, "-");

                    // Extract a substring from the predicate.
                    $sub = substr($predicate, $position - $sign, strlen($function));

                    // Check whether it's the function.
                    if ($sub == $function) {
                        // Don't use the operator.
                        $operator = "";
                        $position = -1;
                    }
                }
            }
        } elseif ($operator == "*") {
            // Get some substrings.
            $character = substr($predicate, $position - 1, 1);
            $attribute = substr($predicate, $position - 11, 11);

            // Check whether it's an attribute selection.
            if (( $character == "@" ) || ( $attribute == "attribute::" )) {
                // Don't use the operator.
                $operator = "";
                $position = -1;
            }
        }

        // Check whether an operator was found.        
        if ($position > 0) {
            // Get the left and the right part of the expression.
            $left = substr($predicate, 0, $position);
            $right = substr($predicate, $position + strlen($operator));

            // Remove whitespaces.
            $left = trim($left);
            $right = trim($right);

            // Evaluate the left and the right part.
            $left = $this->evaluate_predicate($node, $left);
            $right = $this->evaluate_predicate($node, $right);

            // Check the kind of operator.
            switch ($operator) {
                case " or ":
                    // Return the two results connected by an "or".
                    return ( $left or $right );

                case " and ":
                    // Return the two results connected by an "and".
                    return ( $left and $right );

                case "=":
                    // Compare the two results.
                    return ( $left == $right );

                case "!=":
                    // Check whether the two results are not equal.
                    return ( $left != $right );

                case "<=":
                    // Compare the two results.
                    return ( $left <= $right );

                case "<":
                    // Compare the two results.
                    return ( $left < $right );

                case ">=":
                    // Compare the two results.
                    return ( $left >= $right );

                case ">":
                    // Compare the two results.
                    return ( $left > $right );

                case "+":
                    // Return the result by adding one result to the other.
                    return ( $left + $right );

                case "-":
                    // Return the result by decrease one result by the other.
                    return ( $left - $right );

                case "*":
                    // Return a multiplication of the two results.
                    return ( $left * $right );

                case " div ":
                    // Return a division of the two results.
                    if ($right == 0) {
                        // Display an error message.
                        $this->display_error("While parsing an XPath " .
                                "predicate, a error due a division by zero " .
                                "occured.");
                    } else {
                        // Return the result of the division.
                        return ( $left / $right );
                    }
                    break;

                case " mod ":
                    // Return a modulo of the two results.
                    return ( $left % $right );
            }
        }

        // Check whether the predicate is a function.
        //if ( ereg("\(", $predicate) )
        //if ( preg_match("\(", $predicate) )
        if (preg_match("/\(/", $predicate)) {
            // Get the position of the first bracket.
            $start = strpos($predicate, "(");
            $end = strpos($predicate, ")", $start);

            // Get everything before, between and after the brackets.
            $before = substr($predicate, 0, $start);
            $between = substr($predicate, $start + 1, $end - $start - 1);
            $after = substr($predicate, $end + 1);

            // Trim each string.
            $before = trim($before);
            $between = trim($between);
            $after = trim($after);

            // Check whether there's something after the bracket.
            if (!empty($after)) {
                // Display an error message.
                $this->display_error("While parsing an XPath expression " .
                        "there was found an error in the predicate \"%s\", " .
                        "because after a closing bracket there was found " .
                        "something unknown.", str_replace($predicate, "<b>" . $predicate . "</b>", $this->xpath));
            }

            // Check whether it's a function.
            if (empty($before) && empty($after)) {
                // Evaluate the content of the brackets.
                return $this->evaluate_predicate($node, $between);
            } elseif ($this->is_function($before)) {
                // Return the evaluated function.
                return $this->evaluate_function($before, $between, $node);
            } else {
                // Display an error message.
                $this->display_error("While parsing a predicate in an XPath " .
                        "expression, a function \"%s\" was found, which is not " .
                        "yet supported by the parser.", str_replace($before, "<b>" . $before . "</b>", $this->xpath));
            }
        }

        // Check whether the predicate is just a digit.
        //if ( ereg("^[0-9]+(\.[0-9]+)?$", $predicate) || ereg("^\.[0-9]+$", $predicate) )
        //if ( preg_match("^[0-9]+(\.[0-9]+)?$", $predicate) || preg_match("^\.[0-9]+$", $predicate) )
        if (preg_match("/^[0-9]+(\.[0-9]+)?$/", $predicate) || preg_match("/^\.[0-9]+$/", $predicate)) {
            // Return the value of the digit.
            return doubleval($predicate);
        }

        // Check whether it's an XPath expression.
        $result = $this->evaluate($predicate, $node);
        if (count($result) > 0) {
            // Convert the array.
            $result = explode("|", implode("|", $result));

            // Get the value of the first result.
            $value = $this->get_content($result[0]);

            // Return the value.
            return $value;
        }

        // Return the predicate as a string.
        return $predicate;
    }

    /**
     * Checks whether a node matches predicates.
     *
     * This method checks whether a list of nodes passed to this method match
     * a given list of predicates. 
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $nodes Array of full paths of all nodes to be tested.
     * @param     array $predicates Array of predicates to use.
     * @return    array The array returned by this method contains a list of
     *            all nodes matching the given predicates.
     * @see       evaluate_step()
     */
    function check_predicates($nodes, $predicates) {
        // Create an empty set of nodes.
        $result = array();

        // Run through all nodes.
        foreach ($nodes as $node) {
            // Create a variable whether to add this node to the node-set.
            $add = true;

            // Run through all predicates.
            foreach ($predicates as $predicate) {
                // Check whether the predicate is just an number.
                //if ( ereg("^[0-9]+$", $predicate) )
                //if ( preg_match("^[0-9]+$", $predicate) )
                if (preg_match("/^[0-9]+$/", $predicate)) {
                    // Enhance the predicate.
                    $predicate .= "=position()";
                }

                // Do the predicate check.
                $check = $this->evaluate_predicate($node, $predicate);

                // Check whether it's a string.
                if (is_string($check) && ( ( $check == "" ) ||
                        ( $check == $predicate ) )) {
                    // Set the result to false.
                    $check = false;
                }

                // Check whether it's an integer.
                if (is_int($check)) {
                    // Check whether it's the current position.
                    if ($check == $this->handle_function_position($node, "")) {
                        // Set it to true.
                        $check = true;
                    } else {
                        // Set it to false.
                        $check = false;
                    }
                }

                // Check whether the predicate is OK for this node.
                $add = $add && $check;
            }

            // Check whether to add this node to the node-set.
            if ($add) {
                // Add the node to the node-set.
                $result[] = $node;
            }
        }

        // Return the array of nodes.
        return $result;
    }

    /**
     * Checks whether a node matches a node-test.
     *
     * This method checks whether a node in the document matches a given
     * node-test.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $context Full path of the node, which should be tested
     *            for matching the node-test.
     * @param     string $node_test String containing the node-test for the
     *            node.
     * @return    boolean This method returns true if the node matches the
     *            node-test, otherwise false.
     * @see       evaluate()
     */
    function check_node_test($context, $node_test) {
        // Check whether it's a function.
        //if ( ereg("\(", $node_test) )
        if (preg_match("/\(/", $node_test)) {
            // Get the type of function to use.
            $function = $this->prestr($node_test, "(");

            // Check whether the node fits the method.
            switch ($function) {
                case "node":
                    // Add this node to the list of nodes.
                    return true;

                case "text":
                    // Check whether the node has some text.
                    if (!empty($this->nodes[$context]["text"])) {
                        // Add this node to the list of nodes.
                        return true;
                    }
                    break;

                case "comment":
                    // Check whether the node has some comment.
                    if (!empty($this->nodes[$context]["comment"])) {
                        // Add this node to the list of nodes.
                        return true;
                    }
                    break;

                case "processing-instruction":
                    // Get the literal argument.
                    $literal = $this->afterstr($axis["node-test"], "(");

                    // Cut the literal.
                    $literal = substr($literal, 0, strlen($literal) - 1);

                    // Check whether a literal was given.
                    if (!empty($literal)) {
                        // Check whether the node's processing instructions
                        // are matching the literals given.
                        if ($this->nodes[$context]
                                ["processing-instructions"] == $literal) {
                            // Add this node to the node-set.
                            return true;
                        }
                    } else {
                        // Check whether the node has processing
                        // instructions.
                        if (!empty($this->nodes[$context]
                                        ["processing-instructions"])) {
                            // Add this node to the node-set.
                            return true;
                        }
                    }
                    break;

                default:
                    // Display an error message.
                    $this->display_error("While parsing an XPath " .
                            "expression there was found an undefined " .
                            "function called \"%s\".", str_replace($function, "<b>" . $function . "</b>", $this->xpath));
            }
        } elseif ($node_test == "*") {
            // Add this node to the node-set.
            return true;
        }
        //elseif ( ereg("^[a-zA-Z0-9\-_]+", $node_test) )
        //elseif ( preg_match("^[a-zA-Z0-9\-_]+", $node_test) )
        elseif (preg_match("/^[a-zA-Z0-9\-_]+/", $node_test)) {
            // Check whether the node-test can be fulfilled.
            if ($this->nodes[$context]["name"] == $node_test) {
                // Add this node to the node-set.
                return true;
            }
        } else {
            // Display an error message.
            $this->display_error("While parsing the XPath expression \"%s\" " .
                    "an empty and therefore invalid node-test has been found.", $this->xpath);
        }

        // Don't add this context.
        return false;
    }

    /**
     * Handles the XPath child axis.
     *
     * This method handles the XPath child axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_child($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get a list of all children.
        $children = $this->nodes[$context]["children"];

        // Check whether there are children.
        if (!empty($children)) {
            // Run through all children.
            foreach ($children as $child_name => $child_position) {
                // Run through all childs with this name.
                for ($i = 1; $i <= $child_position; $i++) {
                    // Create the path of the child.
                    $child = $context . "/" . $child_name . "[" . $i . "]";

                    // Check whether 
                    if ($this->check_node_test($child, $axis["node-test"])) {
                        // Add the child to the node-set.
                        $nodes[] = $child;
                    }
                }
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath parent axis.
     *
     * This method handles the XPath parent axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_parent($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Check whether the parent matches the node-test.
        if ($this->check_node_test($this->nodes[$context]["parent"], $axis["node-test"])) {
            // Add this node to the list of nodes.
            $nodes[] = $this->nodes[$context]["parent"];
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath attribute axis.
     *
     * This method handles the XPath attribute axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_attribute($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Check whether all nodes should be selected.
        if ($axis["node-test"] == "*") {
            // Check whether there are attributes.
            if (count($this->nodes[$context]["attributes"]) > 0) {
                // Run through the attributes.
                foreach ($this->nodes[$context]["attributes"] as $key => $value) {
                    // Add this node to the node-set.
                    $nodes[] = $context . "/attribute::" . $key;
                }
            }
        } elseif (!empty($this->nodes[$context]["attributes"]
                        [$axis["node-test"]])) {
            // Add this node to the node-set.
            $nodes[] = $context . "/attribute::" . $axis["node-test"];
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath self axis.
     *
     * This method handles the XPath self axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_self($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Check whether the context match the node-test.
        if ($this->check_node_test($context, $axis["node-test"])) {
            // Add this node to the node-set.
            $nodes[] = $context;
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath descendant axis.
     *
     * This method handles the XPath descendant axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_descendant($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Check whether the current node has children.
        if (count($this->nodes[$context]["children"]) > 0) {
            // Get a list of children.
            $children = $this->nodes[$context]["children"];

            // Run through all children.
            foreach ($children as $child_name => $child_position) {
                // Run through all children of this name.
                for ($i = 1; $i <= $child_position; $i++) {
                    // Create the full path for the children.
                    $child = $context . "/" . $child_name . "[" . $i . "]";

                    // Check whether the child matches the node-test.
                    if ($this->check_node_test($child, $axis["node-test"])) {
                        // Add the child to the list of nodes.
                        $nodes[] = $child;
                    }

                    // Recurse to the next level.
                    $nodes = array_merge($nodes, $this->handle_axis_descendant($axis, $child));
                }
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath ancestor axis.
     *
     * This method handles the XPath ancestor axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_ancestor($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get the parent of the current node.
        $parent = $this->nodes[$context]["parent"];

        // Check whether the parent isn't empty.
        if (!empty($parent)) {
            // Check whether the parent matches the node-test.
            if ($this->check_node_test($parent, $axis["node-test"])) {
                // Add the parent to the list of nodes.
                $nodes[] = $parent;
            }

            // Handle all other ancestors.
            $nodes = array_merge($nodes, $this->handle_axis_ancestor($axis, $parent));
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath namespace axis.
     *
     * This method handles the XPath namespace axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_namespace($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Check whether all nodes should be selected.
        if (!empty($this->nodes[$context]["namespace"])) {
            // Add this node to the node-set.
            $nodes[] = $context;
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath following axis.
     *
     * This method handles the XPath following axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_following($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get the current document position.
        $position = $this->nodes[$context]["document-position"];

        // Create a flag, whether we already found the context node.
        $found = false;

        // Run through all nodes of the document.
        foreach ($this->nodes as $node => $data) {
            // Check whether the context node has already been found.
            if ($found) {
                // Check whether the position is correct.
                if ($this->nodes[$node]["document-position"] == $position) {
                    // Check whether the node fits the node-test.
                    if ($this->check_node_test($node, $axis["node-test"])) {
                        // Add the node to the list of nodes.
                        $nodes[] = $node;
                    }
                }
            }

            // Check whether this is the context node.
            if ($node == $context) {
                // After this we'll look for more nodes.
                $found = true;
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath preceding axis.
     *
     * This method handles the XPath preceding axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_preceding($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get the current document position.
        $position = $this->nodes[$context]["document-position"];

        // Create a flag, whether we already found the context node.
        $found = true;

        // Run through all nodes of the document.
        foreach ($this->nodes as $node => $data) {
            // Check whether this is the context node.
            if ($node == $context) {
                // After this we won't look for more nodes.
                $found = false;
            }

            // Check whether the context node has already been found.
            if ($found) {
                // Check whether the position is correct.
                if ($this->nodes[$node]["document-position"] == $position) {
                    // Check whether the node fits the node-test.
                    if ($this->check_node_test($node, $axis["node-test"])) {
                        // Add the node to the list of nodes.
                        $nodes[] = $node;
                    }
                }
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath following-sibling axis.
     *
     * This method handles the XPath following-sibling axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_following_sibling($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get all children from the parent.
        $siblings = $this->handle_axis_child($axis, $this->nodes[$context]["parent"]);

        // Create a flag whether the context node was already found.
        $found = false;

        // Run through all siblings.
        foreach ($siblings as $sibling) {
            // Check whether the context node was already found.
            if ($found) {
                // Check whether the sibling is a real sibling.
                if ($this->nodes[$sibling]["name"] ==
                        $this->nodes[$context]["name"]) {
                    // Check whether the sibling matches the node-test.
                    if ($this->check_node_test($sibling, $axis["node-test"])) {
                        // Add the sibling to the list of nodes.
                        $nodes[] = $sibling;
                    }
                }
            }

            // Check whether this is the context node.
            if ($sibling == $context) {
                // Continue looking for other siblings.
                $found = true;
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath preceding-sibling axis.
     *
     * This method handles the XPath preceding-sibling axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_preceding_sibling($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Get all children from the parent.
        $siblings = $this->handle_axis_child($axis, $this->nodes[$context]["parent"]);

        // Create a flag whether the context node was already found.
        $found = true;

        // Run through all siblings.
        foreach ($siblings as $sibling) {
            // Check whether this is the context node.
            if ($sibling == $context) {
                // Don't continue looking for other siblings.
                $found = false;
            }

            // Check whether the context node was already found.
            if ($found) {
                // Check whether the sibling is a real sibling.
                if ($this->nodes[$sibling]["name"] ==
                        $this->nodes[$context]["name"]) {
                    // Check whether the sibling matches the node-test.
                    if ($this->check_node_test($sibling, $axis["node-test"])) {
                        // Add the sibling to the list of nodes.
                        $nodes[] = $sibling;
                    }
                }
            }
        }

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath descendant-or-self axis.
     *
     * This method handles the XPath descendant-or-self axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_descendant_or_self($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Read the nodes.
        $nodes = array_merge(
                $this->handle_axis_descendant($axis, $context), $this->handle_axis_self($axis, $context));

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath ancestor-or-self axis.
     *
     * This method handles the XPath ancestor-or-self axis.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     array $axis Array containing information about the axis.
     * @param     string $context Node from which starting the axis should
     *            be processed.
     * @return    array This method returns an array containing all nodes 
     *            that were found during the evaluation of the given axis.
     * @see       evaluate()
     */
    function handle_axis_ancestor_or_self($axis, $context) {
        // Create an empty node-set.
        $nodes = array();

        // Read the nodes.
        $nodes = array_merge(
                $this->handle_axis_ancestor($axis, $context), $this->handle_axis_self($axis, $context));

        // Return the nodeset.
        return $nodes;
    }

    /**
     * Handles the XPath function last.
     *
     * This method handles the XPath function last.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_last($node, $arguments) {
        // Calculate the size of the context.
        $parent = $this->nodes[$node]["parent"];
        $children = $this->nodes[$parent]["children"];
        $context = $children[$this->nodes[$node]["name"]];

        // Return the size.
        return $context;
    }

    /**
     * Handles the XPath function position.
     *
     * This method handles the XPath function position.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_position($node, $arguments) {
        // return the context-position.
        return $this->nodes[$node]["context-position"];
    }

    /**
     * Handles the XPath function count.
     *
     * This method handles the XPath function count.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_count($node, $arguments) {
        // Evaluate the argument of the method as an XPath and return
        // the number of results.
        return count($this->evaluate($arguments, $node));
    }

    /**
     * Handles the XPath function id.
     *
     * This method handles the XPath function id.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_id($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Now split the arguments.
        $arguments = explode(" ", $arguments);

        // Check whether 
        // Create a list of nodes.
        $nodes = array();

        // Run through all document node.
        foreach ($this->nodes as $node => $position) {
            // Check whether the node has the ID we're looking for.
            if (in_array($this->nodes[$node]["attributes"]["id"], $arguments)) {
                // Add this node to the list of nodes.
                $nodes[] = $node;
            }
        }

        // Return the list of nodes.
        return $nodes;
    }

    /**
     * Handles the XPath function name.
     *
     * This method handles the XPath function name.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_name($node, $arguments) {
        // Return the name of the node.
        return $this->nodes[$node]["name"];
    }

    /**
     * Handles the XPath function string.
     *
     * This method handles the XPath function string.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_string($node, $arguments) {
        // Check what type of parameter is given
        //if ( ereg("^[0-9]+(\.[0-9]+)?$", $arguments) || ereg("^\.[0-9]+$", $arguments) )
        //if ( preg_match("^[0-9]+(\.[0-9]+)?$", $arguments) || preg_match("^\.[0-9]+$", $arguments) )
        if (preg_match("/^[0-9]+(\.[0-9]+)?$/", $arguments) || preg_match("/^\.[0-9]+$/", $arguments)) {
            // Convert the digits to a number.
            $number = doubleval($arguments);

            // Return the number.
            return strval($number);
        } elseif (is_bool($arguments)) {
            // Check whether it's true.
            if ($arguments == true) {
                // Return true as a string.
                return "true";
            } else {
                // Return false as a string.
                return "false";
            }
        } elseif (!empty($arguments)) {
            // Use the argument as an XPath.
            $result = $this->evaluate($arguments, $node);

            // Get the first argument.
            $result = explode("|", implode("|", $result));

            // Return the first result as a string.
            return $result[0];
        } elseif (empty($arguments)) {
            // Return the current node.
            return $node;
        } else {
            // Return an empty string.
            return "";
        }
    }

    /**
     * Handles the XPath function concat.
     *
     * This method handles the XPath function concat.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_concat($node, $arguments) {
        // Split the arguments.
        $arguments = explode(",", $arguments);

        // Run through each argument and evaluate it.
        for ($i = 0; $i < sizeof($arguments); $i++) {
            // Trim each argument.
            $arguments[$i] = trim($arguments[$i]);

            // Evaluate it.
            $arguments[$i] = $this->evaluate_predicate($node, $arguments[$i]);
        }

        // Put the string together.
        $arguments = implode("", $arguments);

        // Return the string.
        return $arguments;
    }

    /**
     * Handles the XPath function starts-with.
     *
     * This method handles the XPath function starts-with.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_starts_with($node, $arguments) {
        // Get the arguments.
        $first = trim($this->prestr($arguments, ","));
        $second = trim($this->afterstr($arguments, ","));

        // Evaluate each argument.
        $first = $this->evaluate_predicate($node, $first);
        $second = $this->evaluate_predicate($node, $second);

        // Check whether the first string starts with the second one.
        //if ( ereg("^".$second, $first) )
        //if ( preg_match("^".$second, $first) )
        if (preg_match("/^" . $second . "/", $first)) {
            // Return true.
            return true;
        } else {
            // Return false.
            return false;
        }
    }

    /**
     * Handles the XPath function contains.
     *
     * This method handles the XPath function contains.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_contains($node, $arguments) {
        // Get the arguments.
        $first = trim($this->prestr($arguments, ","));
        $second = trim($this->afterstr($arguments, ","));

        // Evaluate each argument.
        $first = $this->evaluate_predicate($node, $first);
        $second = $this->evaluate_predicate($node, $second);

        // Check whether the first string starts with the second one.
        //if ( ereg($second, $first) )
        //if ( preg_match($second, $first) )
        if (preg_match("/^" . $second . "/", $first)) {
            // Return true.
            return true;
        } else {
            // Return false.
            return false;
        }
    }

    /**
     * Handles the XPath function substring-before.
     *
     * This method handles the XPath function substring-before.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_substring_before($node, $arguments) {
        // Get the arguments.
        $first = trim($this->prestr($arguments, ","));
        $second = trim($this->afterstr($arguments, ","));

        // Evaluate each argument.
        $first = $this->evaluate_predicate($node, $first);
        $second = $this->evaluate_predicate($node, $second);

        // Return the substring.
        return $this->prestr(strval($first), strval($second));
    }

    /**
     * Handles the XPath function substring-after.
     *
     * This method handles the XPath function substring-after.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_substring_after($node, $arguments) {
        // Get the arguments.
        $first = trim($this->prestr($arguments, ","));
        $second = trim($this->afterstr($arguments, ","));

        // Evaluate each argument.
        $first = $this->evaluate_predicate($node, $first);
        $second = $this->evaluate_predicate($node, $second);

        // Return the substring.
        return $this->afterstr(strval($first), strval($second));
    }

    /**
     * Handles the XPath function substring.
     *
     * This method handles the XPath function substring.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_substring($node, $arguments) {
        // Split the arguments.
        $arguments = explode(",", $arguments);

        // Run through all arguments.
        for ($i = 0; $i < sizeof($arguments); $i++) {
            // Trim the string.
            $arguments[$i] = trim($arguments[$i]);

            // Evaluate each argument.
            $arguments[$i] = $this->evaluate_predicate($node, $arguments[$i]);
        }

        // Check whether a third argument was given.
        if (!empty($arguments[2])) {
            // Return the substring.
            return substr(strval($arguments[0]), $arguments[1] - 1, $arguments[2]);
        } else {
            // Return the substring.
            return substr(strval($arguments[0]), $arguments[1] - 1);
        }
    }

    /**
     * Handles the XPath function string-length.
     *
     * This method handles the XPath function string-length.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_string_length($node, $arguments) {
        // Trim the argument.
        $arguments = trim($arguments);

        // Evaluate the argument.
        $arguments = $this->evaluate_predicate($node, $arguments);

        // Return the length of the string.
        return strlen(strval($arguments));
    }

    /**
     * Handles the XPath function translate.
     *
     * This method handles the XPath function translate.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_translate($node, $arguments) {
        // Split the arguments.
        $arguments = explode(",", $arguments);

        // Run through all arguments.
        for ($i = 0; $i < sizeof($arguments); $i++) {
            // Trim the argument.
            $arguments[$i] = trim($arguments[$i]);

            // Evaluate the argument.
            $arguments[$i] = $this->evaluate_predicate($node, $arguments[$i]);
        }

        // Return the translated string.
        return strtr($arguments[0], $arguments[1], $arguments[2]);
    }

    /**
     * Handles the XPath function boolean.
     *
     * This method handles the XPath function boolean.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_boolean($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Check what type of parameter is given
        //if ( ereg("^[0-9]+(\.[0-9]+)?$", $arguments) || ereg("^\.[0-9]+$", $arguments) )
        //if ( preg_match("^[0-9]+(\.[0-9]+)?$", $arguments) || preg_match("^\.[0-9]+$", $arguments) )
        if (preg_match("/^[0-9]+(\.[0-9]+)?$/", $arguments) || preg_match("/^\.[0-9]+$/", $arguments)) {
            // Convert the digits to a number.
            $number = doubleval($arguments);

            // Check whether the number zero.
            if ($number == 0) {
                // Return false.
                return false;
            } else {
                // Return true.
                return true;
            }
        } elseif (empty($arguments)) {
            // Sorry, there were no arguments.
            return false;
        } else {
            // Try to evaluate the argument as an XPath.
            $result = $this->evaluate($arguments, $node);

            // Check whether we found something.
            if (count($result) > 0) {
                // Return true.
                return true;
            } else {
                // Return false.
                return false;
            }
        }
    }

    /**
     * Handles the XPath function not.
     *
     * This method handles the XPath function not.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_not($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Return the negative value of the content of the brackets.
        return !$this->evaluate_predicate($node, $arguments);
    }

    /**
     * Handles the XPath function true.
     *
     * This method handles the XPath function true.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_true($node, $arguments) {
        // Return true.
        return true;
    }

    /**
     * Handles the XPath function false.
     *
     * This method handles the XPath function false.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_false($node, $arguments) {
        // Return false.
        return false;
    }

    /**
     * Handles the XPath function lang.
     *
     * This method handles the XPath function lang.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_lang($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Check whether the node has an language attribute.
        if (empty($this->nodes[$node]["attributes"]["xml:lang"])) {
            // Run through the ancestors.
            while (!empty($node)) {
                // Select the parent node.
                $node = $this->nodes[$node]["parent"];

                // Check whether there's a language definition.
                if (!empty($this->nodes[$node]["attributes"]["xml:lang"])) {
                    // Check whether it's the language, the user asks for.
                    //if ( eregi("^".$arguments, $this->nodes[$node]
                    //    ["attributes"]["xml:lang"]) )
                    //if ( preg_match("^/i".$arguments, $this->nodes[$node]
                    if (preg_match("/^" . $arguments . "/i", $this->nodes[$node]
                                    ["attributes"]["xml:lang"])) {
                        // Return true.
                        return true;
                    } else {
                        // Return false.
                        return false;
                    }
                }
            }

            // Return false.
            return false;
        } else {
            // Check whether it's the language, the user asks for.
            //if ( eregi("^".$arguments, $this->nodes[$node]["attributes"]
            //    ["xml:lang"]) )
            //if ( preg_match("^/i".$arguments, $this->nodes[$node]["attributes"]
            if (preg_match("/^" . $arguments . "/i", $this->nodes[$node]["attributes"]
                            ["xml:lang"])) {
                // Return true.
                return true;
            } else {
                // Return false.
                return false;
            }
        }
    }

    /**
     * Handles the XPath function number.
     *
     * This method handles the XPath function number.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_number($node, $arguments) {
        // Check the type of argument.
        //if ( ereg("^[0-9]+(\.[0-9]+)?$", $arguments) ||
        //    ereg("^\.[0-9]+$", $arguments) )
        //if ( preg_match("^[0-9]+(\.[0-9]+)?$", $arguments) || preg_match("^\.[0-9]+$", $arguments) )
        if (preg_match("/^[0-9]+(\.[0-9]+)?$/", $arguments) || preg_match("/^\.[0-9]+$/", $arguments)) {
            // Return the argument as a number.
            return doubleval($arguments);
        } elseif (is_bool($arguments)) {
            // Check whether it's true.
            if ($arguments == true) {
                // Return 1.
                return 1;
            } else {
                // Return 0.
                return 0;
            }
        }
    }

    /**
     * Handles the XPath function sum.
     *
     * This method handles the XPath function sum.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_sum($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Evaluate the arguments as an XPath expression.
        $results = $this->evaluate($arguments, $node);

        // Create a variable to save the sum.
        $sum = 0;

        // Run through all results.
        foreach ($results as $result) {
            // Get the value of the node.
            $result = $this->get_content($result);

            // Add it to the sum.
            $sum += doubleval($result);
        }

        // Return the sum.
        return $sum;
    }

    /**
     * Handles the XPath function floor.
     *
     * This method handles the XPath function floor.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_floor($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Convert the arguments to a number.
        $arguments = doubleval($arguments);

        // Return the result
        return floor($arguments);
    }

    /**
     * Handles the XPath function ceiling.
     *
     * This method handles the XPath function ceiling.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_ceiling($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Convert the arguments to a number.
        $arguments = doubleval($arguments);

        // Return the result
        return ceil($arguments);
    }

    /**
     * Handles the XPath function round.
     *
     * This method handles the XPath function round.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_round($node, $arguments) {
        // Trim the arguments.
        $arguments = trim($arguments);

        // Convert the arguments to a number.
        $arguments = doubleval($arguments);

        // Return the result
        return round($arguments);
    }

    /**
     * Handles the XPath function text.
     *
     * This method handles the XPath function text.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $node Full path of the node on which the function
     *            should be processed.
     * @param     string $arguments String containing the arguments that were
     *            passed to the function.
     * @return    mixed Depending on the type of function being processed this 
     *            method returns different types.
     * @see       evaluate()
     */
    function handle_function_text($node, $arguments) {
        // Return the character data of the node.
        return $this->nodes[$node]["text"];
    }

    /**
     * Retrieves a substring before a delimiter.
     *
     * This method retrieves everything from a string before a given delimiter,
     * not including the delimiter.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $string String, from which the substring should be
     *            extracted.
     * @param     string $delimiter String containing the delimiter to use.
     * @return    string Substring from the original string before the
     *            delimiter.
     * @see       afterstr()
     */
    function prestr($string, $delimiter) {
        // Return the substring.
        return substr($string, 0, strlen($string) - strlen(strstr($string, "$delimiter")));
    }

    /**
     * Retrieves a substring after a delimiter.
     *
     * This method retrieves everything from a string after a given delimiter,
     * not including the delimiter.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $string String, from which the substring should be
     *            extracted.
     * @param     string $delimiter String containing the delimiter to use.
     * @return    string Substring from the original string after the
     *            delimiter.
     * @see       prestr()
     */
    function afterstr($string, $delimiter) {
        // Return the substring.
        return substr($string, strpos($string, $delimiter) + strlen($delimiter));
    }

    /**
     * Displays an error message.
     *
     * This method displays an error messages and stops the execution of the
     * script. This method is called exactly in the same way as the printf
     * function. The first argument contains the message and additional
     * arguments of various types may be passed to this method to be inserted
     * into the message.
     *
     * @access    private
     * @author    Michael P. Mehl <mpm@phpxml.org>
     * @param     string $message Error message to be displayed.
     */
    function display_error($message) {
        // Check whether more than one argument was given.
        if (func_num_args() > 1) {
            // Read all arguments.
            $arguments = func_get_args();

            // Create a new string for the inserting command.
            $command = "\$message = sprintf(\$message, ";

            // Run through the array of arguments.
            for ($i = 1; $i < sizeof($arguments); $i++) {
                // Add the number of the argument to the command.
                $command .= "\$arguments[" . $i . "], ";
            }

            // Replace the last separator.
            //$command = eregi_replace(", $", ");", $command);
            $command = preg_replace("/, $/i", ");", $command);

            // Execute the command.
            eval($command);
        }

        // Display the error message.
        echo "<b>phpXML error:</b> " . $message;

        // End the execution of this script.
        exit;
    }

    //added by ddaemiri, 2007.05.28
    //entity 가 하나만 있다고 가정!! 배열의 첫번째만 가져옴.
    function get_content_fetch($path) {
        $e = $this->evaluate($path);
        $content = $this->get_content($e[0]);
        $a = $this->get_attributes_patch($path, "urlencode");
        if ($a != "")
            $content = urldecode($content);
        return $content;
    }

    function get_attributes_patch($path, $attr) {
        $e = $this->evaluate($path);
        $a = $this->get_attributes($e[0]);
        return $a[$attr];
    }

}

?>