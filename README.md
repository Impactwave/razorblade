# Razorblade
An extension to Laravel's Blade templating engine.

## Instalation

#### Requirements

- PHP version >= 5.4

#### How to install

##### Install via Composer:

```
composer require impactwave/razorblade
```

##### Register the service provider
Open the `config/app.php` file included with Laravel, you will see a providers array.

Add the following string to that array:

```
'Impactwave\Razorblade\RazorbladeServiceProvider'
```

## Blade Extensions

Besides providing some **new Blade directives** (see below), *Razorblade* also adds **macros** to Blade.

##### What are Macros?
Creating a Blade syntax extension is not too difficult, but it's not very easy either.
You'll have to deal with regular expressions and low-level implementation details.
In practice, few people have the skills and patience to do it, so extensions are seldom used.

What if you could easily create your own syntactic extensions to Blade and make your templates more readable and easier
to write?

*Razorblade* provides you with a simple and easy way to do it, called **macros**.
  
You just create a simple static method on a class of your choosing, with a specific call signature,
and you can immediately call it from Blade.  
Implementing the method itself to perform some useful work is also quite simple.

#### Simple Macros

The simplest kind of macro allows you to invoke a method and replace the macro call with the output from it.  
The method can, optionally, receive one or more arguments from the template.

##### Syntax

    @@[class::]method [(args)]
    
> **Note:** `[]` brackets are not part of the syntax, they denotes optional elements.
  
##### Generated code

    {{ class::method (args) }}

* Parenthesis are optional; ex: `@@a::b` instead of `@@a::b()`.  
* `class` is a fully qualified class name; ex: `my\namespace\myClass` or just `myClass`.  
* If class is not specified, `Impactwave\Razorblade\Form` is assumed.

##### Macro implementation

The method implementing the macro should have the following signature:

    static public macroName ($arg1, ...) {}

* `$arg1, ...` denotes a list of optional arguments. It may be completely ommited.
* The method has no return value. The output it generates should be sent to the normal PHP output buffer,
  either using `echo` or a markup block (`?> ...markup... <?php`).

#### Block Macros

A macro, besides (optionally) having regular function-like
arguments, can also process a full block of markup, like some of the predefined Blade constructs do (like, for instance,
the `@if (...args) ...markup... @endif` construct does).

> Razorblade provides you with some predefined macros on the `Impactwave\Razorblade\Form` class.
> See the documentation for them further below.

##### Syntax

    @@[class::]method [(args)]:
      html markup
    @@end[class::]method

> **Note:** `[]` brackets are not part of the syntax, they denotes optional elements.

* `args` it's a list of arguments and it's optional.  
* Parenthesis are optional; ex: `@@a::b` instead of `@@a::b()`.  
* `class` is a fully qualified class name; ex: `my\namespace\myClass` or just `myClass`.  
* If `class` is not specified, `Impactwave\Razorblade\Form` is assumed.

##### Generated code

    {{ class::method (indentSpace,html,...args) }}
     
##### Macro implementation

The method implementing the macro should have the following signature:

    static public macroName ($indentSpace, $html, $arg1, ...) {}

* `$arg1, ...` denotes a list of optional arguments. It may be completely ommited.
* `$indentSpace` is a string comprised of white space, corresponding to the indentation level of the source markup block.
* `$html` is the markup block defined between the opening and the closing macro tag (`@@tag ... @@endtag`).
* The method has no return value. The output it generates should be sent to the normal PHP output buffer,
  either using `echo` or a markup block (`?> ...markup... <?php`).

#### Boolean attribute generation

A shorter and more readable syntax for generating boolean html attributes.

It outputs a valueless attribute if arg is true, otherwise it suppresses the attribute completely, including any
preceding white space.


###### Syntax

    attribute="@boolAttr (expression)"

> **Ex:** `<option selected>` vs `<option>`.

##### Generated code

    {{ Form::boolAttr (precedentSpace,attrName,expression) }}

* Parenthesis are optional; ex: `@attr a::b` instead of `@attr a::b()` 

#### Include static files

Unlinke Blade's `@include`, which includes templates from the `view` directory, this directive includes a static file 
from a location inside the public web directory.  
This is similar to an Apache server-side include. It can be useful for a designer while developing a user interface mockup.

###### Syntax

    @includeStatic ('relative/path/to/file.html')

## Predefined Macros

> TODO: this is being written

## License

The MIT license. See the accompanying `LICENSE` file.

--------------------------------------------------------------------------------

Copyright © 2016 Cláudio Silva and Impactwave, Lda.
