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

```php
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

> Razorblade provides you with some predefined macros on the `Impactwave\Razorblade\Macros` class.
> See the documentation for them further below.

### Simple Macros

The simplest kind of macro allows you to invoke a method and replace the macro call with the output from it.  
The method can, optionally, receive one or more arguments from the template.

##### Syntax

    @@[class::]method [(args)]
    
> **Note:** `[]` brackets are not part of the syntax, they denote optional elements.
  
##### Generated code

    {{ class::method (args) }}

* Parenthesis are optional; ex: `@@a::b` instead of `@@a::b()`.  
* `class` is a fully qualified class name; ex: `my\namespace\myClass` or just `myClass`.  
* If class is not specified, `Impactwave\Razorblade\Macros` is assumed.

##### Macro implementation

The method implementing the macro should have the following signature:

```php
static public function macroName ($arg1, ...) {}
```

* `$arg1, ...` denotes a list of optional arguments. It may be completely ommited.
* The method has no return value. The output it generates should be sent to the normal PHP output buffer,
  either using `echo` or a markup block (`?> ...markup... <?php`).

### Block Macros

A macro, besides (optionally) having regular function-like
arguments, can also process a full block of markup, like some of the predefined Blade constructs do (like, for instance,
the `@if (...args) ...markup... @endif` construct does).

##### Syntax

    @@[class::]method [(args)]:
      html markup
    @@end[class::]method

> **Note:** `[]` brackets are not part of the syntax, they denote optional elements.

* `args` it's a list of arguments and it's optional.  
* Parenthesis are optional; ex: `@@a::b` instead of `@@a::b()`.  
* `class` is a fully qualified class name; ex: `my\namespace\myClass` or just `myClass`.  
* If `class` is not specified, `Impactwave\Razorblade\Macros` is assumed.

##### Generated code

    {{ class::method (indentSpace,html,...args) }}
     
##### Macro implementation

The method implementing the macro should have the following signature:

```php
static public function macroName ($indentSpace, $html, $arg1, ...) {}
```

* `$arg1, ...` denotes a list of optional arguments. It may be completely ommited.
* `$indentSpace` is a string comprised of white space, corresponding to the indentation level of the source markup block.
* `$html` is the markup block defined between the opening and the closing macro tag (`@@tag ... @@endtag`).
* The method has no return value. The output it generates should be sent to the normal PHP output buffer,
  either using `echo` or a markup block (`?> ...markup... <?php`).

### Boolean attribute generation

A shorter and more readable syntax for generating boolean html attributes.

It outputs a valueless attribute if the argument is true, otherwise it suppresses the attribute completely, including any preceding white space.

##### Syntax

    attribute="@boolAttr (expression)"

##### Output

> **Ex:** `<option selected>` se `true`, ou `<option>` se `false`.

##### Generated code

    {{ Macro::boolAttr (precedentSpace,attrName,expression) }}

* Parenthesis are optional; ex: `@attr a::b` instead of `@attr a::b()`
* `Macro` is `Impactwave\Razorblade\Macro`

## Predefined Macros

### @@field

#### Generates a Bootstrap-compatible form field with automatic data-binding and error display

The generated field has several features that significantly simplify your markup, making it shorter and more readable, and saving you from typing repetitive boilerplace markup and Blade directives.

The form field will:

* automatically display the current value from the view's model.
* automatically have an associated label, if one is specified on the macro call.  
  The label is bound to the field, so that it is accessibility-enabled and when the uses clicks the label, the field becames focused.

In case of a form validation error upon submission, the macro generates a form field that:

* displays the value that was submitted;
* replaces mentions to the field name by the corresponding field label;
* replaces mentions to a related field name by the corresponding field label (ex. for a 'Confirm password' field);
* if the field's value is invalid:
  * sets the field CSS class to an error class, so that the field is marked as invalid;
  * displays a validation error message;

You can also specify which IDs and CSS classes to apply to several elements on the generated markup.

The macro should wrap an arbitrary form field, which you should specify in plain HTML.

> **Do not** specify the `name` or `value` attributes on the form field's tag. They will be filled in by the macro based on its arguments.

##### Blade syntax

```html
@@field (name, label, options)
  <input type="any-type-you-want" any-attr-you-want> or
  <textarea></textarea> or
  <select></select>
@@endfield
```

##### PHP call syntax

```php
Macro::field ('', '<input type="text">', 'name', 'Your name', ['id'=>'idField'])
```

* `name`: the field name. For array fields (ex: select multiple), append [] to the field name.
* `label`: if ommited, no label will be generated. If it's empty, an empty label will be output.
* `options`: an array that may specify:

  array key     | meaning
  --------------|---------
  `id`          | iI this is ommited, an id="input-fieldName" attribute is generated. If it's empty, no id attribute will be output.
  `related`     | The field name of a related field (ex. password confirmation field) for field name translation.
  `relatedLabel`| The related field's human-friendly name.
  `noLabel`     | `true` to hide the label, allowing you to still set its name.
  `outerClass`  | CSS class to apply to the outer `div`. Default: `'form-group'`.
  `innerClass`  | CSS class to apply to the inner `div`, which wraps the input(s). Degault: `'controls'`.
  `lblClass`    | CSS class to apply to the input's label. Default: `'control-label'`.
  `outerId`     | Id to apply to the outer `div`.
  `innerId`     | Id to apply to the inner `div`.

---

### @@token

#### Generates a CSRF token

Outputs a hidden form field with a CSRF token for use by Laravel's CSRF middleware.

##### Syntax

    @@token

---

### @@includePublic

#### Includes public files

Unlinke Blade's `@include`, which includes templates from the `view` directory, this directive includes a file from a location inside the public web directory.  
This is similar to an Apache server-side include. It can be useful for a designer while developing a user interface mockup.
The file can be a static HTML page or it can be a dynamic PHP file, but not a template.

##### Syntax

    @@includePublic ('relative/path/to/file.html')

---

### @@includeStatic

#### Includes a static (or client-side) template

Inserts a template read from a location inside the `views` directory, directly into the current Blade template, without any further processing (i.e. the template is inserted exactly as it was read, without any dynamic execution).

##### Syntax

    @@includeStatic (path)
    
* `path`: the template's file path, relative to the `views` directory.

---

### @@clientSideTemplate

#### Embeds a client-side template

This macro is quite useful for embedding client-side templates on the generated page.
You can do it like this:

##### Syntax

    @@clientSideTemplate (id, path, type = 'text/template')
    
* `id`: the javascript template ID.
* `path `: the template's file path, relative to the `views` directory.
* `type` [optional]: the type attribute for the generated `script` tag. Defaults to `'text/template'`.

##### Generated code

```html
<script id="myTemplate" type="text/template">
  ...template markup...
</script>
```
In this example, client-side templates would be placed inside the `views/client-side` directory, right next to the other server-side templates.

The advantage of this kind of template embedding is that templates will be already available when the page loads, and further XHR requests will not be required to load the client-side templates.

---

### @@validationErrors

#### Displays form validation errors

Generates a Bootstrap-compatible alert box that displays an alert title and text, followed by all error messages that resulted from the last form validation.

##### Syntax

    @@validationErrors (type = 'info')

* `type`: The error type: `error|info|success|warning`. Defaults to `info`.

##### Requirements

You must define the following localization keys:

Key              | Meaning
-----------------|------------------------
`auth.PROBLEMS 1`| The alert box title.
`auth.PROBLEMS`  | An explanation message.

---

### @@flashMessage

#### Displays a flash message

Displays a flash message that was set on the previous request.

##### Syntax

    @@flashMessage

A flash message is stored on the `Session`'s `message` key with the format: `'type|message|title'`.

* `type`: The error type: `error|info|success|warning`. Defaults to `warning`.
* `message`: the message.
* `type`: an optional title.

---

### @@toastrMessage

#### Displays a Toastr message

Displays a popup flash message using the Toastr javascript plugin.

##### Syntax

    @@toastrMessage

See `@@flashMessage` for more information.

## The `Form` utility class

The `Impactwave\Razorblade\Form` class provides several static utility methods that are best used in conjunction with the predefined Razorblade macros.

### Form::flash

Allows sending flash messages to be viewed on the next request.
It has support for 4 types of messages and allows setting an optional title.

> **Hint:** use the `@@flashMessage` or `@@toastrMessage` macros for displaying the message.

##### Syntax

```php
flash ($message, $title = '', $type = Form::ALERT_INFO)
```

* `string $message`: The message to be displayed.
* `string $title`: An optional title for the alert box.
* `string $type`: The alert type: `error|info|success|warning`. You can also use one of the `Form::ALERT_xxx` constants.

### Form::fieldIs

Checks if the field's currently submitted value matches the given value.

If the field's value is an array (being the field name suffixed by `[]`), this will match the reference value against all array values (for instance, in the case of multiple radio buttons for the same field, or for a multi-select dropdown).

##### Syntax

```php
Form::fieldIs ($field, $value)
```

* `string $field`: Field name.
* `string $value`: Field value to match.

### Form::fieldWas

Checks if the field's previously submitted value matches the given value.

If the field's value was an array (being the field name suffixed by `[]`), this will match the reference value against all array values (for instance, in the case of multiple radio buttons for the same field, or for a multi-select dropdown).

##### Syntax

```php
Form::fieldWas ($field, $value)
```

* `string $field`: Field name.
* `string $value`: Field value to match.

### Form::setModel

Sets the data to be initially displayed on a form, before the form is submitted for the first time.

Use this in conjunction with the `@@field` macros and `Form::validate()`.

##### Syntax

```php
Form::setModel ($model)
```

* `array $model`: The form data, as a map of field names to field values.

### Form::validate

Shortcut method for form data validation.

If validates the form input and, if it fails:

- flashes an error message;
- generates a redirection to the same URL;
- saves on the session the validation error messages and the submitted form values, so that they can be
  redisplayed on the next request.

> **Hint:** use this in conjunction with `@@field` macros to easily create a form with validation.

##### Usage

Place the `validate` call on the controller method that handles the POST request.

```php
$err = Util::validate([
  'password'  => 'required|min:8',
  'password2' => 'required|same:password',
  etc...
]);
if ($err) return $err;
// continue handling the form
```

* `array $rules`: A map of field names to validation rules.
* `array $messages`: [optional] Custom error messages. See the Laravel documentation for the `Validator` class.
* `array $customAttributes`: [optional] Custom attributes. See the Laravel documentation for the `Validator` class.
* Returns: `false|\Illuminate\Http\RedirectResponse` - `false` if the form validates successfully, otherwise, a redirection response.

##### Requirements

You must define the following localization keys:

Key                       | Meaning
--------------------------|------------------------
`app.formValidationFailed`| A generic message explaining that the form could not be submitted. Details can be found next to each invalid field.

---

## License

The MIT license. See the accompanying `LICENSE` file.

--------------------------------------------------------------------------------

Copyright © 2016 [Cláudio Silva](https://github.com/claudio-silva) and [Impactwave, Lda](https://github.com/impactwave)
