# Laravel Html Generator

[![Latest Stable Version](https://poser.pugx.org/hnhdigital-os/laravel-html-generator/v/stable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-html-generator) [![Total Downloads](https://poser.pugx.org/hnhdigital-os/laravel-html-generator/downloads.svg)](https://packagist.org/packages/hnhdigital-os/laravel-html-generator) [![Latest Unstable Version](https://poser.pugx.org/hnhdigital-os/laravel-html-generator/v/unstable.svg)](https://packagist.org/packages/hnhdigital-os/laravel-html-generator) [![License](https://poser.pugx.org/hnhdigital-os/laravel-html-generator/license.svg)](https://packagist.org/packages/hnhdigital-os/laravel-html-generator)

Create HTML tags and render them efficiently.

Extends upon [Airmanbzh/php-html-generator](https://github.com/Airmanbzh/php-html-generator) with some Laravel related integration.

## Overview

    return HtmlTag::createElement();
    // returns an empty HtmlTag Container

    return HtmlTag::createElement('a');
    // returns an HtmlTag containing a 'a' tag

### Why you should use it

 - it always generates valid HTML and XHTML code
 - it makes templates cleaner
 - it's easy to use and fast to execute

## Render tags

    echo(HtmlTag::createElement('a'));

### Simple tags

    echo $html->tag('div')
    // <div></div>

    echo(HtmlTag::createElement('p')->text('some content'));
    // <p>some content</p>

### Structured tags

    echo(HtmlTag::createElement('div')->addElement('a')->text('a text'));
    // <div><a>a text</a></div>

    $container = HtmlTag::createElement('div');
    $container->addElement('p')->text('a text');
    $container->addElement('a')->text('a link');
    // <div><p>a text</p><a>a link</a></div>
    
### Attributes

#### Classics attributes (method : 'set')

    $tag = $html->tag('a')
        ->set('href','./sample.php')
        ->set('id','myID')
        ->text('my link');
    echo( $tag );
    // <a href='./sample.php' id='myID'>my link</a>
    
#### ID (method : 'id')

    $tag = $html->tag('div')
        ->id('myID');
    echo( $tag );
    // <div id='myID'>my link</a>

#### Class management (method : 'addClass'/'removeClass')

    $tag = $html->tag('div')
        ->addClass('firstClass')
        ->addClass('secondClass')
        ->text('my content')
        ->removeClass('firstClass');
    echo( $tag );
    // <div class="secondClass">my content</div>

    
## Contributing

Please see [CONTRIBUTING](https://github.com/hnhdigital-os/laravel-html-generator/blob/master/CONTRIBUTING.md) for details.

## Credits

* [Rocco Howard](https://github.com/therocis)
* [Lucas Mezêncio](https://github.com/lucasmezencio)
* [All Contributors](https://github.com/hnhdigital-os/laravel-html-generator/contributors)

## License

The MIT License (MIT). Please see [License File](https://github.com/hnhdigital-os/laravel-html-generator/blob/master/LICENSE) for more information.
