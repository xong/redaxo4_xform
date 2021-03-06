<?php

/**
 * XForm
 * @author jan.kristinus[at]redaxo[dot]org Jan Kristinus
 * @author <a href="http://www.yakamara.de">www.yakamara.de</a>
 */

class rex_xform
{

  var $ValueObjectsparams;

  function rex_xform()
  {
    global $REX;

    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/' . 'class.xform.value.abstract.inc.php';
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/' . 'class.xform.action.abstract.inc.php';
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/' . 'class.xform.validate.abstract.inc.php';

    $this->objparams = array();

    // --------------------------- editable via objparams|key|newvalue

    $this->objparams['answertext'] = '';
    $this->objparams['submit_btn_label'] = 'Abschicken';
    $this->objparams['submit_btn_show'] = true;

    $this->objparams['values'] = array();
    $this->objparams['validates'] = array();
    $this->objparams['actions'] = array();

    $this->objparams['error_class'] = 'form_warning';
    $this->objparams['unique_error'] = '';
    $this->objparams['unique_field_warning'] = 'not unique';

    $this->objparams['article_id'] = 0;
    $this->objparams['clang'] = 0;

    $this->objparams['real_field_names'] = false;

    $this->objparams['form_method'] = 'post';
    $this->objparams['form_action'] = 'index.php';
    $this->objparams['form_anchor'] = '';
    $this->objparams['form_showformafterupdate'] = 0;
    $this->objparams['form_show'] = true;
    $this->objparams['form_name'] = 'formular';
    $this->objparams['form_id'] = 'form_formular';
    $this->objparams['form_class'] = 'rex-xform';
    $this->objparams['form_wrap'] = array('<div id="rex-xform" class="xform">', '</div>'); // or: <div id="rex-xform" class="xform">#</div>

    $this->objparams['form_label_type'] = 'html'; // plain

    $this->objparams['actions_executed'] = false;
    $this->objparams['postactions_executed'] = false;

    $this->objparams['Error-occured'] = '';
    $this->objparams['Error-Code-EntryNotFound'] = 'ErrorCode - EntryNotFound';
    $this->objparams['Error-Code-InsertQueryError'] = 'ErrorCode - InsertQueryError';

    $this->objparams['getdata'] = false;


    // --------------------------- do not edit

    $this->objparams['object_path'] = $REX['INCLUDE_PATH'] . '/addons/xform/classes/';
    $this->objparams['debug'] = false;

    $this->objparams['form_data'] = '';
    $this->objparams['output'] = '';

    $this->objparams['main_where'] = ''; // z.B. id=12
    $this->objparams['main_id'] = -1; // unique ID
    $this->objparams['main_table'] = ''; // for db and unique

    $this->objparams['form_hiddenfields'] = array();

    $this->objparams['warning'] = array();
    $this->objparams['warning_messages'] = array();

    $this->objparams['fieldsets_opened'] = 0; //

    $this->objparams['form_elements'] = array();
    $this->objparams['form_output'] = array();

    $this->objparams['value_pool'] = array();
    $this->objparams['value_pool']['email'] = array();
    $this->objparams['value_pool']['sql'] = array();

    $this->objparams['value'] = array(); // reserver for classes - $this->objparams["value"]["text"] ...
    $this->objparams['validate'] = array(); // reserver for classes
    $this->objparams['action'] = array(); // reserver for classes

    $this->objparams['this'] = $this;

  }

  function setDebug($s = true)
  {
    $this->objparams['debug'] = $s;
  }

  function setFormData($form_definitions, $refresh = true)
  {
    $this->setObjectparams('form_data', $form_definitions, $refresh);

    $this->objparams['form_data'] = str_replace("\n\r", "\n" , $this->objparams['form_data']); // Die Definitionen
    $this->objparams['form_data'] = str_replace("\r", "\n" , $this->objparams['form_data']); // Die Definitionen

    if (!is_array($this->objparams['form_elements'])) {
      $this->objparams['form_elements'] = array();
    }

    $form_elements_tmp = array();
    $form_elements_tmp = explode("\n", $this->objparams['form_data']);

    // CLEAR EMPTY AND COMMENT LINES
    foreach ($form_elements_tmp as $form_element) {
      $form_element = trim($form_element);
      if ($form_element != '' && $form_element[0] != '#' && $form_element[0] != '/') {
        $this->objparams['form_elements'][] = explode('|', trim($form_element));
      }
    }
  }

  function setValueField($type = '', $values = array())
  {
    $values = array_merge(array($type), $values);
    $this->objparams['form_elements'][] = $values;
  }

  function setValidateField($type = '', $values = array())
  {
    $values = array_merge(array('validate', $type), $values);
    $this->objparams['form_elements'][] = $values;
  }

  function setActionField($type = '', $values = array())
  {
    $values = array_merge(array('action', $type), $values);
    $this->objparams['form_elements'][] = $values;
  }

  function setRedaxoVars($aid = '', $clang = '', $params = array())
  {
    global $REX;

    if ($clang == '') {
      $clang = $REX['CUR_CLANG'];
    }
    if ($aid == '') {
      $aid = $REX['ARTICLE_ID'];
    }

    $this->setHiddenField('article_id', $aid);
    $this->setHiddenField('clang', $clang);

    $this->setObjectparams('form_action', rex_getUrl($aid, $clang, $params));
  }

  function setHiddenField($k, $v)
  {
    $this->objparams['form_hiddenfields'][$k] = $v;
  }

  function setObjectparams($k, $v, $refresh = true)
  {
    if (!$refresh && isset($this->objparams[$k])) {
      $this->objparams[$k] .= $v;
    } else {
      $this->objparams[$k] = $v;
    }
    return $this->objparams[$k];
  }

  function getObjectparams($k)
  {
    if (!isset($this->objparams[$k])) {
      return false;
    }
    return $this->objparams[$k];
  }

  function getForm()
  {

    global $REX;

    $preg_user_vorhanden = "~\*|:|\(.*\)~Usim"; // Preg der Bestimmte Zeichen/Zeichenketten aus der Bezeichnung entfernt

    $ValueObjects = array();
    $ValidateObjects = array();
    $ActionObjects = array();

    $this->objparams['values'] = $ValueObjects;
    $this->objparams['validates'] = $ValidateObjects;
    $this->objparams['actions'] = $ActionObjects;

    $this->objparams['send'] = 0;

    // *************************************************** VALUE OBJECT INIT

    $rows = count($this->objparams['form_elements']);

    for ($i = 0; $i < $rows; $i++) {

      $element = $this->objparams['form_elements'][$i];

      if ($element[0] == 'validate') {

        foreach ($REX['ADDON']['xform']['classpaths']['validate'] as $validate_path) {
          $classname = 'rex_xform_validate_' . trim($element[1]);
          if (@include_once ($validate_path . 'class.xform.validate_' . trim($element[1]) . '.inc.php')) {
            $ValidateObject = new $classname;
            $ValidateObject->loadParams($this->objparams, $element);
            $ValidateObjects[$element[1]][] = $ValidateObject;
            break;
          }
        }

      } elseif ($element[0] == 'action') {
        foreach ($REX['ADDON']['xform']['classpaths']['action'] as $action_path) {
          $classname = 'rex_xform_action_' . trim($element[1]);
          if (@include_once ($action_path . 'class.xform.action_' . trim($element[1]) . '.inc.php')) {
            $ActionObjects[$i] = new $classname;
            $ActionObjects[$i]->loadParams($this->objparams, $element);
            break;
          }
        }

      } else {
        foreach ($REX['ADDON']['xform']['classpaths']['value'] as $value_path) {
          $classname = 'rex_xform_' . trim($element[0]);
          if (@include_once ($value_path . 'class.xform.' . trim($element[0]) . '.inc.php')) {
            $ValueObjects[$i] = new $classname;
            $ValueObjects[$i]->loadParams($this->objparams, $element);
            $ValueObjects[$i]->setId($i);
            $ValueObjects[$i]->init();
            break;
          }

        }
        $rows = count($this->objparams['form_elements']); // if elements have changed -> new rowcount
      }

      // special case - submit button shows up by default
      if(($rows-1) == $i && $this->objparams['submit_btn_show']) {
        $rows++;
        $this->objparams['form_elements'][] = array('submit', 'rex_xform_submit', $this->objparams['submit_btn_label'], 'no_db');
        $this->objparams['submit_btn_show'] = false;
      }

    }

    foreach($ValueObjects as $ValueObject) {
      $ValueObject->setValue($this->getFieldValue($ValueObject->getId(), '', $ValueObject->getName()));
      $ValueObject->setValueObjects($ValueObjects);
    }

    // *************************************************** OBJECT PARAM "send"
    if ($this->getFieldValue('send', '', 'send') == '1') {
      $this->objparams['send'] = 1;
    }

    // *************************************************** PRE VALUES
    // Felder aus Datenbank auslesen - Sofern Aktualisierung
    if ($this->objparams['getdata']) {
      $this->objparams['sql_object'] = rex_sql::factory();
      $this->objparams['sql_object']->debugsql = $this->objparams['debug'];
      $this->objparams['sql_object']->setQuery('SELECT * from ' . $this->objparams['main_table'] . ' WHERE ' . $this->objparams['main_where']);
      if ($this->objparams['sql_object']->getRows() > 1 || $this->objparams['sql_object']->getRows() == 0) {
        $this->objparams['warning'][] = $this->objparams['Error-Code-EntryNotFound'];
        $this->objparams['warning_messages'][] = $this->objparams['Error-Code-EntryNotFound'];
        $this->objparams['form_show'] = true;
        unset($this->objparams['sql_object']);
      }
    }


    // ----- Felder mit Werten fuellen, fuer wiederanzeige
    // Die Value Objekte werden mit den Werten befuellt die
    // aus dem Formular nach dem Abschicken kommen
    if (!($this->objparams['send'] == 1) && $this->objparams['main_where'] != '') {
      //  && $this->objparams['form_type'] != "3"
      for ($i = 0; $i < count($this->objparams['form_elements']); $i++) {
        $element = $this->objparams['form_elements'][$i];
        if (($element[0] != 'validate' && $element[0] != 'action') and $element[1] != '') {
          if (isset($this->objparams['sql_object'])) {
            $this->setFieldValue($i, @addslashes($this->objparams['sql_object']->getValue($element[1])), '', $element[1]);
          }
        }
        if ($element[0] != 'validate' && $element[0] != 'action') {
          $ValueObjects[$i]->setValue($this->getFieldValue($i, '', $ValueObjects[$i]->getName()));
        }
      }
    }


    // *************************************************** VALIDATE OBJEKTE

    // ***** PreValidateActions
    foreach ($ValueObjects as $ValueObject) {
      $ValueObject->preValidateAction();
    }

    foreach($ValidateObjects as $ValidateType) {
      foreach ($ValidateType as $ValidateObject) {
        $ValidateObject->setObjects($ValueObjects);
      }
    }

    // ***** Validieren
    if ($this->objparams['send'] == 1) {
      foreach($ValidateObjects as $ValidateType) {
        foreach ($ValidateType as $ValidateObject) {
          $ValidateObject->enterObject();
        }
      }
    }

    // ***** PostValidateActions
    foreach ($ValueObjects as $ValueObject) {
      $ValueObject->postValidateAction();
    }

    // *************************************************** FORMULAR ERSTELLEN

    foreach ($ValueObjects as $ValueObject) {
      $ValueObject->enterObject();
    }

    if ($this->objparams['send'] == 1) {
      foreach ($ValidateObjects as $ValidateType) {
        foreach ($ValidateType as $ValidateObject) {
          $ValidateObject->postValueAction();
        }
      }
    }

    // ***** PostFormActions
    foreach ($ValueObjects as $ValueObject) {
      $ValueObject->postFormAction();
    }


    // *************************************************** ACTION OBJEKTE

    // ID setzen, falls vorhanden
    if ($this->objparams['main_id'] > 0) {
      $this->objparams['value_pool']['email']['ID'] = $this->objparams['main_id'];
    }

    $hasWarnings = count($this->objparams['warning']) != 0;
    $hasWarningMessages = count($this->objparams['warning_messages']) != 0;

    // ----- Actions
    if ($this->objparams['send'] == 1 && !$hasWarnings && !$hasWarningMessages) {

      $this->objparams['form_show'] = false;
      foreach ($ActionObjects as $ActionObject) {
        $ActionObject->setObjects($ValueObjects);
      }

      foreach ($ActionObjects as $ActionObject) {
        $ActionObject->execute();
      }
      $this->objparams['actions_executed'] = true;

      // ----- Value - PostActions
      foreach ($ValueObjects as $ValueObject) {
        $ValueObject->postAction($this->objparams['value_pool']['email'], $this->objparams['value_pool']['sql']);
      }
      $this->objparams['postactions_executed'] = true;

    }

    $hasWarnings = count($this->objparams['warning']) != 0;
    $hasWarningMessages = count($this->objparams['warning_messages']) != 0;

    if ($this->objparams['form_showformafterupdate']) {
      $this->objparams['form_show'] = true;
    }

    if ($this->objparams['form_show']) {

      // -------------------- send definition
      $this->setHiddenField($this->getFieldName('send', '', 'send'), 1);

      // -------------------- form start
      if ($this->objparams['form_anchor'] != '') {
        $this->objparams['form_action'] .= '#' . $this->objparams['form_anchor'];
      }

      // -------------------- warnings output
      $warningOut = '';
      $hasWarningMessages = count($this->objparams['warning_messages']) != 0;
      if ($this->objparams['unique_error'] != '' || $hasWarnings || $hasWarningMessages) {
        $warningListOut = '';
        if ($hasWarningMessages) {
          foreach ($this->objparams['warning_messages'] as $k => $v) {
            $warningListOut .= '<li class="el_'.$k.'">' . rex_translate($v, null, false) . '</li>';
          }
        }
        if ($this->objparams['unique_error'] != '') {
          $warningListOut .= '<li>' . rex_translate( preg_replace($preg_user_vorhanden, '', $this->objparams['unique_error']) ) . '</li>';
        }

        if ($warningListOut != '') {
          if ($this->objparams['Error-occured'] != '') {
            $warningOut .= '<dl class="' . $this->objparams['error_class'] . '">';
            $warningOut .= '<dt>' . $this->objparams['Error-occured'] . '</dt>';
            $warningOut .= '<dd><ul>' . $warningListOut . '</ul></dd>';
            $warningOut .= '</dl>';
          } else {
            $warningOut .= '<ul class="' . $this->objparams['error_class'] . '">' . $warningListOut . '</ul>';
          }
        }
      }

      // -------------------- formFieldsOut output
      $formFieldsOut = '';
      foreach ($this->objparams['form_output'] as $v) {
        $formFieldsOut .= $v;
      }

      // -------------------- hidden fields
      $hiddenOut = '';
      foreach ($this->objparams['form_hiddenfields'] as $k => $v) {
        $hiddenOut .= '<input type="hidden" name="' . $k . '" value="' . htmlspecialchars($v) . '" />';
      }

      // -------------------- formOut
      $formOut = $warningOut;
      $formOut .= '<form action="' . $this->objparams['form_action'] . '" method="' . $this->objparams['form_method'] . '" id="' . $this->objparams['form_id'] . '" class="' . $this->objparams['form_class'] . '" enctype="multipart/form-data">';
      $formOut .= $formFieldsOut;
      $formOut .= $hiddenOut;
      for ($i = 0; $i < $this->objparams['fieldsets_opened']; $i++) {
        $formOut .= '</fieldset>';
      }
      $formOut .= '</form>';

      if (!is_array($this->objparams['form_wrap']))
          $this->objparams['form_wrap'] = explode('#', $this->objparams['form_wrap']);

      $this->objparams['output'] .= $this->objparams['form_wrap'][0] . $formOut . $this->objparams['form_wrap'][1];

    }

    return $this->objparams['output'];

  }

  static function includeClass($type_id, $class)
  {
    global $REX;

    $classname = 'rex_xform_' . $type_id . '_' . $class;
    $filename  = 'class.xform.' . $type_id . '.' . $class . '.inc.php';
    switch ($type_id) {
      case 'value':
        if (!class_exists('rex_xform_abstract')) {
          require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.value.abstract.inc.php';
        }
        $filename  = 'class.xform.' . $class . '.inc.php';
        $classname = 'rex_xform_' . $class;
        break;
      case 'validate':
        if (!class_exists('rex_xform_validate_abstract')) {
          require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.validate.abstract.inc.php';
        }
        break;
      case 'action':
        if (!class_exists('rex_xform_action_abstract')) {
          require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.action.abstract.inc.php';
        }
        break;
      default:
        return false;
    }

    if (class_exists($classname))
    return $classname;

    foreach ($REX['ADDON']['xform']['classpaths'][$type_id] as $path) {
      @include_once $path . $filename;

      if (class_exists($classname)) {
        return $classname;
      }
    }
    return false;

  }

  function getTypes()
  {
    return array('value', 'validate', 'action');
  }

  function getFieldName($id = '', $k = '', $label = '')
  {
    $label = $this->prepareLabel($label);
    $k = $this->prepareLabel($k);
    if ($this->objparams['real_field_names'] && $label != '') {
      if ($k == '') {
        return $label;
      } else {
        return $label . '[' . $k . ']';
      }
    } else {
      if ($k == '') {
        return 'FORM[' . $this->objparams['form_name'] . '][' . $id . ']';
      } else {
        return 'FORM[' . $this->objparams['form_name'] . '][' . $id . '][' . $k . ']';
      }
    }
  }

  function getFieldValue($id = '', $k = '', $label = '')
  {
    $label = $this->prepareLabel($label);
    $k = $this->prepareLabel($k);
    if ($this->objparams['real_field_names'] && $label != '') {
      if ($k == '' && isset($_REQUEST[$label])) {
        return $_REQUEST[$label];
      } elseif (isset($_REQUEST[$label][$k])) {
        return $_REQUEST[$label][$k];
      }
    } else {
      if ($k == '' && isset($_REQUEST['FORM'][$this->objparams['form_name']][$id])) {
        return $_REQUEST['FORM'][$this->objparams['form_name']][$id];
      } elseif (isset($_REQUEST['FORM'][$this->objparams['form_name']][$id][$k])) {
        return $_REQUEST['FORM'][$this->objparams['form_name']][$id][$k];
      }
    }
  return '';
  }

  function setFieldValue($id = '', $value = '', $k = '', $label = '')
  {
    $label = $this->prepareLabel($label);
    $k = $this->prepareLabel($k);
    if ($this->objparams['real_field_names'] && $label != '') {
      if ($k == '') {
        $_REQUEST[$label] = $value;
      } else {
        $_REQUEST[$label][$k] = $value;
      }
      return;
    } else {
      if ($k == '') {
        $_REQUEST['FORM'][$this->objparams['form_name']][$id] = $value;
      } else {
        $_REQUEST['FORM'][$this->objparams['form_name']][$id][$k] = $value;
      }
    }
  }

  function prepareLabel($label)
  {
    return preg_replace('/[^a-zA-Z\-\_0-9]/', '-', $label);;
  }

  // ----- Hilfsfunktionen -----

  static function unhtmlentities($text)
  {
    if (!function_exists('unhtmlentities')) {
      function unhtmlentities($string)
      {
        // Ersetzen numerischer Darstellungen
        $string = preg_replace('~&#x([0-9a-f]+);~ei', 'chr(hexdec("\\1"))', $string);
        $string = preg_replace('~&#([0-9]+);~e', 'chr("\\1")', $string);
        // Ersetzen benannter Zeichen
        $trans_tbl = get_html_translation_table(HTML_ENTITIES);
        $trans_tbl = array_flip($trans_tbl);
        return strtr($string, $trans_tbl);
      }
    }
    return unhtmlentities($text);
  }


  static function showHelp($return = false, $script = false)
  {

    global $REX;

    $html = '
<ul class="xform root">
  <li class="type value"><strong class="toggler">Value</strong>
  <ul class="xform type value">
  ';

    if (!class_exists('rex_xform_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.value.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['value'] as $pos => $value_path) {
      if ($pos == 1) $html .= '<li class="value extras"><strong class="toggler opened">Value Extras</strong><ul class="xform type value extras">';
      if ($Verzeichniszeiger = opendir($value_path)) {
        $list = array();
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform)/', $Datei) && !preg_match('/^(class.xform.validate|class.xform.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $classname = 'rex_xform_' . $classname[0];
              if (file_exists($value_path . $Datei)) {
                include_once $value_path . $Datei;
                $class = new $classname;
                $desc = $class->getDescription();
                if ($desc != '') {
                  $list[$classname] = '<li>' . $desc . '</li>';
                }

              }
            }
          }
        }
        ksort($list);
        foreach ($list as $l) {
          $html .= $l;
        }
        closedir($Verzeichniszeiger);
      }
    }
    if ($pos > 0) $html .= '</ul></li>';
    $html .= '</ul>
  </li>
  <li class="type validate"><strong class="toggler">Validate</strong>
  <ul class="xform type validate">
  ';

    if (!class_exists('rex_xform_validate_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.validate.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['validate'] as $pos => $validate_path) {
      if ($pos == 1) $html .= '<li class="validate extras"><strong class="toggler opened">Validate Extras</strong><ul class="xform type validate extras">';
      if ($Verzeichniszeiger = opendir($validate_path)) {
        $list = array();
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform.validate)/', $Datei) && !preg_match('/^(class.xform.validate.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $classname = 'rex_xform_' . $classname[0];
              if (file_exists($validate_path . $Datei)) {
                include_once $validate_path . $Datei;
                $class = new $classname;
                $desc = $class->getDescription();
                if ($desc != '')
                  $list[$classname] = '<li>' . $desc . '</li>';
              }
            }
          }
        }
        ksort($list);
        foreach ($list as $l) {
          $html .= $l;
        }
        closedir($Verzeichniszeiger);
      }
    }
    if ($pos > 0) $html .= '</ul></li>';

    $html .= '</ul>
  </li>

  <li class="type action"><strong class="toggler">Action</strong>
  <ul class="xform type action">
  ';

    if (!class_exists('rex_xform_action_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.action.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['action'] as $pos => $action_path) {
      if ($pos == 1) $html .= '<li class="action extras"><strong class="toggler opened">Action Extras</strong><ul class="xform type action extras">';
      if ($Verzeichniszeiger = opendir($action_path)) {
        $list = array();
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform.action)/', $Datei) && !preg_match('/^(class.xform.action.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $classname = 'rex_xform_' . $classname[0];
              if (file_exists($action_path . $Datei)) {
                include_once $action_path . $Datei;
                $class = new $classname;
                $desc = $class->getDescription();
                if ($desc != '') {
                 $list[$classname] = '<li>' . $desc . '</li>';
                }
              }
            }
          }
        }
        ksort($list);
        foreach ($list as $l) {
          $html .= $l;
        }
        closedir($Verzeichniszeiger);
      }
    }
    if ($pos > 0) $html .= '</ul></li>';

    $html .= '</ul>
  </li>
</ul>';

    if ($script) {
      $html .= '
<script type="text/javascript">
(function($){

  $("ul.xform strong.toggler").click(function(){
    var me = $(this);
    var target = $(this).next("ul.xform");
    target.toggle(0, function(){
      if(target.css("display") == "block"){
        me.addClass("opened");
      }else{
        me.removeClass("opened");
      }
    });

  });

})(jQuery)
</script>
';
    }

    if ($return) {
      return $html;
    } else {
      echo $html;
    }

  }


  static function getTypeArray()
  {

    global $REX;

    $return = array();

    // Value

    if (!class_exists('rex_xform_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.value.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['value'] as $pos => $value_path) {
      if ($Verzeichniszeiger = @opendir($value_path)) {
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform)/', $Datei) && !preg_match('/^(class.xform.validate|class.xform.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $name = $classname[0];
              $classname = 'rex_xform_' . $name;
              if (file_exists($value_path . $Datei)) {
                include_once $value_path . $Datei;
                $class = new $classname;
                $d = $class->getDefinitions();
                if (count($d) > 0)
                $return['value'][$d['name']] = $d;
              }
            }
          }
        }
        closedir($Verzeichniszeiger);
      }
    }


    // Validate

    if (!class_exists('rex_xform_validate_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.validate.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['validate'] as $pos => $validate_path) {
      if ($Verzeichniszeiger = @opendir($validate_path)) {
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform.validate)/', $Datei) && !preg_match('/^(class.xform.validate.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $name = $classname[0];
              $classname = 'rex_xform_' . $name;
              if (file_exists($validate_path . $Datei)) {
                include_once $validate_path . $Datei;
                $class = new $classname;
                $d = $class->getDefinitions();
                if (count($d) > 0)
                $return['validate'][$d['name']] = $d;
              }
            }
          }
        }
        closedir($Verzeichniszeiger);
      }
    }


    // Action

    if (!class_exists('rex_xform_action_abstract'))
    require_once $REX['INCLUDE_PATH'] . '/addons/xform/classes/basic/class.xform.action.abstract.inc.php';

    foreach ($REX['ADDON']['xform']['classpaths']['action'] as $pos => $action_path) {
      if ($Verzeichniszeiger = @opendir($action_path)) {
        while ($Datei = readdir($Verzeichniszeiger)) {
          if (preg_match('/^(class.xform.action)/', $Datei) && !preg_match('/^(class.xform.action.abstract)/', $Datei)) {
            if (!is_dir($Datei)) {
              $classname = (explode('.', substr($Datei, 12)));
              $name = $classname[0];
              $classname = 'rex_xform_' . $name;
              if (file_exists($action_path . $Datei)) {
                include_once $action_path . $Datei;
                $class = new $classname;
                $d = $class->getDefinitions();
                if (count($d) > 0)
                $return['action'][$d['name']] = $d;
              }
            }
          }
        }
        closedir($Verzeichniszeiger);
      }
    }

    return $return;

  }



}
