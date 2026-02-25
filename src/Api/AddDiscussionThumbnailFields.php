<?php

/*
 * This file is part of fof/discussion-thumbnail.
 *
 * Copyright (c) FriendsOfFlarum.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

namespace FoF\DiscussionThumbnail\Api;

use Flarum\Api\Schema;
use Flarum\Discussion\Discussion;
use Flarum\Post\CommentPost;
use Illuminate\Contracts\Cache\Repository;

class AddDiscussionThumbnailFields
{
    public function __construct(protected Repository $cache)
    {
    }

    public function __invoke(): array
    {
        return [
            Schema\Str::make('customThumbnail')
                ->nullable()
                ->get(function (Discussion $discussion) {
                    $post = $discussion->firstPost;

                    if (!($post instanceof CommentPost)) {
                        return null;
                    }

                    $key = "fof:discussion-thumbnail:discussion:{$post->id}";
                    $cached = $this->cache->get($key, false);

                    $stale = is_array($cached) && $post->edited_at
                        && ($cached['date'] === null || $post->edited_at->isAfter($cached['date']));

                    if ($cached === false || $stale) {
                        try {
                            $content = $post->formatContent();
                        } catch (\InvalidArgumentException $e) {
                            $this->cache->forever($key, ['url' => null, 'date' => $post->edited_at]);

                            return null;
                        }

                        if (!$content) {
                            $this->cache->forever($key, ['url' => null, 'date' => null]);

                            return null;
                        }

                        preg_match('/<img.+?src=[\"\'](.+?)[\"\'].*?>/i', $content, $match);

                        $url = $match[1] ?? null;

                        $this->cache->forever($key, ['url' => $url, 'date' => $post->edited_at]);

                        return $url;
                    }

                    return $cached['url'] ?? null;
                }),
        ];
    }
}
