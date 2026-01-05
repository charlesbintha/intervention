<?php

use App\Helpers\QuillHelper;

if (! function_exists('quill_to_html')) {
    /**
     * Convertit le contenu Quill Delta JSON en HTML
     */
    function quill_to_html(?string $deltaJson): string
    {
        return QuillHelper::toHtml($deltaJson);
    }
}
