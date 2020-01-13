<?php

namespace App\Model\Report;

class Base
{
    public const ORIENTATION_PORTRAIT = 'Portrait';
    public const ORIENTATION_LANDSCAPE = 'Landscape';

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
            'orientation' => self::ORIENTATION_PORTRAIT,
            'page-size' => 'Letter',
            'margin-top' => '13mm',
            'margin-bottom' => '21mm',
            'margin-left' => '14mm',
            'margin-right' => '13mm',
            'header-left' => '',
            'header-right' => '',
            'footer-left' => 'SeniorCare Reports',
            'footer-right' => 'Report page [page] of [topage] - ' . date('\ m/d/Y '),
            'footer-center' => '',
            'footer-font-size' => 9,
            'footer-spacing' => 0,
            'lowquality' => false,
            'encoding' => '',
            'print-media-type' => false,
        ];
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    protected function addOption($key, $value): self
    {
        $this->options[$key] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function getOptions(): ?array
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

