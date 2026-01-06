<?php

namespace App\Helpers;

class QuillHelper
{
    /**
     * Convertit le contenu Quill Delta JSON en HTML
     */
    public static function toHtml(?string $deltaJson): string
    {
        if (empty($deltaJson)) {
            return '';
        }

        try {
            $delta = json_decode($deltaJson, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                // Si ce n'est pas du JSON valide, retourner le texte brut
                return '<p>'.htmlspecialchars($deltaJson).'</p>';
            }

            // Vérifier si c'est un objet Delta avec une clé 'ops'
            if (isset($delta['ops']) && is_array($delta['ops'])) {
                $delta = $delta['ops'];
            }

            $html = '';
            $currentLine = '';
            $inList = false;
            $listType = null;
            $lineAttributes = [];

            foreach ($delta as $op) {
                if (! isset($op['insert'])) {
                    continue;
                }

                $text = $op['insert'];
                $attributes = $op['attributes'] ?? [];

                // Capturer les attributs de ligne pour les appliquer plus tard
                if (isset($attributes['header']) || isset($attributes['align']) || isset($attributes['indent'])) {
                    $lineAttributes = $attributes;
                }

                // Appliquer les styles de texte
                $styledText = self::applyTextStyles($text, $attributes);

                // Gérer les listes
                if (isset($attributes['list'])) {
                    if (! $inList || $listType !== $attributes['list']) {
                        if ($inList) {
                            $html .= $listType === 'ordered' ? '</ol>' : '</ul>';
                        }
                        $listType = $attributes['list'];
                        $html .= $listType === 'ordered' ? '<ol>' : '<ul>';
                        $inList = true;
                    }
                    $currentLine .= $styledText;
                } else {
                    if ($inList) {
                        $html .= '</li>';
                        $html .= $listType === 'ordered' ? '</ol>' : '</ul>';
                        $inList = false;
                        $listType = null;
                    }
                    $currentLine .= $styledText;
                }

                // Gérer les sauts de ligne
                if (strpos($text, "\n") !== false) {
                    $parts = explode("\n", $currentLine);
                    foreach ($parts as $index => $part) {
                        if ($index < count($parts) - 1) {
                            if ($inList) {
                                $html .= '<li>'.$part.'</li>';
                            } else {
                                $html .= self::wrapInBlock($part, $lineAttributes);
                                $lineAttributes = [];
                            }
                        } else {
                            $currentLine = $part;
                        }
                    }
                }
            }

            // Fermer les listes ouvertes
            if ($inList) {
                $html .= '</li>';
                $html .= $listType === 'ordered' ? '</ol>' : '</ul>';
            }

            // Ajouter le contenu restant
            if ($currentLine) {
                $html .= self::wrapInBlock($currentLine, $lineAttributes);
            }

            return $html ?: '<p>&nbsp;</p>';
        } catch (\Exception $e) {
            return '<p class="text-red-500">Erreur lors du chargement du contenu</p>';
        }
    }

    /**
     * Enveloppe le contenu dans un bloc HTML approprié
     */
    private static function wrapInBlock(string $content, array $attributes): string
    {
        if (empty($content)) {
            $content = '&nbsp;';
        }

        $styles = [];
        $tag = 'p';

        // Gérer les en-têtes
        if (isset($attributes['header'])) {
            $level = (int) $attributes['header'];
            $tag = 'h'.$level;
        }

        // Gérer l'alignement
        if (isset($attributes['align'])) {
            $styles[] = 'text-align: '.$attributes['align'];
        }

        // Gérer l'indentation
        if (isset($attributes['indent'])) {
            $indent = (int) $attributes['indent'];
            $styles[] = 'margin-left: '.($indent * 30).'px';
        }

        $styleAttr = ! empty($styles) ? ' style="'.implode('; ', $styles).'"' : '';

        return '<'.$tag.$styleAttr.'>'.$content.'</'.$tag.'>';
    }

    /**
     * Applique les styles de texte (gras, italique, etc.)
     */
    private static function applyTextStyles(string $text, array $attributes): string
    {
        $html = htmlspecialchars($text);
        $styles = [];

        // Gérer les couleurs
        if (isset($attributes['color'])) {
            $styles[] = 'color: '.$attributes['color'];
        }

        if (isset($attributes['background'])) {
            $styles[] = 'background-color: '.$attributes['background'];
        }

        // Appliquer les styles si nécessaire
        if (! empty($styles)) {
            $html = '<span style="'.implode('; ', $styles).'">'.$html.'</span>';
        }

        if (isset($attributes['bold']) && $attributes['bold']) {
            $html = '<strong>'.$html.'</strong>';
        }

        if (isset($attributes['italic']) && $attributes['italic']) {
            $html = '<em>'.$html.'</em>';
        }

        if (isset($attributes['underline']) && $attributes['underline']) {
            $html = '<u>'.$html.'</u>';
        }

        if (isset($attributes['strike']) && $attributes['strike']) {
            $html = '<s>'.$html.'</s>';
        }

        if (isset($attributes['link'])) {
            $html = '<a href="'.htmlspecialchars($attributes['link']).'" target="_blank">'.$html.'</a>';
        }

        return $html;
    }
}
