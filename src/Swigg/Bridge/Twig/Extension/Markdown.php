<?php

namespace Swigg\Bridge\Twig\Extension;

class Markdown extends \Twig_Extension
{
    const NAME = 'markdown';

    /**
     * @var \Zend\Filter\AbstractFilter
     */
    protected $filter;

    /**
     * @param \Zend\Filter\AbstractFilter $filter
     */
    public function __construct(\Zend\Filter\AbstractFilter $filter)
    {
        $this->setFilter($filter);
    }

    /**
     * @param string $input
     * @return string
     */
    public function markdown($input)
    {
        return $this->getFilter()->filter($input);
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            'markdown' => new \Twig_Filter_Method($this, 'markdown',  array('is_safe' => array('all'))),
        );
    }

    /**
     * {@inheritdoc}
     */
    function getName()
    {
        return self::NAME;
    }

    /**
     * @param \Zend\Filter\AbstractFilter $filter
     */
    public function setFilter(\Zend\Filter\AbstractFilter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return \Zend\Filter\AbstractFilter
     */
    public function getFilter()
    {
        return $this->filter;
    }
}
