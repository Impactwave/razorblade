<?php
namespace Impactwave\Razorblade;

use Input;
use Lang;
use Redirect;
use Session;
use Validator;

/**
 * Razorblade's form-related utility functions.
 */
class Form
{
  const ALERT_ERROR   = 'error';
  const ALERT_INFO    = 'info';
  const ALERT_SUCCESS = 'success';
  const ALERT_WARNING = 'warning';

  /**
   * Checks if the field's currently submitted value matches the given value.
   *
   * <p>If the field's value is an array (being the field name suffixed by `[]`), this will match the reference value
   * against all array values (for instance, in the case of multiple radio buttons for the same field, or for a
   * multi-select dropdown).
   *
   * @param string $field Field name.
   * @param string $value Field value to match.
   * @return bool
   */
  static function fieldIs ($field, $value)
  {
    $current = Input::get ($field);
    if (is_array ($current)) {
      foreach ($current as $v)
        if ($value == $v)
          return true;
      return false;
    }
    else return $value == $current;
  }

  /**
   * Checks if the field's previously submitted value matches the given value.
   *
   * <p>If the field's value was an array (being the field name suffixed by `[]`), this will match the reference value
   * against all array values (for instance, in the case of multiple radio buttons for the same field, or for a
   * multi-select dropdown).
   *
   * @param string $field Field name.
   * @param string $value Field value to match.
   * @return bool
   */
  static function fieldWas ($field, $value)
  {
    $old = Input::old ($field);
    if (is_array ($old)) {
      foreach ($old as $v)
        if ($value == $v)
          return true;
      return false;
    }
    else return $value == $old;
  }

  /**
   * Allows sending flash messages to be viewed on the next request.
   *
   * <p>It has support for 4 types of messages and allows setting an optional title.
   *
   * > <p>**Hint:** use the `flashMessage` or `toastrMessage` macros for displaying the message.
   *
   * @param string $message The message to be displayed.
   * @param string $title   An optional title for the alert box.
   * @param string $type    The error type: `error|info|success|warning`. Defaults to `info`.
   *
   * @return \Illuminate\Routing\Redirector
   */
  public static function flash ($message, $title = '', $type = self::ALERT_INFO)
  {
    Session::flash ('message', "$type|$message|$title");
  }

  /**
   * Sets the data to be initially displayed on a form, before the form is submitted for the first time.
   *
   * <p>Use this in conjunction with the `field` macros and {@see Form::validate()}.
   *
   * @param array $model The form data, as a map of field names to field values.
   */
  static function setModel (array $model)
  {
    if (is_null (Input::old ('_token')))
      Session::flashInput ($model);
  }

  /**
   * Shortcut method for form data validation.
   *
   * <p>If validates the form input and, if it fails:
   *
   * - flashes an error message;
   * - generates a redirection to the same URL;
   * - saves on the session the validation error messages and the submitted form values, so that they can be
   * redisplayed on the next request.
   *
   * > **Hint:** use this in conjunction with `field` macros to easily create a form with validation.
   *
   * ##### Usage
   *
   * Place the `validate` call on the controller method that handles the POST request.
   * <br><br>
   * ```
   * $err = Util::validate([
   *   'password'  => 'required|min:8',
   *   'password2' => 'required|same:password',
   *   etc...
   * ]);
   * if ($err) return $err;
   * ```
   *
   * @param array $rules            A map of field names to validation rules.
   * @param array $messages         [optional] Custom error messages. See the Laravel documentation for the Validator
   *                                class.
   * @param array $customAttributes [optional] Custom attributes. See the Laravel documentation for the Validator
   *                                class.
   *
   * @return false|\Illuminate\Http\RedirectResponse False if the form validates successfully, otherwise, a redirection
   *                                                 response.
   */
  public static function validate (array $rules, array $messages = [], array $customAttributes = [])
  {
    $validator = Validator::make (Input::all (), $rules, $messages, $customAttributes);
    if ($validator->fails ()) {
      self::flash (Lang::get ('app.formValidationFailed'), '', self::ALERT_ERROR);
      return Redirect::refresh ()
                     ->withErrors ($validator)
                     ->withInput ();
    }
    return false;
  }

}
