<?php

require_once('settings.php');
/* Example settings file:
<?php
    const FILE_SAVE_PATH = "/var/www/opslag/";
?>
 */

$template = file_get_contents('includes/inschrijfdummy.tmpl'); // Generated from md in blog by hugo
list($header, $footer) = explode('<p>PLEKJEVASTHOUDER</p>', $template);
echo $header;

const ARBITRARY_CONSTANT_HIGH_ENOUGH_TO_ENSURE_PROPER_INPUT = 3;

require_once('vendor/autoload.php');
require_once('includes/ExcelWriter.php');
require_once('includes/TextWriter.php');
require_once('includes/InsForm.php');
require_once('includes/Mailer.php');

$form = new InsForm();
$persInf = new InsFieldSet();
$persInf->setTitle("Persoonlijke informatie");

$defaultSize = '30';

$persInf->addField(new InsField('Voornaam', 'fname', true, $defaultSize));
$persInf->addField(new InsField('Achternaam', 'lname', true, $defaultSize));
$persInf->addField(new InsEmailField('Email', 'email', true, $defaultSize));
$form->addFieldset($persInf);

$nogMeer = new InsFieldSet();
$nogMeer->setTitle("Wat we nog meer van je willen weten");
$nogMeer->addField(new InsYesNoField('Wil je graag blijven kamperen?', 'kamperen', true, $defaultSize));
$nogMeer->addField(new InsField('Heb je dieetwensen?', 'dieetwensen', false, $defaultSize));

$nogMeer->addField(new InsField('Wat is je telefoonnummer?', 'telnr', true, $defaultSize));
$nogMeer->addField(new InsField('Welke muziekinstrumenten ben je van plan mee te nemen?', 'muziekinstrumenten', false, $defaultSize));
$nogMeer->addField(new InsField('Zou je zelf een workshop(je) willen geven, en zo ja, waarover?', 'workshopje', false, $defaultSize));
$nogMeer->addField(new InsDateSelectField('Welke dagen kom je naar het KloosterBoerderijFestival?', 'welkedagen', false, '2018-08-11', '2018-08-18'));
$nogMeer->addField(new InsField('Hoe oud ben je?', 'leeftijd', true, $defaultSize));
$nogMeer->addField(new InsField('Wat zoek je of verwacht je op het KloosterBoerderijFestival?', 'verwachtingen', true, $defaultSize));
$nogMeer->addField(new InsField('Waar denk je aan als je aan een boerderij denkt?', 'denkaanboerderij', false, $defaultSize));
$nogMeer->addField(new InsField('Wat is je favoriete eten?', 'favorieteeten', false, $defaultSize));
$nogMeer->addField(new InsField('Waar kom je vandaan?', 'waarvandaan', false, $defaultSize));
$nogMeer->addField(new InsField('Heb je verder nog vragen?', 'verderevragen', false, $defaultSize));
$nogMeer->addField(new InsCaptchaField('Hoeveel is drie maal drie (mag ook als Pippi Langkous)', 'sommetje', false, $defaultSize));

$form->addFieldset($nogMeer);

if (count($_POST) > ARBITRARY_CONSTANT_HIGH_ENOUGH_TO_ENSURE_PROPER_INPUT) {
    $errors = $form->validate();
    if ($errors == "") {
        $name = $_POST['fname'] . ' ' . $_POST['lname'];
        $email = $_POST['email'];

        try {
            $textWriter = new TextWriter(FILE_SAVE_PATH);
            $textWriter->fillRow($form->getAllFields(), $name);

            $excelWriter = new ExcelWriter(FILE_SAVE_PATH . "inschrijvingen.xlsx");
            $excelWriter->fillRow($form->getAllFields());
            $excelWriter->saveSpreadSheet();
        } catch (Exception $e) {
            echo '<span style="color: red; "><b>Er is iets mis gegaan met je inschrijving! Something went wrong!</b></span>';
            throw $e;
        }
        ?>
        <span style="font-weight: bold">Bedankt voor je inschrijving! Hij is binnen. :)</span><br /><br />

        Als je binnen <b>10 minuten</b> nog geen bevestiging hebt, laat het dan even weten via het e-mailadres hieronder: dan hebben wij namelijk waarschijnlijk niet je juiste adres.<br /><br />
        Je inschrijving is definitief als het geld is overgemaakt.<br />
        Ons rekeningnummer is: NL69 TRIO 0390 9403 21, t.n.v. de Kloosterboeren.<br /><br />

        Je hebt ingevuld:<br />
        <?php
        echo $form->getFormattedFieldValues();
        echo "<br />Foutje gemaakt? Vragen? Mail via het hieronder genoemde e-mailadres.";
        $message = $form->getFormattedFieldValues();
        $template = file_get_contents('includes/mail.txt');
        $mailer = new Mailer('Kloosterboerderijfestival', 'info@kloosterboerderijfestival.nl', $email, $name, 'Je inschrijving voor het kloosterboerderijfestival 2018', $template, $message);
        $mailer->send();
    } else {
        echo "<span style=\"color: red; \">Niet alle velden zijn goed ingevuld:<br />\r\n";
        echo $errors;
        echo "</span><br /><br />\r\n";
        $form->display();
//        echo "<pre>";var_dump($_POST);echo "</pre>";
    }
} else {
    $form->display();
}
//echo file_get_contents('phpincludes/footer.inc');
echo $footer;
?>
