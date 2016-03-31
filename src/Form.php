<?php
namespace Impactwave\Razorblade;

use Input;
use Lang;
use Redirect;
use Session;
use Validator;

class Form
{
  const ALERT_ERROR   = 'error';
  const ALERT_INFO    = 'info';
  const ALERT_SUCCESS = 'success';
  const ALERT_WARNING = 'warning';

  /**
   * Allows sending flash messages to be viewed on the next request.
   * Has support for 4 types of message and allows setting a title.
   *
   * @param string $message
   * @param string $title
   * @param string $type
   *
   * @return \Illuminate\Routing\Redirector
   */
  public static function flash ($message, $title = '', $type = self::ALERT_WARNING)
  {
    Session::flash ('message', "$type|$message|$title");
  }

  /**
   * @param string $field
   * @param string $value
   * @return bool|string
   */
  static function fieldIs ($field, $value)
  {
    $current = Input::get ($field);
    if (is_array ($current)) {
      foreach ($current as $v)
        if ($value == $v)
          return true;
      return '';
    }
    else return $value == $current;
  }

  /**
   * @param string $field
   * @param string $value
   * @return bool|string
   */
  static function fieldWas ($field, $value)
  {
    $old = Input::old ($field);
    if (is_array ($old)) {
      foreach ($old as $v)
        if ($value == $v)
          return true;
      return '';
    }
    else return $value == $old;
  }

  /**
   * Shortcut for request data validation.
   *
   * Ex:
   * <code>
   *  $err = Util::validate(...);
   *  if ($err) return $err;
   * </code>
   *
   * @param array $rules
   * @param array $messages
   * @param array $customAttributes
   *
   * @return bool|\Illuminate\Http\RedirectResponse
   */
  public static function validate (array $rules, array $messages = [], array $customAttributes = [])
  {
    $validator = Validator::make (Input::all (), $rules, $messages, $customAttributes);
    if ($validator->fails ()) {
      self::flash (Lang::get ('app.form_validation_failed'));
      return Redirect::refresh ()
                     ->withErrors ($validator)
                     ->withInput ();
    }
    return false;
  }

}
