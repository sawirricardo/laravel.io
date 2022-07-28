<?php

use App\Console\Commands\UpdateArticleViewCounts;
use App\Models\Article;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

uses(TestCase::class);
uses(DatabaseMigrations::class);

test('article view counts can be updated', function () {
    Http::fake(function () {
        return Http::response([[
            'pageviews' => 1234,
        ]]);
    });

    $article = Article::factory()->create([
        'title' => 'My First Article',
        'slug' => 'my-first-article',
        'submitted_at' => now(),
        'approved_at' => now(),
    ]);

    (new UpdateArticleViewCounts)->handle();

    expect($article->fresh()->view_count)->toBe(1234);
});

test('article view counts are not updated if API call fails', function () {
    Http::fake(function () {
        return Http::response('Uh oh', 500);
    });

    $article = Article::factory()->create([
        'title' => 'My First Article',
        'slug' => 'my-first-article',
        'submitted_at' => now(),
        'approved_at' => now(),
    ]);

    (new UpdateArticleViewCounts)->handle();

    expect($article->fresh()->view_count)->toBeNull();
});

test('view counts are not updated for unpublished articles', function () {
    Http::fake();

    Article::factory()->create([
        'title' => 'My First Article',
        'slug' => 'my-first-article',
        'submitted_at' => now(),
    ]);

    (new UpdateArticleViewCounts)->handle();

    Http::assertNothingSent();
});
