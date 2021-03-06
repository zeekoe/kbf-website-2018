<?php

class InsForm
{
    var $fieldsets = array();

    function addFieldset($fieldset)
    {
        $this->fieldsets[] = $fieldset;
    }

    function display()
    {
        echo '<form method="post">';
        foreach ($this->fieldsets as $fieldset) {
            $fieldset->display();
        }

        echo '<input type="Submit" value="Verzenden"></form>';
    }

    function getAllFields()
    {
        $fields = array();
        foreach ($this->fieldsets as $fieldset) {
            $fields = array_merge($fields, $fieldset->fields);
        }
        return $fields;
    }

    public function getAllFieldValues()
    {
        $fieldValues = array();
        foreach ($this->getAllFields() as $field) {
            $value = $_POST[$field->name];
            if (is_array($value)) $value = implode(',', $value);
            $value = trim($value);
            $fieldValues[$field->title] = $value;
        }
        return $fieldValues;
    }

    public function getFormattedFieldValues()
    {
        $ret = '';
        foreach ($this->getAllFieldValues() as $field => $value) {
            $ret .= "<span style='font-weight: bold;'>" . $field . "</span><br />\r\n" . $value . "<br /><br />\r\n\r\n";
        }
        return $ret;
    }

    public function getPlainFieldValues()
    {
        $ret = '';
        foreach ($this->getAllFieldValues() as $field => $value) {
            $ret .= $field . ": \r\n" . $value . "\r\n\r\n";
        }
        return $ret;
    }


    public function validate()
    {
        $ret = '';
        foreach ($this->fieldsets as $fieldset) {
            $ret .= $fieldset->validate();
        }
        return $ret;
    }
}

class InsFieldSet
{
    var $fields = array();
    var $title;

    function addField($field)
    {
        $this->fields[] = $field;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    function display()
    {

        echo "<fieldset><h4>" . $this->title . "</h4>\r\n";

        foreach ($this->fields as $field) {
            $field->display();
        }

    }

    public function validate()
    {
        $ret = '';
        foreach ($this->fields as $field) {
            $ret .= $field->validate();
        }
        return $ret;
    }
}

class InsField
{
    var $required;
    var $name;
    var $title;
    var $size;

    function __construct($title, $name, $required, $size)
    {
        $this->size = $size;
        $this->title = $title;
        $this->name = $name;
        $this->required = $required;
    }

    function display()
    {
        $in = '';
        if ($this->required) {
            $in = '<em>*</em>';
        }
        echo '<div><label for="' . $this->name . '">' . $this->title . $in . ':</label></div><div> ' . $this->getFormField() . '</div><br />' . "\r\n";
    }

    function getFormField()
    {
        return '<input type="text" id="'.$this->name.'" name="' . $this->name . '" value="' . $_POST[$this->name] . '" size="' . $this->size . '"/>';
    }

    function validate()
    {
        if ($this->required && $_POST[$this->name] == '' or is_null($_POST[$this->name])) {
            return 'Je hebt de vraag "' . $this->title . '" niet ingevuld, maar het veld is wel verplicht.' . "<br />\r\n";
        }
        return "";
    }

}

class InsEmailField extends InsField
{
    function validate()
    {
        if (!filter_var($_POST[$this->name], FILTER_VALIDATE_EMAIL)) {
            return 'Het lijkt erop dat je geen geldig e-mailadres hebt ingevuld (' . $_POST[$this->name] . '). Kun je het nog eens proberen?' . "<br />\r\n";
        }
        return "";
    }
}

class InsAreaField extends InsField
{
    function getFormField()
    {
        return '<textarea id="'.$this->name.'" name="' . $this->name . '" cols="'. $this->size .'" rows="5">' . $_POST[$this->name] . '</textarea>';
    }
}

class InsCaptchaField extends InsField
{
    function validate()
    {
        if (!in_array(strtolower($_POST[$this->name]), array('6', '9', 'zes', 'negen'))) {
            return "Je hebt de supergeheime vraag aan het eind niet goed ingevuld. Probeer het nog eens.<br />\r\n";
        }
        return "";
    }
}

class InsYesNoField extends InsField
{

    function getFormField()
    {
        $yesselected = $_POST[$this->name] == 'Ja' ? 'selected="selected"' : '';
        $noselected = $_POST[$this->name] == 'Nee' ? 'selected="selected"' : '';
        return '<select name="' . $this->name . '"><option value="">Selecteer &eacute;&eacute;n</option>'
            . '<option value="Ja" ' . $yesselected . '> Ja</option>'
            . '<option value="Nee" ' . $noselected . '> Nee</option>'
            . '</select>';

    }
}

class InsDateSelectField extends InsField
{
    var $startDate;
    var $endDate;
    var $dates = array();

    const SHORTWEEKDAYS = array('zo', 'ma', 'di', 'wo', 'do', 'vr', 'za');

    function __construct($title, $name, $required, $startDate, $endDate)
    {
        parent::__construct($title, $name, $required, '100');
        $this->getDateArray($startDate, $endDate);
    }

    function getDateArray($startDate, $endDate)
    {
        $this->dates[] = strtotime($startDate);
        $i = 1;
        do {
            $nextDate = strtotime($startDate . '+' . $i . ' days');
            $this->dates[] = $nextDate;
            $i++;
        } while ($nextDate < strtotime($endDate));
    }

    function getFormField()
    {
        $ret = '';
        $ret .= '<ul>';
        $ret .= $this->getCheckItem('De volledige week', 'De volledige week');
        foreach ($this->dates as $date) {
            $ret .= $this->getCheckItem(self::SHORTWEEKDAYS[intval(strftime('%w', $date))] . trim(strftime('%e', $date)), self::SHORTWEEKDAYS[intval(strftime('%w', $date))] . strftime(' %e augustus', $date));
        }
        $ret .= '</ul>';
        return $ret;
    }

    private function getCheckItem($short, $long)
    {
        $checked = '';
        if (in_array($short, $_POST['welkedagen'])) {
            $checked = ' checked';
        }
        $ret2 = '';
        $ret2 .= '<li>';
        $ret2 .= '<input name="' . $this->name . '[]" type="checkbox" value="' . $short . '" id="'.$short.'"" ' . $checked . ' />';
        $ret2 .= '<label for="'.$short.'">' . $long . '</label>';
        $ret2 .= '</li>' . "\r\n";
        return $ret2;
    }
}


