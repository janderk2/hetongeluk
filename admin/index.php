<?php

require_once '../initialize.php';

global $database;
global $user;
global $VERSION;

if ((! $user->loggedIn) || (! $user->isModerator())) {
  $mainHTML = <<<HTML
<div id="main" class="pageInner">
  <div style="text-align: center;">U bent geen moderator of beheerder. Log eerst in als moderator of beheerder.</div>
  <div id="spinnerLoad"><img alt="Spinner" src="/images/spinner.svg"></div>
</div>
HTML;
}

function htmlNoAdmin(){
  return <<<HTML
<div id="main" class="pageInner">
  <div style="text-align: center;">U bent geen beheerder. Log eerst in als beheerder.</div>
  <div id="spinnerLoad"><img alt="Spinner" src="/images/spinner.svg"></div>
</div>
HTML;
}

$htmlEnd = '';

if (containsText($_SERVER['REQUEST_URI'], '/mensen')) {
  if (! $user->admin) $mainHTML = htmlNoAdmin();
  else {

    $texts = $user->translateArray(['Admin', 'Humans', 'Id', 'Name', 'Last_active', 'Permission']);

    $mainHTML = <<<HTML
<div id="main" class="pageInner">
  <div class="pageSubTitle">{$texts['Admin']} - {$texts['Humans']}</div>
  
  <table id="tableData" class="dataTable">
    <thead>
      <tr>
        <th>{$texts['Id']}</th>
        <th>{$texts['Name']}</th>
        <th>{$texts['Last_active']}</th>
        <th>{$texts['Permission']}</th>
        <th></th>
      </tr>
    </thead>  
    <tbody id="tableBody" onclick="tableDataClick(event);">        
    </tbody>
  </table>    
  
  <div id="spinnerLoad"><img alt="Spinner" src="/images/spinner.svg"></div>
</div>
HTML;
  }

  $htmlEnd = getFormEditUser();
} else if (containsText($_SERVER['REQUEST_URI'], '/admin/options')) {

  $sql = "SELECT value FROM options WHERE name=:name;";
  $message = $database->fetchSingleValue($sql, [':name' => 'globalMessage']);

  $texts = $user->translateArray(['The_crashes']);

  $mainHTML = <<<HTML
<div id="main" class="pageInner">
  <div class="pageSubTitle">Beheer - opties</div>

  <div>
  
    <label for="optionGlobalMessage">Website mededeling (wordt op alle ongeluk pagina's getoond)</label>
    <textarea id="optionGlobalMessage" class="textArea" maxlength="1500">$message</textarea>
    <div class="smallFont">Externe link:  [url=https://www.hetongeluk.nl]{$texts['The_crashes']}[/url]<br>
    Interne link:  [url=/aboutthissite]Over deze site[/url]<br>
    Paragrafen: Voeg lege regels toe
    </div>
    
    <div class="buttonBar">
      <button class="button" style="margin-left: 0;" onclick="saveOptions();">Opslaan</button>
    </div>

  </div>
</div>
HTML;

} else if (containsText($_SERVER['REQUEST_URI'], '/admin/translations')) {


  $languages       = $database->fetchAll("SELECT id, name FROM languages ORDER BY name;");
  $languageOptions = '';
  foreach ($languages as $language) {
    $selected = $language['id'] === $user->languageId? 'selected' : '';
    $languageOptions .= "<option value='{$language['id']}' {$selected}>{$language['name']}</option>";
  }

  $texts  = $user->translateArray(['Translations', 'Translation', 'Admin', 'Id', 'English', 'New', 'Delete', 'Save']);

  $mainHTML = <<<HTML
<div id="main" class="pageInner">
  <div class="pageSubTitle">{$texts['Admin']} - {$texts['Translations']}</div>

  <div>
    <button class="button" style="margin-left: 0;" onclick="saveTranslations();">{$texts['Save']}</button>
    <button class="button buttonGray" onclick="newTranslation();" data-inline-admin>{$texts['New']}</button>
    <button class="button buttonRed" onclick="deleteTranslation();" data-inline-admin>{$texts['Delete']}</button>
    
    <select id="selectLanguage" class="searchInput" oninput="changeUserLanguage();">$languageOptions</select>
  </div>

  <table id="tableData" class="dataTable" style="user-select: text;">
    <thead>
      <tr><th>{$texts['Id']}</th><th>{$texts['English']}</th><th>{$texts['Translation']} <span id="translationLanguage"></span></th></tr>
    </thead>
    <tbody id="tableBody" onclick="tableDataClick(event);">    
   </tbody>
  </table>  

  <div id="spinnerLoad"><img alt="Spinner" src="/images/spinner.svg"></div>

</div>
HTML;

  $htmlEnd = getFormNewTranslation();
}

function getFormNewTranslation() {

  global $user;
  $texts = $user->translateArray(['Save', 'Cancel', 'Id', 'English_text']);
  return <<<HTML
<div id="formNewTranslation" class="popupOuter">
  <form class="formFullPage" onclick="event.stopPropagation();" onsubmit="saveNewTranslation(); return false;">

    <div class="popupHeader">New translation item</div>
    <div class="popupCloseCross" onclick="closePopupForm();"></div>
    
    <div id="spinnerLogin" class="spinner"></div>
    
    <div class="notice" style="margin: 10px 0;">To be used by developers only if translation id's have been added to the code.</div>
    
    <label>{$texts['Id']}
    <input id="newTranslationId" class="inputForm" type="text"></label>
    
    <label>{$texts['English_text']}
    <input id="newTranslationEnglishText" class="inputForm" type="text"></label>
       
    <div id="newTranslationError" class="formError"></div>

    <div class="popupFooter">
      <input type="submit" class="button" style="margin-left: 0;" value="{$texts['Save']}">
      <input type="button" class="button buttonGray" value="{$texts['Cancel']}" onclick="closePopupForm();">
    </div>
    
  </form>
</div>  
HTML;
}

$head = "<script src='/admin/admin.js?v=$VERSION'></script>";
$html =
  getHTMLBeginMain('Beheer', $head, 'initAdmin') .
  $mainHTML .
  getHTMLEnd($htmlEnd);

echo $html;