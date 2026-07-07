<?php

namespace Tests\Unit;

use App\Http\Controllers\CeritaController;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class CeritaControllerTest extends TestCase
{
    public function test_chapter_content_preserves_word_separator_dash(): void
    {
        $content = $this->callPrivateNormalizeMethod(
            'normalizeChapterContent',
            'Dia berlari - lari menuju rumah.'
        );

        $this->assertSame('Dia berlari - lari menuju rumah.', $content);
    }

    public function test_chapter_title_preserves_dash_and_normalizes_dash_variants(): void
    {
        $title = $this->callPrivateNormalizeMethod(
            'normalizeChapterTitle',
            'Awal – Akhir'
        );

        $this->assertSame('Awal - Akhir', $title);
    }

    private function callPrivateNormalizeMethod(string $method, ?string $value): string
    {
        $reflection = new ReflectionClass(CeritaController::class);
        $normalizeMethod = $reflection->getMethod($method);

        return $normalizeMethod->invoke(new CeritaController(), $value);
    }
}
