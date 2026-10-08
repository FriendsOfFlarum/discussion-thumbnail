<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail\Tests\integration\api;

use Carbon\Carbon;
use Flarum\Discussion\Discussion;
use Flarum\Post\Post;
use Flarum\Testing\integration\RetrievesAuthorizedUsers;
use Flarum\Testing\integration\TestCase;
use Flarum\User\User;
use PHPUnit\Framework\Attributes\Test;

class DiscussionThumbnailTest extends TestCase
{
    use RetrievesAuthorizedUsers;

    private const DISCUSSIONS = 8;

    protected function setUp(): void
    {
        parent::setUp();

        $this->extension('flarum-bbcode', 'fof-discussion-thumbnail');

        $discussions = $posts = [];

        for ($i = 1; $i <= self::DISCUSSIONS; $i++) {
            $discussions[] = ['id' => $i, 'title' => "Discussion $i", 'created_at' => Carbon::parse('2021-01-0'.$i), 'last_posted_at' => Carbon::parse('2021-01-0'.$i), 'user_id' => 2, 'first_post_id' => $i, 'comment_count' => 1];
            $posts[] = ['id' => $i, 'number' => 1, 'discussion_id' => $i, 'created_at' => Carbon::parse('2021-01-0'.$i), 'user_id' => 2, 'type' => 'comment', 'content' => $this->withImage("https://example.com/$i.png")];
        }

        $this->prepareDatabase([
            User::class       => [$this->normalUser()],
            Discussion::class => $discussions,
            Post::class       => $posts,
        ]);
    }

    private function withImage(string $url): string
    {
        return '<r><p>Look: <IMG src="'.$url.'"><s>[img]</s>'.$url.'<e>[/img]</e></IMG></p></r>';
    }

    /** @return array<string, string|null> discussion id => thumbnail */
    private function thumbnails(?int $actor): array
    {
        $response = $this->send(
            $this->request('GET', '/api/discussions', $actor ? ['authenticatedAs' => $actor] : [])
        );

        $this->assertEquals(200, $response->getStatusCode());

        $body = json_decode($response->getBody()->getContents(), true);

        return array_column(array_map(fn ($d) => [$d['id'], $d['attributes']['customThumbnail'] ?? null], $body['data']), 1, 0);
    }

    #[Test]
    public function the_discussion_list_loads_first_posts_once_for_the_page()
    {
        $this->app();
        $db = $this->database();
        $db->enableQueryLog();
        $db->flushQueryLog();

        $thumbnails = $this->thumbnails(2);

        $sql = array_map(fn ($q) => str_replace(['`', '"'], '', $q), array_column($db->getQueryLog(), 'query'));
        $db->flushQueryLog();

        $this->assertCount(self::DISCUSSIONS, $thumbnails);

        for ($i = 1; $i <= self::DISCUSSIONS; $i++) {
            $this->assertSame("https://example.com/$i.png", $thumbnails[(string) $i], "Discussion $i shows its first post's image");
        }

        $onePost = array_filter($sql, fn ($q) => str_contains($q, 'from posts where posts.id = ?'));
        $this->assertCount(0, $onePost, 'No first post is fetched on its own');
    }
}
