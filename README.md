Ganon
================
Fast (HTML DOM) parser written in PHP.

## Important note
This is a fork of the project [https://code.google.com/p/ganon/](https://code.google.com/p/ganon/)

# Installation

## Composer

    Via [composer](http://getcomposer.org/)
    (https://packagist.org/packages/wangoviridans/ganon)

        {
            "require": {
                "wangoviridans/ganon": "dev-master"
            }
        }

    Or just clone and put somewhere inside your project folder.
    $ cd myapp/vendor
    $ git clone git://github.com/wangoviridans/ganon.git

# Usage

## Including

    <?php
    require 'vendor/autoload.php';
    use Wangoviridans\Ganon;

    // Parse the google code website into a DOM
    $html = file_get_dom('http://code.google.com/');
After including Ganon and loading the DOM, it is time to get started.

## Access
Accessing elements is made easy through the CSS3-like selectors and the object model.

     // Find all the paragraph tags with a class attribute and print the
     // value of the class attribute
     foreach($html('p[class]') as $element) {
       echo $element->class, "<br>\n";
     }


     // Find the first div with ID "gc-header" and print the plain text of
     // the parent element (plain text means no HTML tags, just the text)
     echo $html('div#gc-header', 0)->parent->getPlainText();


     // Find out how many tags there are which are "ns:tag" or "div", but not
     // "a" and do not have a class attribute
     echo count($html('(ns|tag, div + !a)[!class]');
Learn [more](https://code.google.com/p/ganon/wiki/AccesElements) about accessing elements.

## Modification
Elements can be easily modified after you've found them.

     // Find all paragraph tags which are nested inside a div tag, change
     // their ID attribute and print the new HTML code
     foreach($html('div p') as $index => $element) {
       $element->id = "id$index";
     }
     echo $html;


     // Center all the links inside a document which start with "http://"
     // and print out the new HTML
     foreach($html('a[href ^= "http://"]') as $element) {
       $element->wrap('center');
     }
     echo $html;


     // Find all odd indexed "td" elements and change the HTML to make them links
     foreach($html('table td:odd') as $element) {
       $element->setInnerText('<a href="#">'.$element->getPlainText().'</a>');
     }
     echo $html;
Learn [more](https://code.google.com/p/ganon/wiki/ModifyElements) about modifying elements.

## Beautify

Ganon can also help you beautify your code and format it properly.

    // Beautify the old HTML code and print out the new, formatted code
    dom_format($html, array('attributes_case' => CASE_LOWER));
    echo $html;