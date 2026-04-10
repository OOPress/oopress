<?php

declare(strict_types=1);

namespace OOPress\Core\I18n;

class LanguageSelector
{
    private array $languages = [];
    private Translator $translator;
    
    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
        $this->languages = $this->getAvailableLanguages();
    }
    
    private function getAvailableLanguages(): array
    {
        return [
            'en' => ['name' => 'English', 'flag' => '🇺🇸', 'native' => 'English'],
            'es' => ['name' => 'Spanish', 'flag' => '🇪🇸', 'native' => 'Español'],
            'fr' => ['name' => 'French', 'flag' => '🇫🇷', 'native' => 'Français'],
            'de' => ['name' => 'German', 'flag' => '🇩🇪', 'native' => 'Deutsch'],
            'it' => ['name' => 'Italian', 'flag' => '🇮🇹', 'native' => 'Italiano'],
            'pt' => ['name' => 'Portuguese', 'flag' => '🇵🇹', 'native' => 'Português'],
            'ru' => ['name' => 'Russian', 'flag' => '🇷🇺', 'native' => 'Русский'],
            'ja' => ['name' => 'Japanese', 'flag' => '🇯🇵', 'native' => '日本語'],
            'zh' => ['name' => 'Chinese', 'flag' => '🇨🇳', 'native' => '中文'],
            'ar' => ['name' => 'Arabic', 'flag' => '🇸🇦', 'native' => 'العربية'],
        ];
    }
    
    public function renderDropdown(string $id = 'language-selector'): string
    {
        $current = $this->translator->getLocale();
        $html = '<select id="' . htmlspecialchars($id) . '" class="language-selector" onchange="window.location.href=this.value">';
        
        foreach ($this->languages as $code => $info) {
            $selected = $code === $current ? 'selected' : '';
            $html .= sprintf(
                '<option value="?lang=%s" %s>%s %s</option>',
                $code,
                $selected,
                $info['flag'],
                $info['native']
            );
        }
        
        $html .= '</select>';
        return $html;
    }
    
    public function renderFlags(): string
    {
        $current = $this->translator->getLocale();
        $html = '<div class="language-flags">';
        
        foreach ($this->languages as $code => $info) {
            $active = $code === $current ? 'active' : '';
            $html .= sprintf(
                '<a href="?lang=%s" class="language-flag %s" title="%s">%s</a>',
                $code,
                $active,
                $info['name'],
                $info['flag']
            );
        }
        
        $html .= '</div>';
        return $html;
    }
}