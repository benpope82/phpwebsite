<?php

namespace phpws2\Variable;

require_once PHPWS_SOURCE_DIR . 'src-phpws2/config/String.php';

/**
 * Variable object for strings
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @package phpws2
 * @subpackage Variable
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class StringVar extends \phpws2\Variable
{
    /**
     * Contains the form types that can be created from this object
     * @var array
     */
    protected $allowed_inputs = array('text', 'textarea', 'checkbox', 'date', 'datetime', 'email',
        'file', 'month', 'number', 'password', 'search', 'select', 'tel', 'textfield',
        'time', 'url');

    /**
     * @todo assuming this determines whether to slashquote the form value
     * @var boolean
     */
    private $slashquote = false;

    /**
     * A regular expression matched against in the verifyValue function. Must be
     * bookended by delimiters (//, @@)
     *
     * @var string
     * @see String::verifyValue()
     */
    protected $regexp_match;

    /**
     * An array of tags allowed when the value is requested.
     * @var array
     */
    protected $allowed_tags = null;

    /**
     * Number of characters limited to this variable. A zero value is ignored
     * (i.e. "unlimited" characters)
     * @var integer
     */
    protected $limit = 0;
    protected $allow_empty = true;

    public function __construct($value = null, $varname = null, $limit=null)
    {
        parent::__construct($value, $varname);
        if ($limit !== null) {
            $this->setLimit($limit);
        }
    }
    
    
    /**
     * Checks the string to see it is a string, is under limit, and is formatted
     * correctly (dependent on the regexp_match).
     * @param string $value
     * @return boolean|\Error
     */
    protected function verifyValue($value)
    {
        if (!$this->allow_empty && strlen($value) == 0) {
            throw new \Exception(sprintf('Value "%1$s" may not be an empty string', $this->varname));
        }

        if (!is_string($value)) {
            throw new \Exception(sprintf('Value "%1$s" is a %2$s, not a string', $this->varname, gettype($value)));
        }

        if ($this->limit && strlen($value) > $this->limit) {
            throw new \Exception(sprintf('%s is over the %s character limit', $this->getLabel(), $this->getLimit()));
        }

        if (strlen($value) && isset($this->regexp_match) && !preg_match($this->regexp_match, $value)) {
            throw new \Exception(sprintf('String variable "%s" is not formatted correctly', $this->getVarName()));
        }

        return true;
    }

    /**
     * Sets a character limit for the string.
     * @param integer $limit
     */
    public function setLimit($limit)
    {
        $this->limit = (int) $limit;
    }

    /**
     * Sets a regular expression tested against the current value of the object
     * or when the value is set. See verifyValue.
     * @param string $match
     */
    public function setRegexpMatch($match)
    {
        $test = '';
        if (!empty($this->value)) {
            $test = $this->value;
        }

        if (preg_match($match, $test) === false) {
            throw new \Exception(sprintf('Regular expression error: %s', preg_error_msg(preg_last_error())));
        }
        $this->regexp_match = $match;
    }

    /**
     * @return string Current regexp string
     */
    public function getRegexpMatch()
    {
        return $this->regexp_match;
    }

    /**
     * @return integer Number of characters allowed, 0 == no limit
     */
    public function getLimit()
    {
        return $this->limit;
    }

    /**
     * @todo Write
     * @return string Returns the value ready for Javascript insertion
     */
    public function getJavascript()
    {

    }

    /**
     * Returns value stripped of all tags; unlike __toString which uses the
     * allowed_tags property.
     * @return string
     */
    public function getStripped()
    {
        return \strip_tags($this->value);
    }

    /**
     * Receives an array or individual parameters and adds them to the allowed
     * tags stack.
     */
    public function addAllowedTags()
    {
        $args = func_get_args();
        if (is_array($args[0])) {
            $args = $args[0];
        }
        if (empty($args)) {
            $this->allowed_tags = true;
        } else {
            $this->allowed_tags = '<' . implode('><', $args) . '>';
        }
    }

    /**
     * Return the current value. Strips tags if set.
     * @return string
     */
    public function __toString()
    {
        if (is_null($this->value)) {
            return '';
        }

        if (!$this->allowed_tags) {
            return $this->value;
        } else {
            if (is_string($this->allowed_tags)) {
                $tags = & $this->allowed_tags;
            } else {
                $tags = null;
            }
            return strip_tags($this->value, $tags);
        }
    }

    /**
     * A quick short cut to only allow word characters (A - Z, 0-9, underline)
     * as the value.
     */
    public function wordCharactersOnly()
    {
        $this->regexp_match = '/^[a-zA-Z]+[\w]+$/';
    }

    /**
     * Sets the value as a random string of characters.
     * The length is based on the characters parameter.
     * @param string $characters
     */
    public function randomString($characters = 10)
    {
        if ($characters <= 0) {
            throw new \Exception('Too few characters requested');
        }

        $word = null;
        for ($i = 0; $i < $characters; $i++) {
            switch (rand(0, 2)) {
                case 0:
                    $char = chr(rand(65, 90));
                    break;
                case 1:
                    $char = chr(rand(97, 122));
                    break;

                case 2:
                    $char = (string) rand(0, 9);
                    break;
            }
            $word .= $char;
        }
        $this->value = & $word;
    }

    /**
     * Returns true if value may be an empty string, false otherwise. Also accepts
     * a parameter to set whether variable may be empty or not.
     * @param boolean $allow
     * @return boolean
     */
    public function allowEmpty($allow = null)
    {
        if (!isset($allow)) {
            return $this->allow_empty;
        } else {
            return $this->allow_empty = (bool) $allow;
        }
    }

    /**
     * Changes the column name based on the size of the string.
     * NOTE: If the text is extremely long, "Text" may not be enough
     * for MySQL.
     *
     * @param \phpws2\Database\Table $table
     * @return \phpws2\Database\Datatype
     */
    public function loadDataType(\phpws2\Database\Table $table)
    {
        if (empty($this->column_type)) {
            if ($this->limit <= DB_VARCHAR_LIMIT && $this->limit > 0) {
                $this->column_type = 'Varchar';
            } else {
                $this->column_type = 'Text';
            }
        }
        $dt = parent::loadDataType($table);
        if (strtolower($this->column_type) == 'varchar') {
            $dt->setSize($this->limit);
        }
        return $dt;
    }

    public function urlEncode()
    {
        return urlencode($this->__toString());
    }

    public function noLimit()
    {
        $this->limit = 0;
    }

}
