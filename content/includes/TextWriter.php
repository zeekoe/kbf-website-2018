<?php

class TextWriter
{
    private $path;

    function __construct($path)
    {
        $this->path = $path;
    }

    public function fillRow($fields, $name)
    {
        $filename = preg_replace("/[^A-Za-z\s]+/", "", $name) . '_' . date('Y-M-d-H-i-s') . '.txt';
        $ret = '';
        foreach ($fields as $title => $value) {

            $ret .= $title . ': ' . $value. "\r\n";
        }
        if (file_put_contents($this->path . $filename, $ret) === false) {
            throw new Exception("Cannot create file");
        }
    }

}