<?php
namespace Impactwave\Razorblade;

use Blade;
use Illuminate\Support\ServiceProvider;

class RazorbladeServiceProvider extends ServiceProvider
{
  /**
   * Bootstrap any application services.
   */
  public function boot ()
  {
    /*
     * Boolean attribute generation
     *
     *   Syntax: attribute="@boolAttr (expression)"
     *
     * Outputs a valueless attribute if arg is true, otherwise it suppresses the attribute completely.
     * Ex: <option selected> vs <option>.
     *
     * Generated code:
     *
     *   {{ Macro::boolAttr (precedentSpace,attrName,expression) }}
     *
     * Parenthesis are optional; ex: @attr a::b instead of @attr a::b()
     * precedentSpace is the white space preceding the attribute. If the attribute is not output,
     * the space is suppressed.
     */
    Blade::extend (function ($view) {
      return preg_replace_callback ('/(?<!\w)(\s*)([\w\\\]+)\s*=\s*(["\'])\s*@boolAttr\s*(?:\((.*?)\))?\s*\3/s',
        function ($match) {
          list ($all, $space, $attr, $quote, $bool) = $match;
          return "<?php echo $bool ? '$space$attr' : '' ?>";
        }, $view);
    });


    /*
     * Short syntax for invoking a method and outputting its result.
     *
     *   Syntax: @@[class::]method [(args)]
     *
     * Generated code:
     *
     *   {{ class::method (args) }}
     *
     * Parenthesis are optional; ex: @@a::b instead of @@a::b()
     * 'class' is a fully qualified class name; ex: my\namespace\myClass or just myClass
     * If class is not specified, Form is assumed.
     */
    Blade::extend (function ($view) {
      return preg_replace_callback ('/(?<!\w)(\s*)@@((?:([\w\\\\]+)::)?(\w+))\s*(?:\((.*?)\))?\s*(:)?\s*$(.*?@@?end\2)?/ms',
        function ($match) {
          array_push ($match, 0); // allow $args. $colon and $close to be undefined.
          array_push ($match, 0);
          array_push ($match, 0);
          list ($all, $space, $fullName, $class, $method, $args, $colon, $close) = $match;
          if ($colon) return $all; // Skip macro blocks.
          if ($close)
            throw new \RuntimeException ("Missing colon after macro block start @@$fullName($args)");
          if ($class == '')
            $class = Macro::class;
          return "$space<?php echo $class::$method($args) ?>";
        }, $view);
    });


    /*
     * Blade macros.
     *
     * Syntax:
     *
     *   @@[class::]method [(args)]:
     *     html markup
     *   @@end[class::]method
     *
     * Generated code:
     *
     *   {{ class::method (indentSpace,html,args...) }}
     *
     * Args (and parenthesis) are optional.
     * 'class' is a fully qualified class name; ex: my\namespace\myClass or just myClass
     * If class is not specified, Form is assumed.
     * indentSpace is a white space string corresponding to the indentation level of this block.
     */
    Blade::extend (function ($view) {
      return preg_replace_callback ('/(?<!\w)(\s*)^([ \t]*)@@((?:([\w\\\]+)::)?(\w+))\s*(?:\((.*?)\))?\s*:\s*(.*?)(@@?)end\3/sm',
        function ($match) {
          list ($all, $space, $indentSpace, $fullName, $class, $method, $args, $content, $close) = $match;
          if ($close == '@')
            throw new \RuntimeException ("Ill-formed close tag for macro @@$fullName($args)");
          if ($class == '')
            $class = Macro::class;
          if ($args != '')
            $args = ",$args";
          return "$space<?php ob_start() ?>$content<?php echo $class::$method('$indentSpace',ob_get_clean()$args) ?>";
        }, $view);
    });


  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register ()
  {
    // Not used
  }
}
