<?php

class Bubble_Output_Renderer_Html extends Bubble_Output_Renderer_Abstract
{
    protected $_eol = '<br />';

    protected $_indentString = '&nbsp;';

    protected $_successColor = 'limegreen';

    protected $_errorColor = 'red';

    protected $_prefix = '<span>';

    protected $_suffix = '</span>';

    public function __construct()
    {
        echo <<< EOF
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    </head>
    <body>
    <style type="text/css">
        body {
            font: 12px/1.25em Monospace;
        }
    </style>
EOF;
    }

    public function __destruct()
    {
        echo <<< EOF
    <body>
</html>
EOF;

    }

    public function bold($str)
    {
        return sprintf('<strong>%s</strong>', $str);
    }

    public function colorize($str, $color)
    {
        return sprintf('<span style="color: %s;">%s</span>', $color, $str);
    }

    public function pad($str, $length, $type = STR_PAD_RIGHT)
    {
        $strlen = strlen($str);
        $length = max(0, $length - $strlen);

        if ($type == STR_PAD_RIGHT) {
            $str = $str . str_repeat('&nbsp;', $length);
        } else {
            $str = str_repeat('&nbsp;', $length) . $str;
        }

        return $str;
    }

    public function escape($str)
    {
        return htmlspecialchars($str);
    }

    public function wash($str)
    {
        $str = preg_replace('/<br\\s*?\/??>/i', "\n", $str);
        $str = str_replace('&nbsp;', ' ', $str);
        $str = strip_tags($str);

        return $str;
    }

    protected function _afterEcho()
    {
        @flush();
        @ob_flush();
    }
}