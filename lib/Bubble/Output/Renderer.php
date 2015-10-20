<?php

abstract class Bubble_Output_Renderer
{
    static public function factory($name)
    {
        switch ($name) {
            case 'html':
            case 'apache':
            case 'apache2handler':
            case 'fpm-fcgi':
            case 'cgi-fcgi':
            case 'cgi':
                $renderer = new Bubble_Output_Renderer_Html();
                break;
            case 'shell':
            case 'cli':
                $renderer = new Bubble_Output_Renderer_Cli();
                break;
            default:
                throw new Exception('Could not find renderer with name "'. $name .'"');
        }

        return $renderer;
    }
}