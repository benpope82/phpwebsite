<?php

namespace phpws2\Variable;

/**
 * Text without html tags
 * @author Matthew McNaney <mcnaney at gmail dot com>
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */
class TextOnly extends \phpws2\Variable\StringVar
{
    /**
     * Text without html tags
     * @param type $value
     * @param type $varname
     */
    public function __construct($value = null, $varname = null)
    {
        // No tags allowed
        $this->addAllowedTags(null);
        parent::__construct($value, $varname);
    }

    public function set($value)
    {
        if (preg_match('/<\/?[^>]+>/i', $value)) {
            throw new \Exception('Tags are not permitted in TextOnly');
        }
        parent::set($value);
    }

}
