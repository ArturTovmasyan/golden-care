<?php

namespace  App\Model\Report;

class Base
{
    const ORIENTATION_PORTRAIT  = 'Portrait';
    const ORIENTATION_LANDSCAPE = 'Landscape';

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    protected $options = [];

    /**
     * Base constructor.
     */
    public function __construct()
    {
        $this->options = [
            'orientation'      => self::ORIENTATION_PORTRAIT,
            'page-size'        => 'Letter',
            'header-left'      => '',
            'header-right'     => '',
            'footer-left'      => 'SeniorCare Reports',
            'footer-right'     => 'Report page [page] of [topage] - ' . date('\ m/d/Y '),
            'footer-center'    => '',
            'footer-font-size' => 9,
            'footer-spacing'   => 0,
            'lowquality'       => false,
            'encoding'         => '',
            'print-media-type' => false,
        ];
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    protected function addOption($key, $value)
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}

