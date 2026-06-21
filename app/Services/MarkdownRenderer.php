<?php

declare(strict_types=1);

namespace App\Services;

final class MarkdownRenderer
{
    public function render(string $markdown): string
    {
        $html = htmlspecialchars($markdown, ENT_QOUTES, 'UTF-8');
        $html = preg_replace('/^### (.*)$/m', '<h3 class="mt-6 text-sm font-semibold text-white">$1</h3>', $html);
        $html = preg_replace('/^## (.*)$/m',  '<h2 class="mt-6 text-base font-semibold text-white">$1</h2>', $html);
        $html = preg_replace('/^# (.*)$/m',   '<h1 class="mt-6 text-lg font-semibold text-white">$1</h1>',  $html);
        $html = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $html);
        $html = preg_replace('/^- (.*)$/m', '<li class="ml-5 list-disc text-slate-300">$1</li>', $html);
        $html = nl2br($html);
        return '<div class=prose prose-invert max-w-none text-sm leading-6">' . $html . '</div>';
    }
}