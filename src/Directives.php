<?php
namespace Impactwave\Razorblade;

use Illuminate\Support\Str;
use Input;
use Lang;
use Session;
use View;

/**
 * Razorblade's set of predefined custom directives.
 */
class Directives
{
  private static $fieldDefaulOptions = [
    'id'           => '',
    'related'      => '',
    'relatedLabel' => '',
    'noLabel'      => false,
    'outerClass'   => 'form-group',
    'innerClass'   => 'col-sm-9',
    'lblClass'     => 'col-sm-2 control-label',
    'outerId'      => '',
    'innerId'      => '',
    'model'        => '',
  ];

  /**
   * @param string $id
   * @param string $path
   * @param string $type
   */
  static function clientSideTemplate ($id, $path, $type = 'text/template')
  {
    echo "<script type='$type' id='$id'>";
    include app_path () . "/views/$path";
    echo '</script>';
  }

  /**
   * Bootstrap-compatible form field macro.
   *
   * ##### Syntax example for PHP call:
   *
   *     Macro::field ('', '<input type="text">', 'name', 'Your name', ['id'=>'idField'])
   *
   * ##### Syntax of Blade macro:
   *
   * <div style="white-space:pre">
   * &#x40;field (name, label, options)<br>
   * &nbsp;&nbsp;&lt;input type="text"><br>
   * &#x40;endfield
   * </div>
   *
   * <p>If label argument is ommited, no label will be generated. If it's empty, an empty label will be output.
   * For array fields (ex: select multiple), append [] to the field name.
   * The options array is merged with the default options ({@see fieldDefaulOptions}), and it may specify:
   *  - id: if this is ommited, an id="input-fieldName" attribute is generated. If it's empty, no id attribute will be
   *  output.
   *  - related: the field name of a related field (ex. password confirmation field) for field name translation.
   *  - relatedLabel: the related field's human-friendly name.
   *  - noLabel: true to hide the label, allowing you to still set its name.
   *  - outerClass: classe(s) a aplicar ao div mais exterior. Por omissão: 'form-group'.
   *  - innerClass: classe(s) a aplicar ao div mais interior, que envolve o(s) input(s). Por omissão: 'controls'.
   *  - lblClass: classe(s) a aplicar na label do(s) input(s). Por omissão: 'control-label'.
   *  - outerId: id a aplicar ao div exterior.
   *  - innerId: id a aplicar ao div interior.
   *  - model: field name path prefix, for retrieving values from nested arrays (ex: 'authe.user.name')
   *
   * @param string      $space
   * @param string      $html
   * @param string      $name
   * @param string|null $label   [optional]
   * @param array       $options [optional]
   * @return string
   */
  static function field ($space, $html, $name, $label = null, $options = [])
  {
    $options = array_merge (self::$fieldDefaulOptions, $options);
    // Remove [] suffix, if present.
    $field = substr ($name, -1) == ']' ? substr ($name, 0, strlen ($name) - 2) : $name;
    $model = $options ['model'];
    if ($model) $field = "$model.$field";
    $id       = $options['id'] ?: "input-$field";
    $forId    = $id ? " for=\"$id\"" : '';
    $idAttr   = $id ? " id=\"$id\"" : '';
    $errors   = View::shared ('errors');
    $outClass = $options['outerClass'] . ($errors->has ($field) ? ' has-error' : ' ');
    $message  = self::validationMessageFor ($field, $label, $options['related'], $options['relatedLabel']);
    $lblClass = $options['lblClass'];
    $label    =
      isset($label) && !$options['noLabel'] ? "<label$forId class=\"$lblClass\">$label</label>" : '';
    $old      = Input::old ($field);
    $value    = is_array ($old) || is_null ($old) ? '' : $old;
    $html     = Str::contains ($html, '<textarea')
      ?
      preg_replace ('/<(textarea)( .*)?>.*?<\/textarea>/s', "<$1 name=\"$name\"$idAttr class=\"form-control\"$2>$value</$1>", $html)
      :
      preg_replace ('/<(input|select)( .*)?>/s', "<$1 name=\"$name\"$idAttr class=\"form-control\"value=\"$value\"$2>",
        $html);
    $innClass = $options['innerClass'];
    if ($message)
      $message = "$space  $message\n$space";
    $outId = $options['outerId'] ? ' id="' . $options['outerId'] . '"' : '';
    $innId = $options['innerId'] ? ' id="' . $options['innerId'] . '"' : '';
    return <<<HTML
$space<div class="$outClass"$outId>
$space  $label
$space  <div class="$innClass"$innId>
$space    $html$message$space</div>
$space</div>
HTML;
  }

  static function fieldDefaultOptions ($options = [])
  {
    self::$fieldDefaulOptions = array_merge (self::$fieldDefaulOptions, $options);
  }

  /**
   * Displays a flash message that was set on the previous request.
   */
  static function flashMessage ()
  {
    if (Session::has ('message')) {
      list ($flashType, $message, $title) = explode ('|', Session::get ('message')) + [''] + [''];
      $title = $title ? "<h4>$title</h4>" : '';
      return <<<HTML
<div class="alert alert-$flashType">
  $title$message
</div>
</script>
HTML;
    }
  }

  /**
   * Generated the form field's group CSS class.
   *
   * <p>It outputs `form-group`, with an additional `has-error` CSS class if the field failed validation.
   *
   * @param string $field Field name.
   * @return string
   */
  static function groupClass ($field)
  {
    $errors = View::shared ('errors');
    return 'form-group' . ($errors->has ($field) ? ' has-error' : ' ');
  }

  /**
   * Includes public files.
   *
   * <p>Unlinke Blade's &#x40;`include`, which includes templates from the `view` directory, this directive includes a
   * file from a location inside the public web directory.
   * <p>This is similar to an Apache server-side include. It can be useful for a designer while developing a user
   * interface mockup.
   * <p>The file can be a static HTML page or it can be a dynamic PHP file, but not a template.
   *
   * @param string $url
   */
  static function includePublic ($url)
  {
    include public_path ($url);
  }

  /**
   * @param string $path
   */
  static function includeStatic ($path)
  {
    include app_path () . "/views/$path";
  }

  /**
   * Allows the inclusion of a block of PHP code inside a blade template without using `<?php ?>` tags.
   * It is required if you want to embed PHP code on Blade templates that use Razorblade custom directives.
   *
   * @param string $space
   * @param string $html
   * @return string
   */
  static function php_compiler ($space, $html)
  {
    return "<?php\n$space$html?>";
  }

  /**
   * Displays a popup flash message using the Toastr javascript plugin.
   *
   * @see flashMessage()
   * @param int $timeout
   * @return string
   */
  static function toastrMessage ($timeout = 5000)
  {
    if (Session::has ('message')) {
      list ($flashType, $msg, $title) = explode ('|', Session::get ('message')) + [''] + [''];
      $msg   = str_replace ("'", "\\'", $msg);
      $title = str_replace ("'", "\\'", $title);
      return <<<HTML
<script>
  setTimeout(function () {
    toastr.options = {
      timeOut:       $timeout,
      closeButton:   false,
      positionClass: 'toast-top-full-width'
    };
    toastr.$flashType('$msg','$title');
  },0);
</script>
HTML;
    }
    return '';
  }

  /**
   * Outputs a hidden form field with a CSRF token for use by Laravel's CSRF middleware.
   */
  static function token_compiler ()
  {
    return '<input type="hidden" name="_token" value="<?=csrf_token()?>">';
  }

  /**
   * Generates a Bootstrap-compatible alert box that displays an alert title and text, followed by all error messages
   * that resulted from the last form validation.
   *
   * @param string $type [optional] The error type: `error|info|success|warning`. Defaults to `warning`.
   */
  static function validationErrors ($type = 'warning')
  {
    $errors = View::shared ('errors');
    if (count ($errors)): ?>
      <div class="alert alert-<?= $type ?> alert-validation">
        <h4><?= Lang::get ('auth.PROBLEMS 1') ?></h4><?= Lang::get ('auth.PROBLEMS') ?><br><br>
        <ul>
          <?php foreach ($errors->all () as $error)
            echo "<li>$error</li>" ?>
        </ul>
      </div>
      <?php
    endif;
  }

  /**
   * Generates a Bootstrap-compatible error message for a form field that submitted an invalid value.
   *
   * @param string $field        The field name.
   * @param string $label        [optional] If given, the field name on the error message will be replaced by the given
   *                             string (the field label).
   * @param string $related      [optional] The field name of a related field (ex. password confirmation field) for
   *                             field name translation.
   * @param string $relatedLabel [optional] The related field's human-friendly name.
   * @return string
   */

  private static function validationMessageFor ($field, $label = null, $related = null, $relatedLabel = null)
  {
    $errors  = View::shared ('errors');
    $message = $errors->first ($field, '<span class="help-block">:message</span>');
    if (!$message) return '';
    $fieldEsc = str_replace ('_', ' ',
      preg_quote ($field)); // Fix Laravel bug that replaces underscores by spaces on the field name on the message.
    $message  = $label ? preg_replace ("/\\b$fieldEsc\\b/", $label, $message) : $message;
    if (isset($related)) {
      $fieldEsc = preg_quote ($related);
      $message  = preg_replace ("/\\b$fieldEsc\\b/", $relatedLabel, $message);
    }
    return $message;
  }

}
