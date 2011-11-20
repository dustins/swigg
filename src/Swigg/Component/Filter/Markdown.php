<?php

namespace Swigg\Component\Filter;

class Markdown extends \Zend\Filter\AbstractFilter
{
    /**
     * @var \MarkdownExtra_Parser
     */
    protected static $parser;

    /**
     * Returns the result of filtering $value
     *
     * @param  mixed $value
     * @throws Zend\Filter\Exception\RuntimeException If filtering $value is impossible
     * @return mixed
     */
    public function filter($value)
    {
        return $this->parser()->transform($value);
    }

    /**
     * @static
     * @return \MarkdownExtra_Parser
     */
    public static function parser()
    {
        if (is_null(static::$parser)) {
            static::$parser = new \MarkdownExtra_Parser();
        }

        return self::$parser;
    }

}
