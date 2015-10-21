<?php

class Bubble_Output_Renderer_Cli extends Bubble_Output_Renderer_Abstract
{
    protected $_successColor = '1;32m';

    protected $_errorColor = '1;31m';

    public function bold($str)
    {
        return sprintf("\033[1m%s\033[0m", $str);
    }

    public function colorize($str, $color)
    {
        return "\033" . '[' . $color . $str . "\033" . '[0m';
    }

    public function wash($str)
    {
        $str = str_replace("\033[1m", '', $str);
        $str = str_replace("\033[" . $this->_successColor, '', $str);
        $str = str_replace("\033[" . $this->_errorColor, '', $str);
        $str = str_replace("\033[0m", '', $str);

        return $str;
    }
}