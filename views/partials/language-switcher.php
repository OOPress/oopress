<div class="language-switcher">
    <?php
    global $languageSelector;
    if (!isset($languageSelector)) {
        $languageSelector = new OOPress\Core\I18n\LanguageSelector($translator);
    }
    echo $languageSelector->renderDropdown();
    ?>
</div>